<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementParticipation;
use App\Models\Attendance;
use App\Models\User;
use App\Models\EventRaffleEntry;
use App\Services\Geofence;
use App\Services\PointsCalculator;
use App\Services\RaffleService;
use App\Services\BadgeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /** The scanner screen (camera opens here). */
    public function scanner(Request $request, ?string $token = null, ?Announcement $announcement = null)
    {
        $announcement = $announcement ?: ($request->filled('announcement')
            ? Announcement::findOrFail($request->integer('announcement'))
            : null);

        abort_if($announcement && ! $announcement->is_event, 404);

        /** @var User $viewer */
        $viewer = Auth::user();

        if (! $viewer->isOfficial() && ! $viewer->isGuest() && ! $viewer->is_verified) {
            return $this->fail('Your account must be verified by an official before you can scan attendance.');
        }
        abort_if($announcement && $viewer->isGuest() && ! $announcement->allow_guest_scanning, 403, 'Guest scanning is not enabled for this event.');
        abort_if($announcement?->isParticipationActivity() && ! $viewer->isOfficial(), 403);

        return view('pages.attendance', [
            'prefilledToken' => $token,
            'announcement'   => $announcement,
            'officialMode'   => $viewer->isOfficial(),
            'activityMode'   => $announcement?->isParticipationActivity() ?? false,
            'openEvents'     => Announcement::events()
                ->whereNotNull('event_start_at')
                ->where('event_start_at', '<=', now()->addHours(Announcement::SCAN_WINDOW_HOURS))
                ->where(function ($q) {
                    $q->where('event_end_at', '>=', now())->orWhereNull('event_end_at');
                })
                ->orderBy('event_start_at')
                ->get(),
        ]);
    }

    /**
     * Called by the camera page once a QR is decoded.
     * Expects: token, latitude, longitude, accuracy
     */
    public function check(Request $request): JsonResponse
    {
        /** @var User $viewer */
        $viewer = Auth::user();

        $data = $request->validate([
            'token'     => ['required', 'string', 'max:1024'],
            'announcement_id' => ['nullable', 'integer', 'exists:announcements,id'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy'  => ['nullable', 'numeric'],
        ]);

        // The QR may hold the full URL — pull the token out of it.
        $token = trim(basename(parse_url($data['token'], PHP_URL_PATH) ?: $data['token']));

        $event = Announcement::events()->where('qr_token', $token)->first();

        if ($data['announcement_id'] ?? null) {
            $requestedEvent = Announcement::events()->find($data['announcement_id']);

            if ($event && $event->id !== $requestedEvent?->id) {
                return $this->fail('That QR code does not belong to this announcement.');
            }

            if (! $event && ! $viewer->isOfficial()) {
                return $this->fail('Residents must scan this event\'s QR code.');
            }

            $event = $event ?: $requestedEvent;
        }

        if (! $event) {
            return $this->fail('That QR code does not belong to any TalaFair event.');
        }

        if ($viewer->isOfficial()) {
            $scanKey = "attendance_scans.{$event->id}";
            session([$scanKey => min(3, session($scanKey, 0) + 1)]);
        }

        if ($viewer->isGuest() && ! $event->allow_guest_scanning) {
            return $this->fail('Guest QR scanning is not enabled for this event.');
        }

        if ($event->qrIsExpired()) {
            return $this->fail('This QR code expired on ' . $event->qr_expires_at->format('M j, Y g:i A') . '.');
        }

        if (! $event->scanningIsOpen()) {
            $opens = $event->scanOpensAt()?->format('M j, Y g:i A');

            return $this->fail(now()->lessThan($event->scanOpensAt())
                ? "Scanning opens {$opens} — two hours before the event starts."
                : 'This event has already ended.');
        }

        // Geofence: use the venue pin, falling back to the barangay hall coordinates.
        $lat    = (float) ($event->venue_lat ?? config('talafair.barangay_lat'));
        $lng    = (float) ($event->venue_lng ?? config('talafair.barangay_lng'));
        $radius = (int) ($event->geofence_radius ?: config('talafair.default_radius', 300));

        $distance = Geofence::distance((float) $data['latitude'], (float) $data['longitude'], $lat, $lng);

        if ($distance > $radius) {
            return $this->fail(sprintf(
                'You are about %s m from %s. Move within %d m of the venue and scan again.',
                number_format($distance), $event->venue_name ?: 'the venue', $radius
            ));
        }

        /** @var User $user */
        $user = Auth::user();
        $attendee = $user;

        if ($user->isOfficial()) {
            $attendee = $this->residentFromQr($data['token']);
            if ($attendee instanceof JsonResponse) return $attendee;
        }

        return $this->recordAttendance($event, $attendee, $data, $distance);
    }

    /** Officials can use the resident's printed unique ID through the same attendance flow. */
    public function checkById(Request $request): JsonResponse
    {
        /** @var User $viewer */
        $viewer = Auth::user();
        abort_unless($viewer->isOfficial(), 403);

        $data = $request->validate([
            'announcement_id' => ['required', 'integer', 'exists:announcements,id'],
            'unique_id'       => ['required', 'string', 'max:32'],
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],
        ]);

        $data['unique_id'] = trim($data['unique_id']);
        if ($data['unique_id'] === '') {
            return $this->fail('Unique QR ID not found. Please check the ID and try again.');
        }

        $event = Announcement::events()->findOrFail($data['announcement_id']);
        $attendee = User::where('unique_id', trim($data['unique_id']))
            ->where('role', 'resident')
            ->first();

        if (! $attendee) {
            return $this->fail('Unique QR ID not found. Please check the ID and try again.');
        }

        $lat = (float) ($event->venue_lat ?? config('talafair.barangay_lat'));
        $lng = (float) ($event->venue_lng ?? config('talafair.barangay_lng'));
        $distance = Geofence::distance((float) $data['latitude'], (float) $data['longitude'], $lat, $lng);
        $radius = (int) ($event->geofence_radius ?: config('talafair.default_radius', 300));

        if (! $event->scanningIsOpen() || $event->qrIsExpired() || $distance > $radius) {
            return $this->fail('This event is not currently accepting attendance at this location.');
        }

        return $this->recordAttendance($event, $attendee, $data, $distance);
    }

    public function recordParticipation(Request $request, Announcement $announcement): JsonResponse
    {
        /** @var User $official */
        $official = Auth::user();
        abort_unless($official->isOfficial() && $announcement->isParticipationActivity(), 403);

        $data = $request->validate([
            'token' => ['required', 'string', 'max:1024'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $payload = json_decode($data['token'], true);
        $id = is_array($payload) ? ($payload['id'] ?? null) : null;
        $signature = is_array($payload) ? ($payload['sig'] ?? null) : null;

        if (! $id || ! $signature || ! hash_equals(substr(hash_hmac('sha256', $id, config('app.key')), 0, 16), $signature)) {
            return $this->fail('That resident ID QR code is invalid or has been altered.');
        }

        $resident = User::where('unique_id', $id)->where('role', 'resident')->first();
        if (! $resident) {
            return $this->fail('That resident ID could not be found.');
        }

        $lat = (float) ($announcement->venue_lat ?? config('talafair.barangay_lat'));
        $lng = (float) ($announcement->venue_lng ?? config('talafair.barangay_lng'));
        $distance = Geofence::distance((float) $data['latitude'], (float) $data['longitude'], $lat, $lng);
        $radius = (int) ($announcement->geofence_radius ?: config('talafair.default_radius', 300));

        if (! $announcement->scanningIsOpen() || $announcement->qrIsExpired() || $distance > $radius) {
            return $this->fail('This activity is not currently accepting participation at this location.');
        }

        if ($announcement->participations()->where('user_id', $resident->id)->exists()) {
            return $this->fail('This resident already received participation points for this activity.');
        }

        $points = (int) $announcement->participation_points;
        DB::transaction(function () use ($announcement, $resident, $official, $points) {
            AnnouncementParticipation::create([
                'announcement_id' => $announcement->id,
                'user_id' => $resident->id,
                'scanned_by' => $official->id,
                'points_awarded' => $points,
                'scanned_at' => now(),
            ]);
            $resident->increment('points', $points);
            RaffleService::ensureEntry($resident);
            $resident->refresh();
            BadgeService::awardEligible($resident);
        });

        return response()->json([
            'ok' => true,
            'resident' => $resident->full_name,
            'points' => $points,
            'message' => "Participation recorded. +{$points} points.",
        ]);
    }

    private function recordAttendance(Announcement $event, User $attendee, array $data, float $distance): JsonResponse
    {
        $early = $event->isEarlyScan();
        $preRegistered = $event->rsvps()->where('user_id', $attendee->id)->where('status', 'attending')->exists();
        $breakdown = PointsCalculator::breakdown($event, $preRegistered, $early);
        $attendancePosition = null;

        try {
            DB::transaction(function () use ($event, $attendee, $data, $distance, $early, $preRegistered, $breakdown) {
            $lockedEvent = Announcement::query()->lockForUpdate()->findOrFail($event->id);
            if ($lockedEvent->attendances()->where('user_id', $attendee->id)->exists()) {
                throw new \DomainException('This resident has already been recorded for this event.');
            }

            $attendancePosition = $lockedEvent->attendances()->count() + 1;
            Attendance::create([
                'announcement_id' => $lockedEvent->id,
                'user_id' => $attendee->id,
                'scanned_at' => now(),
                'is_early' => $early,
                'pre_registered' => $preRegistered,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'distance_m' => (int) round($distance),
                'points_awarded' => $breakdown['total'],
            ]);
            $attendee->increment('points', $breakdown['total']);
            RaffleService::ensureEntry($attendee);
            if ($lockedEvent->raffle_enabled) {
                EventRaffleEntry::firstOrCreate(
                    ['announcement_id' => $lockedEvent->id, 'user_id' => $attendee->id],
                    ['is_early' => $early, 'weight' => $early ? 1.10 : 1.00]
                );
            }
            $attendee->refresh();
            BadgeService::awardEligible($attendee, $lockedEvent, $attendancePosition);
            });
        } catch (\DomainException $exception) {
            return $this->fail($exception->getMessage());
        }

        return response()->json([
            'ok' => true,
            'scan_count' => session("attendance_scans.{$event->id}", 0),
            'event' => $event->title,
            'resident' => $attendee->full_name,
            'early' => $early,
            'breakdown' => $breakdown,
            'message' => $early
                ? "Checked in early. +{$breakdown['total']} points, including the 10% early bonus."
                : "Checked in. +{$breakdown['total']} points.",
        ]);
    }

    private function residentFromQr(string $value): User|JsonResponse
    {
        $payload = json_decode($value, true);
        $id = is_array($payload) ? ($payload['id'] ?? null) : null;
        $signature = is_array($payload) ? ($payload['sig'] ?? null) : null;

        if (! is_string($id) || ! is_string($signature) || ! hash_equals(
            substr(hash_hmac('sha256', $id, config('app.key')), 0, 16),
            $signature
        )) {
            return $this->fail('Invalid QR code. Resident not found.');
        }

        $resident = User::where('unique_id', $id)->where('role', 'resident')->first();

        return $resident ?: $this->fail('Invalid QR code. Resident not found.');
    }

    /** A resident's own attendance record. */
    public function history()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('pages.attendance-history', [
            'attendances' => $user->attendances()->with('announcement')->latest('scanned_at')->paginate(20),
        ]);
    }

    public function summaryPdf(Announcement $announcement, bool $download = true)
    {
        abort_unless($announcement->is_event, 404);

        $stats = app(\App\Http\Controllers\AnnouncementController::class)->statistics($announcement);
        $attendees = $announcement->attendances()->with('user')->orderBy('scanned_at')->get();

        return $this->pdfResponse(
            Pdf::loadView('pdf.announcement-summary', compact('announcement', 'stats', 'attendees')),
            'announcement-summary-' . str($announcement->title)->slug() . '.pdf',
            $download
        );
    }

    public function printSummary(Announcement $announcement)
    {
        return $this->summaryPdf($announcement, false);
    }

    public function attendanceSheetPdf(Announcement $announcement, bool $download = true)
    {
        abort_unless($announcement->is_event, 404);

        $attendees = $announcement->attendances()->with('user')->orderBy('scanned_at')->get();

        return $this->pdfResponse(
            Pdf::loadView('pdf.attendance-sheet', compact('announcement', 'attendees')),
            'attendance-sheet-' . str($announcement->title)->slug() . '.pdf',
            $download
        );
    }

    public function printAttendanceSheet(Announcement $announcement)
    {
        return $this->attendanceSheetPdf($announcement, false);
    }

    private function pdfResponse($pdf, string $filename, bool $download)
    {
        $pdf->setPaper('a4', 'portrait');

        if ($download) {
            return $pdf->download($filename);
        }

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function fail(string $message): JsonResponse
    {
        return response()->json(['ok' => false, 'message' => $message], 422);
    }
}
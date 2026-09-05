<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\EventReminder;
use App\Models\UserNotification;
use App\Models\EventRaffleEntry;
use App\Models\EventSubstitution;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AnnouncementController extends Controller
{
    /** Officials only for everything except index/show — see routes/web.php. */

    public function index()
    {
        // The featured announcement fills the banner, so keep it out of the grid below it.
        $featured = Announcement::where('is_featured', true)->latest()->first();

        $announcements = Announcement::latest()
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->getKey()))
            ->paginate(15);

        return view('pages.announcements', compact('announcements', 'featured'));
    }

    public function create()
    {
        // The "New Announcement" form is a Bootstrap modal inside the index page,
        // so there is no separate create screen to render.
        return redirect()->route('announcements.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $announcement = new Announcement($data);

        if ($request->hasFile('banner')) {
            $announcement->banner_path = $request->file('banner')->store('banners', config('filesystems.uploads_disk', 'public'));
        }

        if ($announcement->is_event) {
            $announcement->refreshQrToken();
        }

        $announcement->save();   // Auditable stamps created_by / updated_by + audit log

        if ($announcement->is_event) {
            $this->notifyAudience($announcement, 'New event: ' . $announcement->title,
                'You are invited. Please confirm your attendance before ' .
                $announcement->rsvp_due_at?->format('M j, Y g:i A') . '.');
        }

        return redirect()->route('announcements.show', $announcement)
            ->with('success', 'Announcement published.');
    }

    public function show(Announcement $announcement)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $announcement->load('creator', 'editor');

        $myRsvp = $announcement->rsvps()->where('user_id', $user->id)->first();
        $iAmAudience = $user->belongsToAudience($announcement->audiences ?: ['public']);
        $householdMembers = collect();
        $substitution = null;

        if ($announcement->is_event && $user->is_head_of_family) {
            $householdMembers = User::query()
                ->whereKeyNot($user->id)
                ->where('is_head_of_family', false)
                ->where(function ($query) use ($user) {
                    $query->where('head_of_family_id', $user->id)
                        ->orWhereRaw('LOWER(TRIM(head_of_family_name)) = ?', [strtolower(trim($user->full_name))])
                        ->orWhere(function ($addressQuery) use ($user) {
                            $addressQuery->whereRaw('LOWER(TRIM(house_no)) = ?', [strtolower(trim($user->house_no))])
                                ->whereRaw('LOWER(TRIM(street)) = ?', [strtolower(trim($user->street))])
                                ->when($user->zone !== null, fn ($q) => $q->whereRaw('LOWER(TRIM(zone)) = ?', [strtolower(trim($user->zone))]));
                        });
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            $substitution = EventSubstitution::where('announcement_id', $announcement->id)
                ->where('family_head_id', $user->id)
                ->first();
        }

        return view('pages.announcement-show', [
            'announcement' => $announcement,
            'myRsvp'       => $myRsvp,
            'iAmAudience'  => $iAmAudience,
            'stats'        => $this->statistics($announcement),
            'householdMembers' => $householdMembers,
            'substitution' => $substitution,
        ]);
    }

    public function edit(Announcement $announcement)
    {
        // Editing also happens in a modal on the index page.
        return redirect()->route('announcements.index');
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $this->validated($request, $announcement);

        if ($request->hasFile('banner')) {
            if ($announcement->banner_path) {
                Storage::disk(config('filesystems.uploads_disk', 'public'))->delete($announcement->banner_path);
            }
            $data['banner_path'] = $request->file('banner')->store('banners', config('filesystems.uploads_disk', 'public'));
        }

        $announcement->fill($data);

        if ($announcement->is_event) {
            $announcement->refreshQrToken();   // keeps the token, moves the expiry
        }

        $announcement->save();

        return redirect()->route('announcements.show', $announcement)
            ->with('success', 'Announcement updated. The change is logged under your ID.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()->route('announcements')->with('success', 'Announcement deleted.');
    }

    /* ------------------------------------------------------------------
     | Event QR
     * ------------------------------------------------------------------*/

    public function qr(Announcement $announcement)
    {
        abort_unless($announcement->is_event, 404);

        $svg = (string) QrCode::format('svg')->size(320)->margin(1)
            ->errorCorrection('H')
            ->generate($announcement->qrPayload());

        return view('pages.event-qr', compact('announcement', 'svg'));
    }

    /* ------------------------------------------------------------------
     | Extend the survey window / re-notify the audience
     * ------------------------------------------------------------------*/

    public function extendSurvey(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validate([
            'rsvp_due_at' => ['required', 'date', 'after:now', 'before_or_equal:' . ($announcement->event_start_at ?? now()->addYear())],
        ]);

        $announcement->update($data);

        $this->notifyAudience($announcement, 'Attendance survey extended: ' . $announcement->title,
            'You now have until ' . $announcement->rsvp_due_at->format('M j, Y g:i A') . ' to confirm.');

        return back()->with('success', 'Survey deadline moved to ' . $announcement->rsvp_due_at->format('M j, Y g:i A') . '.');
    }

    public function remind(Request $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validate([
            'kind'    => ['required', Rule::in(['event_reminder', 'survey_closing'])],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        // Guardrail from the spec: reminders are for the day before the event,
        // or the final hours of the survey window.
        if ($data['kind'] === 'event_reminder') {
            abort_unless(
                $announcement->event_start_at && now()->greaterThanOrEqualTo($announcement->event_start_at->copy()->subDay()),
                422,
                'Event reminders can only be sent within 24 hours of the event.'
            );
        } else {
            abort_unless(
                $announcement->rsvp_due_at && now()->greaterThanOrEqualTo($announcement->rsvp_due_at->copy()->subHours(24))
                    && now()->lessThanOrEqualTo($announcement->rsvp_due_at),
                422,
                'Survey reminders can only be sent in the last 24 hours before the survey closes.'
            );
        }

        $title = $data['kind'] === 'event_reminder'
            ? 'Reminder: ' . $announcement->title . ' is tomorrow'
            : 'Last call: confirm your attendance for ' . $announcement->title;

        $count = $this->notifyAudience($announcement, $title, $data['message'] ?? null, onlyPending: $data['kind'] === 'survey_closing');

        EventReminder::create([
            'announcement_id'  => $announcement->id,
            'sent_by'          => Auth::id(),
            'kind'             => $data['kind'],
            'message'          => $data['message'] ?? null,
            'recipients_count' => $count,
        ]);

        return back()->with('success', "Reminder sent to {$count} resident(s).");
    }

    /* ------------------------------------------------------------------
     | Statistics — visible to officials and to the audience
     * ------------------------------------------------------------------*/

    public function statistics(Announcement $announcement): array
    {
        $audience = (clone $announcement->audienceQuery())->count();

        $attending    = $announcement->rsvps()->where('status', 'attending')->count();
        $notAttending = $announcement->rsvps()->where('status', 'not_attending')->count();
        $scanned      = $announcement->attendances()->count();
        $early        = $announcement->attendances()->where('is_early', true)->count();

        return [
            'audience'        => $audience,
            'attending'       => $attending,
            'not_attending'   => $notAttending,
            'no_response'     => max(0, $audience - $attending - $notAttending),
            'scanned'         => $scanned,
            'early'           => $early,
            'on_time'         => $scanned - $early,
            'turnout_rate'    => $attending > 0 ? round($scanned / $attending * 100, 1) : 0.0,
            'reasons'         => $announcement->rsvps()
                                    ->where('status', 'not_attending')
                                    ->whereNotNull('reason')
                                    ->latest('responded_at')
                                    ->limit(50)
                                    ->get(['user_id', 'reason', 'responded_at']),
        ];
    }

    public function statisticsPage(Announcement $announcement)
    {
        $userName = trim(request()->string('user_name')->toString());

        return view('pages.announcement-statistics', [
            'announcement' => $announcement,
            'stats'        => $this->statistics($announcement),
            'attendees'    => $announcement->attendances()
                                ->with('user')
                                ->when($userName !== '', fn ($query) => $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%' . $userName . '%')))
                                ->orderBy('scanned_at')
                                ->get(),
            'userName'     => $userName,
            'raffleEntries' => $announcement->raffleEntries()->with('user')->orderByDesc('weight')->orderBy('created_at')->get(),
        ]);
    }

    public function drawRaffle(Announcement $announcement): RedirectResponse
    {
        abort_unless($announcement->is_event && $announcement->raffle_enabled, 422);

        $entries = $announcement->raffleEntries()->whereNull('selected_at')->get();
        if ($entries->isEmpty()) {
            return back()->with('error', 'There are no eligible raffle entries yet.');
        }

        $total = (int) round($entries->sum('weight') * 100);
        $pick = random_int(1, max(1, $total));
        $running = 0;
        $winner = $entries->last();
        foreach ($entries as $entry) {
            $running += (int) round($entry->weight * 100);
            if ($pick <= $running) {
                $winner = $entry;
                break;
            }
        }
        $winner->update(['selected_at' => now()]);

        return back()->with('success', "Raffle winner: {$winner->user->full_name}.");
    }

    /* ------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------*/

    private function validated(Request $request, ?Announcement $existing = null): array
    {
        $rules = [
            'title'       => ['required', 'string', 'max:255'],
            'category'    => ['required', Rule::in(['events', 'updates', 'rewards', 'maintenance', 'ice_breaker', 'q_and_a', 'game', 'intermission'])],
            'body'        => ['required', 'string'],
            'is_featured' => ['nullable', 'boolean'],
            'is_event'    => ['nullable', 'boolean'],
            'allow_guest_scanning' => ['nullable', 'boolean'],
            'raffle_enabled' => ['nullable', 'boolean'],
            'banner'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];

        if ($request->boolean('is_event')) {
            $rules += [
                'event_start_at'  => ['required', 'date'],
                'event_end_at'    => ['required', 'date', 'after:event_start_at'],
                'rsvp_due_at'     => ['required', 'date', 'before_or_equal:event_start_at'],
                'audiences'       => ['required', 'array', 'min:1'],
                'audiences.*'     => [Rule::in(array_keys(Announcement::AUDIENCES))],
                'base_points'     => [Rule::requiredIf(fn () => ! in_array($request->input('category'), ['ice_breaker', 'q_and_a', 'game', 'intermission'], true)), 'integer', 'min:0', 'max:100000'],
                'weight_points'   => ['nullable', 'numeric', 'min:0', 'max:1000'],
                'participation_points' => [Rule::requiredIf(fn () => in_array($request->input('category'), ['ice_breaker', 'q_and_a', 'game', 'intermission'], true)), 'integer', 'min:0', 'max:100000'],
                'venue_name'      => ['nullable', 'string', 'max:255'],
                'venue_lat'       => ['required', 'numeric', 'between:-90,90'],
                'venue_lng'       => ['required', 'numeric', 'between:-180,180'],
                'geofence_radius' => ['required', 'integer', 'min:20', 'max:5000'],
            ];
        }

        $data = $request->validate($rules);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_event']    = $request->boolean('is_event');
        $data['allow_guest_scanning'] = $data['is_event'] && $request->boolean('allow_guest_scanning');
        $data['raffle_enabled'] = $data['is_event'] && $request->boolean('raffle_enabled');

        $isActivity = in_array($data['category'], ['ice_breaker', 'q_and_a', 'game', 'intermission'], true);
        $data['base_points'] = $isActivity ? 0 : (int) ($data['base_points'] ?? 0);
        $data['weight_points'] = 0;
        $data['participation_points'] = $isActivity ? (int) $data['participation_points'] : 0;

        unset($data['banner']);

        return $data;
    }

    /** Drops a notification into every targeted resident's inbox. Returns the count. */
    private function notifyAudience(Announcement $announcement, string $title, ?string $body, bool $onlyPending = false): int
    {
        $query = $announcement->audienceQuery();

        if ($onlyPending) {
            $query->whereDoesntHave('rsvps', fn ($q) => $q->where('announcement_id', $announcement->id));
        }

        $count = 0;

        $query->select('id')->chunkById(500, function ($users) use ($announcement, $title, $body, &$count) {
            $rows = $users->map(fn ($u) => [
                'user_id'         => $u->id,
                'announcement_id' => $announcement->id,
                'title'           => $title,
                'body'            => $body,
                'created_by'      => Auth::id(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ])->all();

            UserNotification::insert($rows);
            $count += count($rows);
        });

        return $count;
    }
}
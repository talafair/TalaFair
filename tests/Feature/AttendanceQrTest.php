<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\EventRsvp;
use App\Models\EventSubstitution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_unique_id_records_attendance_through_shared_flow(): void
    {
        [$official, $resident, $event] = $this->attendanceSetup();

        $response = $this->actingAs($official)->postJson(route('attendance.check-by-id'), [
            'announcement_id' => $event->id,
            'unique_id' => $resident->unique_id,
            'latitude' => config('talafair.barangay_lat'),
            'longitude' => config('talafair.barangay_lng'),
        ]);

        $response->assertOk()->assertJsonPath('ok', true);
        $this->assertDatabaseHas('attendances', [
            'announcement_id' => $event->id,
            'user_id' => $resident->id,
            'points_awarded' => 100,
        ]);
        $this->assertSame(100, $resident->fresh()->points);
    }

    public function test_signed_resident_qr_uses_the_same_attendance_processing(): void
    {
        [$official, $resident, $event] = $this->attendanceSetup();
        $payload = json_encode([
            'id' => $resident->unique_id,
            'sig' => substr(hash_hmac('sha256', $resident->unique_id, config('app.key')), 0, 16),
        ]);

        $response = $this->actingAs($official)->postJson(route('attendance.check'), [
            'token' => $payload,
            'announcement_id' => $event->id,
            'latitude' => config('talafair.barangay_lat'),
            'longitude' => config('talafair.barangay_lng'),
        ]);

        $response->assertOk()->assertJsonPath('resident', $resident->full_name);
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_wrong_event_qr_is_rejected_with_a_clear_message(): void
    {
        [, $resident, $event] = $this->attendanceSetup();

        $response = $this->actingAs($resident)->postJson(route('attendance.check'), [
            'token' => 'not-the-event-qr',
            'announcement_id' => $event->id,
            'latitude' => config('talafair.barangay_lat'),
            'longitude' => config('talafair.barangay_lng'),
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('ok', false)
            ->assertJsonPath('message', 'Invalid event QR code. Please scan this event\'s QR code.');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_correct_event_qr_outside_the_radius_does_not_record_attendance(): void
    {
        [, $resident, $event] = $this->attendanceSetup();

        $response = $this->actingAs($resident)->postJson(route('attendance.check'), [
            'token' => $event->qr_token,
            'announcement_id' => $event->id,
            'latitude' => (float) config('talafair.barangay_lat') + 1,
            'longitude' => config('talafair.barangay_lng'),
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('ok', false)
            ->assertJsonFragment(['message' => 'Attendance was not recorded. You are about 111,195 m from the venue. Move within 300 m of the venue and scan again.']);
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_correct_event_qr_and_location_record_attendance_successfully(): void
    {
        [, $resident, $event] = $this->attendanceSetup();

        $response = $this->actingAs($resident)->postJson(route('attendance.check'), [
            'token' => $event->qr_token,
            'announcement_id' => $event->id,
            'latitude' => config('talafair.barangay_lat'),
            'longitude' => config('talafair.barangay_lng'),
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('message', 'Attendance recorded successfully. You earned +100 points.');
        $this->assertDatabaseHas('attendances', [
            'announcement_id' => $event->id,
            'user_id' => $resident->id,
        ]);
    }

    public function test_attendance_is_blocked_before_the_two_hour_window(): void
    {
        [, $resident, $event] = $this->attendanceSetup();
        Carbon::setTestNow($event->event_start_at->copy()->subHours(2)->subSecond());

        try {
            $response = $this->actingAs($resident)->postJson(route('attendance.check'), [
                'token' => $event->qr_token,
                'announcement_id' => $event->id,
                'latitude' => config('talafair.barangay_lat'),
                'longitude' => config('talafair.barangay_lng'),
            ]);

            $response->assertUnprocessable()
                ->assertJsonPath('message', 'Attendance scanning is not available yet. Scanning opens 2 hours before the event starts at ' . $event->event_start_at->format('M j, Y g:i A') . '.');
            $this->assertDatabaseCount('attendances', 0);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_attendance_is_allowed_exactly_at_the_two_hour_window(): void
    {
        [, $resident, $event] = $this->attendanceSetup();
        Carbon::setTestNow($event->event_start_at->copy()->subHours(2));

        try {
            $this->actingAs($resident)->postJson(route('attendance.check'), [
                'token' => $event->qr_token,
                'announcement_id' => $event->id,
                'latitude' => config('talafair.barangay_lat'),
                'longitude' => config('talafair.barangay_lng'),
            ])->assertOk()->assertJsonPath('ok', true);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_resident_outside_the_event_audience_is_rejected(): void
    {
        [, $resident, $event] = $this->attendanceSetup();
        $event->update(['audiences' => ['family_heads']]);

        $this->actingAs($resident)->postJson(route('attendance.check'), [
            'token' => $event->qr_token,
            'announcement_id' => $event->id,
            'latitude' => config('talafair.barangay_lat'),
            'longitude' => config('talafair.barangay_lng'),
        ])->assertUnprocessable()->assertJsonPath('message', 'You are not eligible to attend this event.');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_invalid_or_duplicate_manual_id_is_rejected_without_extra_points(): void
    {
        [$official, $resident, $event] = $this->attendanceSetup();
        $payload = [
            'announcement_id' => $event->id,
            'unique_id' => $resident->unique_id,
            'latitude' => config('talafair.barangay_lat'),
            'longitude' => config('talafair.barangay_lng'),
        ];

        $this->actingAs($official)->postJson(route('attendance.check-by-id'), [
            ...$payload,
            'unique_id' => 'not-a-real-id',
        ])->assertUnprocessable()->assertJsonPath('ok', false);

        $this->actingAs($official)->postJson(route('attendance.check-by-id'), $payload)->assertOk();
        $this->actingAs($official)->postJson(route('attendance.check-by-id'), $payload)
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This resident has already been recorded for this event.');

        $this->assertDatabaseCount('attendances', 1);
        $this->assertSame(100, $resident->fresh()->points);
    }

    public function test_only_officials_can_use_manual_unique_id_fallback(): void
    {
        [, $resident, $event] = $this->attendanceSetup();

        $this->actingAs($resident)->postJson(route('attendance.check-by-id'), [
            'announcement_id' => $event->id,
            'unique_id' => $resident->unique_id,
            'latitude' => config('talafair.barangay_lat'),
            'longitude' => config('talafair.barangay_lng'),
        ])->assertForbidden();
    }

    public function test_assigned_substitute_can_answer_the_event_rsvp(): void
    {
        [, $substitute, $event] = $this->substituteSetup();

        $this->actingAs($substitute)
            ->get(route('announcements.show', $event))
            ->assertOk()
            ->assertSee('You were assigned to attend this event as a substitute.');

        $this->actingAs($substitute)->post(route('announcements.rsvp', $event), [
            'status' => 'attending',
        ])->assertRedirect();

        $this->assertDatabaseHas('event_rsvps', [
            'announcement_id' => $event->id,
            'user_id' => $substitute->id,
            'status' => 'attending',
        ]);
    }

    public function test_assigned_substitute_can_change_their_answer_to_no(): void
    {
        [, $substitute, $event] = $this->substituteSetup();

        EventRsvp::create([
            'announcement_id' => $event->id,
            'user_id' => $substitute->id,
            'status' => 'attending',
            'responded_at' => now(),
        ]);

        $this->actingAs($substitute)->post(route('announcements.rsvp', $event), [
            'status' => 'not_attending',
            'reason' => 'I am unavailable.',
        ])->assertRedirect();

        $this->assertDatabaseHas('event_rsvps', [
            'announcement_id' => $event->id,
            'user_id' => $substitute->id,
            'status' => 'not_attending',
            'reason' => 'I am unavailable.',
        ]);
    }

    private function attendanceSetup(): array
    {
        $official = User::factory()->create([
            'role' => 'official',
            'official_group' => 'barangay_council',
            'official_position' => 'barangay_captain',
        ]);
        $resident = User::factory()->create([
            'role' => 'resident',
            'is_verified' => true,
            'unique_id' => 'Z2-26-000000001',
            'points' => 0,
        ]);
        $event = Announcement::create([
            'title' => 'Test event',
            'category' => 'events',
            'body' => 'Attendance test',
            'is_event' => true,
            'event_start_at' => now()->subHour(),
            'event_end_at' => now()->addHour(),
            'qr_token' => 'event-token-' . $resident->id,
            'qr_expires_at' => now()->addHour(),
            'base_points' => 100,
            'audiences' => ['public'],
            'venue_lat' => config('talafair.barangay_lat'),
            'venue_lng' => config('talafair.barangay_lng'),
            'geofence_radius' => 300,
        ]);

        return [$official, $resident, $event];
    }

    private function substituteSetup(): array
    {
        [$official, $substitute, $event] = $this->attendanceSetup();
        $head = User::factory()->create([
            'role' => 'resident',
            'is_head_of_family' => true,
        ]);

        $event->update([
            'audiences' => ['family_heads'],
            'rsvp_due_at' => now()->addHour(),
        ]);
        EventSubstitution::create([
            'announcement_id' => $event->id,
            'family_head_id' => $head->id,
            'substitute_user_id' => $substitute->id,
        ]);

        return [$official, $substitute, $event];
    }
}

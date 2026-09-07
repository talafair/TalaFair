<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\EventFacilitator;
use App\Models\UserNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_announcement_does_not_require_or_store_event_data(): void
    {
        $official = $this->official();

        $this->actingAs($official)
            ->post(route('announcements.store'), [
                'title' => 'Barangay update',
                'category' => 'updates',
                'body' => 'Regular announcement content.',
            ])
            ->assertRedirect();

        $announcement = Announcement::query()->latest('id')->firstOrFail();

        $this->assertFalse($announcement->is_event);
        $this->assertSame(0, $announcement->confirmation_points);
        $this->assertSame(0, $announcement->base_points);
        $this->assertSame(0, $announcement->participation_points);
        $this->assertNull($announcement->qr_token);
        $this->assertNull($announcement->event_start_at);
    }

    public function test_event_creation_stores_confirmation_points_and_generates_qr_token(): void
    {
        $official = $this->official();

        $this->actingAs($official)
            ->post(route('announcements.store'), [
                'title' => 'Community meeting',
                'category' => 'events',
                'body' => 'Event details.',
                'is_event' => '1',
                'event_start_at' => now()->addDay()->format('Y-m-d H:i'),
                'event_end_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i'),
                'rsvp_due_at' => now()->addDay()->subHour()->format('Y-m-d H:i'),
                'audiences' => ['public'],
                'base_points' => 50,
                'confirmation_points' => 15,
                'venue_lat' => 14.1,
                'venue_lng' => 122.9,
                'geofence_radius' => 300,
            ])
            ->assertRedirect();

        $announcement = Announcement::query()->latest('id')->firstOrFail();

        $this->assertTrue($announcement->is_event);
        $this->assertSame(15, $announcement->confirmation_points);
        $this->assertNotNull($announcement->qr_token);
    }

    public function test_event_can_be_published_without_facilitators(): void
    {
        $official = $this->official();

        $this->actingAs($official)->post(route('announcements.store'), $this->eventPayload())->assertRedirect();

        $event = Announcement::query()->latest('id')->firstOrFail();
        $this->assertCount(0, $event->facilitators);
    }

    public function test_event_facilitators_can_be_added_without_duplicates(): void
    {
        $official = $this->official();
        $facilitator = $this->official('event.personnel');

        $this->actingAs($official)->post(route('announcements.store'), [
            ...$this->eventPayload(),
            'facilitator_ids' => [$facilitator->id, $facilitator->id],
        ])->assertRedirect();

        $event = Announcement::query()->latest('id')->firstOrFail();
        $this->assertDatabaseHas('event_facilitators', [
            'announcement_id' => $event->id,
            'user_id' => $facilitator->id,
            'assigned_by' => $official->id,
        ]);
        $this->assertSame(1, EventFacilitator::where('announcement_id', $event->id)->count());
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $facilitator->id,
            'announcement_id' => $event->id,
            'title' => 'Event facilitator assignment',
        ]);
    }

    public function test_event_facilitators_can_be_updated_and_removed(): void
    {
        $official = $this->official();
        $first = $this->official('event.first');
        $second = $this->official('event.second');

        $this->actingAs($official)->post(route('announcements.store'), [
            ...$this->eventPayload(),
            'facilitator_ids' => [$first->id],
        ])->assertRedirect();
        $event = Announcement::query()->latest('id')->firstOrFail();

        $this->actingAs($official)->put(route('announcements.update', $event), [
            ...$this->eventPayload(),
            'facilitator_ids' => [$second->id],
        ])->assertRedirect();

        $this->assertDatabaseMissing('event_facilitators', ['announcement_id' => $event->id, 'user_id' => $first->id]);
        $this->assertDatabaseHas('event_facilitators', ['announcement_id' => $event->id, 'user_id' => $second->id]);
        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $second->id,
            'announcement_id' => $event->id,
            'title' => 'Event facilitator assignment',
        ]);
        $this->assertSame(1, UserNotification::where('user_id', $first->id)
            ->where('announcement_id', $event->id)
            ->where('title', 'Event facilitator assignment')
            ->count());

        $this->actingAs($official)->put(route('announcements.update', $event), $this->eventPayload())->assertRedirect();
        $this->assertDatabaseCount('event_facilitators', 0);
    }

    private function eventPayload(): array
    {
        return [
            'title' => 'Community meeting',
            'category' => 'events',
            'body' => 'Event details.',
            'is_event' => '1',
            'event_start_at' => now()->addDay()->format('Y-m-d H:i'),
            'event_end_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i'),
            'rsvp_due_at' => now()->addDay()->subHour()->format('Y-m-d H:i'),
            'audiences' => ['public'],
            'base_points' => 50,
            'venue_lat' => 14.1,
            'venue_lng' => 122.9,
            'geofence_radius' => 300,
        ];
    }

    private function official(string $username = 'official'): User
    {
        return User::factory()->create([
            'username' => $username,
            'role' => 'official',
            'official_group' => 'barangay_council',
            'official_position' => 'kagawad',
            'is_verified' => true,
        ]);
    }
}

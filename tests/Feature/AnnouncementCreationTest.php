<?php

namespace Tests\Feature;

use App\Models\Announcement;
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

    private function official(): User
    {
        return User::factory()->create([
            'role' => 'official',
            'official_group' => 'barangay_council',
            'official_position' => 'kagawad',
        ]);
    }
}

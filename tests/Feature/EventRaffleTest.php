<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\EventRafflePrize;
use App\Models\EventRaffleWinner;
use App\Models\User;
use App\Services\RaffleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRaffleTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_point_attendee_is_eligible_and_snapshot_is_used(): void
    {
        [$event, $zeroPointResident] = $this->eventWithAttendance(points: 0);
        $prize = EventRafflePrize::create([
            'announcement_id' => $event->id,
            'name' => 'Grocery package',
            'quantity' => 1,
        ]);

        $winner = RaffleService::draw($event, $prize);

        $this->assertSame($zeroPointResident->id, $winner->user_id);
        $this->assertSame(0, $event->raffleEntries()->first()->points_snapshot);
        $this->assertSame(0.0, $event->raffleEntries()->first()->weight);
    }

    public function test_a_resident_can_only_win_once_per_event(): void
    {
        [$event, $resident] = $this->eventWithAttendance(points: 100);
        $firstPrize = EventRafflePrize::create(['announcement_id' => $event->id, 'name' => 'Rice', 'quantity' => 1]);
        $secondPrize = EventRafflePrize::create(['announcement_id' => $event->id, 'name' => 'Voucher', 'quantity' => 1]);

        RaffleService::draw($event, $firstPrize);

        $this->assertSame($resident->id, EventRaffleWinner::first()->user_id);
        $this->expectExceptionMessage('There are no eligible attendees remaining for this raffle.');
        RaffleService::draw($event, $secondPrize);
    }

    public function test_early_check_in_receives_the_additional_weight_multiplier(): void
    {
        [$event, $resident] = $this->eventWithAttendance(points: 1, basePoints: 100, isEarly: true);
        $prize = EventRafflePrize::create(['announcement_id' => $event->id, 'name' => 'Early prize', 'quantity' => 1]);

        RaffleService::draw($event, $prize);

        $this->assertEqualsWithDelta(110.0, $event->raffleEntries()->where('user_id', $resident->id)->value('weight'), 0.0001);
    }

    public function test_sample_raffle_has_multiple_attendees_and_typed_prizes(): void
    {
        [$event, $attendees] = $this->eventWithSampleAttendees();
        $prize = EventRafflePrize::create([
            'announcement_id' => $event->id,
            'name' => 'Community grocery package',
            'type' => 'Grocery package',
            'quantity' => 2,
        ]);
        $secondPrize = EventRafflePrize::create([
            'announcement_id' => $event->id,
            'name' => 'School supply kit',
            'type' => 'School supplies',
            'quantity' => 1,
        ]);

        RaffleService::snapshotEntries($event);

        $basePoints = (int) $event->base_points;
        $this->assertSame(4, $event->raffleEntries()->count());
        $this->assertSame(2, $event->raffleEntries()->where('is_early', true)->count());
        $this->assertSame(2, $event->raffleEntries()->where('weight', $basePoints)->count());
        $this->assertSame(2, $event->raffleEntries()->where('weight', $basePoints * 1.10)->count());
        $event->raffleEntries()->where('is_early', false)->get()->each(
            fn ($entry) => $this->assertEqualsWithDelta($basePoints, $entry->weight, 0.0001)
        );
        $event->raffleEntries()->where('is_early', true)->get()->each(
            fn ($entry) => $this->assertEqualsWithDelta($basePoints * 1.10, $entry->weight, 0.0001)
        );
        $this->assertSame('Grocery package', $prize->type);
        $this->assertSame('School supplies', $secondPrize->type);

        $firstWinner = RaffleService::draw($event, $prize);
        $secondWinner = RaffleService::draw($event, $prize);

        $this->assertNotSame($firstWinner->user_id, $secondWinner->user_id);
        $this->assertCount(2, $prize->winners);
        $this->assertCount(2, $event->raffleWinners()->get());
        $this->assertContains($firstWinner->user_id, $attendees->pluck('id')->all());
        $this->assertContains($secondWinner->user_id, $attendees->pluck('id')->all());
    }

    public function test_filled_prize_is_rejected_without_creating_another_winner(): void
    {
        [$event, $resident] = $this->eventWithAttendance(points: 1, basePoints: 100);
        $prize = EventRafflePrize::create(['announcement_id' => $event->id, 'name' => 'Filled prize', 'quantity' => 1]);
        EventRaffleWinner::create([
            'announcement_id' => $event->id,
            'event_raffle_prize_id' => $prize->id,
            'user_id' => $resident->id,
            'winner_name_snapshot' => $resident->full_name,
            'draw_sequence' => 1,
            'drawn_at' => now(),
        ]);

        $this->expectExceptionMessage('All winner slots for this prize have already been filled.');
        RaffleService::draw($event, $prize);

        $this->assertSame(1, $prize->winners()->count());
    }

    public function test_closed_raffle_is_rejected(): void
    {
        [$event, $resident] = $this->eventWithAttendance(points: 1, basePoints: 100);
        $event->update(['raffle_enabled' => false]);
        $prize = EventRafflePrize::create(['announcement_id' => $event->id, 'name' => 'Closed prize', 'quantity' => 1]);

        $this->expectExceptionMessage('This raffle is not active.');
        RaffleService::draw($event, $prize);
    }

    public function test_prize_from_another_event_is_rejected(): void
    {
        [$event] = $this->eventWithAttendance(points: 1, basePoints: 100);
        [$otherEvent] = $this->eventWithAttendance(points: 1, basePoints: 100);
        $prize = EventRafflePrize::create(['announcement_id' => $otherEvent->id, 'name' => 'Other prize', 'quantity' => 1]);

        $this->expectExceptionMessage('This prize does not belong to this raffle.');
        RaffleService::draw($event, $prize);
    }

    public function test_raffle_with_no_successful_check_ins_is_rejected(): void
    {
        $event = Announcement::create([
            'title' => 'Empty raffle',
            'category' => 'events',
            'body' => 'Raffle test',
            'is_event' => true,
            'raffle_enabled' => true,
            'base_points' => 100,
            'audiences' => ['public'],
        ]);
        $prize = EventRafflePrize::create(['announcement_id' => $event->id, 'name' => 'Empty prize', 'quantity' => 1]);

        $this->expectExceptionMessage('There are no eligible attendees remaining for this raffle.');
        RaffleService::draw($event, $prize);
    }

    private function eventWithAttendance(int $points, int $basePoints = 0, bool $isEarly = false): array
    {
        $resident = User::factory()->create(['role' => 'resident', 'points' => $points]);
        $event = Announcement::create([
            'title' => 'Raffle event',
            'category' => 'events',
            'body' => 'Raffle test',
            'is_event' => true,
            'raffle_enabled' => true,
            'base_points' => $basePoints,
            'audiences' => ['public'],
        ]);

        Attendance::create([
            'announcement_id' => $event->id,
            'user_id' => $resident->id,
            'scanned_at' => now(),
            'is_early' => $isEarly,
            'pre_registered' => false,
            'points_awarded' => 0,
        ]);

        return [$event, $resident];
    }

    private function eventWithSampleAttendees(): array
    {
        $event = Announcement::create([
            'title' => 'Community raffle sample',
            'category' => 'events',
            'body' => 'Sample raffle event',
            'is_event' => true,
            'raffle_enabled' => true,
            'base_points' => 100,
            'audiences' => ['public'],
        ]);

        $attendees = collect([
            ['name' => 'Resident A', 'is_early' => false],
            ['name' => 'Resident B', 'is_early' => true],
            ['name' => 'Resident C', 'is_early' => false],
            ['name' => 'Resident D', 'is_early' => true],
        ])->map(function (array $sample) use ($event) {
            $resident = User::factory()->create([
                'name' => $sample['name'],
                'role' => 'resident',
                'points' => 0,
            ]);

            Attendance::create([
                'announcement_id' => $event->id,
                'user_id' => $resident->id,
                'scanned_at' => now(),
                'is_early' => $sample['is_early'],
                'pre_registered' => false,
                'points_awarded' => 100,
            ]);

            return $resident;
        });

        return [$event, $attendees];
    }
}
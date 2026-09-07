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
}
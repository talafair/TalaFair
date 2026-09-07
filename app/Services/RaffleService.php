<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\EventRaffleEntry;
use App\Models\EventRafflePrize;
use App\Models\EventRaffleWinner;
use App\Models\RaffleEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RaffleService
{
    public const WEIGHT_FORMULA = 'base_points x multiplier';

    public static function ensureEntry(User $user): ?RaffleEntry
    {
        if ($user->isOfficial() || ! self::hasPrizeWheel()) {
            return null;
        }

        return RaffleEntry::firstOrCreate(['user_id' => $user->id]);
    }

    public static function hasPrizeWheel(): bool
    {
        return \App\Models\Prize::count() >= 2;
    }

    public static function snapshotEntries(Announcement $event): void
    {
        $now = now();

        $event->attendances()->with('user')->each(function ($attendance) use ($event, $now): void {
            $basePoints = (int) $event->base_points;

            EventRaffleEntry::updateOrCreate(
                ['announcement_id' => $event->id, 'user_id' => $attendance->user_id],
                [
                    'is_early' => $attendance->is_early,
                    'points_snapshot' => $basePoints,
                    'weight' => self::weight($basePoints, (bool) $attendance->is_early),
                    'snapshot_at' => $now,
                ]
            );
        });
    }

    public static function weight(int $basePoints, bool $isEarly = false): float
    {
        return max(0, $basePoints) * ($isEarly ? 1.10 : 1.00);
    }

    public static function draw(Announcement $event, EventRafflePrize $prize): EventRaffleWinner
    {
        return DB::transaction(function () use ($event, $prize): EventRaffleWinner {
            $lockedEvent = Announcement::query()->lockForUpdate()->findOrFail($event->id);
            $lockedPrize = EventRafflePrize::query()->lockForUpdate()->findOrFail($prize->id);

            abort_unless($lockedEvent->is_event && $lockedEvent->raffle_enabled, 422, 'This raffle is not active.');
            abort_unless($lockedPrize->announcement_id === $lockedEvent->id, 422, 'This prize does not belong to this raffle.');
            abort_if($lockedPrize->remainingSlots() < 1, 422, 'All winner slots for this prize have already been filled.');

            if (! $lockedEvent->raffleEntries()->whereNotNull('snapshot_at')->exists()) {
                self::snapshotEntries($lockedEvent);
            }

            $winnerIds = EventRaffleWinner::where('announcement_id', $lockedEvent->id)->pluck('user_id');
            $entries = $lockedEvent->raffleEntries()
                ->whereNotNull('snapshot_at')
                ->whereNull('selected_at')
                ->whereNotIn('user_id', $winnerIds)
                ->get();

            abort_if($entries->isEmpty(), 422, 'There are no eligible attendees remaining for this raffle.');

            $entry = self::weightedPick($entries);
            $drawnAt = now();
            $entry->update(['selected_at' => $drawnAt]);

            return EventRaffleWinner::create([
                'announcement_id' => $lockedEvent->id,
                'event_raffle_prize_id' => $lockedPrize->id,
                'user_id' => $entry->user_id,
                'winner_name_snapshot' => $entry->user->full_name,
                'draw_sequence' => $lockedPrize->winners()->count() + 1,
                'drawn_at' => $drawnAt,
            ]);
        });
    }

    private static function weightedPick(Collection $entries): EventRaffleEntry
    {
        $total = $entries->sum(fn (EventRaffleEntry $entry) => max(0.0001, (float) $entry->weight));
        $pick = random_int(1, max(1, (int) ceil($total * 10000)));
        $running = 0;

        foreach ($entries as $entry) {
            $running += (int) round(max(0.0001, (float) $entry->weight) * 10000);
            if ($pick <= $running) {
                return $entry;
            }
        }

        return $entries->last();
    }
}
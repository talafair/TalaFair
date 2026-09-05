<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Announcement;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\GameRun;

class BadgeService
{
    public static function awardEligible(User $user, ?Announcement $event = null, ?int $attendancePosition = null, bool $includeGameBadges = false): void
    {
        $attendanceCount = $user->attendances()->count();
        $attendanceIds = $user->attendances()->pluck('announcement_id')->flip();
        $streak = 0;
        foreach (Announcement::where('is_event', true)->whereNotNull('event_start_at')->where('event_start_at', '<=', now())->orderByDesc('event_start_at')->pluck('id') as $eventId) {
            if (! $attendanceIds->has($eventId)) break;
            $streak++;
        }
        $badges = Badge::where('award_method', 'automatic')->get();

        foreach ($badges as $badge) {
            if ($badge->category === 'games' && ! $includeGameBadges) continue;
            if ($badge->announcement_id && (! $event || $badge->announcement_id !== $event->id)) {
                continue;
            }

            $eligible = match ($badge->condition_key) {
                'first_event', 'event_attendance' => $attendanceCount >= 1,
                'early_arrival' => $user->attendances()->where('is_early', true)->exists(),
                'early_bird' => $attendancePosition !== null && $attendancePosition <= ($badge->limited_total ?: 20),
                'participation_count' => $user->participations()->count() >= max(1, (int) $badge->points_required),
                'attendance_count' => $attendanceCount >= max(1, (int) $badge->points_required),
                'streak' => $streak >= max(1, (int) $badge->points_required),
                'points' => $user->points >= $badge->points_required,
                'best_player_week', 'best_player_month', 'best_player_all_time' => self::isBestGamePlayer($user, $badge->condition_key),
                default => false,
            };

            if (! $eligible || ($badge->limited_total && $badge->condition_key !== 'early_bird' && $badge->users()->count() >= $badge->limited_total)) {
                continue;
            }

            if ($user->badges()->whereKey($badge->id)->exists()) continue;

            $awardRank = $badge->condition_key === 'early_bird' ? $attendancePosition : null;
            $user->badges()->attach($badge->id, ['awarded_by' => null, 'award_rank' => $awardRank]);
            UserNotification::create([
                'user_id' => $user->id,
                'title' => 'Badge unlocked',
                'body' => $awardRank ? "You earned {$badge->name} #{$awardRank}/" . ($badge->limited_total ?: 20) . '.' : "You earned the {$badge->name} badge.",
                'created_by' => null,
            ]);
        }
    }

    public static function awardGameBadges(User $user): void
    {
        self::awardEligible($user, null, null, true);
    }

    private static function isBestGamePlayer(User $user, string $condition): bool
    {
        $start = match ($condition) {
            'best_player_week' => now()->startOfWeek(),
            'best_player_month' => now()->startOfMonth(),
            default => null,
        };

        $scores = GameRun::query()
            ->when($start, fn ($query) => $query->whereDate('played_on', '>=', $start->toDateString()))
            ->selectRaw('user_id, SUM(total_score) as total_score')
            ->groupBy('user_id')
            ->orderByDesc('total_score')
            ->get();

        return $scores->first()?->user_id === $user->id;
    }
}
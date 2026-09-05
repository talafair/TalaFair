<?php

namespace App\Services;

use App\Models\Announcement;

/**
 * Attendance score for resident i at event e:
 *
 *   S(i,e) = B_e + (0.10 x B_e x E(i,e))
 *
 *   B_e      base_points of the event
 *   E(i,e)   engagement value: 1 when the resident scanned before the event start, otherwise 0
 */
class PointsCalculator
{
    public const EARLY_BONUS_RATE = 0.10;

    public static function score(Announcement $event, bool $preRegistered, bool $early): int
    {
         $base         = (int) $event->base_points;
         $engagement    = $early ? 1 : 0;

        $score = $base
             + (self::EARLY_BONUS_RATE * $base * $engagement);

        return (int) round($score);
    }

    /** Human-readable breakdown, handy for the receipt shown after a scan. */
    public static function breakdown(Announcement $event, bool $preRegistered, bool $early): array
    {
        $base          = (int) $event->base_points;
        $engagement    = $early ? 1 : 0;
        $bonus         = (int) round(self::EARLY_BONUS_RATE * $base * $engagement);

        return [
            'base'           => $base,
            'participation'  => 0,
            'pre_registered' => 0,
            'engagement'     => $engagement,
            'early_bonus'    => $bonus,
            'total'          => self::score($event, $preRegistered, $early),
        ];
    }
}

//new file 08/17/2026
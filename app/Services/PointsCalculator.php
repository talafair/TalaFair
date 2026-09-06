<?php

namespace App\Services;

use App\Models\Announcement;

/**
 * Attendance score for resident i at event e:
 *
 *   S(i,e) = B_e + C_e(i) + (0.10 x B_e x E(i,e))
 *
 *   B_e      base_points of the event
 *   C_e(i)   confirmation_points when the resident answered Yes, otherwise 0
 *   E(i,e)   early value: 1 when the resident scanned before the event start, otherwise 0
 */
class PointsCalculator
{
    public const EARLY_BONUS_RATE = 0.10;

    public static function score(Announcement $event, bool $preRegistered, bool $early): int
    {
           $base         = (int) $event->base_points;
           $confirmation = $preRegistered ? (int) $event->confirmation_points : 0;
           $engagement   = $early ? 1 : 0;

        $score = $base
               + $confirmation
             + (self::EARLY_BONUS_RATE * $base * $engagement);

        return (int) round($score);
    }

    /** Human-readable breakdown, handy for the receipt shown after a scan. */
    public static function breakdown(Announcement $event, bool $preRegistered, bool $early): array
    {
        $base          = (int) $event->base_points;
        $confirmation  = $preRegistered ? (int) $event->confirmation_points : 0;
        $engagement    = $early ? 1 : 0;
        $bonus         = (int) round(self::EARLY_BONUS_RATE * $base * $engagement);

        return [
            'base'           => $base,
            'participation'  => 0,
            'pre_registered' => $confirmation,
            'confirmation'   => $confirmation,
            'engagement'     => $engagement,
            'early_bonus'    => $bonus,
            'total'          => self::score($event, $preRegistered, $early),
        ];
    }
}

//new file 08/17/2026
<?php

namespace App\Services;

use App\Models\Announcement;

class PointsCalculator
{
    public const EARLY_BONUS_RATE = 0.10;

    public static function score(Announcement $event, bool $preRegistered, bool $early): int
    {
        return (int) round(self::raffleWeight($event, $early))
            + ($preRegistered ? (int) $event->confirmation_points : 0);
    }

    public static function raffleWeight(Announcement $event, bool $early): float
    {
        return (int) $event->base_points * ($early ? 1.10 : 1.00);
    }

    public static function breakdown(Announcement $event, bool $preRegistered, bool $early): array
    {
        $base = (int) $event->base_points;
        $bonus = (int) round(self::EARLY_BONUS_RATE * $base * ($early ? 1 : 0));

        return [
            'base'           => $base,
            'participation'  => 0,
            'pre_registered' => $preRegistered ? 1 : 0,
            'confirmation'   => $preRegistered ? (int) $event->confirmation_points : 0,
            'engagement'     => $early ? 1 : 0,
            'early_bonus'    => $bonus,
            'total'          => self::score($event, $preRegistered, $early),
        ];
    }
}

//new file 08/17/2026
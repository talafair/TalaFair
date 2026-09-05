<?php

namespace App\Services;

class Geofence
{
    /** Great-circle distance between two points, in metres. */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371000; // metres

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public static function within(float $lat1, float $lng1, float $lat2, float $lng2, int $radius): bool
    {
        return self::distance($lat1, $lng1, $lat2, $lng2) <= $radius;
    }
}

//new file 08/17/2026
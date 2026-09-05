<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Builds resident IDs in the format  Z{zone}-{yy}{9-digit sequence}
 * Example: zone 2, year 2026, 1st resident  ->  Z2-26000000001
 */
class UniqueIdGenerator
{
    public static function for(User $user): string
    {
        $zone = preg_replace('/[^0-9A-Za-z]/', '', (string) $user->zone) ?: '0';
        $yy   = $user->created_at?->format('y') ?? now()->format('y');

        return DB::transaction(function () use ($zone, $yy) {
            $prefix = "Z{$zone}-{$yy}";

            $last = User::where('unique_id', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('unique_id')
                ->value('unique_id');

            $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

            return $prefix . str_pad((string) $next, 9, '0', STR_PAD_LEFT);
        });
    }
}

//new file 08/17/2026
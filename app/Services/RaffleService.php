<?php

namespace App\Services;

use App\Models\RaffleEntry;
use App\Models\User;

class RaffleService
{
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
}
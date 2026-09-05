<?php

namespace App\Http\Controllers;

use App\Models\Prize;
use App\Models\SpinHistory;
use App\Services\RaffleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpinController extends Controller
{
    public function index()
    {
        $prizes = Prize::orderBy('id')->get();

        $wheelSegments = $prizes->map(fn (Prize $p) => [
            'id' => $p->id,
            'label' => $p->label,
            'color' => $p->color,
            'light' => $p->light_text,
        ])->values();

        $recentWinners = SpinHistory::with('user')
            ->where('prize_type', '!=', 'none')
            ->latest()
            ->take(6)
            ->get();

        $user = request()->user();
        $entry = RaffleService::ensureEntry($user);
        $spunToday = SpinHistory::where('user_id', $user->id)->whereDate('created_at', today())->exists();

        return view('pages.spin', [
            'prizes' => $prizes,
            'wheelSegments' => $wheelSegments,
            'recentWinners' => $recentWinners,
            'spunToday' => $spunToday,
            'extraChances' => $entry?->extra_chances ?? 0,
        ]);
    }

    public function spin(Request $request)
    {
        $prizes = Prize::orderBy('id')->get();

        if ($prizes->count() < 2) {
            return response()->json(['message' => 'The prize wheel needs at least 2 prizes.'], 422);
        }

        $user = $request->user();
        $result = DB::transaction(function () use ($user, $prizes) {
            $entry = RaffleService::ensureEntry($user);
            if (! $entry) {
                abort(422, 'Only resident raffle entries can spin the prize wheel.');
            }

            $entry = $entry->newQuery()->whereKey($entry->id)->lockForUpdate()->first();
            $spunToday = SpinHistory::where('user_id', $user->id)->whereDate('created_at', today())->exists();
            $source = 'daily';

            if ($spunToday) {
                if ($entry->extra_chances < 1) {
                    abort(422, 'You have already used your daily spin. Complete an official-issued task for another chance.');
                }
                $entry->decrement('extra_chances');
                $source = 'task';
            }

            $index = random_int(0, $prizes->count() - 1);
            $prize = $prizes[$index];
            SpinHistory::create([
                'user_id' => $user->id,
                'prize_label' => $prize->label,
                'prize_type' => $prize->prize_type,
                'amount' => $prize->amount,
            ]);

            if ($prize->prize_type === 'points' && $prize->amount > 0) {
                $user->increment('points', $prize->amount);
            }

            return [$index, $prize, $source, $entry->fresh()->extra_chances];
        });

        [$index, $prize, $source, $extraChances] = $result;

        $user->refresh();

        return response()->json([
            'index' => $index,
            'id' => $prize->id,
            'label' => $prize->label,
            'type' => $prize->prize_type,
            'icon' => $prize->icon,
            'points' => $user->points,
            'source' => $source,
            'extraChances' => $extraChances,
        ]);
    }
}

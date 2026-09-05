<?php

namespace App\Http\Controllers;

use App\Models\SpinHistory;
use App\Models\Badge;
use App\Models\User;
use App\Models\Announcement;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home', [
            'topPlayers' => User::orderByDesc('points')->orderBy('created_at')->take(5)->get(),
            'recentWinners' => SpinHistory::with('user')
                ->where('prize_type', '!=', 'none')
                ->latest()
                ->take(5)
                ->get(),
            'newMembers' => User::latest()->take(5)->get(),
            'badges' => Badge::latest()->take(4)->get(),
            'announcements' => Announcement::latest()->take(4)->get(),
            'earnedBadgeIds' => request()->user()->badges()->pluck('badges.id'),
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        Announcement::create([
            'title' => 'Welcome to TalaFair!',
            'category' => 'updates',
            'body' => 'TalaFair is now live! Create your account and climb the leaderboard. Rankings are based purely on the points you earn — everyone starts from zero, so the top spot is up for grabs.',
            'is_featured' => true,
        ]);

        Announcement::create([
            'title' => 'How the Leaderboard Works',
            'category' => 'updates',
            'body' => 'Your rank is determined by your total points (XP). Every point you earn counts toward your rank, and Prize Wheel winners are announced here automatically.',
        ]);

        Announcement::create([
            'title' => 'Prize Wheel Now Open',
            'category' => 'rewards',
            'body' => 'The prize wheel is live! Winning spins are credited instantly and announced here automatically for everyone to see.',
        ]);

        Announcement::create([
            'title' => 'Community Launch Event',
            'category' => 'events',
            'body' => 'Join our launch celebration! Invite your friends, earn points together, and see who can reach the top of the leaderboard first.',
        ]);

        Announcement::create([
            'title' => 'Scheduled Maintenance Notice',
            'category' => 'maintenance',
            'body' => 'We perform routine maintenance to keep TalaFair fast and reliable. Any downtime will be announced here in advance — your points are always safe.',
        ]);
    }
}

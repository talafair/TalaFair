<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['name' => 'First Step', 'description' => 'Attended your first event.', 'category' => 'milestones', 'rarity' => 'common', 'condition_key' => 'first_event', 'points_required' => 0],
            ['name' => 'Event Attendee', 'description' => 'Successfully attended an event.', 'category' => 'attendance', 'rarity' => 'common', 'condition_key' => 'event_attendance', 'points_required' => 0],
            ['name' => 'Early Bird', 'description' => 'One of the first 20 verified attendees of an event.', 'category' => 'attendance', 'rarity' => 'rare', 'condition_key' => 'early_bird', 'limited_total' => 20, 'points_required' => 0],
            ['name' => 'Regular', 'description' => 'Attended 5 events.', 'category' => 'milestones', 'rarity' => 'uncommon', 'condition_key' => 'attendance_count', 'points_required' => 5],
            ['name' => 'Dedicated', 'description' => 'Attended 10 events.', 'category' => 'milestones', 'rarity' => 'rare', 'condition_key' => 'attendance_count', 'points_required' => 10],
            ['name' => 'TalaFair Veteran', 'description' => 'Attended 25 events.', 'category' => 'milestones', 'rarity' => 'epic', 'condition_key' => 'attendance_count', 'points_required' => 25],
            ['name' => 'TalaFair Legend', 'description' => 'Attended 50 events.', 'category' => 'milestones', 'rarity' => 'legendary', 'condition_key' => 'attendance_count', 'points_required' => 50],
            ['name' => 'Getting Started', 'description' => 'Attended 3 consecutive events.', 'category' => 'streaks', 'rarity' => 'uncommon', 'condition_key' => 'streak', 'points_required' => 3],
            ['name' => 'On a Roll', 'description' => 'Attended 5 consecutive events.', 'category' => 'streaks', 'rarity' => 'rare', 'condition_key' => 'streak', 'points_required' => 5],
            ['name' => 'Unstoppable', 'description' => 'Attended 10 consecutive events.', 'category' => 'streaks', 'rarity' => 'epic', 'condition_key' => 'streak', 'points_required' => 10],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['name' => $badge['name']], array_merge($badge, ['award_method' => 'automatic']));
        }
    }
}

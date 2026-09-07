<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\EventRaffleEntry;
use App\Models\EventRafflePrize;
use App\Models\User;
use App\Services\RaffleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EventRaffleDemoSeeder extends Seeder
{
    public function run(): void
    {
        $official = User::firstOrCreate(
            ['username' => 'raffle.demo.official'],
            [
                'name' => 'Raffle Demo Official',
                'first_name' => 'Raffle Demo',
                'last_name' => 'Official',
                'email' => 'raffle.demo.official@example.test',
                'password' => Hash::make('password'),
                'role' => 'official',
                'official_group' => 'barangay_council',
                'official_position' => 'barangay_captain',
                'is_verified' => true,
                'email_verified_at' => now(),
            ]
        );

        $event = Announcement::updateOrCreate(
            ['title' => 'TalaFair Raffle Demo Event'],
            [
                'category' => 'events',
                'body' => 'Sample event for testing the live weighted raffle experience.',
                'is_event' => true,
                'raffle_enabled' => true,
                'is_featured' => false,
                'audiences' => ['public'],
                'event_start_at' => now()->subHour(),
                'event_end_at' => now()->addDay(),
                'rsvp_due_at' => now()->subHours(2),
                'base_points' => 50,
                'confirmation_points' => 10,
                'weight_points' => 1,
                'venue_name' => 'TalaFair Demo Venue',
                'venue_lat' => config('talafair.barangay_lat'),
                'venue_lng' => config('talafair.barangay_lng'),
                'geofence_radius' => 300,
                'created_by' => $official->id,
                'updated_by' => $official->id,
            ]
        );

        $attendees = [
            ['first_name' => 'Ana', 'last_name' => 'Santos', 'points' => 0],
            ['first_name' => 'Ben', 'last_name' => 'Cruz', 'points' => 25],
            ['first_name' => 'Carla', 'last_name' => 'Reyes', 'points' => 100],
            ['first_name' => 'Diego', 'last_name' => 'Garcia', 'points' => 500],
            ['first_name' => 'Elena', 'last_name' => 'Dela Cruz', 'points' => 1000],
        ];

        foreach ($attendees as $index => $data) {
            $username = 'raffle.demo.resident.' . ($index + 1);
            $resident = User::updateOrCreate(
                ['username' => $username],
                [
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $username . '@example.test',
                    'password' => Hash::make('password'),
                    'role' => 'resident',
                    'is_verified' => true,
                    'email_verified_at' => now(),
                    'points' => $data['points'],
                    'unique_id' => 'RAFFLE-DEMO-' . ($index + 1),
                ]
            );

            Attendance::updateOrCreate(
                ['announcement_id' => $event->id, 'user_id' => $resident->id],
                [
                    'scanned_at' => now()->subMinutes(30 - ($index * 3)),
                    'is_early' => $index === 0,
                    'pre_registered' => true,
                    'latitude' => config('talafair.barangay_lat'),
                    'longitude' => config('talafair.barangay_lng'),
                    'distance_m' => 10,
                    'points_awarded' => 50,
                ]
            );

            EventRaffleEntry::updateOrCreate(
                ['announcement_id' => $event->id, 'user_id' => $resident->id],
                [
                    'is_early' => $index === 0,
                    'points_snapshot' => 50,
                    'weight' => RaffleService::weight(50, $index === 0),
                    'snapshot_at' => now(),
                    'selected_at' => null,
                ]
            );
        }

        EventRafflePrize::where('announcement_id', $event->id)->delete();

        foreach ([
            ['name' => 'Grand Prize - Grocery Package', 'type' => 'Grocery package', 'description' => 'A family grocery package.', 'quantity' => 1],
            ['name' => 'Major Prize - Gift Certificate', 'type' => 'Gift certificate', 'description' => 'A TalaFair gift certificate.', 'quantity' => 2],
            ['name' => 'Consolation Prize - School Supplies', 'type' => 'School supplies', 'description' => 'A school supplies bundle.', 'quantity' => 2],
        ] as $sortOrder => $prize) {
            EventRafflePrize::create([
                ...$prize,
                'announcement_id' => $event->id,
                'sort_order' => $sortOrder,
                'created_by' => $official->id,
            ]);
        }

        $this->command?->info("Raffle demo ready: event #{$event->id} (5 attendees, 3 prizes).");
    }
}
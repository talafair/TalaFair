<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('is_event')->default(false)->after('is_featured');

            // Schedule
            $table->dateTime('event_start_at')->nullable()->after('is_event');
            $table->dateTime('event_end_at')->nullable()->after('event_start_at');
            $table->dateTime('rsvp_due_at')->nullable()->after('event_end_at');

            // Targeting: ["youth","senior","public","family_heads"]
            $table->json('audiences')->nullable()->after('rsvp_due_at');

            // Points
            $table->unsignedInteger('base_points')->default(0)->after('audiences');   // B_e
            $table->decimal('weight_points', 8, 2)->default(1)->after('base_points'); // w_p

            // Attendance QR + geofence
            $table->string('qr_token', 64)->nullable()->unique()->after('weight_points');
            $table->dateTime('qr_expires_at')->nullable()->after('qr_token');
            $table->string('venue_name')->nullable()->after('qr_expires_at');
            $table->decimal('venue_lat', 10, 7)->nullable()->after('venue_name');
            $table->decimal('venue_lng', 10, 7)->nullable()->after('venue_lat');
            $table->unsignedInteger('geofence_radius')->default(300)->after('venue_lng'); // metres

            // Media + audit
            $table->string('banner_path')->nullable()->after('geofence_radius');
            $table->foreignId('created_by')->nullable()->after('banner_path')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();

            $table->index(['is_event', 'event_start_at']);
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropIndex(['is_event', 'event_start_at']);
            $table->dropColumn([
                'is_event', 'event_start_at', 'event_end_at', 'rsvp_due_at', 'audiences',
                'base_points', 'weight_points', 'qr_token', 'qr_expires_at',
                'venue_name', 'venue_lat', 'venue_lng', 'geofence_radius',
                'banner_path', 'created_by', 'updated_by',
            ]);
        });
    }
};

//New file 08/17/2026
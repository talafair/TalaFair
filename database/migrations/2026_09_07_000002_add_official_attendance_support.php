<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add support for official attendance tracking.
     *
     * This migration adds:
     * - user_category: Distinguish between 'resident' and 'official' attendees
     * - attendance_method: Track how attendance was recorded (event_qr_scan, official_qr_scan, manual_unique_id)
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Add user category to distinguish resident vs official
            $table->enum('user_category', ['resident', 'official'])->default('resident')->after('user_id');
            
            // Track how attendance was recorded
            $table->enum('attendance_method', ['event_qr_scan', 'official_qr_scan', 'manual_unique_id'])
                ->default('event_qr_scan')->after('user_category');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['user_category', 'attendance_method']);
        });
    }
};

//new file 09/07/2026

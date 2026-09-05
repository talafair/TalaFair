<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('resident', 'guest', 'official') NOT NULL DEFAULT 'resident'");
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('allow_guest_scanning')->default(false)->after('is_event');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('allow_guest_scanning');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('resident', 'official') NOT NULL DEFAULT 'resident'");
        }
    }
};
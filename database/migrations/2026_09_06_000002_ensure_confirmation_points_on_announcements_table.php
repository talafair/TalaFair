<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('announcements', 'confirmation_points')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->unsignedInteger('confirmation_points')->default(0)->after('base_points');
            });
        }
    }

    public function down(): void
    {
        // The original feature migration owns this column and remains reversible.
    }
};

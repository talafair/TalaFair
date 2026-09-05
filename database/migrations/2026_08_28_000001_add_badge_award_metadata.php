<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('image_path')->nullable()->change();
        });

        Schema::table('badge_user', function (Blueprint $table) {
            $table->unsignedInteger('award_rank')->nullable()->after('awarded_by');
        });
    }

    public function down(): void
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropColumn('award_rank');
        });

        Schema::table('badges', function (Blueprint $table) {
            $table->string('image_path')->nullable(false)->change();
        });
    }
};

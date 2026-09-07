<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_raffle_prizes', function (Blueprint $table) {
            $table->string('prize_type')->default('foods')->after('type');
            $table->unsignedInteger('points_amount')->nullable()->after('prize_type');
        });
    }

    public function down(): void
    {
        Schema::table('event_raffle_prizes', function (Blueprint $table) {
            $table->dropColumn(['prize_type', 'points_amount']);
        });
    }
};

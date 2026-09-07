<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_raffle_prizes', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('event_raffle_prizes', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

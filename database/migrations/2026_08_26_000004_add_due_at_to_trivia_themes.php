<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trivia_themes', function (Blueprint $table) {
            $table->dateTime('due_at')->nullable()->after('base_points');
        });
    }

    public function down(): void
    {
        Schema::table('trivia_themes', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });
    }
};

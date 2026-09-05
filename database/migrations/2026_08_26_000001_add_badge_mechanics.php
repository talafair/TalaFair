<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->string('category')->default('attendance')->after('description');
            $table->string('rarity')->default('common')->after('category');
            $table->string('award_method')->default('automatic')->after('rarity');
            $table->string('condition_key')->default('points')->after('award_method');
            $table->unsignedInteger('limited_total')->nullable()->after('condition_key');
            $table->foreignId('announcement_id')->nullable()->after('limited_total')->constrained('announcements')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('badges', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropColumn([
                'category', 'rarity', 'award_method', 'condition_key', 'limited_total', 'announcement_id',
            ]);
        });
    }
};
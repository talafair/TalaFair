<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('raffle_enabled')->default(false)->after('allow_guest_scanning');
        });

        Schema::create('event_raffle_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_early')->default(false);
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->timestamp('selected_at')->nullable();
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('event_raffle_entries');
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('raffle_enabled');
        });
    }
};

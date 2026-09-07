<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_raffle_entries', function (Blueprint $table) {
            $table->unsignedInteger('points_snapshot')->nullable()->after('is_early');
            $table->decimal('weight', 12, 4)->nullable()->change();
            $table->timestamp('snapshot_at')->nullable()->after('weight');
        });

        Schema::create('event_raffle_prizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['announcement_id', 'sort_order']);
        });

        Schema::create('event_raffle_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_raffle_prize_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('winner_name_snapshot');
            $table->unsignedInteger('draw_sequence');
            $table->timestamp('drawn_at');
            $table->timestamps();

            $table->unique(['announcement_id', 'user_id']);
            $table->unique(['event_raffle_prize_id', 'draw_sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_raffle_winners');
        Schema::dropIfExists('event_raffle_prizes');

        Schema::table('event_raffle_entries', function (Blueprint $table) {
            $table->dropColumn(['points_snapshot', 'snapshot_at']);
            $table->decimal('weight', 5, 2)->default(1.00)->change();
        });
    }
};
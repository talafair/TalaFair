<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('game', 30);
            $table->date('played_on');
            $table->json('stage_scores');
            $table->unsignedInteger('total_score');
            $table->timestamps();
            $table->unique(['user_id', 'game', 'played_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_runs');
    }
};

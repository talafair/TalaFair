<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('trivia_themes', 'base_points')) {
            Schema::table('trivia_themes', function (Blueprint $table) {
                $table->unsignedInteger('base_points')->default(100)->after('title');
            });
        }

        if (! Schema::hasTable('trivia_answers')) Schema::create('trivia_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trivia_theme_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('question_index');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('rank')->nullable();
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamp('answered_at');
            $table->unique(['trivia_theme_id', 'user_id', 'question_index']);
            $table->index(['trivia_theme_id', 'question_index', 'is_correct', 'answered_at'], 'trivia_answers_rank_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trivia_answers');
        Schema::table('trivia_themes', fn (Blueprint $table) => $table->dropColumn('base_points'));
    }
};

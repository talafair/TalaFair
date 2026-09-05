<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_substitutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_head_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('substitute_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['announcement_id', 'family_head_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_substitutions');
    }
};

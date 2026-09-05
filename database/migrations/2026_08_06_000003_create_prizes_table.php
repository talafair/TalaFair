<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();
            $table->string('label', 50);
            $table->enum('prize_type', ['points', 'foods', 'electronics', 'cash', 'essentials', 'none']);
            $table->unsignedInteger('amount')->default(0);
            $table->string('color', 7); // hex, e.g. #9ACD32
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prizes');
    }
};

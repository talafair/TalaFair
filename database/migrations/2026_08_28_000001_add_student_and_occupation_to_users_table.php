<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_student')->default(false)->after('contact_number');
            $table->string('school')->nullable()->after('is_student');
            $table->string('occupation')->nullable()->after('school');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_student', 'school', 'occupation']);
        });
    }
};
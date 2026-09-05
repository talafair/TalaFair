<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // --- Name parts (the old `name` column is kept and auto-filled) ---
            $table->string('first_name')->nullable()->after('name');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('suffix', 20)->nullable()->after('last_name');

            // --- Personal ---
            $table->enum('gender', ['female', 'male', 'others'])->nullable()->after('suffix');
            $table->string('gender_other')->nullable()->after('gender');
            $table->date('birthdate')->nullable()->after('gender_other');
            $table->string('contact_number', 20)->nullable()->after('birthdate');

            // --- Address (only the first three are supplied by the resident) ---
            $table->string('house_no')->nullable()->after('contact_number');
            $table->string('street')->nullable()->after('house_no');
            $table->string('zone', 10)->nullable()->after('street');
            $table->string('barangay')->default('San Jose')->after('zone');
            $table->string('city')->default('Iriga City')->after('barangay');
            $table->string('province')->default('Camarines Sur')->after('city');
            $table->string('country')->default('Philippines')->after('province');
            $table->string('postal_code', 10)->default('4431')->after('country');

            // --- Household ---
            $table->boolean('is_head_of_family')->default(false)->after('postal_code');
            $table->string('head_of_family_name')->nullable()->after('is_head_of_family');
            $table->foreignId('head_of_family_id')->nullable()->after('head_of_family_name')
                  ->constrained('users')->nullOnDelete();

            // --- Identity / media ---
            $table->string('unique_id', 24)->nullable()->unique()->after('head_of_family_id');
            $table->string('avatar_path')->nullable()->after('unique_id');

            $table->index('birthdate');
            $table->index('is_head_of_family');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['head_of_family_id']);
            $table->dropIndex(['birthdate']);
            $table->dropIndex(['is_head_of_family']);
            $table->dropColumn([
                'first_name', 'middle_name', 'last_name', 'suffix',
                'gender', 'gender_other', 'birthdate', 'contact_number',
                'house_no', 'street', 'zone', 'barangay', 'city', 'province',
                'country', 'postal_code',
                'is_head_of_family', 'head_of_family_name', 'head_of_family_id',
                'unique_id', 'avatar_path',
            ]);
        });
    }
};

//new file 08/17/2026
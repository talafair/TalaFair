<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'sex_at_birth')) {
                $table->string('sex_at_birth')->nullable()->after('gender_other');
            }
            if (! Schema::hasColumn('users', 'preferred_gender_identity')) {
                $table->string('preferred_gender_identity')->nullable()->after('sex_at_birth');
            }
            if (! Schema::hasColumn('users', 'gender_identity_other')) {
                $table->string('gender_identity_other')->nullable()->after('preferred_gender_identity');
            }
            if (! Schema::hasColumn('users', 'is_lgbtqia')) {
                $table->boolean('is_lgbtqia')->default(false)->after('gender_identity_other');
            }
            if (! Schema::hasColumn('users', 'is_pwd')) {
                $table->boolean('is_pwd')->default(false)->after('is_student');
            }
            if (! Schema::hasColumn('users', 'is_out_of_school_youth')) {
                $table->boolean('is_out_of_school_youth')->default(false)->after('is_pwd');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['sex_at_birth', 'preferred_gender_identity', 'gender_identity_other', 'is_lgbtqia', 'is_pwd', 'is_out_of_school_youth'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_4ps_member')) {
                $table->boolean('is_4ps_member')->default(false)->after('is_pwd');
            }
            if (! Schema::hasColumn('users', 'is_solo_parent')) {
                $table->boolean('is_solo_parent')->default(false)->after('is_4ps_member');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['is_4ps_member', 'is_solo_parent'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
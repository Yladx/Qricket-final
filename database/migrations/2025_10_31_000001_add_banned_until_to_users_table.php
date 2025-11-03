<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'banned_until')) {
                $table->dateTime('banned_until')->nullable()->after('banned_at');
            }
            if (!Schema::hasColumn('users', 'banned_reason')) {
                $table->string('banned_reason', 255)->nullable()->after('banned_until');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status', 32)->nullable()->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'banned_until')) {
                $table->dropColumn('banned_until');
            }
            if (Schema::hasColumn('users', 'banned_reason')) {
                $table->dropColumn('banned_reason');
            }
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};



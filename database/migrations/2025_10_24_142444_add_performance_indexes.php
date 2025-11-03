<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes for better performance
        
        // Sessions table indexes
        Schema::table('sessions', function (Blueprint $table) {
            if (!Schema::hasIndex('sessions', 'sessions_role_index')) {
                $table->index('role', 'sessions_role_index');
            }
            if (!Schema::hasIndex('sessions', 'sessions_user_id_index')) {
                $table->index('user_id', 'sessions_user_id_index');
            }
            if (!Schema::hasIndex('sessions', 'sessions_last_activity_index')) {
                $table->index('last_activity', 'sessions_last_activity_index');
            }
        });

        // Events table indexes - already included in original creation file, skipping duplicates

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_organizer_verified') && !Schema::hasIndex('users', 'users_is_organizer_verified_index')) {
                $table->index('is_organizer_verified', 'users_is_organizer_verified_index');
            }
        });

        // Event ticket information indexes - already included in original creation file, skipping duplicates

        // Ticket purchases indexes - already included in original creation file, skipping duplicates

        // Saved events indexes
        Schema::table('saved_events', function (Blueprint $table) {
            if (!Schema::hasIndex('saved_events', 'se_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at'], 'se_user_id_created_at_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropIndex('sessions_role_index');
            $table->dropIndex('sessions_user_id_index');
            $table->dropIndex('sessions_last_activity_index');
        });

        // Events table indexes - already in original creation file, no drop needed

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_is_organizer_verified_index');
        });

        // Event ticket information indexes - already in original creation file, no drop needed

        // Ticket purchases indexes - already in original creation file, no drop needed

        Schema::table('saved_events', function (Blueprint $table) {
            $table->dropIndex('se_user_id_created_at_index');
        });
    }
};
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
        // Create admins table
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role')->default('admin'); // admin, superadmin, etc.
            $table->text('pin'); // Encrypted PIN
            $table->timestamp('last_login_at')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('current_session_id')->nullable();
            $table->timestamps();
        });

        // Create admin_accesses table (merged for cleanliness)
        Schema::create('admin_accesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->unique();
            $table->boolean('can_manage_admins')->default(false);
            $table->boolean('can_manage_users')->default(false);
            $table->boolean('can_manage_verifications')->default(false);
            $table->boolean('can_view_logs')->default(true);
            $table->boolean('can_manage_support')->default(false);
            $table->json('custom_permissions')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });

        // Create admin activity logs table
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('action'); // login, logout, login_failed, etc.
            $table->text('description')->nullable(); // Added description field
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional data like failure reason
            $table->timestamps();
            
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->index(['admin_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_accesses');
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('admins');
    }
};




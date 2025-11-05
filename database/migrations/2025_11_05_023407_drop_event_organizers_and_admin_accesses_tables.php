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
        // Drop admin_accesses table first (has foreign key to admins)
        Schema::dropIfExists('admin_accesses');
        
        // Drop event_organizers table (has foreign keys to events and users)
        Schema::dropIfExists('event_organizers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate event_organizers table
        Schema::create('event_organizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['organizer', 'co_organizer'])->default('organizer');
            $table->timestamps();
            
            $table->unique(['event_id', 'user_id']);
            $table->index(['user_id']);
        });

        // Recreate admin_accesses table
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
    }
};

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
        Schema::create('qricket_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            // Star ratings (1-5)
            $table->tinyInteger('navigation_ease')->nullable(); // How easy was it to navigate
            $table->tinyInteger('information_found')->nullable(); // Did you find all information needed
            $table->tinyInteger('design_rating')->nullable(); // Design and appearance rating
            $table->tinyInteger('performance_rating')->nullable(); // Load speed and functionality
            $table->tinyInteger('process_satisfaction')->nullable(); // Registration/ticketing satisfaction
            
            // Open feedback
            $table->text('improvements_suggestions')->nullable();
            $table->text('additional_comments')->nullable();
            
            // Metadata
            $table->string('feedback_type')->default('website'); // website, event, etc.
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('set null'); // If feedback is event-related
            $table->foreignId('ticket_purchase_id')->nullable()->constrained()->onDelete('set null'); // If feedback is ticket-related
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['session_id', 'created_at']);
            $table->index(['feedback_type', 'created_at']);
            $table->index(['event_id', 'created_at']);
            
            // Foreign key for user_id (nullable)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qricket_feedback');
    }
};
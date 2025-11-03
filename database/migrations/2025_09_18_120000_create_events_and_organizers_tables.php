<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create events table
        Schema::create('events', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // 10-digit randomized ID (non-auto-incrementing, must be UNSIGNED to match foreignId)
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('location');
            $table->text('location_url')->nullable();
            $table->text('tags')->nullable();
            $table->string('poster')->nullable();
            $table->string('thumbnail')->nullable();
            $table->text('layout_path')->nullable();
            $table->string('ticket_settings_path')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected', 'scheduled', 'published'])->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('submitted_for_approval_at')->nullable();
            $table->timestamp('scheduled_publish_at')->nullable();
            $table->timestamp('feedback_emails_sent_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->boolean('certificate_enabled')->default(true);
            $table->timestamp('feedback_notified_at')->nullable();
            $table->timestamp('end_notified_at')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['status', 'start_date']);
            $table->index(['category']);
            $table->index(['start_date']);
            $table->index(['user_id']);
            $table->index(['user_id', 'status']);
        });

        // Create event_organizers table
        Schema::create('event_organizers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['organizer', 'co_organizer'])->default('organizer');
            $table->timestamps();
            
            $table->unique(['event_id', 'user_id']);
            $table->index(['user_id']);
        });

        // Create event_news table
        Schema::create('event_news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->json('attachments')->nullable();
            $table->enum('visibility', ['public', 'ticket_buyers', 'saved_users'])->default('public');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['event_id', 'published_at']);
        });

        // Create saved_events table
        Schema::create('saved_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->timestamp('saved_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'event_id']);
            $table->index(['user_id']);
        });

        // Create event_tickets table
        Schema::create('event_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->nullable();
            $table->integer('sold_count')->default(0);
            $table->datetime('sale_start')->nullable();
            $table->datetime('sale_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['event_id', 'is_active']);
            $table->index(['event_id', 'sort_order']);
        });

        // Create event_activity_logs table
        Schema::create('event_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['event_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Create event_staff table
        Schema::create('event_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role');
            $table->boolean('can_manage_event')->default(false);
            $table->boolean('can_manage_news')->default(false);
            $table->boolean('can_manage_attendance')->default(false);
            $table->boolean('can_manage_sales')->default(false);
            $table->boolean('can_manage_ticket')->default(false);
            $table->boolean('can_manage_staff')->default(false);
            $table->boolean('can_edit_event_info')->default(false);
            $table->timestamps();
            
            $table->unique(['event_id', 'user_id']);
            $table->index(['event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_feedback');
        Schema::dropIfExists('event_staff');
        Schema::dropIfExists('event_activity_logs');
        Schema::dropIfExists('event_tickets');
        Schema::dropIfExists('saved_events');
        Schema::dropIfExists('event_news');
        Schema::dropIfExists('event_organizers');
        Schema::dropIfExists('events');
    }
};

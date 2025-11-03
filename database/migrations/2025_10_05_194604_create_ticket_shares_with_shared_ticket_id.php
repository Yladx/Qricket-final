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
        Schema::create('event_ticket_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->nullable()->constrained('event_tickets')->onDelete('cascade');
            $table->foreignId('sharer_id')->constrained('users')->onDelete('cascade'); // User who is sharing
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade'); // User receiving the share
            $table->integer('quantity')->default(1); // Number of tickets being shared
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->timestamp('expires_at')->nullable(); // Optional expiration date
            $table->json('shared_ticket_ids')->nullable(); // Array of specific ticket IDs being shared
            $table->text('message')->nullable(); // Optional message from sharer
            $table->foreignId('shared_ticket_id')->nullable()->constrained('event_ticket_information')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure a user can't share the same event to the same recipient multiple times
            $table->unique(['event_id', 'sharer_id', 'recipient_id']);
            
            // Index for performance
            $table->index(['recipient_id', 'status']);
            $table->index(['sharer_id', 'status']);
            $table->index(['ticket_type_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_ticket_shares');
    }
};
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
        Schema::create('event_ticket_information', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->nullable()->constrained('event_tickets')->onDelete('cascade');
            $table->foreignId('ticket_purchase_id')->constrained()->onDelete('cascade');
            // Sharing fields (shared_to_user_id was later removed, keeping only shared_at and shared_by_user_id)
            $table->timestamp('shared_at')->nullable();
            $table->foreignId('shared_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('qr_code_data')->nullable(); // Encrypted QR code data
            $table->timestamp('checked_in_at')->nullable();
            $table->boolean('certificate_given')->default(false);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'event_id']);
            $table->index('ticket_purchase_id');
            $table->index(['ticket_type_id', 'user_id']);
            $table->index(['ticket_type_id', 'event_id']);
            $table->index(['shared_by_user_id', 'event_id']);
            $table->index('shared_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_ticket_information');
    }
};
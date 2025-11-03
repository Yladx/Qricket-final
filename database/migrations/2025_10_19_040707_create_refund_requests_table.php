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
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['ticket', 'subscription', 'additional_ticket']);
            $table->unsignedBigInteger('item_id'); // ticket_purchase_id or subscription_id
            $table->string('purchase_id'); // external purchase reference
            $table->decimal('amount', 10, 2);
            $table->text('reason');
            $table->string('referral_id')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed', 'completed', 'failed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->string('xendit_refund_id')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->json('refunded_ticket_ids')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
    }
};
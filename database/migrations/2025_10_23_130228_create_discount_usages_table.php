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
        Schema::create('discount_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('discount_id')->constrained()->onDelete('cascade');
            $table->string('usage_type')->default('general'); // 'ticket_purchase', 'subscription', 'ticket_limit_purchase', 'general'
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the purchase/subscription that used this discount
            $table->string('reference_type')->nullable(); // Type of reference (TicketPurchase, Subscription, etc.)
            $table->decimal('discount_amount', 10, 2)->nullable(); // Amount saved with this usage
            $table->decimal('original_amount', 10, 2)->nullable(); // Original amount before discount
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'discount_id']);
            $table->index(['discount_id', 'user_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->unique(['user_id', 'discount_id', 'reference_type', 'reference_id'], 'discount_usages_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_usages');
    }
};
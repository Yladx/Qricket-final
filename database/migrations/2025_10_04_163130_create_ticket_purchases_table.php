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
        Schema::create('ticket_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_type_id')->nullable()->constrained('event_tickets')->onDelete('cascade');
            // Note: Avoid circular FK. ticket_information references ticket_purchases (ticket_purchase_id).
            // We do not need a back-reference column here; relate via hasOne on purchase.id = ticket_information.ticket_purchase_id
            $table->string('purchase_id')->unique(); // Unique identifier for this purchase
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('transaction_fee', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->decimal('total_fees', 10, 2)->default(0);
            $table->string('currency', 3)->default('PHP');
            $table->string('status')->default('pending'); // pending, paid, failed, expired, refunded
            $table->string('payment_method')->nullable(); // credit_card, bank_transfer, ewallet, etc.
            $table->string('xendit_payment_id')->nullable(); // Xendit payment ID
            $table->string('xendit_invoice_id')->nullable(); // Xendit invoice ID
            $table->json('payment_details')->nullable(); // Store payment response from Xendit
            $table->json('customer_details')->nullable(); // Customer information
            $table->timestamp('expires_at')->nullable(); // Payment expiration
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('purchase_id');
            $table->index('xendit_payment_id');
            $table->index('xendit_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_purchases');
    }
};

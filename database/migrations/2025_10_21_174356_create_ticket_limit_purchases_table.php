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
        if (Schema::hasTable('ticket_limit_purchases')) {
            return; // table already exists
        }

        Schema::create('ticket_limit_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            // Define column first; FK added in later migration to avoid order issues
            $table->unsignedBigInteger('ticket_limit_pricing_id')->nullable();
            $table->index('ticket_limit_pricing_id');
            $table->string('purchase_id')->unique(); // Unique identifier for this purchase
            $table->integer('additional_tickets'); // Number of additional tickets purchased
            $table->decimal('amount', 10, 2); // Price paid for additional tickets
            $table->decimal('transaction_fee', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->decimal('total_fees', 10, 2)->default(0);
            $table->string('currency', 3)->default('PHP');
            $table->string('discount_code')->nullable(); // Discount code applied
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('xendit_invoice_id')->nullable();
            $table->string('xendit_payment_id')->nullable();
            $table->json('payment_details')->nullable(); // Store payment response from Xendit
            $table->json('customer_details')->nullable(); // Customer information
            $table->timestamp('expires_at')->nullable(); // Payment expiration
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable(); // Additional data like payment details
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'event_id']);
            $table->index('status');
            $table->index('paid_at');
            $table->index('purchase_id');
            $table->index('xendit_invoice_id');
            $table->index('xendit_payment_id');
            $table->index('ticket_limit_pricing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_limit_purchases');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('subscription_plan_id')->nullable();
            $table->string('plan_id');
            $table->string('status');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('cancel_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('discount_code', 20)->nullable();
            $table->json('metadata')->nullable();
            $table->string('xendit_invoice_id')->nullable();
            $table->string('xendit_payment_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('transaction_fee', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);
            $table->decimal('total_fees', 10, 2)->default(0);
            $table->string('currency', 3)->default('PHP');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamps();
            
            // Add indexes
            $table->index(['user_id', 'status']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
}; 
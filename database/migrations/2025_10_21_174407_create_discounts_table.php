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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "New User Discount", "Early Bird Special"
            $table->string('code')->unique(); // Discount code like "NEWUSER10", "EARLY20"
            $table->enum('type', ['percentage', 'fixed_amount']); // Percentage or fixed amount
            $table->decimal('value', 10, 2); // Discount value (10 for 10%, 50 for $50)
            $table->string('currency', 3)->default('PHP');
            $table->enum('applicable_to', ['ticket_limit_purchase', 'subscription', 'all']); // What this discount applies to
            $table->integer('usage_limit')->nullable(); // Maximum number of times this can be used
            $table->integer('per_user_limit')->nullable()->comment('Maximum times a single user can use this discount');
            $table->integer('used_count')->default(0); // How many times it's been used
            $table->timestamp('starts_at')->nullable(); // When discount becomes active
            $table->timestamp('expires_at')->nullable(); // When discount expires
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->json('conditions')->nullable(); // Additional conditions like "new users only"
            $table->timestamps();
            
            // Indexes
            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
            $table->index('applicable_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
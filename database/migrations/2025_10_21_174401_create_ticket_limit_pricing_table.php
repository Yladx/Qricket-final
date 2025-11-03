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
        Schema::create('ticket_limit_pricing', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "100 Additional Tickets", "200 Additional Tickets"
            $table->integer('ticket_quantity'); // Number of tickets (100, 200, etc.)
            $table->decimal('price', 10, 2); // Price for this quantity
            $table->string('currency', 3)->default('PHP');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // For ordering in admin
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->unique('ticket_quantity'); // Each quantity should be unique
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_limit_pricing');
    }
};
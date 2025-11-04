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
        Schema::table('ticket_limit_pricing', function (Blueprint $table) {
            // Drop the old unique constraint on ticket_quantity
            $table->dropUnique(['ticket_quantity']);
            
            // Add composite unique constraint on ticket_quantity and type
            // This allows the same quantity for free and paid events
            $table->unique(['ticket_quantity', 'type'], 'ticket_limit_pricing_ticket_quantity_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_limit_pricing', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('ticket_limit_pricing_ticket_quantity_type_unique');
            
            // Restore the old unique constraint on ticket_quantity
            $table->unique('ticket_quantity');
        });
    }
};

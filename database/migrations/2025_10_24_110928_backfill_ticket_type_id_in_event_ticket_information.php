<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill ticket_type_id for existing tickets
        // This will get the ticket type from the ticket_purchase's payment_details breakdown
        DB::statement("
            UPDATE event_ticket_information eti
            JOIN ticket_purchases tp ON eti.ticket_purchase_id = tp.id
            SET eti.ticket_type_id = (
                SELECT JSON_UNQUOTE(JSON_EXTRACT(tp.payment_details, '$.ticket_breakdown[0].ticket_type.id'))
                WHERE JSON_EXTRACT(tp.payment_details, '$.ticket_breakdown[0].ticket_type.id') IS NOT NULL
            )
            WHERE eti.ticket_type_id IS NULL
            AND tp.payment_details IS NOT NULL
            AND JSON_EXTRACT(tp.payment_details, '$.ticket_breakdown[0].ticket_type.id') IS NOT NULL
        ");
        
        // For tickets that couldn't be updated via JSON extraction, try to match by event_tickets
        // This handles cases where the breakdown might be stored differently
        DB::statement("
            UPDATE event_ticket_information eti
            JOIN ticket_purchases tp ON eti.ticket_purchase_id = tp.id
            JOIN event_tickets et ON tp.event_id = et.event_id
            SET eti.ticket_type_id = et.id
            WHERE eti.ticket_type_id IS NULL
            AND tp.payment_details IS NOT NULL
            AND JSON_EXTRACT(tp.payment_details, '$.ticket_breakdown') IS NOT NULL
            AND et.id = (
                SELECT MIN(et2.id) 
                FROM event_tickets et2 
                WHERE et2.event_id = tp.event_id
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all ticket_type_id to NULL
        DB::statement("UPDATE event_ticket_information SET ticket_type_id = NULL");
    }
};
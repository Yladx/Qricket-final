<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\TicketPurchase;
use App\Models\EventTicketInformation;
use Illuminate\Support\Facades\DB;

class TestEventRevenue extends Command
{
    protected $signature = 'test:event-revenue {event_id=1}';
    protected $description = 'Test event revenue calculation for payout accuracy';

    public function handle()
    {
        $eventId = $this->argument('event_id');
        $event = Event::find($eventId);
        
        if (!$event) {
            $this->error("Event ID {$eventId} not found!");
            return 1;
        }
        
        $this->info("=== Testing Revenue Calculation for Event: {$event->title} (ID: {$eventId}) ===\n");
        
        // Method 1: Using ticketInformation relationship
        $this->info("Method 1: Using ticketInformation relationship");
        $ticketInfos = EventTicketInformation::where('event_id', $eventId)
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->with('ticketPurchase')
            ->get();
        
        $method1Total = 0;
        foreach ($ticketInfos as $ticket) {
            $amount = $ticket->ticketPurchase->total_amount ?? 0;
            $method1Total += $amount;
            $this->line("  - Ticket ID: {$ticket->id}, Amount: ₱{$amount}");
        }
        $this->info("  Total (Method 1): ₱{$method1Total}\n");
        
        // Method 2: Using ticket_purchases with event_id
        $this->info("Method 2: Using ticket_purchases with event_id");
        $purchases = TicketPurchase::where('event_id', $eventId)
            ->where('status', 'paid')
            ->get();
        
        $method2Total = 0;
        foreach ($purchases as $purchase) {
            $method2Total += $purchase->total_amount;
            $this->line("  - Purchase ID: {$purchase->id}, Amount: ₱{$purchase->total_amount}");
        }
        $this->info("  Total (Method 2): ₱{$method2Total}\n");
        
        // Check if ticket_purchases has event_id column
        $this->info("Checking database schema...");
        if (DB::getSchemaBuilder()->hasColumn('ticket_purchases', 'event_id')) {
            $this->info("  ✓ ticket_purchases table has event_id column");
        } else {
            $this->warn("  ✗ ticket_purchases table does NOT have event_id column");
        }
        
        // Get detailed breakdown
        $this->info("\n=== Detailed Revenue Breakdown ===");
        
        $allPurchases = TicketPurchase::where('status', 'paid')
            ->get();
        
        $this->table(
            ['Purchase ID', 'Event ID', 'Amount', 'Net Amount', 'Transaction Fee', 'VAT'],
            $allPurchases->map(function($purchase) {
                return [
                    $purchase->id,
                    $purchase->event_id ?? 'NULL',
                    '₱' . number_format($purchase->total_amount ?? 0, 2),
                    '₱' . number_format($purchase->net_amount ?? $purchase->total_amount ?? 0, 2),
                    '₱' . number_format($purchase->transaction_fee ?? 0, 2),
                    '₱' . number_format($purchase->vat_amount ?? 0, 2),
                ];
            })
        );
        
        // Calculate Qricket fee and net payout
        $qricketFeePercentage = config('app.qricket_fee_percentage', 5);
        $qricketFee = $method1Total * ($qricketFeePercentage / 100);
        $netPayout = $method1Total - $qricketFee;
        
        $this->info("\n=== Payout Calculation ===");
        $this->info("Total Earnings: ₱{$method1Total}");
        $this->info("Qricket Fee ({$qricketFeePercentage}%): ₱{$qricketFee}");
        $this->info("Net Payout: ₱{$netPayout}");
        
        // Check existing payout requests
        $payoutRequest = \App\Models\EventPayoutRequest::where('event_id', $eventId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($payoutRequest) {
            $this->info("\n=== Existing Payout Request ===");
            $this->info("Status: {$payoutRequest->status}");
            $this->info("Total Earnings: ₱{$payoutRequest->total_earnings}");
            $this->info("Qricket Fee: ₱{$payoutRequest->qricket_fee}");
            $this->info("Net Amount: ₱{$payoutRequest->net_amount}");
        } else {
            $this->info("\nNo existing payout request found.");
        }
        
        return 0;
    }
}


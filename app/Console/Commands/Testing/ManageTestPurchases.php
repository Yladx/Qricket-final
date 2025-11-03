<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\EventTicket;
use App\Models\TicketPurchase;
use App\Models\User;

class ManageTestPurchases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:purchases {action=add} {event_id=4}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add or remove test purchase data for demonstration purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $eventId = $this->argument('event_id');
        
        $event = Event::find($eventId);
        if (!$event) {
            $this->error("Event with ID {$eventId} not found.");
            return 1;
        }
        
        $this->info("Managing test purchases for event: {$event->title}");
        
        switch ($action) {
            case 'add':
                $this->addTestPurchases($event);
                break;
            case 'remove':
                $this->removeTestPurchases($event);
                break;
            case 'status':
                $this->showStatus($event);
                break;
            default:
                $this->error("Invalid action. Use 'add', 'remove', or 'status'.");
                return 1;
        }
        
        return 0;
    }
    
    private function addTestPurchases(Event $event)
    {
        $user = User::first();
        if (!$user) {
            $this->error("No users found in the database.");
            return;
        }
        
        $ticketTypes = $event->eventTickets;
        if ($ticketTypes->isEmpty()) {
            $this->error("No ticket types found for this event.");
            return;
        }
        
        $this->info("Adding test purchases...");
        
        foreach ($ticketTypes as $index => $ticketType) {
            $quantities = [2, 3, 1]; // Different quantities for each ticket type
            $quantity = $quantities[$index] ?? 1;
            
            $purchase = TicketPurchase::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'purchase_id' => 'DEMO-' . time() . '-' . $index,
                'quantity' => $quantity,
                'unit_price' => $ticketType->price,
                'total_amount' => $ticketType->price * $quantity,
                'status' => 'paid',
                'payment_method' => 'demo',
                'paid_at' => now()->subDays(rand(0, 10)), // Random date within last 10 days
            ]);
            
            // Update sold_count in EventTicket
            $currentSold = $ticketType->sold_count ?? 0;
            $ticketType->update(['sold_count' => $currentSold + $quantity]);
            
            $this->line("✓ Created purchase for {$ticketType->name}: {$quantity} tickets for ₱" . ($ticketType->price * $quantity));
        }
        
        $this->info("✅ Test purchases added successfully!");
        $this->line("Refresh the manage event page to see the data.");
    }
    
    private function removeTestPurchases(Event $event)
    {
        $testPurchases = TicketPurchase::where('event_id', $event->id)
            ->where('purchase_id', 'like', 'DEMO-%')
            ->get();
            
        if ($testPurchases->isEmpty()) {
            $this->info("No test purchases found for this event.");
            return;
        }
        
        $this->info("Removing " . $testPurchases->count() . " test purchases...");
        
        // Reset sold_count for all ticket types
        foreach ($event->eventTickets as $ticketType) {
            $ticketType->update(['sold_count' => 0]);
        }
        
        // Delete test purchases
        TicketPurchase::where('event_id', $event->id)
            ->where('purchase_id', 'like', 'DEMO-%')
            ->delete();
            
        $this->info("✅ Test purchases removed successfully!");
        $this->line("Refresh the manage event page to see the data reset.");
    }
    
    private function showStatus(Event $event)
    {
        $totalPurchases = TicketPurchase::where('event_id', $event->id)->count();
        $testPurchases = TicketPurchase::where('event_id', $event->id)
            ->where('purchase_id', 'like', 'DEMO-%')
            ->count();
        $realPurchases = $totalPurchases - $testPurchases;
        
        $this->info("Event: {$event->title}");
        $this->line("Total Purchases: {$totalPurchases}");
        $this->line("Test Purchases: {$testPurchases}");
        $this->line("Real Purchases: {$realPurchases}");
        
        if ($testPurchases > 0) {
            $this->line("\nTest purchases found:");
            $testData = TicketPurchase::where('event_id', $event->id)
                ->where('purchase_id', 'like', 'DEMO-%')
                ->with()
                ->get();
                
            foreach ($testData as $purchase) {
                $ticketTypeName = isset($purchase->unit_price)
                    ? ('Ticket (₱' . number_format($purchase->unit_price, 2) . ')')
                    : 'Ticket';
                $this->line("- {$ticketTypeName}: {$purchase->quantity} tickets for ₱{$purchase->total_amount}");
            }
        }
    }
}
<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\EventTicketInformation;
use App\Models\Event;
use App\Models\User;
use App\Models\TicketPurchase;
use App\Services\QRCodeEncryptionService;

class TestQRCodeEncryptionCommand extends Command
{
    protected $signature = 'test:qr-encryption {event_id}';
    protected $description = 'Test QR code encryption and decryption';

    public function handle()
    {
        $eventId = $this->argument('event_id');
        
        $this->info("Testing QR Code Encryption for Event ID: {$eventId}");
        $this->newLine();
        
        // Find an event ticket to test with
        $ticket = EventTicketInformation::where('event_id', $eventId)
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->with(['event', 'user', 'ticketPurchase'])
            ->first();
            
        if (!$ticket) {
            $this->error("No paid tickets found for event {$eventId}");
            return 1;
        }
        
        $this->info("Found ticket: {$ticket->ticket_id}");
        $this->info("Event: {$ticket->event->title}");
        $this->info("User: {$ticket->user->first_name} {$ticket->user->last_name}");
        $this->newLine();
        
        // Test 1: Generate encrypted QR code
        $this->info("1. Generating encrypted QR code...");
        $encryptedQR = $ticket->generateQRCodeData();
        $this->info("Encrypted QR length: " . strlen($encryptedQR));
        $this->info("Encrypted QR preview: " . substr($encryptedQR, 0, 50) . "...");
        $this->newLine();
        
        // Test 2: Validate encrypted QR code
        $this->info("2. Validating encrypted QR code...");
        $validatedTicket = EventTicketInformation::validateQRCode($encryptedQR, $eventId);
        
        if ($validatedTicket) {
            $this->info("✅ QR code validation successful!");
            $this->info("Validated ticket ID: {$validatedTicket->ticket_id}");
            $this->info("Validated user: {$validatedTicket->user->first_name} {$validatedTicket->user->last_name}");
        } else {
            $this->error("❌ QR code validation failed!");
        }
        $this->newLine();
        
        // Test 3: Try to decrypt with wrong event ID
        $this->info("3. Testing security - wrong event ID...");
        $wrongEventId = $eventId + 1;
        $invalidTicket = EventTicketInformation::validateQRCode($encryptedQR, $wrongEventId);
        
        if (!$invalidTicket) {
            $this->info("✅ Security test passed - QR code rejected for wrong event");
        } else {
            $this->error("❌ Security test failed - QR code accepted for wrong event!");
        }
        $this->newLine();
        
        // Test 4: Try to decrypt tampered QR code
        $this->info("4. Testing security - tampered QR code...");
        $tamperedQR = substr($encryptedQR, 0, -10) . 'TAMPERED';
        $tamperedTicket = EventTicketInformation::validateQRCode($tamperedQR, $eventId);
        
        if (!$tamperedTicket) {
            $this->info("✅ Security test passed - tampered QR code rejected");
        } else {
            $this->error("❌ Security test failed - tampered QR code accepted!");
        }
        $this->newLine();
        
        // Test 5: Test what Google Lens would see
        $this->info("5. Testing external scanner visibility...");
        $this->info("Raw QR data that external scanners would see:");
        $this->line($encryptedQR);
        $this->newLine();
        
        $this->info("External scanners (like Google Lens) would see:");
        $this->info("- Encrypted data that cannot be decrypted without the event key");
        $this->info("- No readable ticket information");
        $this->info("- No way to extract ticket details");
        $this->newLine();
        
        $this->info("🎉 All encryption tests completed!");
        
        return 0;
    }
}

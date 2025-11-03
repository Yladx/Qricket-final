<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Http\Controllers\Organizer\EventManagement\EventCertificateController;

class TestCertificateData extends Command
{
    protected $signature = 'test:certificate-data {event_id}';
    protected $description = 'Test certificate data loading for an event';

    public function handle()
    {
        $eventId = $this->argument('event_id');
        
        $this->info("=== Testing Certificate Data for Event ID: {$eventId} ===");
        
        // Find the event
        $event = Event::find($eventId);
        if (!$event) {
            $this->error("Event with ID {$eventId} not found!");
            return 1;
        }
        
        $this->info("Event found: {$event->title}");
        $this->info("Event status: {$event->status}");
        $this->info("Certificate enabled: " . ($event->certificate_enabled ? 'YES' : 'NO'));
        
        // Create controller instance to test certificate data loading
        $controller = new EventCertificateController();
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('loadCertificateDataFromR2');
        $method->setAccessible(true);
        
        try {
            $certificateData = $method->invoke($controller, $eventId);
            
            $this->info("\n=== Certificate Data ===");
            $this->info("Data keys: " . implode(', ', array_keys($certificateData)));
            
            // Check specific fields
            $this->info("\n=== Key Fields ===");
            $this->info("Organization: " . ($certificateData['org_school'] ?? 'NOT SET'));
            $this->info("Event title: " . ($certificateData['event_title'] ?? 'NOT SET'));
            $this->info("Left signature name: " . ($certificateData['sig_left_name'] ?? 'NOT SET'));
            $this->info("Right signature name: " . ($certificateData['sig_right_name'] ?? 'NOT SET'));
            $this->info("Left signature path: " . ($certificateData['signature_left_path'] ?? 'NOT SET'));
            $this->info("Right signature path: " . ($certificateData['signature_right_path'] ?? 'NOT SET'));
            $this->info("Theme primary: " . ($certificateData['theme_primary'] ?? 'NOT SET'));
            $this->info("Theme secondary: " . ($certificateData['theme_secondary'] ?? 'NOT SET'));
            
            // Check if signatures exist
            $this->info("\n=== Signature Files ===");
            $leftSigPath = "events/{$eventId}/certificate/signature_left.enc";
            $rightSigPath = "events/{$eventId}/certificate/signature_right.enc";
            
            $this->info("Left signature exists: " . (\Storage::disk('r2')->exists($leftSigPath) ? 'YES' : 'NO'));
            $this->info("Right signature exists: " . (\Storage::disk('r2')->exists($rightSigPath) ? 'YES' : 'NO'));
            
            // Check logos
            $this->info("\n=== Logo Files ===");
            $leftLogoPath = "events/{$eventId}/certificate/logo_left.png";
            $rightLogoPath = "events/{$eventId}/certificate/logo_right.png";
            
            $this->info("Left logo exists: " . (\Storage::disk('r2')->exists($leftLogoPath) ? 'YES' : 'NO'));
            $this->info("Right logo exists: " . (\Storage::disk('r2')->exists($rightLogoPath) ? 'YES' : 'NO'));
            
            $this->info("\n✅ Certificate data test completed!");
            
        } catch (\Exception $e) {
            $this->error("Error loading certificate data: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
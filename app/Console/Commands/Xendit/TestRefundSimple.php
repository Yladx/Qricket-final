<?php

namespace App\Console\Commands\Xendit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestRefundSimple extends Command
{
    protected $signature = 'test:refund-simple';
    protected $description = 'Simple refund test with detailed logging';

    public function handle()
    {
        $this->info('🧪 Simple Refund Test');
        $this->newLine();

        $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
        
        if (!$apiKey) {
            $this->error('❌ Xendit API key not configured!');
            return 1;
        }

        $this->info("🔑 API Key: " . substr($apiKey, 0, 8) . "...");
        $this->newLine();

        // Test payload - using v2 refunds format
        $payload = [
            'external_id' => 'test_refund_' . time(),
            'amount' => 10000, // 100 PHP in cents
            'reason' => 'Test refund from Qricket',
            'payment_method' => 'ewallet',
        ];

        $this->info('📤 Payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));
        $this->newLine();

        try {
            $this->info('⏳ Making API call...');
            
            // Test both authorization methods
            $this->info('Testing with withBasicAuth method (like disbursements)...');
            $response1 = Http::withBasicAuth($apiKey, '')
                ->asJson()
                ->post('https://api.xendit.co/refunds', $payload);

            $this->newLine();
            $this->info('📥 Response Status: ' . $response1->status());
            $this->info('📄 Response Body:');
            $this->line(json_encode($response1->json(), JSON_PRETTY_PRINT));
            $this->newLine();

            if ($response1->successful()) {
                $this->info('✅ SUCCESS with withBasicAuth!');
            } else {
                $this->error('❌ FAILED with withBasicAuth');
                
                $this->newLine();
                $this->info('Testing with Basic header method (current refund method)...');
                
                $response2 = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
                    'Content-Type' => 'application/json',
                ])->post('https://api.xendit.co/refunds', $payload);

                $this->newLine();
                $this->info('📥 Response Status: ' . $response2->status());
                $this->info('📄 Response Body:');
                $this->line(json_encode($response2->json(), JSON_PRETTY_PRINT));
                $this->newLine();

                if ($response2->successful()) {
                    $this->info('✅ SUCCESS with Basic header!');
                } else {
                    $this->error('❌ FAILED with both methods');
                }
            }

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Exception: ' . $e->getMessage());
        }

        return 0;
    }
}

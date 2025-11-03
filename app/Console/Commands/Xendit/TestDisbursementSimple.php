<?php

namespace App\Console\Commands\Xendit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestDisbursementSimple extends Command
{
    protected $signature = 'test:disbursement-simple';
    protected $description = 'Simple disbursement test with detailed logging';

    public function handle()
    {
        $this->info('🧪 Simple Disbursement Test');
        $this->newLine();

        $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
        
        if (!$apiKey) {
            $this->error('❌ Xendit API key not configured!');
            return 1;
        }

        $this->info("🔑 API Key: " . substr($apiKey, 0, 8) . "...");
        $this->newLine();

        // Test payload - using v2 payouts format with channel_properties
        $payload = [
            'reference_id' => 'test_' . time(),
            'currency' => 'PHP',
            'channel_code' => 'PH_BPI',
            'amount' => 100,
            'description' => 'Test disbursement from Qricket',
            'channel_properties' => [
                'account_number' => '1234567890',
                'account_holder_name' => 'Test Account Holder',
            ],
        ];

        $this->info('📤 Payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));
        $this->newLine();

        try {
            $this->info('⏳ Making API call...');
            
            $response = Http::withBasicAuth($apiKey, '')
                ->asJson()
                ->withHeaders([
                    'idempotency-key' => 'test_' . time(),
                ])
                ->post('https://api.xendit.co/v2/payouts', $payload);

            $this->newLine();
            $this->info('📥 Response Status: ' . $response->status());
            $this->info('📥 Response Headers:');
            foreach ($response->headers() as $key => $value) {
                $this->line("  {$key}: " . (is_array($value) ? implode(', ', $value) : $value));
            }
            
            $this->newLine();
            $this->info('📄 Response Body:');
            $this->line($response->body());

            if ($response->successful()) {
                $this->newLine();
                $this->info('✅ SUCCESS!');
            } else {
                $this->newLine();
                $this->error('❌ FAILED!');
            }

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Exception: ' . $e->getMessage());
        }

        return 0;
    }
}

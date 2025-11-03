<?php

namespace App\Console\Commands\Xendit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckXenditAccount extends Command
{
    protected $signature = 'xendit:check-account';
    protected $description = 'Check Xendit account status and available APIs';

    public function handle()
    {
        $this->info('🔍 Checking Xendit Account Status...');
        $this->newLine();

        $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
        
        if (!$apiKey) {
            $this->error('❌ Xendit API key not configured!');
            $this->line('Please set XENDIT_SECRET_KEY or XENDIT_API_KEY in your .env file');
            return 1;
        }

        $this->info("🔑 API Key: " . substr($apiKey, 0, 8) . "...");
        $this->newLine();

        // Test different endpoints to see what's available
        $endpoints = [
            'https://api.xendit.co/v2/invoices' => 'Invoices API',
            'https://api.xendit.co/v2/payouts' => 'Payouts API',
            'https://api.xendit.co/v2/disbursements' => 'Disbursements API',
            'https://api.xendit.co/v2/balance' => 'Balance API',
            'https://api.xendit.co/v2/account' => 'Account API',
        ];

        $this->info('📡 Testing Available APIs:');
        $this->newLine();

        foreach ($endpoints as $endpoint => $name) {
            $this->line("Testing {$name}...");
            
            try {
                $response = Http::withBasicAuth($apiKey, '')
                    ->asJson()
                    ->get($endpoint);

                if ($response->successful()) {
                    $this->info("  ✅ {$name}: Available");
                } else {
                    $this->warn("  ❌ {$name}: " . $response->status() . " - " . $response->json()['message'] ?? 'Unknown error');
                }
            } catch (\Exception $e) {
                $this->error("  ❌ {$name}: Error - " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('💡 Next Steps:');
        $this->line('1. Check your Xendit dashboard for account verification status');
        $this->line('2. Contact Xendit support to enable payouts/disbursements API');
        $this->line('3. Verify your business documents and compliance requirements');
        $this->line('4. Check if you need to complete KYC/AML verification');

        $this->newLine();
        $this->info('🔗 Useful Links:');
        $this->line('• Xendit Dashboard: https://dashboard.xendit.co/');
        $this->line('• Xendit Support: https://support.xendit.co/');
        $this->line('• API Documentation: https://docs.xendit.co/');

        return 0;
    }
}

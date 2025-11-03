<?php

namespace App\Console\Commands\Xendit;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestXenditApi extends Command
{
    protected $signature = 'test:xendit-api 
                            {endpoint=https://api.xendit.co/payouts : API endpoint to test}
                            {method=POST : HTTP method}
                            {--payload= : JSON payload to send}';

    protected $description = 'Test Xendit API endpoints directly';

    public function handle()
    {
        $this->info('🔗 Testing Xendit API Endpoint...');
        $this->newLine();

        $endpoint = $this->argument('endpoint');
        $method = strtoupper($this->argument('method'));
        $payloadJson = $this->option('payload');

        // Display test parameters
        $this->info('📋 Test Parameters:');
        $this->table(
            ['Parameter', 'Value'],
            [
                ['Endpoint', $endpoint],
                ['Method', $method],
                ['Payload', $payloadJson ?: 'None'],
            ]
        );

        $this->newLine();

        try {
            // Get API key
            $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
            
            if (!$apiKey) {
                $this->error('❌ Xendit API key not configured!');
                $this->line('Please set XENDIT_SECRET_KEY or XENDIT_API_KEY in your .env file');
                return 1;
            }

            $this->info("🔑 API Key: " . substr($apiKey, 0, 8) . "...");
            $this->newLine();

            // Parse payload
            $payload = [];
            if ($payloadJson) {
                $payload = json_decode($payloadJson, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('❌ Invalid JSON payload: ' . json_last_error_msg());
                    return 1;
                }
            } else {
                // Default test payload
                $payload = [
                    'reference_id' => 'test_' . time(),
                    'amount' => 100,
                    'channel_code' => 'BDO_ONLINE_BANKING',
                    'account_name' => 'John Doe',
                    'account_number' => '1234567890',
                    'description' => 'API test',
                    'currency' => 'PHP',
                ];
            }

            $this->info('📤 Sending Request...');
            $this->line('Payload: ' . json_encode($payload, JSON_PRETTY_PRINT));
            $this->newLine();

            // Make request
            $startTime = microtime(true);
            
            $response = Http::withBasicAuth($apiKey, '')
                ->asJson()
                ->withHeaders([
                    'idempotency-key' => 'test_' . time(),
                ])
                ->{strtolower($method)}($endpoint, $payload);
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            // Display results
            $this->newLine();
            
            if ($response->successful()) {
                $this->info('✅ API Request Successful!');
            } else {
                $this->error('❌ API Request Failed!');
            }

            $this->newLine();
            
            $this->table(
                ['Result', 'Value'],
                [
                    ['Status Code', $response->status()],
                    ['Success', $response->successful() ? 'YES' : 'NO'],
                    ['Execution Time', $executionTime . 'ms'],
                    ['Response Size', strlen($response->body()) . ' bytes'],
                ]
            );

            $this->newLine();
            $this->info('📥 Response Headers:');
            foreach ($response->headers() as $key => $value) {
                $this->line("  {$key}: " . (is_array($value) ? implode(', ', $value) : $value));
            }

            $this->newLine();
            $this->info('📄 Response Body:');
            $this->line($response->body());

            // Log the test
            Log::info('Xendit API test completed', [
                'endpoint' => $endpoint,
                'method' => $method,
                'payload' => $payload,
                'status' => $response->status(),
                'success' => $response->successful(),
                'response' => $response->body(),
                'execution_time' => $executionTime,
            ]);

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ API Test Failed!');
            $this->newLine();
            
            $this->table(
                ['Error', 'Value'],
                [
                    ['Error Message', $e->getMessage()],
                    ['Error Code', $e->getCode()],
                ]
            );

            $this->newLine();
            $this->warn('📝 Full error details logged to: storage/logs/laravel.log');
            
            Log::error('Xendit API test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'endpoint' => $endpoint,
                'method' => $method,
                'payload' => $payload ?? [],
            ]);
        }

        $this->newLine();
        $this->info('💡 Usage Examples:');
        $this->line('  php artisan test:xendit-api');
        $this->line('  php artisan test:xendit-api https://api.xendit.co/v2/disbursements');
        $this->line('  php artisan test:xendit-api https://api.xendit.co/payouts POST --payload=\'{"amount":500,"channel_code":"BPI_ONLINE_BANKING"}\'');

        return 0;
    }
}

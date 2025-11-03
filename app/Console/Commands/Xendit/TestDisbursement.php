<?php

namespace App\Console\Commands\Xendit;

use App\Services\XenditDisbursementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestDisbursement extends Command
{
    protected $signature = 'test:disbursement 
                            {amount=100 : Amount to disburse}
                            {bank_code=BDO_ONLINE_BANKING : Bank code to use}
                            {account_number=1234567890 : Account number}
                            {account_name=John Doe : Account holder name}
                            {description=Test disbursement : Description}';

    protected $description = 'Test Xendit disbursement functionality';

    public function handle()
    {
        $this->info('🚀 Starting Xendit Disbursement Test...');
        $this->newLine();

        // Get parameters
        $amount = (float) $this->argument('amount');
        $bankCode = $this->argument('bank_code');
        $accountNumber = $this->argument('account_number');
        $accountName = $this->argument('account_name');
        $description = $this->argument('description');

        // Display test parameters
        $this->info('📋 Test Parameters:');
        $this->table(
            ['Parameter', 'Value'],
            [
                ['Amount', '₱' . number_format($amount, 2)],
                ['Bank Code', $bankCode],
                ['Account Number', $accountNumber],
                ['Account Name', $accountName],
                ['Description', $description],
            ]
        );

        $this->newLine();

        try {
            // Initialize service
            $service = new XenditDisbursementService();
            
            // Generate external ID
            $externalId = 'test_' . time() . '_' . rand(1000, 9999);
            
            $this->info("🆔 External ID: {$externalId}");
            $this->newLine();

            // Test disbursement
            $this->info('⏳ Testing disbursement...');
            
            $startTime = microtime(true);
            $result = $service->disburse(
                $externalId,
                $amount,
                $bankCode,
                $accountNumber,
                $accountName,
                $description
            );
            $endTime = microtime(true);
            
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            // Display results
            $this->newLine();
            $this->info('✅ Disbursement Test Completed Successfully!');
            $this->newLine();
            
            $this->table(
                ['Result', 'Value'],
                [
                    ['Status', 'SUCCESS'],
                    ['Disbursement ID', $result],
                    ['Execution Time', $executionTime . 'ms'],
                    ['External ID', $externalId],
                ]
            );

            $this->newLine();
            $this->info('📝 Check the logs for detailed information: storage/logs/laravel.log');

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Disbursement Test Failed!');
            $this->newLine();
            
            $this->table(
                ['Error', 'Value'],
                [
                    ['Status', 'FAILED'],
                    ['Error Message', $e->getMessage()],
                    ['Error Code', $e->getCode()],
                ]
            );

            $this->newLine();
            $this->warn('📝 Full error details logged to: storage/logs/laravel.log');
            
            // Log the error
            Log::error('Disbursement test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'parameters' => [
                    'amount' => $amount,
                    'bank_code' => $bankCode,
                    'account_number' => $accountNumber,
                    'account_name' => $accountName,
                    'description' => $description,
                ]
            ]);
        }

        $this->newLine();
        $this->info('🔧 Available Bank Codes:');
        $this->newLine();
        
        $bankCodes = [
            'BANK_TRANSFER' => 'Bank Transfer (Generic)',
            'BDO_ONLINE_BANKING' => 'BDO Bank Transfer',
            'BPI_ONLINE_BANKING' => 'BPI Online Banking',
            'METROBANK_ONLINE_BANKING' => 'Metrobank Bank Transfer',
            'PNB_ONLINE_BANKING' => 'PNB Bank Transfer',
            'RCBC_ONLINE_BANKING' => 'RCBC Online Banking',
            'SECURITY_BANK_ONLINE_BANKING' => 'Security Bank Bank Transfer',
            'UNIONBANK_ONLINE_BANKING' => 'UnionBank Online Banking',
            'CHINABANK_ONLINE_BANKING' => 'Chinabank Online Banking',
            'PSBANK_ONLINE_BANKING' => 'PSBank Bank Transfer',
            'LANDBANK_ONLINE_BANKING' => 'LandBank Bank Transfer',
            'MAYBANK_ONLINE_BANKING' => 'Maybank Bank Transfer',
            'BOC_ONLINE_BANKING' => 'Bank of China Bank Transfer',
            'ROBINSONS_BANK_ONLINE_BANKING' => 'Robinsons Bank Bank Transfer',
            'INSTAPAY_ONLINE_BANKING' => 'InstaPay Bank Transfer',
            'PESONET_ONLINE_BANKING' => 'PESONet Bank Transfer',
        ];

        $this->table(
            ['Bank Code', 'Description'],
            collect($bankCodes)->map(function ($description, $code) {
                return [$code, $description];
            })->toArray()
        );

        $this->newLine();
        $this->info('💡 Usage Examples:');
        $this->line('  php artisan test:disbursement');
        $this->line('  php artisan test:disbursement 500 BPI_ONLINE_BANKING 9876543210 "Jane Smith" "Payment test"');
        $this->line('  php artisan test:disbursement 1000 METROBANK_ONLINE_BANKING 1111111111 "Test User" "Sample disbursement"');

        return 0;
    }
}

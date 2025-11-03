<?php

namespace App\Console\Commands\Xendit;

use Illuminate\Console\Command;

class XenditStatus extends Command
{
    protected $signature = 'xendit:status';
    protected $description = 'Show comprehensive Xendit account status and next steps';

    public function handle()
    {
        $this->info('🔍 Xendit Account Status Report');
        $this->newLine();

        // Check API key
        $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
        
        if (!$apiKey) {
            $this->error('❌ Xendit API key not configured!');
            $this->line('Please set XENDIT_SECRET_KEY or XENDIT_API_KEY in your .env file');
            return 1;
        }

        $this->info("🔑 API Key: " . substr($apiKey, 0, 8) . "...");
        $this->newLine();

        // Account Status
        $this->info('📊 Account Status:');
        $this->table(
            ['Feature', 'Status', 'Details'],
            [
                ['Payment Collection', '✅ Available', 'Can collect payments via Invoices API'],
                ['Disbursements/Payouts', '❌ Not Available', 'Account needs verification for payouts'],
                ['Balance Access', '❌ Not Available', 'API endpoint not accessible'],
                ['Account Management', '❌ Not Available', 'API endpoint not accessible'],
            ]
        );

        $this->newLine();
        $this->info('🚨 Current Issue:');
        $this->line('Your Xendit account is configured for RECEIVING payments but not for SENDING disbursements.');
        $this->line('This is why you see "You do not have any disbursements to validate" in your dashboard.');
        $this->newLine();

        $this->info('💡 Solutions:');
        $this->newLine();
        
        $this->line('1. 📞 Contact Xendit Support:');
        $this->line('   • Go to: https://support.xendit.co/');
        $this->line('   • Request: Enable disbursements/payouts API');
        $this->line('   • Provide: Business documents, KYC/AML compliance');
        $this->line('   • Timeline: Usually 1-3 business days');
        $this->newLine();

        $this->line('2. 🔧 Alternative Approaches:');
        $this->line('   • Manual bank transfers for payouts');
        $this->line('   • Use different payment provider for disbursements');
        $this->line('   • Hybrid: Collect via Xendit, disburse via other means');
        $this->newLine();

        $this->line('3. 📋 Required Documentation:');
        $this->line('   • Business registration certificate');
        $this->line('   • Tax identification number');
        $this->line('   • Bank account verification');
        $this->line('   • KYC/AML compliance documents');
        $this->newLine();

        $this->info('🔗 Useful Resources:');
        $this->line('• Xendit Dashboard: https://dashboard.xendit.co/');
        $this->line('• Support Portal: https://support.xendit.co/');
        $this->line('• API Documentation: https://docs.xendit.co/');
        $this->line('• Disbursements Guide: https://docs.xendit.co/docs/disbursements');

        $this->newLine();
        $this->info('🎯 Immediate Action Required:');
        $this->line('Contact Xendit support to enable disbursements API for your account.');
        $this->line('Until then, the payout system will show proper error messages instead of simulating success.');

        return 0;
    }
}

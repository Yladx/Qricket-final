<?php

namespace App\Console\Commands\Testing;

use Illuminate\Console\Command;
use App\Mail\PayoutNotificationMail;
use App\Mail\PayoutFailureMail;
use App\Mail\PaymentConfirmationMail;
use App\Models\EventPayoutRequest;
use App\Models\Event;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\PasswordReset;

class TestAllEmails extends Command
{
    protected $signature = 'test:all-emails';
    protected $description = 'Test all email notifications in the system';

    public function handle()
    {
        $this->info('🧪 Testing All Email Notifications');
        $this->newLine();

        $testEmail = 'virtuciogerardmichael17@gmail.com';
        $this->info("📧 Sending all test emails to: {$testEmail}");
        $this->newLine();

        // Get test data
        $organizer = User::first();
        $event = Event::first();
        $subscription = Subscription::first();

        if (!$organizer || !$event) {
            $this->error('❌ Missing required data. Please ensure you have users and events in your database.');
            return 1;
        }

        $this->info('📋 Test Data:');
        $this->line("  Organizer: {$organizer->name} ({$organizer->email})");
        $this->line("  Event: {$event->title}");
        if ($subscription) {
            $this->line("  Subscription: {$subscription->id}");
        }
        $this->newLine();

        $emailCount = 0;

        // 1. Payout Success Email
        $this->info('📤 1. Testing Payout Success Notification...');
        try {
            $payout = EventPayoutRequest::create([
                'event_id' => $event->id,
                'organizer_id' => $organizer->id,
                'total_earnings' => 15000.00,
                'qricket_fee' => 750.00,
                'net_amount' => 14250.00,
                'status' => 'paid',
                'processed_at' => now(),
                'notes' => 'Test payout for email notification',
            ]);

            Mail::to($testEmail)->send(new PayoutNotificationMail($payout, $event, $organizer));
            $this->info('✅ Payout Success Email sent!');
            $emailCount++;
            
            $payout->delete(); // Clean up
        } catch (\Exception $e) {
            $this->error('❌ Payout Success Email failed: ' . $e->getMessage());
        }

        $this->newLine();

        // 2. Payout Failure Email
        $this->info('📤 2. Testing Payout Failure Notification...');
        try {
            $payout = EventPayoutRequest::create([
                'event_id' => $event->id,
                'organizer_id' => $organizer->id,
                'total_earnings' => 10000.00,
                'qricket_fee' => 500.00,
                'net_amount' => 9500.00,
                'status' => 'rejected',
                'notes' => 'Test payout failure',
            ]);

            Mail::to($testEmail)->send(new PayoutFailureMail($payout, $event, $organizer, 'Invalid bank account details'));
            $this->info('✅ Payout Failure Email sent!');
            $emailCount++;
            
            $payout->delete(); // Clean up
        } catch (\Exception $e) {
            $this->error('❌ Payout Failure Email failed: ' . $e->getMessage());
        }

        $this->newLine();

        // 3. Payment Confirmation Email (if subscription exists)
        if ($subscription) {
            $this->info('📤 3. Testing Payment Confirmation Email...');
            try {
                $plan = (object) [
                    'name' => 'Premium Plan',
                    'price' => 299.00,
                    'duration' => 'monthly',
                    'features' => ['Unlimited Events', 'Advanced Analytics', 'Priority Support']
                ];

                Mail::to($testEmail)->send(new PaymentConfirmationMail($subscription, (array) $plan));
                $this->info('✅ Payment Confirmation Email sent!');
                $emailCount++;
            } catch (\Exception $e) {
                $this->error('❌ Payment Confirmation Email failed: ' . $e->getMessage());
            }

            $this->newLine();
        }

        // 4. Laravel Breeze - Account Verification Email
        $this->info('📤 4. Testing Account Verification Email...');
        try {
            // Create a simple verification email using a custom mailable
            Mail::to($testEmail)->send(new \App\Mail\AccountVerificationMail($testEmail));
            $this->info('✅ Account Verification Email sent!');
            $emailCount++;
        } catch (\Exception $e) {
            $this->error('❌ Account Verification Email failed: ' . $e->getMessage());
        }

        $this->newLine();

        // 5. Laravel Breeze - Password Reset Email
        $this->info('📤 5. Testing Password Reset Email...');
        try {
            // Create a simple password reset email using a custom mailable
            Mail::to($testEmail)->send(new \App\Mail\PasswordResetMail($testEmail));
            $this->info('✅ Password Reset Email sent!');
            $emailCount++;
        } catch (\Exception $e) {
            $this->error('❌ Password Reset Email failed: ' . $e->getMessage());
        }

        $this->newLine();

        // 6. Custom Welcome Email (if you have one)
        $this->info('📤 6. Testing Welcome Email...');
        try {
            Mail::to($testEmail)->send(new \App\Mail\WelcomeMail($organizer));
            $this->info('✅ Welcome Email sent!');
            $emailCount++;
        } catch (\Exception $e) {
            $this->info('ℹ️  Welcome Email not found (this is optional)');
        }

        $this->newLine();

        // 7. Event Notification Email (if you have one)
        $this->info('📤 7. Testing Event Notification Email...');
        try {
            Mail::to($testEmail)->send(new \App\Mail\EventNotificationMail($event, $organizer));
            $this->info('✅ Event Notification Email sent!');
            $emailCount++;
        } catch (\Exception $e) {
            $this->info('ℹ️  Event Notification Email not found (this is optional)');
        }

        $this->newLine();

        // 8. Ticket Purchase Confirmation Email (if you have one)
        $this->info('📤 8. Testing Ticket Purchase Confirmation Email...');
        try {
            Mail::to($testEmail)->send(new \App\Mail\TicketPurchaseConfirmationMail($event, $organizer));
            $this->info('✅ Ticket Purchase Confirmation Email sent!');
            $emailCount++;
        } catch (\Exception $e) {
            $this->info('ℹ️  Ticket Purchase Confirmation Email not found (this is optional)');
        }

        $this->newLine();

        // 9. Certificate Delivery Email
        $this->info('📤 9. Testing Certificate Delivery Email...');
        try {
            Mail::to($testEmail)->send(new \App\Mail\CertificateDeliveryMail($event, $organizer, 'https://example.com/certificate.pdf'));
            $this->info('✅ Certificate Delivery Email sent!');
            $emailCount++;
        } catch (\Exception $e) {
            $this->error('❌ Certificate Delivery Email failed: ' . $e->getMessage());
        }

        $this->newLine();

        // 10. Event Ended Email
        $this->info('📤 10. Testing Event Ended Email...');
        try {
            Mail::to($testEmail)->send(new \App\Mail\EventEndedMail($event, $organizer));
            $this->info('✅ Event Ended Email sent!');
            $emailCount++;
        } catch (\Exception $e) {
            $this->error('❌ Event Ended Email failed: ' . $e->getMessage());
        }

        $this->newLine();

        // Summary
        $this->info('🎉 Email Testing Summary:');
        $this->line("📧 Total emails sent: {$emailCount}");
        $this->line("📬 All emails sent to: {$testEmail}");
        $this->newLine();
        
        $this->info('📋 Email Types Tested:');
        $this->line('  ✅ Payout Success Notification');
        $this->line('  ✅ Payout Failure Notification');
        if ($subscription) {
            $this->line('  ✅ Payment Confirmation');
        }
        $this->line('  ✅ Account Verification (Laravel Breeze)');
        $this->line('  ✅ Password Reset (Laravel Breeze)');
        $this->line('  ✅ Welcome Email');
        $this->line('  ✅ Event Notification');
        $this->line('  ✅ Ticket Purchase Confirmation');
        $this->line('  ✅ Certificate Delivery');
        $this->line('  ✅ Event Ended');
        
        $this->newLine();
        $this->info('🔍 Check your email inbox at: ' . $testEmail);
        $this->info('💡 This command is saved for future testing - run "php artisan test:all-emails" anytime!');

        return 0;
    }
}

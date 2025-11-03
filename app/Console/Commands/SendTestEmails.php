<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Event;
use App\Mail\{
    WelcomeMail,
    TicketPurchaseConfirmationMail,
    EventNotificationMail,
    EventEndedMail,
    PaymentConfirmationMail,
    InvoiceMail,
    AccountVerificationMail,
    PasswordResetMail,
    CertificateDeliveryMail,
    TicketInvoiceMail,
    AdditionalTicketPurchaseConfirmation,
    RefundNotificationMail,
    PayoutNotificationMail,
    PayoutFailureMail
};

class SendTestEmails extends Command
{
    protected $signature = 'test:emails {email=virtuciogerardmichael17@gmail.com}';
    protected $description = 'Send test emails of all types to verify email design';

    public function handle()
    {
        $email = $this->argument('email');
        $this->info("Sending test emails to: {$email}");

        // Get or create a test user
        $user = User::first() ?? $this->createTestUser();
        $event = Event::first() ?? $this->createTestEvent($user);

        try {
            // 1. Welcome Mail
            Mail::to($email)->send(new WelcomeMail($user));
            $this->info("✓ Welcome mail sent");

            // 2. Ticket Purchase Confirmation
            Mail::to($email)->send(new TicketPurchaseConfirmationMail($event, $user));
            $this->info("✓ Ticket Purchase Confirmation sent");

            // 3. Event Notification
            Mail::to($email)->send(new EventNotificationMail($event, $user));
            $this->info("✓ Event Notification sent");

            // 4. Event Ended Mail
            Mail::to($email)->send(new EventEndedMail($event, $user));
            $this->info("✓ Event Ended mail sent");

            // 5. Payment Confirmation (for subscription)
            if ($user->subscriptions()->exists()) {
                $subscription = $user->subscriptions()->latest()->first();
                $planDetails = [
                    'name' => $subscription->plan->name ?? 'Test Plan',
                    'price' => $subscription->plan->price ?? 0,
                ];
                Mail::to($email)->send(new PaymentConfirmationMail($subscription, $planDetails));
                $this->info("✓ Payment Confirmation sent");
            }

            // 6. Invoice Mail
            if ($user->subscriptions()->exists()) {
                $subscription = $user->subscriptions()->latest()->first();
                $invoiceUrl = route('subscription.invoice', $subscription->id);
                $plan = $subscription->plan ?? (object)['name' => 'Test Plan'];
                Mail::to($email)->send(new InvoiceMail($subscription, $invoiceUrl, $plan));
                $this->info("✓ Invoice mail sent");
            }

            // 7. Account Verification
            Mail::to($email)->send(new AccountVerificationMail($email));
            $this->info("✓ Account Verification sent");

            // 8. Password Reset
            Mail::to($email)->send(new PasswordResetMail($email));
            $this->info("✓ Password Reset sent");

            // 9. Certificate Delivery
            $certificateUrl = route('events.show', $event->id);
            Mail::to($email)->send(new CertificateDeliveryMail($event, $user, $certificateUrl));
            $this->info("✓ Certificate Delivery sent");

            // 10. Ticket Invoice
            $this->info("Skipping Ticket Invoice (requires ticket data)");

            // 11. Additional Ticket Purchase
            $this->info("Skipping Additional Ticket Purchase (requires purchase data)");

            // 12. Refund Notification
            $this->info("Skipping Refund Notification (requires refund data)");

            // 13. Payout Notification
            $this->info("Skipping Payout Notification (requires payout data)");

            // 14. Payout Failure
            $this->info("Skipping Payout Failure (requires payout data)");

            $this->info("\n✓ All test emails sent successfully!");
            $this->info("Check your inbox at: {$email}");

        } catch (\Exception $e) {
            $this->error("Error sending emails: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function createTestUser()
    {
        return User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => \Hash::make('password'),
            'organizer' => true,
        ]);
    }

    private function createTestEvent($user)
    {
        return Event::create([
            'user_id' => $user->id,
            'title' => 'Test Event - Sample Event Name',
            'description' => 'This is a test event to demonstrate email templates',
            'category' => 'Music',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(8),
            'location' => 'Test Location, Test City',
            'status' => 'published',
        ]);
    }
}

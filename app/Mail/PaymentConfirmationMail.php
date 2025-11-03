<?php

namespace App\Mail;

use App\Models\Subscription;
use App\Models\TicketPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;
    public $planDetails;
    public $isTicketPurchase = false;

    /**
     * Create a new message instance.
     */
    public function __construct($subscription, array $planDetails)
    {
        $this->subscription = $subscription;
        $this->planDetails = $planDetails;
        
        // Check if this is a ticket purchase
        if ($subscription instanceof TicketPurchase) {
            $this->isTicketPurchase = true;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Confirmation - ' . $this->planDetails['name'] . ' - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-confirmation',
            with: [
                'subscription' => $this->subscription,
                'planDetails' => $this->planDetails,
                'user' => $this->isTicketPurchase ? $this->subscription->user : $this->subscription->user,
                'isTicketPurchase' => $this->isTicketPurchase,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
} 
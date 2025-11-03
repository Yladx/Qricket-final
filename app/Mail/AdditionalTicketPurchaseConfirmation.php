<?php

namespace App\Mail;

use App\Models\TicketLimitPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdditionalTicketPurchaseConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $purchase;

    /**
     * Create a new message instance.
     */
    public function __construct(TicketLimitPurchase $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Additional Ticket Purchase Confirmation - ' . $this->purchase->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.additional-ticket-purchase-confirmation',
            with: [
                'purchase' => $this->purchase,
                'user' => $this->purchase->user,
                'event' => $this->purchase->event,
                'pricing' => $this->purchase->ticketLimitPricing,
            ]
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

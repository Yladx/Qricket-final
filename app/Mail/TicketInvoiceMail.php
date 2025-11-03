<?php

namespace App\Mail;

use App\Models\TicketPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public TicketPurchase $purchase;
    public bool $pending;
    public ?string $invoiceUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(TicketPurchase $purchase, bool $pending = false)
    {
        $this->purchase = $purchase->loadMissing(['event', 'user', 'ticketInformation']);
        $this->pending = $pending;

        if ($pending) {
            $this->invoiceUrl = $this->purchase->payment_details['invoice_url'] ?? null;
        } else {
            // Prefer the first ticket id for the invoice route if available
            $firstTicketId = $this->purchase->payment_details['ticket_ids'][0] ?? ($this->purchase->ticketInformation->first()->id ?? null);
            $this->invoiceUrl = $firstTicketId
                ? route('tickets.invoice', ['purchaseId' => $this->purchase->purchase_id, 'ticketId' => $firstTicketId])
                : null;
        }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subjectPrefix = $this->pending ? 'Your Ticket Invoice (Pending)' : 'Your Ticket Invoice (Paid)';
        $subject = $subjectPrefix . ' - ' . ($this->purchase->event->title ?? 'Event');

        return $this->subject($subject)
            ->view('emails.tickets.invoice')
            ->with([
                'purchase' => $this->purchase,
                'pending' => $this->pending,
                'invoiceUrl' => $this->invoiceUrl,
            ]);
    }
}



<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutFailureMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payout;
    public $event;
    public $organizer;
    public $failureReason;

    /**
     * Create a new message instance.
     */
    public function __construct($payout, $event, $organizer, $failureReason)
    {
        $this->payout = $payout;
        $this->event = $event;
        $this->organizer = $organizer;
        $this->failureReason = $failureReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payout Failed - ' . $this->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payout-failure',
            with: [
                'payout' => $this->payout,
                'event' => $this->event,
                'organizer' => $this->organizer,
                'failureReason' => $this->failureReason,
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
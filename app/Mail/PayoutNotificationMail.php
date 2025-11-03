<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payout;
    public $event;
    public $organizer;

    /**
     * Create a new message instance.
     */
    public function __construct($payout, $event, $organizer)
    {
        $this->payout = $payout;
        $this->event = $event;
        $this->organizer = $organizer;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payout Processed - ' . $this->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payout-notification',
            with: [
                'payout' => $this->payout,
                'event' => $this->event,
                'organizer' => $this->organizer,
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
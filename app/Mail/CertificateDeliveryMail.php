<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;
use App\Models\User;

class CertificateDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $participant;
    public $certificateUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Event $event, User $participant, $certificateUrl = null)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->certificateUrl = $certificateUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Certificate is Ready - ' . $this->event->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate-delivery',
            with: [
                'event' => $this->event,
                'participant' => $this->participant,
                'certificateUrl' => $this->certificateUrl,
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
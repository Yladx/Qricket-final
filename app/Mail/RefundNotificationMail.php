<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $refundData;
    public $user;
    public $refundType;

    /**
     * Create a new message instance.
     */
    public function __construct($refundData, $user, $refundType = 'general')
    {
        $this->refundData = $refundData;
        $this->user = $user;
        $this->refundType = $refundType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Refund Processed - ' . ucfirst($this->refundType),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.refund-notification',
            with: [
                'refundData' => $this->refundData,
                'user' => $this->user,
                'refundType' => $this->refundType,
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

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $verificationUrl;

    public function __construct($email, $verificationUrl = null)
    {
        $this->email = $email;
        $this->verificationUrl = $verificationUrl ?? url('/verify-email?token=test-verification-token');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address - Qricket',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-verification',
            with: [
                'email' => $this->email,
                'verificationUrl' => $this->verificationUrl,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
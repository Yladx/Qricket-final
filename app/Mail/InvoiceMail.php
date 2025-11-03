<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;
    public $invoiceUrl;
    public $plan;
    public $status;

    /**
     * Create a new message instance.
     */
    public function __construct($subscription, $invoiceUrl, $plan, $status = 'success')
    {
        $this->subscription = $subscription;
        $this->invoiceUrl = $invoiceUrl;
        $this->plan = $plan;
        $this->status = $status;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->status === 'failed' 
            ? 'Payment Failed - Invoice from ' . config('app.name')
            : 'Your Invoice from ' . config('app.name');
            
        return $this->subject($subject)
                    ->view('emails.invoice')
                    ->with([
                        'subscription' => $this->subscription,
                        'invoiceUrl' => $this->invoiceUrl,
                        'plan' => $this->plan,
                        'status' => $this->status,
                    ]);
    }
} 
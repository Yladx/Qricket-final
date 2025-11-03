<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\OrganizerVerification;

class OrganizerVerificationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $verification;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(OrganizerVerification $verification, User $user)
    {
        $this->verification = $verification;
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Organizer Verification Request - Qricket')
            ->greeting('Hello Admin!')
            ->line('A new organizer verification request has been submitted.')
            ->line('**Organizer Details:**')
            ->line('Name: ' . $this->verification->full_name)
            ->line('Email: ' . $this->user->email)
            ->line('Organization: ' . $this->verification->organization_name)
            ->line('Document Type: ' . ucfirst(str_replace('_', ' ', $this->verification->document_type)))
            ->line('Submitted: ' . $this->verification->created_at->format('M d, Y H:i'))
            ->action('Review Verification', route('admin.organizer-verifications.show', $this->verification->id))
            ->line('Please review the verification request and documents.')
            ->salutation('Best regards, Qricket System');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'organizer_verification_submitted',
            'verification_id' => $this->verification->id,
            'user_id' => $this->user->id,
            'user_name' => $this->verification->full_name,
            'user_email' => $this->user->email,
            'organization_name' => $this->verification->organization_name,
            'document_type' => $this->verification->document_type,
            'submitted_at' => $this->verification->created_at,
            'message' => 'New organizer verification request from ' . $this->verification->full_name,
            'action_url' => route('admin.organizer-verifications.show', $this->verification->id),
        ];
    }
}


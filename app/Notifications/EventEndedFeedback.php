<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Event;

class EventEndedFeedback extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $feedbackUrl = route('events.feedback.show', $this->event->id);
        
        return (new MailMessage)
            ->subject('🎉 ' . $this->event->title . ' - Event Ended! Please Share Your Feedback')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Thank you for attending **' . $this->event->title . '**!')
            ->line('The event has now ended, and we would love to hear about your experience.')
            ->line('Your feedback helps us improve future events and ensures we continue to provide great experiences for our community.')
            ->action('Share Your Feedback', $feedbackUrl)
            ->line('**What you can share:**')
            ->line('• Rate your overall experience (1-5 stars)')
            ->line('• Tell us what you enjoyed most')
            ->line('• Suggest improvements for future events')
            ->line('• Share any other thoughts or comments')
            ->line('It only takes 2 minutes, and your input is invaluable to us!')
            ->salutation('Best regards,')
            ->line('The ' . config('app.name') . ' Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'message' => 'Event ended - feedback requested',
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

class EventFeedbackRequest extends Notification
{
    use Queueable;

    public function __construct(private readonly Event $event, private readonly string $feedbackUrl)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $userName = $notifiable->full_name ?? ($notifiable->name ?? 'there');
        $eventTitle = $this->event->title ?? 'the event';
        $eventWhen = optional($this->event->end_date ?: $this->event->start_date)->format('M d, Y');
        $eventLocation = $this->event->location ?? '';
        $thumb = $this->event->thumbnail_url ?? $this->event->poster_url ?? '';

        return (new MailMessage)
            ->subject("We'd love your feedback for " . $eventTitle)
            ->view('emails.event-feedback', [
                'userName' => $userName,
                'eventTitle' => $eventTitle,
                'eventWhen' => $eventWhen,
                'eventLocation' => $eventLocation,
                'eventThumbnail' => $thumb,
                'feedbackUrl' => $this->feedbackUrl,
            ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\EventNews;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventNewsPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Event $event, public EventNews $news)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Update from ' . $this->event->name)
            ->greeting('New update: ' . $this->news->title)
            ->line(strip_tags(str(\Illuminate\Support\Str::limit($this->news->body, 180))))
            ->action('View Update', route('events.news.show', [$this->event, $this->news]))
            ->line('You are receiving this because you saved or purchased tickets for this event.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_name' => $this->event->name,
            'news_id' => $this->news->id,
            'title' => $this->news->title,
        ];
    }
}

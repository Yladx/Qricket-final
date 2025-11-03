<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Event;
use App\Models\EventNews;
use App\Models\Notification as CustomNotification;

class EventNewsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $event;
    protected $news;

    /**
     * Create a new notification instance.
     */
    public function __construct(Event $event, EventNews $news)
    {
        $this->event = $event;
        $this->news = $news;
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
        return (new MailMessage)
            ->subject('Event News Update - ' . $this->event->title)
            ->view('emails.event-news-notification', [
                'event' => $this->event,
                'news' => $this->news,
                'user' => $notifiable
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Store in our custom notifications table
        CustomNotification::create([
            'user_id' => $notifiable->id,
            'type' => 'event',
            'action' => 'news',
            'reference_id' => $this->event->id,
            'title' => 'Event News Update',
            'message' => 'New news from "' . $this->event->title . '": ' . $this->news->title,
            'is_read' => false,
        ]);

        return [
            'type' => 'event_news',
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'news_id' => $this->news->id,
            'news_title' => $this->news->title,
            'news_content' => $this->news->content,
            'news_image_url' => $this->news->image_url,
            'message' => 'New news update for ' . $this->event->title . ': ' . $this->news->title,
        ];
    }
}

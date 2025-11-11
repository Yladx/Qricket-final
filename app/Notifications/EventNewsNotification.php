<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Event;
use App\Models\EventNews;

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

}

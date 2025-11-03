<?php

namespace App\Events;

use App\Models\EventNews;
use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventNewsPublished implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public array $notification;

    public function __construct(int $userId, EventNews $news, Event $event)
    {
        $this->userId = $userId;
        $this->notification = [
            'message' => "New news from '{$event->title}': {$news->title}",
            'type' => 'info',
            'title' => 'Event News Update',
            'event_id' => $event->id,
            'news_id' => $news->id,
            'event_title' => $event->title,
            'news_title' => $news->title,
        ];
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'user.notification';
    }
}

<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message;
    public string $type;
    public ?string $title;
    public ?int $notificationId;

    public function __construct(string $message, string $type = 'info', ?string $title = null, ?int $notificationId = null)
    {
        $this->message = $message;
        $this->type = $type;
        $this->title = $title;
        $this->notificationId = $notificationId;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('admin.notifications');
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
            'title' => $this->title,
            'id' => $this->notificationId,
            'received_at' => now()->toISOString(),
        ];
    }
}



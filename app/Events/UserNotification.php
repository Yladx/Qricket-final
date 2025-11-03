<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public array $notification;

    public function __construct(int $userId, string $message, string $type = 'info', ?string $title = null)
    {
        $this->userId = $userId;
        $this->notification = [
            'message' => $message,
            'type' => $type,
            'title' => $title,
        ];
        
        // Debug logging
        \Log::info('UserNotification event created', [
            'user_id' => $userId,
            'notification' => $this->notification
        ]);
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





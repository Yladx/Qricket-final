<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserActivity implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $activity;
    public int $userCount;
    public int $guestCount;
    public array $adminIds;

    /**
     * Create a new event instance.
     */
    public function __construct(string $activity, int $userCount, int $guestCount, array $adminIds = [])
    {
        $this->activity = $activity;
        $this->userCount = $userCount;
        $this->guestCount = $guestCount;
        $this->adminIds = $adminIds;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('admin-dashboard');
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.activity';
    }

    /**
     * The data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'activity' => $this->activity,
            'userCount' => $this->userCount,
            'guestCount' => $this->guestCount,
            'admins' => $this->adminIds,
        ];
    }
}





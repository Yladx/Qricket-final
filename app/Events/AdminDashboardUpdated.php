<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminDashboardUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $adminId;
    public $data;
    public $type;

    /**
     * Create a new event instance.
     */
    public function __construct($adminId, $data, $type = 'update')
    {
        $this->adminId = $adminId;
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->adminId === 'all') {
            return [
                new Channel('admin-dashboard')
            ];
        }

        return [
            new PrivateChannel('admin-dashboard.' . $this->adminId)
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'admin_id' => $this->adminId,
            'data' => $this->data,
            'type' => $this->type,
            'timestamp' => now()->toISOString()
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'dashboard.updated';
    }
}

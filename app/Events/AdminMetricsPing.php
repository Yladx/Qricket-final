<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminMetricsPing implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $reason;

    public function __construct(string $reason = 'metrics_update')
    {
        $this->reason = $reason;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('admin-dashboard');
    }

    public function broadcastAs(): string
    {
        return 'metrics.update';
    }

    public function broadcastWith(): array
    {
        return [ 'reason' => $this->reason ];
    }
}



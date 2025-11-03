<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesReportUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eventId;
    public $organizerId;
    public $salesData;
    public $eventStats;

    /**
     * Create a new event instance.
     */
    public function __construct($eventId, $organizerId, $salesData, $eventStats)
    {
        $this->eventId = $eventId;
        $this->organizerId = $organizerId;
        $this->salesData = $salesData;
        $this->eventStats = $eventStats;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('organizer-dashboard.' . $this->organizerId),
            new Channel('sales-updates'),
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
            'event_id' => $this->eventId,
            'organizer_id' => $this->organizerId,
            'sales_data' => $this->salesData,
            'event_stats' => $this->eventStats,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'sales.report.updated';
    }
}

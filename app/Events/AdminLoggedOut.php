<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminLoggedOut implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $adminId;
    public string $reason;
    public string $message;
    public array $metadata;

    public function __construct(string $adminId, string $reason = 'session_conflict', array $metadata = [])
    {
        $this->adminId = $adminId;
        $this->reason = $reason;
        $this->metadata = $metadata;
        
        // Set appropriate message based on reason
        switch ($reason) {
            case 'session_conflict':
                $this->message = 'You have been logged out due to login from another location.';
                break;
            case 'manual_logout':
                $this->message = 'You have been logged out by another session.';
                break;
            case 'security_logout':
                $this->message = 'You have been logged out for security reasons.';
                break;
            default:
                $this->message = 'You have been logged out.';
        }
        
    }

    public function broadcastOn(): Channel
    {
        return new Channel('admin-session.' . $this->adminId);
    }

    public function broadcastAs(): string
    {
        return 'admin.logged.out';
    }

    public function broadcastWith(): array
    {
        return [
            'admin_id' => $this->adminId,
            'reason' => $this->reason,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportReplyAdded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $replyId;
    public int $userId;
    public string $ticketNumber;
    public string $message;
    public bool $isAdminReply;
    public string $author;
    public string $createdAt;

    public function __construct(
        int $replyId,
        int $userId,
        string $ticketNumber,
        string $message,
        bool $isAdminReply,
        string $author,
        string $createdAt
    ) {
        $this->replyId = $replyId;
        $this->userId = $userId;
        $this->ticketNumber = $ticketNumber;
        $this->message = $message;
        $this->isAdminReply = $isAdminReply;
        $this->author = $author;
        $this->createdAt = $createdAt;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId),
            new PrivateChannel('admin.support.' . $this->ticketNumber),
        ];
    }

    public function broadcastAs(): string
    {
        return 'support.reply.added';
    }

    public function broadcastWith(): array
    {
        return [
            'reply_id' => $this->replyId,
            'user_id' => $this->userId,
            'ticket_number' => $this->ticketNumber,
            'message' => $this->message,
            'is_admin_reply' => $this->isAdminReply,
            'author' => $this->author,
            'created_at' => $this->createdAt,
        ];
    }
}

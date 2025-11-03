<?php

namespace App\Notifications;

use App\Models\TicketShare;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TicketShareAccepted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TicketShare $share)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'ticket_share_accepted',
            'title' => 'Ticket Share Accepted',
            'message' => "{$this->share->recipient->full_name} accepted your ticket share for '{$this->share->event->title}'.",
            'share_id' => $this->share->id,
            'event_id' => $this->share->event_id,
            'event_title' => $this->share->event->title,
            'recipient_id' => $this->share->recipient_id,
            'shared_ticket_id' => $this->share->shared_ticket_id,
            'status' => $this->share->status,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'ticket_share_accepted',
            'share_id' => $this->share->id,
            'event_id' => $this->share->event_id,
            'event_title' => $this->share->event->title,
            'message' => 'Ticket share accepted',
            'created_at' => now()->toISOString(),
        ]);
    }
}



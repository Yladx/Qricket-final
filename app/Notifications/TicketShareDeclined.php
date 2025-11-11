<?php

namespace App\Notifications;

use App\Models\TicketShare;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TicketShareDeclined extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TicketShare $share)
    {
    }

    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'ticket_share_declined',
            'share_id' => $this->share->id,
            'event_id' => $this->share->event_id,
            'event_title' => $this->share->event->title,
            'message' => 'Ticket share declined',
            'created_at' => now()->toISOString(),
        ]);
    }
}



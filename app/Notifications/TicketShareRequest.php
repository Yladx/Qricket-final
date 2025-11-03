<?php

namespace App\Notifications;

use App\Models\TicketShare;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketShareRequest extends Notification
{
    use Queueable;

    protected $ticketShare;

    /**
     * Create a new notification instance.
     */
    public function __construct(TicketShare $ticketShare)
    {
        $this->ticketShare = $ticketShare;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        try {
            $sharer = $this->ticketShare->sharer;
            $event = $this->ticketShare->event;
            $ticketType = $this->ticketShare->ticketType;

            return (new MailMessage)
                ->subject('New Ticket Share Request')
                ->greeting('Hello ' . $notifiable->first_name . '!')
                ->line($sharer->first_name . ' ' . $sharer->last_name . ' wants to share tickets with you.')
                ->line('**Event:** ' . $event->title)
                ->line('**Ticket Type:** ' . $ticketType->name)
                ->line('**Quantity:** ' . $this->ticketShare->quantity . ' ticket(s)')
                ->line('**Price:** ₱' . number_format($ticketType->price) . ' each')
                ->line('This share request will expire on ' . $this->ticketShare->expires_at->format('M d, Y \a\t g:i A'))
                ->action('View Share Request', route('my-tickets.index'))
                ->line('You can accept or decline this request from your My Tickets page.');
        } catch (\Exception $e) {
            \Log::error('Failed to generate ticket share email notification', [
                'ticket_share_id' => $this->ticketShare->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return (new MailMessage)
                ->subject('New Ticket Share Request')
                ->greeting('Hello ' . $notifiable->first_name . '!')
                ->line('You have received a new ticket share request.')
                ->action('View Share Request', route('my-tickets.index'))
                ->line('Please check your My Tickets page for details.');
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        try {
            return [
                'type' => 'ticket_share_request',
                'ticket_share_id' => $this->ticketShare->id,
                'sharer_name' => $this->ticketShare->sharer->first_name . ' ' . $this->ticketShare->sharer->last_name,
                'event_title' => $this->ticketShare->event->title,
                'ticket_type_name' => $this->ticketShare->ticketType->name,
                'quantity' => $this->ticketShare->quantity,
                'expires_at' => $this->ticketShare->expires_at,
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to generate ticket share array notification', [
                'ticket_share_id' => $this->ticketShare->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'type' => 'ticket_share_request',
                'ticket_share_id' => $this->ticketShare->id,
                'sharer_name' => 'Unknown User',
                'event_title' => 'Event',
                'ticket_type_name' => 'Ticket',
                'quantity' => $this->ticketShare->quantity ?? 1,
                'expires_at' => $this->ticketShare->expires_at,
            ];
        }
    }
}
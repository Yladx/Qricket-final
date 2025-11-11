<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @var Event */
    protected $event;
    /** @var array */
    protected $permissions;
    /** @var string */
    protected $role;

    public function __construct(Event $event, string $role = 'staff', array $permissions = [])
    {
        $this->event = $event;
        $this->role = $role;
        $this->permissions = $permissions;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $eventUrl = route('organizer.events.manage', $this->event);
        $perms = array_keys(array_filter($this->permissions));
        $permList = empty($perms) ? 'No special permissions' : implode(', ', array_map(function ($k) {
            return str_replace('_', ' ', $k);
        }, $perms));

        return (new MailMessage)
            ->subject('You were added to an event: ' . ($this->event->title ?? 'Event'))
            ->greeting('Hello!')
            ->line('You have been added as ' . ucfirst($this->role) . ' for the event "' . ($this->event->title ?? 'Event') . '".')
            ->line('Permissions: ' . $permList)
            ->action('Open Event', $eventUrl)
            ->line('If this was a mistake, please contact the organizer.');
    }
}



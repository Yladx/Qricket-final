<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $notifications = Notification::where('user_id', $userId)
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function go($id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$notification) {
            return redirect()->back();
        }

        // Debug logging
        \Log::info('Notification redirect', [
            'notification_id' => $id,
            'type' => $notification->type,
            'action' => $notification->action,
            'reference_id' => $notification->reference_id,
            'user_id' => Auth::id()
        ]);

        // Handle organizer notifications
        if ($notification->type === 'organizer' && in_array($notification->action, ['verified', 'accept', 'reject'])) {
            return redirect()->route('organizer.verification');
        }

        // Basic routing by type/action (extend as needed)
        if ($notification->type === 'ticket' && $notification->action === 'reply' && $notification->reference_id) {
            return redirect()->route('support.ticket', ['ticketNumber' => $notification->reference_id]);
        }

        // Handle ticket share notifications
        if ($notification->type === 'ticket' && in_array($notification->action, ['share_request', 'share_accepted', 'share_declined']) && $notification->reference_id) {
            $ticketShare = \App\Models\EventTicketShare::find($notification->reference_id);
            if ($ticketShare) {
                return redirect()->route('my-tickets.index');
            }
        }

        // Handle event notifications
        if ($notification->type === 'event' && $notification->reference_id) {
            if ($notification->action === 'news') {
                return redirect()->route('events.show', ['event' => $notification->reference_id, 'section' => 'news']);
            } elseif (in_array($notification->action, ['accept', 'reject'])) {
                return redirect()->route('organizer.events.manage', ['event' => $notification->reference_id]);
            } elseif ($notification->action === 'event_ended') {
                return redirect()->route('events.show', ['event' => $notification->reference_id]);
            } elseif ($notification->action === 'feedback_request') {
                return redirect()->route('events.feedback.show', ['event' => $notification->reference_id]);
            } elseif ($notification->action === 'staff_added') {
                return redirect()->route('organizer.events.manage', ['event' => $notification->reference_id]);
            }
        }

        // Handle support notifications
        if ($notification->type === 'support' && $notification->reference_id) {
            if ($notification->action === 'admin_replied') {
                // Get the support ticket to find the ticket number
                $supportTicket = \App\Models\SupportTicket::find($notification->reference_id);
                if ($supportTicket) {
                    return redirect()->route('support.ticket', ['ticketNumber' => $supportTicket->ticket_number]);
                }
            }
        }

        // Fallback routing based on notification type
        switch ($notification->type) {
            case 'organizer':
                return redirect()->route('organizer.verification');
            case 'ticket':
                return redirect()->route('tickets.my-purchases');
            case 'event':
                return redirect()->route('events.browse');
            case 'support':
                return redirect()->route('help.index');
            default:
                return redirect()->route('notifications.index');
        }
    }

    public function markAsRead($id)
    {
        $updated = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['is_read' => true]);

        return response()->json(['success' => (bool) $updated]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
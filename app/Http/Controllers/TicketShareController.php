<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTicket;
use App\Models\EventTicketInformation;
use App\Models\TicketShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TicketShareController extends Controller
{
    /**
     * Show the ticket sharing interface for an event
     */
    public function show(Event $event)
    {
        $user = Auth::user();
        
        // Get user's tickets for this event, grouped by ticket type
        $userTickets = EventTicketInformation::where('user_id', $user->id)
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->whereHas('ticketPurchase.event', function($query) use ($event) {
                $query->where('id', $event->id);
            })
            ->with(['ticketPurchase.ticketType'])
            ->get()
            ->groupBy('ticketPurchase.ticketType.id');

        // Get pending shares received by this user
        $pendingShares = TicketShare::where('recipient_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', 'pending')
            ->with(['sharer', 'ticketType', 'event'])
            ->get();

        // Get pending shares sent by this user
        $sentShares = TicketShare::where('sharer_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', 'pending')
            ->with(['recipient', 'ticketType', 'event'])
            ->get();

        return view('tickets.share', compact('event', 'userTickets', 'pendingShares', 'sentShares'));
    }

    /**
     * Create a new ticket share
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'ticket_type_id' => 'required|exists:event_tickets,id',
            'recipient_email' => 'required|email',
            'quantity' => 'required|integer|min:1',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $event = Event::findOrFail($request->event_id);
        $ticketType = EventTicket::findOrFail($request->ticket_type_id);

        // Verify the ticket type belongs to the event
        if ($ticketType->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ticket type for this event'
            ], 400);
        }

        // Check if user has enough tickets of this type
        $userTicketCount = EventTicketInformation::where('user_id', $user->id)
            ->whereHas('ticketPurchase', function($query) use ($ticketType) {
                $query->where('status', 'paid')
                      ->where('ticket_type_id', $ticketType->id);
            })
            ->count();

        if ($userTicketCount < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => "You only have {$userTicketCount} tickets of this type. Cannot share {$request->quantity} tickets."
            ], 400);
        }

        // Find recipient by email
        $recipient = User::where('email', $request->recipient_email)->first();
        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'User with this email not found'
            ], 404);
        }

        // Check if recipient is the same as sharer
        if ($recipient->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot share tickets with yourself'
            ], 400);
        }

        // Check if there's already a pending share for this combination
        $existingShare = TicketShare::where('event_id', $event->id)
            ->where('sharer_id', $user->id)
            ->where('recipient_id', $recipient->id)
            ->where('ticket_type_id', $ticketType->id)
            ->where('status', 'pending')
            ->first();

        if ($existingShare) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending share request for this ticket type with this user'
            ], 400);
        }

        // Create the ticket share
        $share = TicketShare::create([
            'event_id' => $event->id,
            'ticket_type_id' => $ticketType->id,
            'sharer_id' => $user->id,
            'recipient_id' => $recipient->id,
            'quantity' => $request->quantity,
            'message' => $request->message,
            'status' => 'pending',
            'expires_at' => now()->addDays(7), // Expire in 7 days
        ]);

        // Send notification to recipient about share request
        try {
            $sharerName = $user->full_name ?? trim("{$user->first_name} {$user->last_name}") ?: $user->email;
            $shareMessage = "{$sharerName} wants to share a ticket for '{$event->title}' with you.";
            
            // Note: We don't use Laravel's notify() here because we have a custom notifications table structure
            // Create in-app notification
            \App\Models\Notification::create([
                'user_id' => $recipient->id,
                'admin_id' => null,
                'type' => 'ticket',
                'action' => 'share_request',
                'reference_id' => $share->id,
                'title' => 'Ticket Share Request',
                'message' => $shareMessage,
                'is_read' => false,
                'data' => [
                    'share_id' => $share->id,
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'sharer_id' => $user->id,
                    'sharer_name' => $sharerName,
                ],
            ]);
            
            // Broadcast real-time notification
            \App\Services\NotificationService::sendUserNotification(
                $recipient->id,
                $shareMessage,
                'info',
                'Ticket Share Request'
            );
            
            \Log::info('Ticket share request notification created', [
                'share_id' => $share->id,
                'recipient_id' => $recipient->id,
                'sharer_id' => $user->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send share request notification', [
                'error' => $e->getMessage(),
                'share_id' => $share->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Ticket share request sent successfully',
            'share' => $share->load(['recipient', 'ticketType'])
        ]);
    }

    /**
     * Accept a ticket share
     */
    public function accept(TicketShare $share)
    {
        $user = Auth::user();

        // Verify the user is the recipient
        if ($share->recipient_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if share can be accepted
        if (!$share->canBeAccepted()) {
            return response()->json([
                'success' => false,
                'message' => 'This share request has expired or is no longer valid'
            ], 400);
        }

        // Check if sharer has any tickets of this type (including checked-in ones)
        $totalTickets = EventTicketInformation::where('user_id', $share->sharer_id)
            ->whereHas('ticketPurchase', function($query) use ($share) {
                $query->where('status', 'paid')
                      ->where('ticket_type_id', $share->ticket_type_id);
            })
            ->count();

        if ($totalTickets === 0) {
            return response()->json([
                'success' => false,
                'message' => 'The sharer no longer has any tickets of this type'
            ], 400);
        }

        // Get available tickets of this type from the sharer
        $availableTickets = EventTicketInformation::where('user_id', $share->sharer_id)
            ->whereHas('ticketPurchase', function($query) use ($share) {
                $query->where('status', 'paid')
                      ->where('ticket_type_id', $share->ticket_type_id);
            })
            ->where(function($query) use ($share) {
                $query->whereNull('shared_by_user_id') // Not shared to others
                      ->orWhere('shared_by_user_id', '!=', $share->sharer_id); // Or received from others
            })
            ->whereNull('checked_in_at') // Not checked in
            ->limit($share->quantity)
            ->get();

        if ($availableTickets->count() < $share->quantity) {
            // Check if all tickets are checked-in
            $checkedInTickets = EventTicketInformation::where('user_id', $share->sharer_id)
                ->whereHas('ticketPurchase', function($query) use ($share) {
                    $query->where('status', 'paid')
                          ->where('ticket_type_id', $share->ticket_type_id);
                })
                ->whereNotNull('checked_in_at')
                ->count();

            if ($checkedInTickets > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot accept this share request. The tickets have already been checked-in and cannot be transferred.'
                ], 400);
            }

            return response()->json([
                'success' => false,
                'message' => 'Not enough available tickets to fulfill this share request. Some tickets may be checked-in or already shared.'
            ], 400);
        }

        // Transfer the tickets - update user_id to make recipient the owner
        foreach ($availableTickets as $ticket) {
            $ticket->update([
                'user_id' => $user->id, // Make recipient the owner
                'shared_by_user_id' => $share->sharer_id, // Keep track of who shared it
                'shared_at' => now(),
            ]);
        }

        // Update the share status
        $share->update([
            'status' => 'accepted',
            'shared_ticket_ids' => $availableTickets->pluck('id')->toArray(),
        ]);

        // Send notification to sharer about acceptance
        try {
            $share->load(['event', 'recipient']);
            $recipientName = $share->recipient->full_name ?? 'Someone';
            $acceptMessage = "{$recipientName} accepted your ticket share for '{$share->event->title}'.";
            
            // Note: We don't use Laravel's notify() here because we have a custom notifications table structure
            // Create in-app notification
            \App\Models\Notification::create([
                'user_id' => $share->sharer_id,
                'admin_id' => null,
                'type' => 'ticket',
                'action' => 'share_accepted',
                'reference_id' => $share->id,
                'title' => 'Ticket Share Accepted',
                'message' => $acceptMessage,
                'is_read' => false,
                'data' => [
                    'share_id' => $share->id,
                    'event_id' => $share->event_id,
                    'event_title' => $share->event->title,
                    'recipient_id' => $share->recipient_id,
                    'recipient_name' => $recipientName,
                ],
            ]);
            
            // Broadcast real-time notification
            \App\Services\NotificationService::sendUserNotification(
                $share->sharer_id,
                $acceptMessage,
                'success',
                'Ticket Share Accepted'
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send acceptance notification: ' . $e->getMessage());
        }

        return redirect()->route('my-tickets.index')->with('success', 'Ticket share accepted successfully! You received ' . $availableTickets->count() . ' ticket(s).');
    }

    /**
     * Decline a ticket share
     */
    public function decline(TicketShare $share)
    {
        $user = Auth::user();

        // Verify the user is the recipient
        if ($share->recipient_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Update the share status
        $share->update(['status' => 'declined']);

        // Clear shared status from tickets so they can be shared again
        if ($share->shared_ticket_ids) {
            \App\Models\EventTicketInformation::whereIn('id', $share->shared_ticket_ids)
                ->update([
                    'shared_by_user_id' => null,
                    'shared_at' => null,
                ]);
        }

        // Send notification to sharer about decline
        try {
            $share->load(['event', 'recipient']);
            $recipientName = $share->recipient->full_name ?? 'Someone';
            $declineMessage = "{$recipientName} declined your ticket share for '{$share->event->title}'.";
            
            // Note: We don't use Laravel's notify() here because we have a custom notifications table structure
            // Create in-app notification
            \App\Models\Notification::create([
                'user_id' => $share->sharer_id,
                'admin_id' => null,
                'type' => 'ticket',
                'action' => 'share_declined',
                'reference_id' => $share->id,
                'title' => 'Ticket Share Declined',
                'message' => $declineMessage,
                'is_read' => false,
                'data' => [
                    'share_id' => $share->id,
                    'event_id' => $share->event_id,
                    'event_title' => $share->event->title,
                    'recipient_id' => $share->recipient_id,
                    'recipient_name' => $recipientName,
                ],
            ]);
            
            // Broadcast real-time notification
            \App\Services\NotificationService::sendUserNotification(
                $share->sharer_id,
                $declineMessage,
                'warning',
                'Ticket Share Declined'
            );
            
            \Log::info('Ticket share declined notification created', [
                'share_id' => $share->id,
                'sharer_id' => $share->sharer_id,
                'recipient_id' => $share->recipient_id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send decline notification', [
                'error' => $e->getMessage(),
                'share_id' => $share->id,
                'trace' => $e->getTraceAsString()
            ]);
        }

        return redirect()->route('my-tickets.index')->with('success', 'Ticket share declined successfully.');
    }

    /**
     * Cancel a ticket share (by sharer)
     */
    public function cancel(TicketShare $share)
    {
        $user = Auth::user();

        // Verify the user is the sharer
        if ($share->sharer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Only allow canceling pending shares
        if ($share->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only cancel pending share requests'
            ], 400);
        }

        // Update the share status
        $share->update(['status' => 'expired']);

        // Clear shared status from tickets so they can be shared again
        if ($share->shared_ticket_ids) {
            \App\Models\EventTicketInformation::whereIn('id', $share->shared_ticket_ids)
                ->update([
                    'shared_by_user_id' => null,
                    'shared_at' => null,
                ]);
        }

        return redirect()->route('my-tickets.index')->with('success', 'Ticket share request cancelled successfully.');
    }

    /**
     * Get user's available tickets for sharing by type
     */
    public function getAvailableTickets(Request $request)
    {
        $user = Auth::user();
        $eventId = $request->event_id;
        $ticketTypeId = $request->ticket_type_id;

        $availableTickets = EventTicketInformation::where('user_id', $user->id)
            ->whereHas('ticketPurchase', function($query) use ($eventId, $ticketTypeId) {
                $query->where('status', 'paid')
                      ->where('event_id', $eventId)
                      ->where('ticket_type_id', $ticketTypeId);
            })
            ->whereNull('shared_at') // Not already shared
            ->with(['ticketPurchase.ticketType'])
            ->get();

        return response()->json([
            'success' => true,
            'available_tickets' => $availableTickets,
            'count' => $availableTickets->count()
        ]);
    }
}
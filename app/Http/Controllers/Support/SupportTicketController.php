<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function myTickets(Request $request)
    {
        $user = Auth::user();
        $tickets = \App\Models\SupportTicket::where('user_id', $user?->id)
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        $from = $request->query('from');
        return view('support.my-tickets', compact('tickets', 'from'));
    }

    public function myTicketShow($ticketNumber)
    {
        $userId = Auth::id();
        $ticket = \App\Models\SupportTicket::where('ticket_number', $ticketNumber)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->with(['replies', 'user'])
            ->firstOrFail();

        return view('support.ticket-show', compact('ticket'));
    }

    public function userReply(Request $request, $ticketNumber)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $ticket = \App\Models\SupportTicket::where('ticket_number', $ticketNumber)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $savedReply = \App\Models\SupportReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_admin_reply' => false,
        ]);

        // Update ticket updated_at
        $ticket->touch();

        // Broadcast real-time event
        try {
            event(new \App\Events\SupportReplyAdded(
                $savedReply->id,
                Auth::id(),
                $ticket->ticket_number,
                $validated['message'],
                false,
                Auth::user()->first_name ?? Auth::user()->name ?? 'You',
                $savedReply->created_at->format('M d, Y H:i')
            ));
        } catch (\Exception $e) {
            // \Log::error('Failed to broadcast user support reply: ' . $e->getMessage());
        }

        // Create admin notification (and collapse previous unread ones) for user reply
        try {
            // Remove old unread admin notifications for this ticket reply to avoid stacking
            \App\Models\Notification::whereNull('user_id')
                ->whereNull('admin_id')
                ->where('type', 'support')
                ->where('action', 'reply')
                ->where('reference_id', $ticket->id)
                ->where('is_read', false)
                ->delete();

            $user = Auth::user();
            $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->name ?? $user->email);
            $notificationMessage = $userName . ' replied to support ticket #' . $ticket->ticket_number;

            $notification = \App\Models\Notification::create([
                'user_id' => null, // Admin notification
                'admin_id' => null, // For all admins
                'type' => 'support',
                'action' => 'reply',
                'reference_id' => $ticket->id,
                'title' => 'Support Ticket Reply',
                'message' => $notificationMessage,
                'data' => [
                    'ticket_id' => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'user_id' => $user->id,
                    'user_name' => $userName,
                    'user_email' => $user->email,
                ],
                'is_read' => false,
            ]);

            // Broadcast real-time admin notification
            try {
                event(new \App\Events\AdminNotification(
                    $notificationMessage,
                    'support',
                    'Support Ticket Reply',
                    $notification->id
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast admin support reply notification', ['error' => $e->getMessage()]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create admin notification for user support reply', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully',
                'reply' => [
                    'id' => $savedReply->id,
                    'author' => Auth::user()->first_name ?? Auth::user()->name ?? 'You',
                    'message' => $validated['message'],
                    'created_at' => $savedReply->created_at->format('M d, Y H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Reply sent successfully');
    }

    /**
     * Admin: Show all support tickets
     */
    public function index(Request $request)
    {
        $query = \App\Models\SupportTicket::with(['user', 'assignedAdmin', 'replies']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Dropped filter: assigned_to (assignment deprecated to simplify workflow)

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest('updated_at')->paginate(20)->withQueryString();
        $admins = \App\Models\Admin::all();

        return view('admin.support.index', compact('tickets', 'admins'));
    }

    /**
     * Admin: Show specific support ticket
     */
    public function show(\App\Models\SupportTicket $ticket)
    {
        $ticket->load(['user', 'assignedAdmin', 'replies.user', 'replies.admin']);
        $admins = \App\Models\Admin::all();

        return view('admin.support.show', compact('ticket', 'admins'));
    }

    /**
     * Admin: Reply to support ticket
     */
    public function reply(Request $request, \App\Models\SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'status' => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        $admin = Auth::guard('admin')->user();

        // Create reply
        $savedReply = \App\Models\SupportReply::create([
            'ticket_id' => $ticket->id,
            'admin_id' => $admin->id,
            'message' => $validated['message'],
            'is_admin_reply' => true,
        ]);

        // Update ticket status: allow explicit update, else auto-progress open -> in_progress
        if (!empty($validated['status'])) {
            $ticket->update(['status' => $validated['status']]);
        } elseif ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Update ticket timestamp
        $ticket->touch();

        // Refresh ticket and load relationships
        $ticket->refresh();
        $ticket->load('user');

        // Try to find user by email if user_id is null
        if (!$ticket->user_id && $ticket->email) {
            $user = \App\Models\User::where('email', $ticket->email)->first();
            if ($user) {
                $ticket->update(['user_id' => $user->id]);
                $ticket->refresh();
                $ticket->load('user');
            }
        }

        // Create notification for user if user_id exists
        if ($ticket->user_id) {
            try {
                // Delete existing unread notifications for this ticket to prevent stacking
                \App\Models\Notification::where('user_id', $ticket->user_id)
                    ->where('type', 'support')
                    ->where('action', 'admin_replied')
                    ->where('reference_id', $ticket->id)
                    ->where('is_read', false)
                    ->delete();

                // Create new notification
                \App\Models\Notification::create([
                    'user_id' => $ticket->user_id,
                    'type' => 'support',
                    'action' => 'admin_replied',
                    'reference_id' => $ticket->id,
                    'title' => 'Support Ticket Reply',
                    'message' => 'An admin has replied to your support ticket #' . $ticket->ticket_number,
                    'data' => [
                        'ticket_id' => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'admin_name' => $admin->username,
                    ],
                    'is_read' => false,
                ]);

                // Send real-time notification
                try {
                    \App\Services\NotificationService::sendUserNotification(
                        $ticket->user_id,
                        'An admin has replied to your support ticket #' . $ticket->ticket_number,
                        'info',
                        'Support Ticket Reply'
                    );
                } catch (\Exception $e) {
                    // \Log::error('Failed to send real-time notification: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                // \Log::error('Failed to create notification: ' . $e->getMessage());
            }

            // Broadcast real-time event
            try {
                event(new \App\Events\SupportReplyAdded(
                    $savedReply->id,
                    $ticket->user_id,
                    $ticket->ticket_number,
                    $validated['message'],
                    true,
                    $admin->username ?? 'Admin',
                    $savedReply->created_at->format('M d, Y H:i')
                ));
            } catch (\Exception $e) {
                // \Log::error('Failed to broadcast admin support reply: ' . $e->getMessage());
            }
        }

        // Log admin activity
        if ($ticket->user) {
            $admin->logActivity('support_reply', [
                'ticket_id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'user_id' => $ticket->user_id,
                'user_name' => $ticket->user->full_name ?? 'Unknown',
            ]);
        }

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply sent successfully.',
                'reply' => [
                    'id' => $savedReply->id,
                    'message' => $validated['message'],
                    'admin_name' => $admin->username,
                    'created_at' => $savedReply->created_at->format('M d, Y H:i'),
                    'is_admin_reply' => true
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Admin: Update ticket status
     */
    public function updateStatus(Request $request, \App\Models\SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Ticket status updated successfully.');
    }

    /**
     * Admin: Assign ticket to admin
     */
    public function assign(Request $request, \App\Models\SupportTicket $ticket)
    {
        $validated = $request->validate([
            'assigned_admin_id' => 'nullable|exists:admins,id',
        ]);

        $ticket->update(['assigned_admin_id' => $validated['assigned_admin_id']]);

        return redirect()->back()->with('success', 'Ticket assignment updated successfully.');
    }
}
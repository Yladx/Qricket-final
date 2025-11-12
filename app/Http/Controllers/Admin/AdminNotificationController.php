<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AdminNotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications');
    }

    public function data()
    {
        try {
            // Get admin notifications from the notifications table
            // admin_id = null means notifications for all admins
            // admin_id = current admin means targeted notification for that admin
            $currentAdminId = Auth::guard('admin')->id();
            $notifications = Notification::whereNull('user_id')
                ->where(function($q) use ($currentAdminId) {
                    $q->whereNull('admin_id');
                    if ($currentAdminId) {
                        $q->orWhere('admin_id', $currentAdminId);
                    }
                })
                ->latest()
                ->limit(100)
                ->get();

            \Log::info('Admin notifications query result:', [
                'count' => $notifications->count(),
                'admin_id' => $currentAdminId,
                'sample_notification' => $notifications->first() ? [
                    'id' => $notifications->first()->id,
                    'type' => $notifications->first()->type,
                    'action' => $notifications->first()->action,
                    'user_id' => $notifications->first()->user_id,
                    'admin_id' => $notifications->first()->admin_id,
                    'stored_title' => $notifications->first()->getRawOriginal('title'),
                    'stored_message' => $notifications->first()->getRawOriginal('message'),
                    'data' => $notifications->first()->data,
                ] : null
            ]);

            $result = [
                'notifications' => $notifications->map(function($notification) {
                    // Get raw stored values (bypass accessors)
                    $storedTitle = $notification->getRawOriginal('title');
                    $storedMessage = $notification->getRawOriginal('message');
                    $data = $notification->data ?? [];
                    
                    // Derive a display tag that is more specific for admins
                    $displayType = $notification->type;
                    if ($notification->type === 'ticket') {
                        if (($storedTitle === 'Refund Request') || isset($data['refund_request_id']) || in_array($notification->action, ['refund_requested','refund_completed','refund_rejected'])) {
                            $displayType = 'refund';
                        } elseif ($notification->action === 'reply') {
                            $displayType = 'support';
                        }
                    } elseif ($notification->type === 'event') {
                        if (($storedTitle === 'Payout Request') || isset($data['payout_request_id']) || $notification->action === 'payout_processed') {
                            $displayType = 'payout';
                        } elseif (in_array($notification->action, ['accept','reject'])) {
                            $displayType = 'event';
                        }
                    }

                    return [
                        'id' => $notification->id,
                        'title' => $this->generateNotificationTitle($notification),
                        'message' => $this->generateNotificationMessage($notification),
                        'type' => $displayType,
                        'action' => $notification->action,
                        'reference_id' => $notification->reference_id,
                        'created_at' => $notification->created_at->toISOString(),
                        'is_read' => (bool) $notification->is_read,
                    ];
                })
            ];

            \Log::info('Admin notifications response:', ['count' => count($result['notifications'])]);
            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Error in AdminNotificationController@data:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['notifications' => [], 'error' => $e->getMessage()], 500);
        }
    }

    private function generateNotificationTitle($notification)
    {
        // Use stored title if available and not empty (get raw value to bypass accessor)
        $storedTitle = $notification->getRawOriginal('title');
        if (!empty($storedTitle)) {
            return $storedTitle;
        }

        // For admin notifications, be more specific based on type and action
        switch ($notification->type) {
            case 'ticket':
                // Check for specific actions
                if ($notification->action === 'refund_completed' || $notification->action === 'refund_rejected') {
                    return 'Refund Update';
                }
                if (isset($notification->data['refund_request_id'])) {
                    return 'Refund Request';
                }
                // For admin, ticket type usually means refund or support ticket
                if ($notification->action === 'reply') {
                    return 'Support Ticket Reply';
                }
                return 'Ticket Update';
            case 'support':
                // Check action and data for more specific titles
                if (isset($notification->data['ticket_number'])) {
                    return 'New Support Ticket';
                }
                return 'Support Ticket Update';
            case 'organizer':
                if ($notification->action === 'verified') {
                    return 'Organizer Verification Request';
                }
                if ($notification->action === 'accept') {
                    return 'Organizer Verified';
                }
                if ($notification->action === 'reject') {
                    return 'Organizer Rejected';
                }
                return 'Organizer Update';
            case 'event':
                if ($notification->action === 'accept' && isset($notification->data['event_title'])) {
                    return 'Event Approval Request';
                }
                if ($notification->action === 'payout_processed' || isset($notification->data['payout_request_id'])) {
                    return 'Payout Request';
                }
                if ($notification->action === 'accept') {
                    return 'Event Approved';
                }
                if ($notification->action === 'reject') {
                    return 'Event Rejected';
                }
                return 'Event Update';
            default:
                return 'System Notification';
        }
    }

    private function generateNotificationMessage($notification)
    {
        // Use stored message if available (get raw value to bypass accessor)
        $storedMessage = $notification->getRawOriginal('message');
        if (!empty($storedMessage)) {
            return $storedMessage;
        }

        switch ($notification->type) {
            case 'ticket':
                if ($notification->action === 'reply') {
                    $ticket = \App\Models\SupportTicket::find($notification->reference_id);
                    return 'User replied to ticket #' . ($ticket ? $ticket->ticket_number : $notification->reference_id);
                }
                // Check for refund request (stored in title or data)
                $storedTitle = $notification->getRawOriginal('title');
                if ($storedTitle === 'Refund Request' || isset($notification->data['refund_request_id'])) {
                    $data = $notification->data ?? [];
                    $userName = $data['user_name'] ?? 'A user';
                    $amount = isset($data['amount']) ? '₱' . number_format($data['amount'], 2) : '';
                    $refundType = ucfirst($data['type'] ?? 'ticket');
                    return "{$userName} requested a {$refundType} refund" . ($amount ? " of {$amount}" : '');
                }
                break;
            case 'support':
                $storedTitle = $notification->getRawOriginal('title');
                if ($storedTitle === 'New Support Ticket' || isset($notification->data['ticket_number'])) {
                    $data = $notification->data ?? [];
                    $userName = $data['user_name'] ?? 'A user';
                    $ticketNumber = $data['ticket_number'] ?? '';
                    $subject = $data['subject'] ?? '';
                    if ($ticketNumber && $subject) {
                        return "{$userName} submitted ticket #{$ticketNumber}: {$subject}";
                    } elseif ($ticketNumber) {
                        return "{$userName} submitted ticket #{$ticketNumber}";
                    } elseif ($subject) {
                        return "{$userName} submitted a new ticket: {$subject}";
                    }
                    return "{$userName} submitted a new support ticket";
                }
                break;
            case 'organizer':
                if ($notification->action === 'verified') {
                    $data = $notification->data ?? [];
                    $userName = $data['user_name'] ?? 'A user';
                    $orgName = $data['organization_name'] ?? 'an organization';
                    return "{$userName} submitted an organizer verification request for '{$orgName}'";
                } elseif ($notification->action === 'accept') {
                    return 'Organizer application accepted';
                } elseif ($notification->action === 'reject') {
                    return 'Organizer application rejected';
                }
                break;
            case 'event':
                $storedTitle = $notification->getRawOriginal('title');
                if ($notification->action === 'accept' && $storedTitle === 'Event Approval Request') {
                    $data = $notification->data ?? [];
                    $organizerName = $data['organizer_name'] ?? 'An organizer';
                    $eventTitle = $data['event_title'] ?? 'an event';
                    return "{$organizerName} submitted event '{$eventTitle}' for approval";
                } elseif ($notification->action === 'payout_processed' && $storedTitle === 'Payout Request') {
                    $data = $notification->data ?? [];
                    $organizerName = $data['organizer_name'] ?? 'An organizer';
                    $eventTitle = $data['event_title'] ?? 'an event';
                    $amount = isset($data['net_amount']) ? '₱' . number_format($data['net_amount'], 2) : '';
                    return "{$organizerName} requested a payout" . ($amount ? " of {$amount}" : '') . " for event '{$eventTitle}'";
                } elseif ($notification->action === 'accept') {
                    return 'Event approved';
                } elseif ($notification->action === 'reject') {
                    return 'Event rejected';
                }
                break;
        }
        
        return $notification->message ?? 'New notification received';
    }

    public function markAsRead(Request $request, $id)
    {
        try {
            $currentAdminId = Auth::guard('admin')->id();
            $notification = Notification::whereNull('user_id')
                ->where(function($q) use ($currentAdminId) {
                    $q->whereNull('admin_id');
                    if ($currentAdminId) {
                        $q->orWhere('admin_id', $currentAdminId);
                    }
                })
                ->findOrFail($id);
                
            $notification->update(['is_read' => true]);
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error marking admin notification as read:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $currentAdminId = Auth::guard('admin')->id();
            Notification::whereNull('user_id')
                ->where(function($q) use ($currentAdminId) {
                    $q->whereNull('admin_id');
                    if ($currentAdminId) {
                        $q->orWhere('admin_id', $currentAdminId);
                    }
                })
                ->where('is_read', false)
                ->update(['is_read' => true]);
                
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error marking all admin notifications as read:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function go($id)
    {
        try {
            $currentAdminId = Auth::guard('admin')->id();
            $notification = Notification::whereNull('user_id')
                ->where(function($q) use ($currentAdminId) {
                    $q->whereNull('admin_id');
                    if ($currentAdminId) {
                        $q->orWhere('admin_id', $currentAdminId);
                    }
                })
                ->findOrFail($id);

            // Mark notification as read when clicked
            if (!$notification->is_read) {
                $notification->update(['is_read' => true]);
            }

            \Log::info('Admin notification redirect', [
                'notification_id' => $id,
                'type' => $notification->type,
                'action' => $notification->action,
                'reference_id' => $notification->reference_id,
                'admin_id' => $currentAdminId
            ]);

            // Handle organizer verification notifications
            if ($notification->type === 'organizer' && $notification->reference_id) {
                if ($notification->action === 'verified') {
                    return redirect()->route('admin.organizer-verifications.show', ['verification' => $notification->reference_id]);
                }
                return redirect()->route('admin.organizer-verifications.index');
            }

            // Handle event notifications
            if ($notification->type === 'event' && $notification->reference_id) {
                if (in_array($notification->action, ['accept', 'reject', 'pending'])) {
                    // Event approval/rejection - go to event approval page
                    return redirect()->route('admin.events.approval.show', ['event' => $notification->reference_id]);
                } elseif ($notification->action === 'payout_processed' || isset($notification->data['payout_request_id'])) {
                    // Payout request - go to payouts page
                    return redirect()->route('admin.payouts.index');
                }
                // Default: go to event approval page or event management dashboard
                try {
                    return redirect()->route('admin.events.approval.show', ['event' => $notification->reference_id]);
                } catch (\Exception $e) {
                    // Fallback to events list if event not found
                    return redirect()->route('admin.events.approval.index');
                }
            }

            // Handle ticket/refund notifications
            if ($notification->type === 'ticket' && $notification->reference_id) {
                if (isset($notification->data['refund_request_id']) || in_array($notification->action, ['refund_requested', 'refund_completed', 'refund_rejected'])) {
                    // Refund request - go to refunds page
                    return redirect()->route('admin.refunds.index');
                }
                // Support ticket reply
                if ($notification->action === 'reply') {
                    $supportTicket = \App\Models\SupportTicket::find($notification->reference_id);
                    if ($supportTicket) {
                        return redirect()->route('admin.support.show', ['ticket' => $supportTicket->id]);
                    }
                }
                return redirect()->route('admin.support');
            }

            // Handle support notifications
            if ($notification->type === 'support' && $notification->reference_id) {
                $supportTicket = \App\Models\SupportTicket::find($notification->reference_id);
                if ($supportTicket) {
                    return redirect()->route('admin.support.show', ['ticket' => $supportTicket->id]);
                }
                return redirect()->route('admin.support');
            }

            // Fallback routing based on notification type
            switch ($notification->type) {
                case 'organizer':
                    return redirect()->route('admin.organizer-verifications.index');
                case 'ticket':
                    // Check if it's a refund or support ticket
                    if (isset($notification->data['refund_request_id']) || in_array($notification->action, ['refund_requested', 'refund_completed', 'refund_rejected'])) {
                        return redirect()->route('admin.refunds.index');
                    }
                    return redirect()->route('admin.support');
                case 'event':
                    return redirect()->route('admin.events.approval.index');
                case 'support':
                    return redirect()->route('admin.support');
                default:
                    return redirect()->route('admin.notifications');
            }
        } catch (\Exception $e) {
            \Log::error('Error in admin notification redirect:', ['error' => $e->getMessage(), 'notification_id' => $id]);
            return redirect()->route('admin.notifications')->with('error', 'Notification not found or invalid.');
        }
    }
}

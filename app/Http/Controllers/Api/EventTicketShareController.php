<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventTicketShare;
use App\Models\Event;
use App\Models\EventTicketInformation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EventTicketShareController extends Controller
{
    /**
     * Share tickets with users.
     */
    public function share(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $event = Event::findOrFail($request->event_id);
        $userIds = $request->users;
        $message = $request->message;

        // For now, allow sharing for all events (bypass R2 storage check)
        $allowSharing = true;

        // Get individual tickets owned by the sharer for this event that haven't already been shared
        $availableTickets = EventTicketInformation::where('user_id', auth()->id())
            ->where('event_id', $event->id)
            ->whereDoesntHave('shares', function($q) {
                $q->where('status', 'pending');
            })
            ->get();

        if ($availableTickets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have any available tickets to share for this event.'
            ], 400);
        }

        // Calculate total shares needed (number of users)
        $totalSharesNeeded = count($userIds);
        
        if ($availableTickets->count() < $totalSharesNeeded) {
            return response()->json([
                'success' => false,
                'message' => "You only have {$availableTickets->count()} ticket(s) available to share, but trying to share with {$totalSharesNeeded} user(s)."
            ], 400);
        }

        DB::beginTransaction();
        try {
            $sharesCreated = 0;
            $ticketIndex = 0;

            // Create individual share records for each user-ticket combination
            foreach ($userIds as $recipientId) {
                // Check if recipient is not the sharer
                if ($recipientId == auth()->id()) {
                    continue;
                }

                // Get one available ticket for this recipient
                if ($ticketIndex >= $availableTickets->count()) {
                    break; // No more tickets available
                }

                $ticket = $availableTickets[$ticketIndex];

                // Prevent sharing the same ticket twice while a share is pending (to anyone)
                $existingShare = EventTicketShare::where('shared_ticket_id', $ticket->id)
                    ->where('status', 'pending')
                    ->first();

                if ($existingShare) {
                    $ticketIndex++; // Skip this ticket, try the next one
                    continue;
                }

                // If a share already exists for this (event, sharer, recipient),
                // update it instead of inserting a duplicate (due to unique index)
                $existingPairShare = EventTicketShare::where('event_id', $event->id)
                    ->where('sharer_id', auth()->id())
                    ->where('recipient_id', $recipientId)
                    ->orderByDesc('id')
                    ->first();

                if ($existingPairShare) {
                    // Skip if there is already a pending request to this recipient
                    if ($existingPairShare->status === 'pending') {
                        $ticketIndex++;
                        continue;
                    }

                    // Reuse the row for a new share attempt
                    $existingPairShare->update([
                        'ticket_type_id' => $ticket->ticket_type_id, // Update ticket type
                        'shared_ticket_id' => $ticket->id,
                        'quantity' => 1, // Single ticket share
                        'status' => 'pending',
                        'expires_at' => now()->addDays(7),
                        'message' => $message,
                    ]);
                    $ticketShare = $existingPairShare->fresh();
                } else {
                    // Create individual share record
                    $ticketShare = EventTicketShare::create([
                        'event_id' => $event->id,
                        'ticket_type_id' => $ticket->ticket_type_id, // Include ticket type
                        'sharer_id' => auth()->id(),
                        'recipient_id' => $recipientId,
                        'shared_ticket_id' => $ticket->id, // Store the specific ticket ID
                        'quantity' => 1, // Single ticket share
                        'status' => 'pending',
                        'expires_at' => now()->addDays(7), // Expire after 7 days
                        'message' => $message,
                    ]);
                }

                // Ensure relationships are loaded before creating notification
                $ticketShare->load(['sharer', 'recipient', 'event']);

                // Log share created
                try {
                    \App\Models\EventActivityLog::log(
                        $event->id,
                        $this->getActivityLogUserId(),
                        'ticket_share_created',
                        'Requested ticket share to user ID ' . (int)$recipientId,
                        [
                            'shared_ticket_id' => $ticket->id,
                            'recipient_id' => (int)$recipientId,
                            'share_id' => (int)$ticketShare->id,
                        ]
                    );
                } catch (\Throwable $e) { /* ignore */ }
                
                // Ticket will be transferred when accepted, no need to mark as shared now

                $sharesCreated++;

                // Create notification (custom notifications table) for the recipient
                try {
                    // Reload relationships to ensure they're fresh
                    $ticketShare->refresh();
                    $ticketShare->loadMissing(['sharer', 'recipient', 'event']);
                    
                    // Validate relationships exist
                    if (!$ticketShare->sharer) {
                        \Log::error('Ticket share sharer not found', [
                            'share_id' => $ticketShare->id,
                            'sharer_id' => $ticketShare->sharer_id,
                        ]);
                        $ticketIndex++;
                        continue;
                    }
                    
                    if (!$ticketShare->recipient) {
                        \Log::error('Ticket share recipient not found', [
                            'share_id' => $ticketShare->id,
                            'recipient_id' => $recipientId,
                        ]);
                        $ticketIndex++;
                        continue;
                    }
                    
                    if (!$ticketShare->event && !$event) {
                        \Log::error('Ticket share event not found', [
                            'share_id' => $ticketShare->id,
                            'event_id' => $ticketShare->event_id,
                        ]);
                        $ticketIndex++;
                        continue;
                    }
                    
                    // Get names safely
                    $sharerName = $ticketShare->sharer->full_name ?? 
                                  ($ticketShare->sharer->first_name . ' ' . $ticketShare->sharer->last_name) ?? 
                                  'Someone';
                    $eventTitle = $ticketShare->event->title ?? $event->title ?? 'Event';
                    $shareMessage = "{$sharerName} wants to share a ticket for '{$eventTitle}' with you.";
                    
                    // Create notification in database
                    $notification = \App\Models\Notification::create([
                        'user_id' => (int) $recipientId,
                        'admin_id' => null,
                        'type' => 'ticket',
                        'action' => 'share_request',
                        'reference_id' => (int) $ticketShare->id,
                        'title' => 'Ticket Share Request',
                        'message' => $shareMessage,
                        'is_read' => false,
                        'data' => [
                            'share_id' => (int) $ticketShare->id,
                            'event_id' => (int) ($ticketShare->event_id ?? $event->id),
                            'event_title' => $eventTitle,
                            'sharer_id' => (int) $ticketShare->sharer_id,
                            'sharer_name' => $sharerName,
                        ],
                    ]);
                    
                    \Log::info('Notification created successfully', [
                        'notification_id' => $notification->id,
                        'share_id' => $ticketShare->id,
                        'recipient_id' => (int) $recipientId,
                    ]);
                    
                    // Note: We don't use Laravel's notify() here because we have a custom notifications table structure
                    // The notification is already created above in our custom table and broadcasted via NotificationService
                    
                    // Broadcast a private user notification
                    try {
                        \App\Services\NotificationService::sendUserNotification(
                            (int) $recipientId,
                            $shareMessage,
                            'info',
                            'Ticket Share Request'
                        );
                    } catch (\Exception $e) {
                        \Log::warning('Failed to broadcast notification', [
                            'error' => $e->getMessage(),
                            'recipient_id' => $recipientId,
                        ]);
                    }
                    
                    \Log::info('Ticket share request notification created', [
                        'notification_id' => $notification->id,
                        'share_id' => $ticketShare->id,
                        'recipient_id' => (int) $recipientId,
                        'sharer_id' => (int) $ticketShare->sharer_id,
                        'event_id' => (int) ($ticketShare->event_id ?? $event->id),
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create notification for ticket share request', [
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'share_id' => $ticketShare->id ?? null,
                        'recipient_id' => (int) $recipientId,
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue without notification if it fails
                }
                
                $ticketIndex++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully shared {$sharesCreated} ticket(s) with " . count($userIds) . " user(s).",
                'shares_created' => $sharesCreated
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to share tickets: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to share tickets: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Accept a ticket share.
     */
    public function accept(Request $request, $id): JsonResponse
    {
        // Find the ticket share manually to handle potential binding issues
        $ticketShare = EventTicketShare::find($id);
        
        if (!$ticketShare) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket share not found.'
            ], 404);
        }

        // Debug: Log the share data
        \Log::info('Accept share request', [
            'share_id' => $ticketShare->id,
            'share_status' => $ticketShare->status,
            'recipient_id' => $ticketShare->recipient_id,
            'auth_id' => auth()->id()
        ]);

        // Verify the share belongs to the authenticated user
        if ($ticketShare->recipient_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        if ($ticketShare->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This share cannot be accepted (already processed or expired).'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $ticketsToTransfer = collect();
            $quantity = $ticketShare->quantity ?? 1;
            
            // Case 1: shared_ticket_id is set - transfer specific ticket
            if (!is_null($ticketShare->shared_ticket_id)) {
                $ticketToTransfer = EventTicketInformation::where('id', $ticketShare->shared_ticket_id)
                    ->where('event_id', $ticketShare->event_id)
                    ->where('user_id', $ticketShare->sharer_id)
                    ->first();

                if (!$ticketToTransfer) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false, 
                        'message' => 'The shared ticket is no longer available for transfer.'
                    ], 400);
                }
                
                $ticketsToTransfer->push($ticketToTransfer);
            }
            // Case 2: shared_ticket_id is NULL - find available tickets by ticket_type_id
            else if (!is_null($ticketShare->ticket_type_id)) {
                // First, get all ticket IDs that have pending shares (either in shared_ticket_id or shared_ticket_ids)
                // Exclude the current share being accepted
                $pendingShareTicketIds = EventTicketShare::where('status', 'pending')
                    ->where('sharer_id', $ticketShare->sharer_id)
                    ->where('event_id', $ticketShare->event_id)
                    ->where('id', '!=', $ticketShare->id) // Exclude current share
                    ->where(function($q) {
                        $q->whereNotNull('shared_ticket_id')
                          ->orWhereNotNull('shared_ticket_ids');
                    })
                    ->get()
                    ->flatMap(function($share) {
                        $ids = [];
                        if ($share->shared_ticket_id) {
                            $ids[] = $share->shared_ticket_id;
                        }
                        if ($share->shared_ticket_ids && is_array($share->shared_ticket_ids)) {
                            $ids = array_merge($ids, $share->shared_ticket_ids);
                        }
                        return $ids;
                    })
                    ->unique()
                    ->values()
                    ->toArray();
                
                // Get available tickets that are:
                // 1. Owned by the sharer (user_id = sharer_id)
                // 2. Of the specified ticket type
                // 3. Not checked in
                // 4. Not already shared/transferred to others (shared_by_user_id should be NULL)
                // 5. Not in the list of tickets with pending shares
                // 
                // Important: After a ticket is transferred, user_id changes to the recipient,
                // so it won't be in this query. Only tickets still owned by the sharer will be included.
                $availableTicketsQuery = EventTicketInformation::where('user_id', $ticketShare->sharer_id)
                    ->where('event_id', $ticketShare->event_id)
                    ->where('ticket_type_id', $ticketShare->ticket_type_id)
                    ->whereNull('checked_in_at') // Not checked in
                    ->whereNull('shared_by_user_id'); // Not already shared/transferred to others
                
                // Exclude tickets that have pending shares
                if (!empty($pendingShareTicketIds)) {
                    $availableTicketsQuery->whereNotIn('id', $pendingShareTicketIds);
                }
                
                $availableTickets = $availableTicketsQuery->limit($quantity)->get();
                
                if ($availableTickets->count() < $quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough available tickets to fulfill this share request. Required: {$quantity}, Available: {$availableTickets->count()}"
                    ], 400);
                }
                
                $ticketsToTransfer = $availableTickets;
            }
            else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid share request: missing ticket information.'
                ], 400);
            }

            // Update share status
            $ticketShare->update([
                'status' => 'accepted',
                // Store the transferred ticket IDs if not already set
                'shared_ticket_ids' => $ticketShare->shared_ticket_ids ?? $ticketsToTransfer->pluck('id')->toArray(),
            ]);

            // Transfer the tickets to the recipient
            // Keep shared_by_user_id so the sharer can still see they shared it
            foreach ($ticketsToTransfer as $ticket) {
                $ticket->update([
                    'user_id' => $ticketShare->recipient_id,
                    'shared_by_user_id' => $ticketShare->sharer_id, // Track who originally shared it
                    'shared_at' => now(),
                ]);
            }

            // Create notification for the sharer (accepted)
            try {
                // Reload relationships to ensure they're fresh
                $ticketShare->refresh();
                $ticketShare->loadMissing(['sharer', 'recipient', 'event']);
                
                // Validate relationships
                if (!$ticketShare->recipient || !$ticketShare->sharer || !$ticketShare->event) {
                    \Log::error('Missing relationships for accepted notification', [
                        'share_id' => $ticketShare->id,
                        'has_recipient' => !!$ticketShare->recipient,
                        'has_sharer' => !!$ticketShare->sharer,
                        'has_event' => !!$ticketShare->event,
                    ]);
                    throw new \Exception('Required relationships missing');
                }
                
                $recipientName = $ticketShare->recipient->full_name ?? 
                                 ($ticketShare->recipient->first_name . ' ' . $ticketShare->recipient->last_name) ?? 
                                 'Someone';
                $acceptMessage = "{$recipientName} accepted your ticket share for '{$ticketShare->event->title}'.";
                
                $notification = \App\Models\Notification::create([
                    'user_id' => (int) $ticketShare->sharer_id,
                    'admin_id' => null,
                    'type' => 'ticket',
                    'action' => 'share_accepted',
                    'reference_id' => (int) $ticketShare->id,
                    'title' => 'Ticket Share Accepted',
                    'message' => $acceptMessage,
                    'is_read' => false,
                    'data' => [
                        'share_id' => (int) $ticketShare->id,
                        'event_id' => (int) $ticketShare->event_id,
                        'event_title' => $ticketShare->event->title,
                        'recipient_id' => (int) $ticketShare->recipient_id,
                        'recipient_name' => $recipientName,
                    ],
                ]);
                
                \Log::info('Accept notification created', [
                    'notification_id' => $notification->id,
                    'share_id' => $ticketShare->id,
                ]);
                
                // Note: We don't use Laravel's notify() here because we have a custom notifications table structure
                
                // Broadcast a private user notification to the sharer
                \App\Services\NotificationService::sendUserNotification(
                    (int) $ticketShare->sharer_id,
                    $acceptMessage,
                    'success',
                    'Ticket Share Accepted'
                );
                
                \Log::info('Ticket share accepted notification created', [
                    'share_id' => $ticketShare->id,
                    'sharer_id' => (int) $ticketShare->sharer_id,
                    'recipient_id' => (int) $ticketShare->recipient_id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create notification for ticket share accepted', [
                    'error' => $e->getMessage(),
                    'share_id' => $ticketShare->id,
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue without notification if it fails
            }

            // Log share accepted
            try {
                \App\Models\EventActivityLog::log(
                    (int)$ticketShare->event_id,
                    (int)$ticketShare->recipient_id,
                    'ticket_share_accepted',
                    'Ticket share accepted by recipient.',
                    [
                        'share_id' => (int)$ticketShare->id,
                        'shared_ticket_id' => (int)$ticketShare->shared_ticket_id,
                        'sharer_id' => (int)$ticketShare->sharer_id,
                        'recipient_id' => (int)$ticketShare->recipient_id,
                    ]
                );
            } catch (\Throwable $e) { /* ignore */ }

            DB::commit();
            $ticketCount = $ticketsToTransfer->count();
            return response()->json([
                'success' => true, 
                'message' => "Ticket share accepted. {$ticketCount} ticket" . ($ticketCount > 1 ? 's are' : ' is') . " now in your account."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to accept ticket share: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to accept ticket share.'], 500);
        }
    }

    /**
     * Decline a ticket share.
     */
    public function decline(Request $request, $id): JsonResponse
    {
        // Find the ticket share manually to handle potential binding issues
        $ticketShare = EventTicketShare::find($id);
        
        if (!$ticketShare) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket share not found.'
            ], 404);
        }

        // Debug: Log the share data
        \Log::info('Decline share request', [
            'share_id' => $ticketShare->id,
            'share_status' => $ticketShare->status,
            'recipient_id' => $ticketShare->recipient_id,
            'auth_id' => auth()->id()
        ]);

        // Verify the share belongs to the authenticated user
        if ($ticketShare->recipient_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        if ($ticketShare->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This share cannot be declined (already processed).'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $ticketShare->update(['status' => 'declined']);

            // No need to clear anything since tickets weren't marked as shared

            // Create notification for the sharer (declined)
            try {
                // Reload relationships to ensure they're fresh
                $ticketShare->refresh();
                $ticketShare->loadMissing(['sharer', 'recipient', 'event']);
                
                // Validate relationships
                if (!$ticketShare->recipient || !$ticketShare->sharer || !$ticketShare->event) {
                    \Log::error('Missing relationships for declined notification', [
                        'share_id' => $ticketShare->id,
                        'has_recipient' => !!$ticketShare->recipient,
                        'has_sharer' => !!$ticketShare->sharer,
                        'has_event' => !!$ticketShare->event,
                    ]);
                    throw new \Exception('Required relationships missing');
                }
                
                $recipientName = $ticketShare->recipient->full_name ?? 
                                 ($ticketShare->recipient->first_name . ' ' . $ticketShare->recipient->last_name) ?? 
                                 'Someone';
                $declineMessage = "{$recipientName} declined your ticket share for '{$ticketShare->event->title}'.";
                
                $notification = \App\Models\Notification::create([
                    'user_id' => (int) $ticketShare->sharer_id,
                    'admin_id' => null,
                    'type' => 'ticket',
                    'action' => 'share_declined',
                    'reference_id' => (int) $ticketShare->id,
                    'title' => 'Ticket Share Declined',
                    'message' => $declineMessage,
                    'is_read' => false,
                    'data' => [
                        'share_id' => (int) $ticketShare->id,
                        'event_id' => (int) $ticketShare->event_id,
                        'event_title' => $ticketShare->event->title,
                        'recipient_id' => (int) $ticketShare->recipient_id,
                        'recipient_name' => $recipientName,
                    ],
                ]);
                
                \Log::info('Decline notification created', [
                    'notification_id' => $notification->id,
                    'share_id' => $ticketShare->id,
                ]);
                
                // Note: We don't use Laravel's notify() here because we have a custom notifications table structure
                
                // Broadcast a private user notification to the sharer
                \App\Services\NotificationService::sendUserNotification(
                    (int) $ticketShare->sharer_id,
                    $declineMessage,
                    'warning',
                    'Ticket Share Declined'
                );
                
                \Log::info('Ticket share declined notification created', [
                    'share_id' => $ticketShare->id,
                    'sharer_id' => (int) $ticketShare->sharer_id,
                    'recipient_id' => (int) $ticketShare->recipient_id,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create notification for ticket share declined', [
                    'error' => $e->getMessage(),
                    'share_id' => $ticketShare->id,
                    'trace' => $e->getTraceAsString()
                ]);
                // Continue without notification if it fails
            }

            // Log share declined
            try {
                \App\Models\EventActivityLog::log(
                    (int)$ticketShare->event_id,
                    (int)$ticketShare->recipient_id,
                    'ticket_share_declined',
                    'Ticket share declined by recipient.',
                    [ 'share_id' => (int)$ticketShare->id ]
                );
            } catch (\Throwable $e) { /* ignore */ }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Ticket share declined.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to decline ticket share: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Failed to decline ticket share.'], 500);
        }
    }

    /**
     * Get received ticket shares for the authenticated user.
     */
    public function getReceivedShares(Request $request): JsonResponse
    {
        $ticketShares = EventTicketShare::with(['event', 'sharer'])
            ->receivedBy(auth()->id())
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'shares' => $ticketShares->map(function ($ticketShare) {
                return [
                    'id' => $ticketShare->id,
                    'event' => [
                        'id' => $ticketShare->event->id,
                        'title' => $ticketShare->event->title,
                        'start_date' => $ticketShare->event->start_date,
                        'location' => $ticketShare->event->location,
                        'thumbnail_url' => $ticketShare->event->thumbnail_url,
                    ],
                    'sharer' => [
                        'id' => $ticketShare->sharer->id,
                        'name' => $ticketShare->sharer->full_name,
                        'email' => $ticketShare->sharer->email,
                    ],
                    'quantity' => $ticketShare->quantity,
                    'message' => $ticketShare->message,
                    'expires_at' => $ticketShare->expires_at,
                    'created_at' => $ticketShare->created_at,
                ];
            })
        ]);
    }

    /**
     * Log ticket sharing activity.
     */
    public function logSharing(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,id',
            'quantity' => 'required|integer|min:1',
            'user_count' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Event::findOrFail($request->event_id);
            $user = auth()->user();

            // Log to user activity log
            $user->activityLogs()->create([
                'action' => 'ticket_shared',
                'description' => "Shared {$request->quantity} ticket(s) for '{$event->title}' with {$request->user_count} user(s)",
                'metadata' => [
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'quantity' => $request->quantity,
                    'user_count' => $request->user_count,
                ]
            ]);

            // Log to event activity log
            $event->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'tickets_shared',
                'description' => "{$user->full_name} shared {$request->quantity} ticket(s) with {$request->user_count} user(s)",
                'metadata' => [
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'quantity' => $request->quantity,
                    'user_count' => $request->user_count,
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity logged successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to log ticket sharing activity: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to log activity'
            ], 500);
        }
    }

    /**
     * Load ticket settings from R2 storage.
     */
    private function loadTicketSettings(Event $event): array
    {
        try {
            $disk = \Storage::disk('r2');
            $eventDir = "events/{$event->id}";
            $settingsPath = $eventDir . '/ticket_settings.json';
            
            if ($disk->exists($settingsPath)) {
                $settings = json_decode($disk->get($settingsPath), true);
                if ($settings && is_array($settings)) {
                    return $settings;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load ticket settings from R2: ' . $e->getMessage());
        }
        
        // Return default settings if file doesn't exist or is invalid
        return [
            'max_tickets_per_order' => 10,
            'refund_policy' => 'Refunds are available up to 7 days before the event. A processing fee may apply.',
            'allow_refunds' => true,
            'require_approval' => false,
            'allow_share_ticket' => false,
            'limit_per_user' => false,
            'max_tickets_per_user' => 1,
        ];
    }

    /**
     * Get the correct user ID for activity logging.
     * Returns null for admin users, regular user ID for regular users.
     */
    private function getActivityLogUserId()
    {
        // If admin is logged in, return null (admin-created events have null user_id)
        if (Auth::guard('admin')->check()) {
            return null;
        }
        
        // Return regular user ID
        return auth()->id();
    }
}

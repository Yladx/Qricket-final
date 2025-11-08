<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventTicketInformation;
use App\Models\TicketShare;
use App\Models\EventTicketShare;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MyTicketsController extends Controller
{
    /**
     * Display the user's tickets page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hideShared = $request->boolean('hide_shared', false);
        $hidePastEvents = $request->boolean('hide_past_events', false);
        $sortBy = $request->get('sort_by', 'date_desc');
        
        try {
            // Get user's tickets with related data
            // Include tickets owned by user OR tickets user originally shared (even if transferred)
            $query = EventTicketInformation::where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('shared_by_user_id', $user->id); // Tickets user shared (even after transfer)
                })
                ->with([
                    'event:id,title,start_date,end_date,location,thumbnail',
                    'ticketType:id,name,price',
                    'ticketPurchase:id,status,purchase_id',
                    'sharedByUser:id,first_name,last_name,email',
                    'user:id,first_name,last_name,email', // Current owner
                    'shares' => function($q) {
                        $q->where('status', 'pending')
                          ->with(['recipient:id,first_name,last_name,email', 'sharer:id,first_name,last_name,email']); // Load recipient and sharer for pending shares
                    }
                ])
                ->whereHas('ticketPurchase', function($query) {
                    $query->where('status', 'paid');
                });

            // Optionally hide shared tickets (hide tickets that are currently shared TO others)
            if ($hideShared) {
                $query->where(function($q) use ($user) {
                    $q->whereNull('shared_by_user_id') // Not shared to others
                      ->orWhere('shared_by_user_id', '!=', $user->id); // Or received from others
                });
            }

            $tickets = $query->get();

            // Filter out tickets without ticket types and group by event
            $tickets = $tickets->filter(function($ticket) {
                return $ticket->ticketType !== null && $ticket->event !== null;
            });
            
            // Optionally hide past events
            if ($hidePastEvents) {
                $tickets = $tickets->filter(function($ticket) {
                    return $ticket->event && $ticket->event->end_date && $ticket->event->end_date > now();
                });
            }
            
            $ticketsByEvent = $tickets->groupBy('event_id');

            // Apply sorting
            $ticketsByEvent = $ticketsByEvent->sort(function($a, $b) use ($sortBy) {
                $eventA = $a->first()->event;
                $eventB = $b->first()->event;
                
                switch ($sortBy) {
                    case 'date_asc':
                        return $eventA->start_date <=> $eventB->start_date;
                    case 'date_desc':
                    default:
                        return $eventB->start_date <=> $eventA->start_date;
                    case 'title_asc':
                        return strcasecmp($eventA->title, $eventB->title);
                    case 'title_desc':
                        return strcasecmp($eventB->title, $eventA->title);
                }
            });

            // Get pending shares received by this user
            $pendingSharesReceived = TicketShare::where('recipient_id', $user->id)
                ->where('status', 'pending')
                ->with(['sharer', 'ticketType', 'event'])
                ->get()
                ->groupBy('event_id');

            // Get pending shares sent by this user
            $pendingSharesSent = TicketShare::where('sharer_id', $user->id)
                ->where('status', 'pending')
                ->with(['recipient', 'ticketType', 'event'])
                ->get()
                ->groupBy('event_id');

            // Check if sharing is allowed for each event (load from R2 ticket settings)
            $eventSharingSettings = [];
            foreach ($ticketsByEvent as $eventId => $tickets) {
                $event = $tickets->first()->event;
                if ($event) {
                    // Check if user owns tickets for this event (not just received)
                    $hasOwnedTickets = $tickets->filter(function($ticket) use ($user) {
                        return $ticket->user_id === $user->id && 
                               ($ticket->shared_by_user_id === null || $ticket->shared_by_user_id === $user->id);
                    })->count() > 0;
                    
                    $allowSharing = false;
                    if ($hasOwnedTickets) {
                        try {
                            $ticketSettings = $this->loadTicketSettings($event);
                            $allowSharing = $ticketSettings['allow_share_ticket'] ?? false;
                        } catch (\Exception $e) {
                            // If settings can't be loaded, default to false
                            Log::warning('Failed to load ticket settings for sharing check', [
                                'event_id' => $eventId,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    $eventSharingSettings[$eventId] = $allowSharing;
                }
            }

            return view('tickets.my-tickets', compact('ticketsByEvent', 'pendingSharesReceived', 'pendingSharesSent', 'hideShared', 'hidePastEvents', 'sortBy', 'eventSharingSettings'));
            
        } catch (\Exception $e) {
            Log::error('Error loading tickets: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('tickets.my-tickets', [
                'ticketsByEvent' => collect(),
                'pendingSharesReceived' => collect(),
                'pendingSharesSent' => collect(),
                'hideShared' => $hideShared,
                'hidePastEvents' => $hidePastEvents,
                'sortBy' => $sortBy,
                'eventSharingSettings' => [],
                'error' => 'Failed to load tickets. Please try again.'
            ]);
        }
    }

    /**
     * Get filtered tickets via AJAX
     */
    public function getFilteredTickets(Request $request)
    {
        $user = Auth::user();
        $hideShared = $request->boolean('hide_shared', false);
        $hidePastEvents = $request->boolean('hide_past_events', false);
        $sortBy = $request->get('sort_by', 'date_desc');
        
        try {
            $query = EventTicketInformation::where(function($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->orWhere('shared_by_user_id', $user->id); // Tickets user shared (even after transfer)
                })
                ->with([
                    'event:id,title,start_date,end_date,location,thumbnail',
                    'ticketType:id,name,price',
                    'ticketPurchase:id,status,purchase_id',
                    'sharedByUser:id,first_name,last_name,email',
                    'user:id,first_name,last_name,email', // Current owner
                    'shares' => function($q) {
                        $q->where('status', 'pending')
                          ->with(['recipient:id,first_name,last_name,email', 'sharer:id,first_name,last_name,email']); // Load recipient and sharer for pending shares
                    }
                ])
                ->whereHas('ticketPurchase', function($query) {
                    $query->where('status', 'paid');
                });

            // Optionally hide shared tickets (hide tickets that are currently shared TO others)
            if ($hideShared) {
                $query->where(function($q) use ($user) {
                    $q->whereNull('shared_by_user_id') // Not shared to others
                      ->orWhere('shared_by_user_id', '!=', $user->id); // Or received from others
                });
            }

            $tickets = $query->get();

            // Filter out tickets without ticket types and group by event
            $tickets = $tickets->filter(function($ticket) {
                return $ticket->ticketType !== null && $ticket->event !== null;
            });
            
            // Optionally hide past events
            if ($hidePastEvents) {
                $tickets = $tickets->filter(function($ticket) {
                    return $ticket->event && $ticket->event->end_date && $ticket->event->end_date > now();
                });
            }
            
            $ticketsByEvent = $tickets->groupBy('event_id');

            // Apply sorting
            $ticketsByEvent = $ticketsByEvent->sort(function($a, $b) use ($sortBy) {
                $eventA = $a->first()->event;
                $eventB = $b->first()->event;
                
                switch ($sortBy) {
                    case 'date_asc':
                        return $eventA->start_date <=> $eventB->start_date;
                    case 'date_desc':
                    default:
                        return $eventB->start_date <=> $eventA->start_date;
                    case 'title_asc':
                        return strcasecmp($eventA->title, $eventB->title);
                    case 'title_desc':
                        return strcasecmp($eventB->title, $eventA->title);
                }
            });

            // Check if sharing is allowed for each event (load from R2 ticket settings)
            $eventSharingSettings = [];
            foreach ($ticketsByEvent as $eventId => $tickets) {
                $event = $tickets->first()->event;
                if ($event) {
                    // Check if user owns tickets for this event (not just received)
                    $hasOwnedTickets = $tickets->filter(function($ticket) use ($user) {
                        return $ticket->user_id === $user->id && 
                               ($ticket->shared_by_user_id === null || $ticket->shared_by_user_id === $user->id);
                    })->count() > 0;
                    
                    $allowSharing = false;
                    if ($hasOwnedTickets) {
                        try {
                            $ticketSettings = $this->loadTicketSettings($event);
                            $allowSharing = $ticketSettings['allow_share_ticket'] ?? false;
                        } catch (\Exception $e) {
                            // If settings can't be loaded, default to false
                            Log::warning('Failed to load ticket settings for sharing check', [
                                'event_id' => $eventId,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    $eventSharingSettings[$eventId] = $allowSharing;
                }
            }

            $html = view('tickets.partials.tickets-list', compact('ticketsByEvent', 'eventSharingSettings'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading filtered tickets: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tickets. Please try again.'
            ], 500);
        }
    }

    /**
     * Get tickets for a specific event (for sharing modal)
     */
    public function getEventTickets(Request $request)
    {
        $user = Auth::user();
        $eventId = $request->event_id;

        if (!$eventId) {
            return response()->json([
                'success' => false,
                'message' => 'Event ID is required'
            ], 400);
        }
        
        try {
            // Get ALL tickets for this event owned by the user
            $allTickets = EventTicketInformation::where('user_id', $user->id)
                ->where('event_id', $eventId)
                ->with([
                    'event:id,title,start_date,end_date,location,thumbnail',
                    'ticketType:id,name,price',
                    'ticketPurchase:id,status,purchase_id',
                ])
                ->whereHas('ticketPurchase', function($query) {
                    $query->where('status', 'paid');
                })
                ->get();

            // Filter out tickets without ticket types
            $allTickets = $allTickets->filter(function($ticket) {
                return $ticket->ticketType !== null;
            });

            // Get all ticket IDs
            $ticketIds = $allTickets->pluck('id')->toArray();

            // Get the event to filter shares by event_id as well
            $event = $allTickets->first()?->event;
            $eventId = $event?->id;

            // Get all pending shares for this event sent by this user
            // Use the same approach as the sidebar (TicketShare model uses same table)
            $pendingSharesQuery = EventTicketShare::where('status', 'pending')
                ->where('sharer_id', $user->id); // Only shares sent by this user
            
            // Add event_id filter if we have an event
            if ($eventId) {
                $pendingSharesQuery->where('event_id', $eventId);
            }
            
            $pendingShares = $pendingSharesQuery
                ->with(['recipient:id,first_name,last_name,email', 'sharer:id,first_name,last_name,email'])
                ->get();
            
            // Debug logging
            Log::info('Pending shares query for modal', [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'ticket_ids' => $ticketIds,
                'ticket_ids_count' => count($ticketIds),
                'pending_shares_count' => $pendingShares->count(),
                'pending_shares' => $pendingShares->map(function($share) {
                    return [
                        'id' => $share->id,
                        'shared_ticket_id' => $share->shared_ticket_id,
                        'shared_ticket_ids' => $share->shared_ticket_ids,
                        'recipient_id' => $share->recipient_id,
                        'sharer_id' => $share->sharer_id,
                        'status' => $share->status,
                        'event_id' => $share->event_id,
                    ];
                })->toArray()
            ]);

            // Create a map of ticket_id => pending shares
            // Filter shares to only those that reference our tickets
            $ticketPendingSharesMap = [];
            $ticketPendingRecipientsMap = [];
            
            // Also track pending shares by ticket_type_id when shared_ticket_id is NULL
            $pendingSharesByTypeId = [];
            
            // Create a set of ticket IDs for faster lookup
            $ticketIdSet = array_flip($ticketIds);
            
            foreach ($pendingShares as $share) {
                // Handle shared_ticket_id (single ticket reference)
                if (!is_null($share->shared_ticket_id) && isset($ticketIdSet[$share->shared_ticket_id])) {
                    $ticketId = $share->shared_ticket_id;
                    if (!isset($ticketPendingSharesMap[$ticketId])) {
                        $ticketPendingSharesMap[$ticketId] = [];
                        $ticketPendingRecipientsMap[$ticketId] = [];
                    }
                    $ticketPendingSharesMap[$ticketId][] = $share;
                    if ($share->recipient) {
                        $recipientName = $share->recipient->first_name . ' ' . $share->recipient->last_name;
                        if (!in_array($recipientName, $ticketPendingRecipientsMap[$ticketId])) {
                            $ticketPendingRecipientsMap[$ticketId][] = $recipientName;
                        }
                    }
                }
                // Handle case where shared_ticket_id is NULL - track by ticket_type_id
                else if (is_null($share->shared_ticket_id) && !is_null($share->ticket_type_id)) {
                    if (!isset($pendingSharesByTypeId[$share->ticket_type_id])) {
                        $pendingSharesByTypeId[$share->ticket_type_id] = [];
                    }
                    $pendingSharesByTypeId[$share->ticket_type_id][] = $share;
                }
                
                // Handle shared_ticket_ids (JSON array of ticket IDs)
                if ($share->shared_ticket_ids && is_array($share->shared_ticket_ids) && !empty($share->shared_ticket_ids)) {
                    foreach ($share->shared_ticket_ids as $ticketId) {
                        if (isset($ticketIdSet[$ticketId])) {
                            if (!isset($ticketPendingSharesMap[$ticketId])) {
                                $ticketPendingSharesMap[$ticketId] = [];
                                $ticketPendingRecipientsMap[$ticketId] = [];
                            }
                            $ticketPendingSharesMap[$ticketId][] = $share;
                            if ($share->recipient) {
                                $recipientName = $share->recipient->first_name . ' ' . $share->recipient->last_name;
                                if (!in_array($recipientName, $ticketPendingRecipientsMap[$ticketId])) {
                                    $ticketPendingRecipientsMap[$ticketId][] = $recipientName;
                                }
                            }
                        }
                    }
                }
            }
            
            // Log pending shares by type for debugging
            Log::info('Pending shares by ticket_type_id (where shared_ticket_id is NULL)', [
                'pending_shares_by_type' => array_map(function($shares) {
                    return count($shares);
                }, $pendingSharesByTypeId),
            ]);
            
            // Debug logging for the map
            Log::info('Ticket pending shares map after filtering', [
                'total_pending_shares' => $pendingShares->count(),
                'tickets_with_pending' => array_keys($ticketPendingSharesMap),
                'map_details' => array_map(function($shares) {
                    return count($shares);
                }, $ticketPendingSharesMap),
                'all_ticket_ids' => $ticketIds,
            ]);

            // Group all tickets by type (including those with pending shares)
            $ticketsByType = $allTickets->groupBy('ticket_type_id');

            return response()->json([
                'success' => true,
                'event' => $allTickets->first()?->event,
                'tickets_by_type' => $ticketsByType->map(function($ticketsOfType) use ($user, $ticketPendingSharesMap, $ticketPendingRecipientsMap, $pendingSharesByTypeId) {
                    $ticketType = $ticketsOfType->first()->ticketType;
                    $ticketTypeId = $ticketType->id;
                    
                    // Get pending shares for this ticket type where shared_ticket_id is NULL
                    $typePendingShares = $pendingSharesByTypeId[$ticketTypeId] ?? [];
                    $typePendingQuantity = collect($typePendingShares)->sum('quantity');
                    
                    // Count available vs pending tickets using the map
                    // First, get tickets that have explicit pending shares (shared_ticket_id is set)
                    $ticketsWithExplicitPending = $ticketsOfType->filter(function($ticket) use ($ticketPendingSharesMap) {
                        return isset($ticketPendingSharesMap[$ticket->id]) && count($ticketPendingSharesMap[$ticket->id]) > 0;
                    });
                    
                    // Get available tickets (not checked in, not shared to others, no explicit pending)
                    // A ticket is available if:
                    // 1. Currently owned by the user (user_id = user->id) - already filtered in main query
                    // 2. Not checked in
                    // 3. Not already shared/transferred to others (shared_by_user_id is NULL)
                    // 4. No explicit pending shares
                    // 
                    // Note: If a ticket has shared_by_user_id set, it means it was transferred to someone else,
                    // so it's no longer owned by the user (user_id would be different). Since we already filter
                    // by user_id = user->id in the main query, any tickets here are still owned by the user.
                    // So shared_by_user_id should be NULL for available tickets (not yet shared/transferred).
                    $availableTickets = $ticketsOfType->filter(function($ticket) use ($user, $ticketPendingSharesMap) {
                        // Double-check: ticket must be owned by the user
                        if ($ticket->user_id != $user->id) {
                            return false;
                        }
                        
                        // Ticket must not be checked in
                        if (!is_null($ticket->checked_in_at)) {
                            return false;
                        }
                        
                        // Ticket must not have explicit pending shares
                        $hasPending = isset($ticketPendingSharesMap[$ticket->id]) && count($ticketPendingSharesMap[$ticket->id]) > 0;
                        if ($hasPending) {
                            return false;
                        }
                        
                        // Ticket must not be already shared/transferred to others
                        // If shared_by_user_id is set, it means the ticket was shared/transferred,
                        // but since user_id = user->id, the ticket is still owned by the user.
                        // This shouldn't happen in normal flow, but to be safe, we check:
                        // - shared_by_user_id should be NULL (not shared/transferred)
                        return is_null($ticket->shared_by_user_id);
                    });
                    
                    // Count explicit pending (where shared_ticket_id is set)
                    $explicitPendingCount = $ticketsWithExplicitPending->count();
                    
                    // Calculate available count: available tickets minus type-level pending quantity
                    // Type-level pending (where shared_ticket_id is NULL) reduces available count
                    $availableCount = max(0, $availableTickets->count() - $typePendingQuantity);
                    
                    // Pending count: explicit pending + type-level pending
                    $pendingCount = $explicitPendingCount + $typePendingQuantity;
                    
                    // Get pending share details (unique recipients across all pending tickets of this type)
                    $pendingSharesInfo = [];
                    // Add recipients from explicit pending shares (shared_ticket_id is set)
                    foreach ($ticketsOfType as $ticket) {
                        if (isset($ticketPendingRecipientsMap[$ticket->id])) {
                            foreach ($ticketPendingRecipientsMap[$ticket->id] as $recipientName) {
                                if (!in_array($recipientName, $pendingSharesInfo)) {
                                    $pendingSharesInfo[] = $recipientName;
                                }
                            }
                        }
                    }
                    // Add recipients from type-level pending shares (shared_ticket_id is NULL)
                    foreach ($typePendingShares as $share) {
                        if ($share->recipient) {
                            $recipientName = $share->recipient->first_name . ' ' . $share->recipient->last_name;
                            if (!in_array($recipientName, $pendingSharesInfo)) {
                                $pendingSharesInfo[] = $recipientName;
                            }
                        }
                    }
                    
                    // Log counts for debugging
                    Log::info('Ticket type counts', [
                        'ticket_type_id' => $ticketType->id,
                        'ticket_type_name' => $ticketType->name,
                        'total_tickets' => $ticketsOfType->count(),
                        'available_tickets_count' => $availableTickets->count(),
                        'type_pending_quantity' => $typePendingQuantity,
                        'explicit_pending_count' => $explicitPendingCount,
                        'available_count' => $availableCount,
                        'pending_count' => $pendingCount,
                        'ticket_ids' => $ticketsOfType->pluck('id')->toArray(),
                        'tickets_with_explicit_pending' => $ticketsWithExplicitPending->pluck('id')->toArray(),
                        'type_pending_shares' => count($typePendingShares),
                    ]);
                    
                    return [
                        'ticket_type' => $ticketType,
                        'available_count' => $availableCount,
                        'pending_count' => $pendingCount,
                        'total_count' => $ticketsOfType->count(),
                        'pending_recipients' => $pendingSharesInfo,
                        'tickets' => $ticketsOfType->map(function($ticket) use ($user, $ticketPendingSharesMap, $ticketPendingRecipientsMap) {
                            $hasPending = isset($ticketPendingSharesMap[$ticket->id]) && count($ticketPendingSharesMap[$ticket->id]) > 0;
                            $pendingRecipients = $ticketPendingRecipientsMap[$ticket->id] ?? [];
                            
                            return [
                                'id' => $ticket->id,
                                'ticket_id' => $ticket->ticket_id,
                                'is_shared_to_others' => !is_null($ticket->shared_by_user_id) && $ticket->shared_by_user_id == $user->id,
                                'is_received_from_others' => !is_null($ticket->shared_by_user_id) && $ticket->shared_by_user_id != $user->id,
                                'is_checked_in' => !is_null($ticket->checked_in_at),
                                'has_pending_shares' => $hasPending,
                                'pending_recipients' => $pendingRecipients,
                                'checked_in_at' => $ticket->checked_in_at,
                                'shared_by' => $ticket->sharedByUser ? [
                                    'name' => $ticket->sharedByUser->first_name . ' ' . $ticket->sharedByUser->last_name,
                                    'email' => $ticket->sharedByUser->email
                                ] : null
                            ];
                        })
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading event tickets: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tickets for this event'
            ], 500);
        }
    }

    /**
     * Share tickets with other users
     */
    public function shareTickets(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'event_id' => 'required|integer',
            'ticket_type_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'required|integer|exists:users,id'
        ]);

        $eventId = $validated['event_id'];
        $ticketTypeId = $validated['ticket_type_id'];
        $quantity = $validated['quantity'];
        $recipients = $validated['recipients'];

        // Check if event exists
        $event = \App\Models\Event::find($eventId);
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found'
            ], 404);
        }

        // Check if sharing is allowed for this event
        try {
            $ticketSettings = $this->loadTicketSettings($event);
            $allowSharing = $ticketSettings['allow_share_ticket'] ?? false;
            
            if (!$allowSharing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket sharing is not allowed for this event'
                ], 403);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to load ticket settings for sharing validation', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unable to verify sharing settings for this event'
            ], 500);
        }

        // Check if event has ended
        if ($event->end_date && $event->end_date < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot share tickets for events that have already ended'
            ], 400);
        }

        // Verify the user has enough tickets of this type (exclude checked-in and already shared tickets)
        $availableTickets = EventTicketInformation::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('ticket_type_id', $ticketTypeId)
            ->whereNull('checked_in_at') // Don't count checked-in tickets
            ->where(function($query) use ($user) {
                $query->whereNull('shared_by_user_id') // Not shared to others
                      ->orWhere('shared_by_user_id', '!=', $user->id); // Or received from others
            })
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->count();

        if ($availableTickets < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "You only have {$availableTickets} tickets of this type available for sharing"
            ], 400);
        }

        // Get random tickets of this type to share (exclude checked-in and already shared tickets)
        $ticketsToShare = EventTicketInformation::where('user_id', $user->id)
            ->where('event_id', $eventId)
            ->where('ticket_type_id', $ticketTypeId)
            ->whereNull('checked_in_at') // Don't share checked-in tickets
            ->where(function($query) use ($user) {
                $query->whereNull('shared_by_user_id') // Not shared to others
                      ->orWhere('shared_by_user_id', '!=', $user->id); // Or received from others
            })
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->inRandomOrder()
            ->take($quantity)
            ->get();

        if ($ticketsToShare->count() < $quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough tickets available for sharing'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $errors = [];
            $successCount = 0;

            foreach ($recipients as $recipientId) {
                $recipient = User::find($recipientId);
                if (!$recipient) {
                    $errors[] = "User with ID {$recipientId} not found";
                    continue;
                }

                // Check if there's already a share for this combination
                $existingShare = TicketShare::where('event_id', $eventId)
                    ->where('sharer_id', $user->id)
                    ->where('recipient_id', $recipientId)
                    ->first();

                if ($existingShare) {
                    if ($existingShare->status === 'pending') {
                        $errors[] = "You already have a pending share request with {$recipient->email} for this event";
                        continue;
                    } else {
                        // Update existing share to create a new request
                        $existingShare->update([
                            'ticket_type_id' => $ticketTypeId,
                            'quantity' => $quantity,
                            'status' => 'pending',
                            'expires_at' => now()->addDays(7),
                            'shared_ticket_ids' => [],
                            'message' => null,
                        ]);
                        $share = $existingShare->fresh();
                        
                        Log::info('Updated existing ticket share to create new request', [
                            'share_id' => $share->id,
                            'previous_status' => $existingShare->status,
                            'new_status' => 'pending',
                            'event_id' => $eventId,
                            'sharer_id' => $user->id,
                            'recipient_id' => $recipientId
                        ]);
                    }
                } else {
                    // Create new share record
                    try {
                        $share = TicketShare::create([
                            'event_id' => $eventId,
                            'ticket_type_id' => $ticketTypeId,
                            'sharer_id' => $user->id,
                            'recipient_id' => $recipientId,
                            'quantity' => $quantity,
                            'status' => 'pending',
                            'expires_at' => now()->addDays(7),
                            'shared_ticket_ids' => [],
                        ]);
                    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                        $errors[] = "You already have a pending share request with {$recipient->email} for this event";
                        continue;
                    } catch (\Exception $e) {
                        Log::error('Failed to create ticket share: ' . $e->getMessage(), [
                            'event_id' => $eventId,
                            'sharer_id' => $user->id,
                            'recipient_id' => $recipientId,
                            'ticket_type_id' => $ticketTypeId
                        ]);
                        $errors[] = "Failed to create share request for {$recipient->email}";
                        continue;
                    }
                }

                // Load relationships for notification
                $share->load(['sharer', 'event', 'ticketType']);

                // Send notification to recipient
                try {
                    Log::info('Attempting to send ticket share notification', [
                        'share_id' => $share->id,
                        'recipient_id' => $recipientId,
                        'sharer_id' => $user->id,
                        'event_id' => $eventId
                    ]);
                    
                    // Note: We don't use Laravel's notify() here because we have a custom notifications table structure
                    // Create in-app notification
                    $sharerName = trim("{$user->first_name} {$user->last_name}") ?: $user->email;
                    $shareMessage = "{$sharerName} wants to share a ticket for '{$event->title}' with you.";
                    
                    \App\Models\Notification::create([
                        'user_id' => $recipientId,
                        'admin_id' => null,
                        'type' => 'ticket',
                        'action' => 'share_request',
                        'reference_id' => $share->id,
                        'title' => 'Ticket Share Request',
                        'message' => $shareMessage,
                        'is_read' => false,
                        'data' => [
                            'share_id' => $share->id,
                            'event_id' => $eventId,
                            'event_title' => $event->title,
                            'sharer_id' => $user->id,
                            'sharer_name' => $sharerName,
                        ],
                    ]);
                    
                    // Broadcast real-time notification
                    \App\Services\NotificationService::sendUserNotification(
                        $recipientId,
                        $shareMessage,
                        'info',
                        'Ticket Share Request'
                    );
                    
                    Log::info('Ticket share notification sent successfully', [
                        'share_id' => $share->id,
                        'recipient_id' => $recipientId,
                        'sharer_id' => $user->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send share request notification: ' . $e->getMessage(), [
                        'share_id' => $share->id,
                        'recipient_id' => $recipientId,
                        'sharer_id' => $user->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                $successCount++;
            }

            DB::commit();

            if ($successCount > 0) {
                $message = "Successfully shared tickets with {$successCount} user(s)";
                if (!empty($errors)) {
                    $message .= ". Some errors occurred: " . implode(', ', $errors);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No share requests were created. Errors: ' . implode(', ', $errors),
                    'errors' => $errors
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ticket sharing transaction failed: ' . $e->getMessage(), [
                'event_id' => $eventId,
                'sharer_id' => $user->id,
                'recipients' => $recipients
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process ticket sharing request. Please try again.'
            ], 500);
        }
    }

    /**
     * Search users for ticket sharing
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        
        Log::info('User search request', [
            'query' => $query,
            'user_id' => auth()->id()
        ]);
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'users' => []
            ]);
        }

        try {
            $users = User::where('id', '!=', auth()->id())
                ->where(function($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->select('id', 'first_name', 'last_name', 'email')
                ->limit(10)
                ->get();

            Log::info('User search results', [
                'query' => $query,
                'results_count' => $users->count(),
                'users' => $users->toArray()
            ]);

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error searching users: ' . $e->getMessage(), [
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users'
            ], 500);
        }
    }

    /**
     * Load ticket settings from R2 storage.
     */
    private function loadTicketSettings(Event $event)
    {
        try {
            $eventDir = "events/{$event->id}";
            $disk = Storage::disk('r2');
            $settingsPath = $eventDir . '/ticket_settings.json';
            
            if ($disk->exists($settingsPath)) {
                $content = $disk->get($settingsPath);
                
                if ($content) {
                    $settings = json_decode($content, true);
                    
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error("Failed to parse ticket settings JSON: " . json_last_error_msg());
                        return [];
                    }
                    
                    return $settings;
                }
            }
            
            // Return default settings if file doesn't exist
            return [
                'max_tickets_per_order' => 10,
                'refund_policy' => 'Refunds are available up to 7 days before the event. A processing fee may apply.',
                'allow_refunds' => true,
                'require_approval' => false,
                'allow_share_ticket' => true,
                'limit_per_user' => false,
                'max_tickets_per_user' => 1,
            ];
            
        } catch (\Exception $e) {
            Log::error('Error loading ticket settings from R2', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
            
            // Return default settings on error
            return [
                'max_tickets_per_order' => 10,
                'refund_policy' => 'Refunds are available up to 7 days before the event. A processing fee may apply.',
                'allow_refunds' => true,
                'require_approval' => false,
                'allow_share_ticket' => true,
                'limit_per_user' => false,
                'max_tickets_per_user' => 1,
            ];
        }
    }
}

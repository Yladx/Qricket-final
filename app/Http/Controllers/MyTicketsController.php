<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventTicketInformation;
use App\Models\TicketShare;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                        $q->where('status', 'pending'); // Only load pending shares for performance
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

            return view('tickets.my-tickets', compact('ticketsByEvent', 'pendingSharesReceived', 'pendingSharesSent', 'hideShared', 'hidePastEvents', 'sortBy'));
            
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
                        $q->where('status', 'pending'); // Only load pending shares for performance
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

            $html = view('tickets.partials.tickets-list', compact('ticketsByEvent'))->render();
            
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
                    'shares' => function($query) {
                        $query->where('status', 'pending');
                    }
                ])
                ->whereHas('ticketPurchase', function($query) {
                    $query->where('status', 'paid');
                })
                ->get();

            // Filter out tickets without ticket types
            $allTickets = $allTickets->filter(function($ticket) {
                return $ticket->ticketType !== null;
            });

            // Now filter for available tickets (not checked-in, not shared to others, no pending shares)
            $availableTickets = $allTickets->filter(function($ticket) use ($user) {
                return is_null($ticket->checked_in_at) && // Not checked in
                       (is_null($ticket->shared_by_user_id) || $ticket->shared_by_user_id != $user->id) && // Not shared to others
                       (!$ticket->shares || $ticket->shares->where('status', 'pending')->count() === 0); // No pending shares
            });

            $ticketsByType = $availableTickets->groupBy('ticket_type_id');

            return response()->json([
                'success' => true,
                'event' => $allTickets->first()?->event,
                'tickets_by_type' => $ticketsByType->map(function($ticketsOfType) use ($user) {
                    $ticketType = $ticketsOfType->first()->ticketType;
                    return [
                        'ticket_type' => $ticketType,
                        'tickets' => $ticketsOfType->map(function($ticket) use ($user) {
                            return [
                                'id' => $ticket->id,
                                'ticket_id' => $ticket->ticket_id,
                                'is_shared_to_others' => !is_null($ticket->shared_by_user_id) && $ticket->shared_by_user_id == $user->id,
                                'is_received_from_others' => !is_null($ticket->shared_by_user_id) && $ticket->shared_by_user_id != $user->id,
                                'is_checked_in' => !is_null($ticket->checked_in_at),
                                'has_pending_shares' => $ticket->shares && $ticket->shares->where('status', 'pending')->count() > 0,
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
}

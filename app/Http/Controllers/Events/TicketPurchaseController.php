<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventTicketInformation;
use App\Models\TicketPurchase;
use App\Models\EventTicket;
use App\Models\ActivityLog;
use App\Models\User;
use App\Events\AdminDashboardUpdated;
use App\Events\SalesReportUpdated;
use App\Events\DashboardStatsUpdated;
use App\Events\EventStatsChanged;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketPurchaseConfirmationMail;
use Carbon\Carbon;

class TicketPurchaseController extends Controller
{
    /**
     * Base view namespace for ticket views by area.
     * Adjust this method to point to 'admin.tickets.' or 'support.tickets.' when needed.
     */
    private function ticketView(string $view): string
    {
        // Event-facing views live under resources/views/events/tickets/
        return 'events.tickets.' . $view;
    }
    /**
     * Show the ticket purchase form for an event.
     */
    public function show(Event $event)
    {
        // Block purchase if event is not published (except for organizers and staff)
        $user = Auth::user();
        $isEventOrganizer = $user && $event->user_id === $user->id;
        $isEventStaff = $user && $event->staff()->where('user_id', $user->id)->exists();
        $isAdmin = Auth::guard('admin')->check();
        
        if ($event->status !== 'published' && !$isEventOrganizer && !$isEventStaff && !$isAdmin) {
            return redirect()->route('events.show', $event)->with('error', 'This event is not yet published and tickets are not available for purchase.');
        }

        // Block purchase if event already ended
        if ($event->end_date && \Carbon\Carbon::parse($event->end_date)->isPast()) {
            return redirect()->route('events.show', $event)->with('error', 'Ticket sales are closed. This event has already ended.');
        }

        // Allow organizers and staff to buy tickets as requested (no blocks here)

        // Get available ticket types for this event
        // For organizers, staff, and admins, show all tickets regardless of sale dates
        if ($isEventOrganizer || $isEventStaff || $isAdmin) {
            $ticketTypes = $event->eventTickets()
                ->where(function($query) {
                    // Only show tickets that have available quantity (quantity - sold_count > 0 or quantity is null for unlimited)
                    $query->whereNull('quantity')
                          ->orWhereRaw('(quantity - COALESCE(sold_count, 0)) > 0');
                })
                ->get();
        } else {
            // For regular users, apply sale date filters
            $ticketTypes = $event->eventTickets()
                ->where(function($query) {
                    $query->whereNull('sale_start')
                          ->orWhere('sale_start', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('sale_end')
                          ->orWhere('sale_end', '>=', now());
                })
                ->where(function($query) {
                    // Only show tickets that have available quantity (quantity - sold_count > 0 or quantity is null for unlimited)
                    $query->whereNull('quantity')
                          ->orWhereRaw('(quantity - COALESCE(sold_count, 0)) > 0');
                })
                ->get();
        }

        return view($this->ticketView('ticket-purchase'), compact('event', 'ticketTypes'));
    }

    /**
     * Process ticket purchase and create Xendit payment.
     */
    public function purchase(Request $request, Event $event)
    {
        // Block purchase if event is not published (except for organizers and staff)
        $user = Auth::user();
        $isEventOrganizer = $user && $event->user_id === $user->id;
        $isEventStaff = $user && $event->staff()->where('user_id', $user->id)->exists();
        $isAdmin = Auth::guard('admin')->check();
        
        if ($event->status !== 'published' && !$isEventOrganizer && !$isEventStaff && !$isAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'This event is not yet published and tickets are not available for purchase.'
            ], 400);
        }

        // Block purchase if event already ended
        if ($event->end_date && \Carbon\Carbon::parse($event->end_date)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket sales are closed. This event has already ended.'
            ], 400);
        }

        // Allow organizers and staff to buy tickets as requested (no blocks here)

        $validator = Validator::make($request->all(), [
            'tickets' => 'required|array|min:1',
            'tickets.*.ticket_type_id' => 'required|exists:event_tickets,id',
            'tickets.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tickets = $request->input('tickets', []);
            
            // Load ticket settings from R2 to get limits and policies
            $ticketSettings = $this->loadTicketSettings($event);
            $maxTicketsPerOrder = $ticketSettings['max_tickets_per_order'] ?? 10;
            $limitPerUser = $ticketSettings['limit_per_user'] ?? false;
            $maxTicketsPerUser = $ticketSettings['max_tickets_per_user'] ?? 1;
            $refundPolicy = $ticketSettings['refund_policy'] ?? null;

            // Admins have no ticket limits; also organizers/staff already bypass publish checks above
            if ($isAdmin) {
                $maxTicketsPerOrder = PHP_INT_MAX;
                $limitPerUser = false;
                $maxTicketsPerUser = PHP_INT_MAX;
            }
            
            // Calculate total quantity and validate
            $totalQuantity = array_sum(array_column($tickets, 'quantity'));
            
            // Validate total quantity against max_tickets_per_order
            if ($totalQuantity > $maxTicketsPerOrder) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum {$maxTicketsPerOrder} tickets allowed per order."
                ], 400);
            }

            // Validate max tickets per user if limit is enabled
            if ($limitPerUser && $maxTicketsPerUser > 0) {
                // Count existing tickets for this event only (per-event limiter)
                // If user is not authenticated, fall back to email-based matching
                $buyerId = Auth::id();
                $buyerEmail = trim(strtolower($request->input('customer_email')));

                $existingTicketsQuery = TicketPurchase::where('event_id', $event->id)
                    ->where('status', 'paid')
                    ->where(function($q) use ($buyerId, $buyerEmail) {
                        // Match by authenticated user id
                        if ($buyerId) {
                            $q->where('user_id', $buyerId);
                        }
                        // Also match by email captured at checkout (JSON column)
                        if (!empty($buyerEmail)) {
                            // Use Laravel's JSON column syntax to avoid DB-specific functions
                            $q->orWhere('customer_details->email', $buyerEmail)
                              ->orWhere('customer_details->email', strtoupper($buyerEmail))
                              ->orWhere('customer_details->email', strtolower($buyerEmail));
                        }
                    });

                $existingTicketsCount = (int) $existingTicketsQuery->sum('quantity');
                
                $totalTicketsAfterPurchase = $existingTicketsCount + $totalQuantity;
                
                if ($totalTicketsAfterPurchase > $maxTicketsPerUser) {
                    $remainingAllowed = $maxTicketsPerUser - $existingTicketsCount;
                    if ($remainingAllowed <= 0) {
                        return response()->json([
                            'success' => false,
                            'message' => "You have already reached the maximum limit of {$maxTicketsPerUser} tickets for this event."
                        ], 400);
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "You can only purchase {$remainingAllowed} more ticket(s) for this event (limit: {$maxTicketsPerUser} tickets per user)."
                        ], 400);
                    }
                }
            }

            // Validate each ticket type
            $totalAmount = 0;
            $ticketDetails = [];
            
            foreach ($tickets as $ticketData) {
                $ticketType = EventTicket::findOrFail($ticketData['ticket_type_id']);
                
                // Verify ticket belongs to this event
                if ($ticketType->event_id !== $event->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid ticket type for this event'
                    ], 400);
                }

                // Check if tickets are still available
                $availableQuantity = $ticketType->quantity ? ($ticketType->quantity - ($ticketType->sold_count ?? 0)) : null;
                if ($availableQuantity && $availableQuantity < $ticketData['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Not enough {$ticketType->name} tickets available. Only {$availableQuantity} tickets left."
                    ], 400);
                }

                $ticketTotal = $ticketType->price * $ticketData['quantity'];
                $totalAmount += $ticketTotal;
                
                $ticketDetails[] = [
                    'ticket_type' => $ticketType,
                    'quantity' => $ticketData['quantity'],
                    'unit_price' => $ticketType->price,
                    'total_price' => $ticketTotal
                ];
            }

            // Check if bypass is enabled or if this is a free event (totalAmount = 0)
            $bypassPayment = config('services.xendit.bypass_payment', false);
            $isFreeEvent = $totalAmount == 0;

            // Create ticket purchase record with multiple ticket types
            $purchase = TicketPurchase::create([
                'user_id' => Auth::id(),
                'event_id' => $event->id,
                'purchase_id' => TicketPurchase::generatePurchaseId(),
                'quantity' => $totalQuantity,
                'unit_price' => $totalAmount / $totalQuantity, // Average unit price
                'total_amount' => $totalAmount,
                'currency' => config('services.xendit.currency', 'PHP'),
                'status' => ($bypassPayment || $isFreeEvent) ? 'paid' : 'pending',
                'customer_details' => [
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'phone' => $request->customer_phone,
                    'address' => $request->customer_address,
                ],
                'expires_at' => ($bypassPayment || $isFreeEvent) ? null : now()->addHours(24), // No expiry for bypass or free events
                'payment_details' => [
                    'refund_policy' => $refundPolicy,
                    'bypass_mode' => $bypassPayment,
                    'is_free_event' => $isFreeEvent,
                    'max_tickets_per_order' => $maxTicketsPerOrder,
                    'ticket_breakdown' => $ticketDetails, // Store detailed breakdown
                ],
            ]);

            // Log purchase initiated/created
            try {
                \App\Models\EventActivityLog::log(
                    $event->id,
                    $this->getActivityLogUserId(),
                    ($bypassPayment || $isFreeEvent) ? 'ticket_purchased' : 'purchase_created',
                    ($bypassPayment || $isFreeEvent) ? ($isFreeEvent ? 'Free ticket purchase completed.' : 'Ticket purchase completed (bypass).') : 'Purchase created, awaiting payment.',
                    [
                        'event_title' => $event->title,
                        'purchase_id' => $purchase->purchase_id,
                        'quantity' => $totalQuantity,
                        'total_amount' => $totalAmount,
                    ]
                );
            } catch (\Throwable $e) { /* ignore */ }

            if ($bypassPayment || $isFreeEvent) {
                // Bypass mode or Free event: Mark as paid and update sold count immediately
                $prefix = $isFreeEvent ? 'FREE' : 'BYPASS';
                
                // Calculate fees even for bypass/free events (for consistency)
                $paymentMethod = 'BYPASS';
                $fees = \App\Services\XenditFeeCalculator::calculateFees($totalAmount, $paymentMethod);
                
                $purchase->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $paymentMethod,
                    'transaction_fee' => $fees['transaction_fee'],
                    'vat_amount' => $fees['vat_amount'],
                    'net_amount' => $fees['net_amount'],
                    'total_fees' => $fees['total_fees'],
                    'xendit_payment_id' => $prefix . '_' . $purchase->purchase_id,
                    'xendit_invoice_id' => $prefix . '_INV_' . $purchase->purchase_id,
                ]);

                // Update sold count for each ticket type
                foreach ($ticketDetails as $detail) {
                    $detail['ticket_type']->increment('sold_count', $detail['quantity']);
                }

                // Generate individual tickets with QR codes
                $this->generateIndividualTickets($purchase);

                // Email invoice to user (first ticket link)
                try { $this->sendInvoiceEmail($purchase); } catch (\Throwable $t) { Log::error('Send invoice email failed (bypass): '.$t->getMessage()); }

                // Broadcast real-time updates (don't fail the purchase if broadcasting fails)
                try {
                    $this->broadcastTicketPurchaseUpdates($event, $purchase);
                } catch (\Exception $e) {
                    Log::error('Failed to broadcast purchase updates (bypass mode): ' . $e->getMessage());
                }
                
                // Log ticket purchase activity
                try {
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'ticket_purchased',
                        'description' => "User purchased {$totalQuantity} ticket(s) for event: {$event->title}",
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'metadata' => [
                            'purchase_id' => $purchase->purchase_id,
                            'event_id' => $event->id,
                            'event_title' => $event->title,
                            'quantity' => $totalQuantity,
                            'total_amount' => $totalAmount,
                            'currency' => $purchase->currency,
                            'payment_method' => 'bypass',
                            'timestamp' => now()->toISOString()
                        ]
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to log ticket purchase activity: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => $isFreeEvent ? 'Free ticket purchase completed successfully' : 'Purchase completed successfully (Bypass Mode)',
                    'data' => [
                        'purchase_id' => $purchase->purchase_id,
                        'bypass_mode' => true,
                        'is_free_event' => $isFreeEvent,
                        'redirect_url' => route('tickets.purchase.success', $purchase->purchase_id)
                    ]
                ]);
            } else {
                // Normal mode: Create Xendit invoice
                $xenditResponse = $this->createXenditInvoice($purchase);

                if ($xenditResponse['success']) {
                    // Defer DB changes until paid: cache an intent instead of updating row here
                    try {
                        $intent = [
                            'type' => 'ticket',
                            'purchase_id' => $purchase->purchase_id,
                            'user_id' => $purchase->user_id,
                            'event_id' => $purchase->event_id,
                            'amount' => $totalAmount,
                            'created_at' => now()->toIso8601String(),
                        ];
                        (new \App\Services\PaymentIntentService())->store($xenditResponse['data']['id'], $intent);
                    } catch (\Exception $e) { Log::warning('Cache intent store failed (ticket)', ['error' => $e->getMessage()]); }

                    // No email sent here - only send on successful payment

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment invoice created successfully',
                        'data' => [
                            'purchase_id' => $purchase->purchase_id,
                            'invoice_url' => $xenditResponse['data']['invoice_url'],
                            'amount' => $totalAmount,
                            'expires_at' => $purchase->expires_at->format('Y-m-d H:i:s'),
                        ]
                    ]);
                } else {
                    // Do not create or update rows here; simply return error
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create payment invoice: ' . $xenditResponse['message']
                    ], 500);
                }
            }

        } catch (\Exception $e) {
            Log::error('Ticket purchase failed: ' . $e->getMessage(), [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your purchase. Please try again.'
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

    /**
     * Create Xendit invoice for payment.
     */
    private function createXenditInvoice(TicketPurchase $purchase)
    {
        try {
            $xenditApiKey = config('services.xendit.secret_key');
            
            if (!$xenditApiKey) {
                throw new \Exception('Xendit API key not configured');
            }

            $data = [
                'external_id' => $purchase->purchase_id,
                'amount' => $purchase->total_amount,
                'description' => "Ticket purchase for {$purchase->event->title}",
                'invoice_duration' => 1800, // 30 minutes in seconds
                'customer' => [
                    'given_names' => $purchase->customer_details['name'],
                    'email' => $purchase->customer_details['email'],
                    'mobile_number' => $purchase->customer_details['phone'],
                ],
                'customer_notification_preference' => [
                    'invoice_created' => ['email'],
                    'invoice_reminder' => ['email'],
                    'invoice_paid' => ['email'],
                    'invoice_expired' => ['email'],
                ],
                'success_redirect_url' => route('tickets.purchase.success', $purchase->purchase_id),
                'failure_redirect_url' => route('tickets.purchase.failure', $purchase->purchase_id),
                'currency' => $purchase->currency,
                'payment_methods' => ['CREDIT_CARD', 'GCASH', 'PAYMAYA', 'SHOPEEPAY'],
                'items' => $this->buildInvoiceItems($purchase),
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.xendit.co/v2/invoices');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode($xenditApiKey . ':'),
                'Content-Type: application/json',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 || $httpCode === 201) {
                $responseData = json_decode($response, true);
                return [
                    'success' => true,
                    'data' => $responseData
                ];
            } else {
                $errorData = json_decode($response, true);
                return [
                    'success' => false,
                    'message' => $errorData['message'] ?? 'Unknown error occurred',
                    'http_code' => $httpCode
                ];
            }

        } catch (\Exception $e) {
            Log::error('Xendit invoice creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Build invoice items for Xendit invoice
     */
    private function buildInvoiceItems(TicketPurchase $purchase)
    {
        $items = [];
        $ticketBreakdown = $purchase->payment_details['ticket_breakdown'] ?? [];
        
        if (!empty($ticketBreakdown)) {
            // Handle multiple ticket types
            foreach ($ticketBreakdown as $detail) {
                // Handle both object and array cases
                $ticketTypeName = 'Event Ticket';
                if (is_object($detail['ticket_type'])) {
                    $ticketTypeName = $detail['ticket_type']->name ?? 'Event Ticket';
                } elseif (is_array($detail['ticket_type'])) {
                    $ticketTypeName = $detail['ticket_type']['name'] ?? 'Event Ticket';
                } elseif (is_string($detail['ticket_type'])) {
                    $ticketTypeName = $detail['ticket_type'];
                }
                
                $items[] = [
                    'name' => $ticketTypeName,
                    'quantity' => $detail['quantity'],
                    'price' => $detail['unit_price'],
                    'category' => 'Event Ticket',
                ];
            }
        } else {
            // Fallback for single ticket type (backward compatibility)
            $items[] = [
                'name' => 'Event Ticket',
                'quantity' => $purchase->quantity,
                'price' => $purchase->unit_price,
                'category' => 'Event Ticket',
            ];
        }
        
        return $items;
    }

    /**
     * Handle successful payment redirect.
     */
    public function success($purchaseId)
    {
        // Require authentication
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $purchase = TicketPurchase::with(['event', 'user'])
            ->where('purchase_id', $purchaseId)
            ->firstOrFail();

        // Ensure the authenticated user owns this purchase (or received ticket)
        if ((int)$purchase->user_id !== (int)Auth::id()) {
            // As a fallback, allow if the user owns at least one ticket from this purchase
            $ownsAnyTicket = \App\Models\EventTicketInformation::where('ticket_purchase_id', $purchase->id)
                ->where('user_id', Auth::id())
                ->exists();
            if (!$ownsAnyTicket) {
                abort(403, 'Unauthorized to view this purchase.');
            }
        }
        
        return view($this->ticketView('purchase-success'), compact('purchase'));
    }

    /**
     * Handle failed payment redirect.
     */
    public function failure($purchaseId)
    {
        $purchase = TicketPurchase::with(['event'])
            ->where('purchase_id', $purchaseId)
            ->firstOrFail();
        
        return view($this->ticketView('purchase-failure'), compact('purchase'));
    }

    /**
     * Show user's ticket purchases.
     */
    public function myPurchases()
    {
        // Get all tickets where user_id = current_user (both owned and received)
        $userTickets = \App\Models\EventTicketInformation::where('user_id', Auth::id())
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->with(['ticketPurchase.event', 'ticketPurchase.user'])
            ->get()
            ->groupBy('ticketPurchase.event_id');
        
        // Get tickets that current user purchased but gave to others
        $giftedTickets = \App\Models\EventTicketInformation::whereHas('ticketPurchase', function($query) {
                $query->where('user_id', Auth::id())
                      ->where('status', 'paid');
            })
            ->where('user_id', '!=', Auth::id()) // Given to someone else
            ->with(['ticketPurchase.event', 'user'])
            ->get()
            ->groupBy('ticketPurchase.event_id');
        
        // Get all unique event IDs
        $allEventIds = $userTickets->keys()
            ->merge($giftedTickets->keys())
            ->unique();
        
        // Process each event
        $events = $allEventIds->map(function ($eventId) use ($userTickets, $giftedTickets) {
                // Check what type of tickets this event has
                $hasUserTickets = $userTickets->has($eventId);
                $hasGifted = $giftedTickets->has($eventId);
                
                // Get event info from user tickets or gifted tickets
                if ($hasUserTickets) {
                    $userTicketData = $userTickets->get($eventId);
                    $event = $userTicketData->first()->ticketPurchase->event;
                    $totalAmount = 0; // No amount for individual tickets
                    $latestPurchase = null;
                } else {
                    $giftedData = $giftedTickets->get($eventId);
                    $event = $giftedData->first()->ticketPurchase->event;
                    $totalAmount = 0;
                    $latestPurchase = null;
                }
                
                // Collect all individual tickets for this event
                $individualTickets = collect();
                $totalTickets = 0;
                $hasOwnedTickets = false;
                $hasReceivedTickets = false;
                
                // Add user tickets (both owned and received)
                if ($hasUserTickets) {
                    $userTicketData = $userTickets->get($eventId);
                    $userTicketsForEvent = $userTicketData->map(function ($ticket) use (&$hasOwnedTickets, &$hasReceivedTickets) {
                        // Check if this ticket was purchased by current user or received
                        if ($ticket->ticketPurchase->user_id == Auth::id()) {
                            $ticket->ticket_type = 'owned'; // These are owned tickets
                            $hasOwnedTickets = true;
                        } else {
                            $ticket->ticket_type = 'received'; // These are received tickets
                            $hasReceivedTickets = true;
                        }
                        return $ticket;
                    });
                    $individualTickets = $individualTickets->merge($userTicketsForEvent);
                    $totalTickets += $userTicketData->count();
                }
                
                // Add gifted tickets (purchased by current user but given to others)
                if ($hasGifted) {
                    $giftedData = $giftedTickets->get($eventId);
                    $giftedTicketsForEvent = $giftedData->map(function ($ticket) {
                        $ticket->ticket_type = 'gifted'; // These are gifted tickets
                        return $ticket;
                    });
                    $individualTickets = $individualTickets->merge($giftedTicketsForEvent);
                    $totalTickets += $giftedData->count();
                }
                
                // Determine if this is a shared event (has received or gifted tickets)
                $isShared = $hasReceivedTickets || $hasGifted;
                
                // Check if ticket sharing is allowed for this event
                $allowSharing = false;
                if ($hasOwnedTickets) { // Only allow sharing for owned tickets
                    try {
                        $ticketSettings = $this->loadTicketSettings($event);
                        $allowSharing = $ticketSettings['allow_share_ticket'] ?? false;
                    } catch (\Exception $e) {
                        // If settings can't be loaded, default to false
                    }
                }
                
                // Get pending share requests for this event where current user is recipient
                $shareRequests = \App\Models\EventTicketShare::where('event_id', $event->id)
                    ->where('recipient_id', auth()->id())
                    ->where('status', 'pending')
                    ->with(['sharer'])
                    ->get();
                
                return (object) [
                    'event' => $event,
                    'purchases' => collect(), // No longer using purchases collection
                    'total_tickets' => $totalTickets,
                    'total_amount' => $totalAmount,
                    'latest_purchase_date' => $latestPurchase ? $latestPurchase->created_at : now(),
                    'individual_tickets' => $individualTickets,
                    'allow_sharing' => $allowSharing,
                    'share_requests' => $shareRequests,
                    'is_shared' => $isShared,
                    'has_purchased' => $hasOwnedTickets,
                    'has_received' => $hasReceivedTickets,
                    'has_gifted' => $hasGifted,
                ];
            })
            ->sortByDesc('latest_purchase_date')
            ->values();

        return view($this->ticketView('my-purchases'), compact('events'));
    }

    /**
     * Show ticket details with QR codes.
     */
    public function details($purchaseId)
    {
        $purchase = TicketPurchase::with(['event', 'ticketInformation.user'])
            ->where('purchase_id', $purchaseId)
            ->where('user_id', Auth::id())
            ->where('status', 'paid')
            ->firstOrFail();

        // If no individual tickets exist, generate them
        if ($purchase->ticketInformation->isEmpty()) {
            $this->generateIndividualTickets($purchase);
            $purchase->refresh();
        }

        // Load ticket settings from R2 for color theming
        $ticketSettings = $this->loadTicketSettingsFromR2($purchase->event);
        $ticketColors = $ticketSettings['ticket_colors'] ?? [];
        
        // Set default colors if not specified
        $primaryColor = $ticketColors['primary'] ?? '#10b981';
        $secondaryColor = $ticketColors['secondary'] ?? '#f59e0b';
        $accentColor = $ticketColors['accent'] ?? '#059669';

        return view($this->ticketView('ticket-details'), compact('purchase', 'primaryColor', 'secondaryColor', 'accentColor'));
    }

    /**
     * Show individual ticket details with QR code.
     */
    public function ticketDetails($ticketId)
    {
        $ticket = EventTicketInformation::with(['event', 'ticketPurchase'])
            ->where('id', $ticketId)
            ->firstOrFail();

        // Check if user owns this ticket or if it's shared with them
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to view this ticket.');
        }

        // If ticket is shared (shared_with_user_id is set), check if current user is the recipient
        if ($ticket->shared_with_user_id && $ticket->shared_with_user_id !== Auth::id()) {
            abort(403, 'This ticket has been shared with another user.');
        }

        // Load ticket settings from R2 for color theming
        $ticketSettings = $this->loadTicketSettingsFromR2($ticket->event);
        $ticketColors = $ticketSettings['ticket_colors'] ?? [];
        
        // Set default colors if not specified
        $primaryColor = $ticketColors['primary'] ?? '#10b981';
        $secondaryColor = $ticketColors['secondary'] ?? '#f59e0b';
        $accentColor = $ticketColors['accent'] ?? '#059669';

        return view($this->ticketView('individual-ticket-details'), compact('ticket', 'primaryColor', 'secondaryColor', 'accentColor'));
    }

    /**
     * Generate individual tickets with encrypted QR data for a purchase.
     */
    private function generateIndividualTickets(TicketPurchase $purchase)
    {
        $tickets = [];
        $ticketBreakdown = $purchase->payment_details['ticket_breakdown'] ?? [];
        
        // Handle ticket generation based on breakdown
        foreach ($ticketBreakdown as $detail) {
            $quantity = $detail['quantity'];
            $ticketTypeId = null;
            
            // Get ticket type ID from the detail
            if (isset($detail['ticket_type'])) {
                if (is_object($detail['ticket_type'])) {
                    $ticketTypeId = $detail['ticket_type']->id ?? null;
                } elseif (is_array($detail['ticket_type'])) {
                    $ticketTypeId = $detail['ticket_type']['id'] ?? null;
                } elseif (is_numeric($detail['ticket_type'])) {
                    $ticketTypeId = $detail['ticket_type'];
                }
            }
            
            for ($i = 1; $i <= $quantity; $i++) {
                \App\Models\EventTicketInformation::create([
                    'user_id' => $purchase->user_id,
                    'event_id' => $purchase->event_id,
                    'ticket_type_id' => $ticketTypeId,
                    'ticket_purchase_id' => $purchase->id,
                    'qr_code_data' => null, // Will be generated when ticket is first accessed
                ]);
            }
        }
        
        // Generate QR code data for each ticket after insertion
        $insertedTickets = \App\Models\EventTicketInformation::where('ticket_purchase_id', $purchase->id)
            ->orderBy('id', 'desc')
            ->limit($purchase->quantity)
            ->get();
            
        $ticketIds = [];
        foreach ($insertedTickets as $ticket) {
            $ticketModel = EventTicketInformation::find($ticket->id);
            if ($ticketModel) {
                $ticketModel->update(['qr_code_data' => $ticketModel->generateQRCodeData()]);
                $ticketIds[] = $ticket->id;
            }
        }
        
        // Store ticket IDs in payment details
        $paymentDetails = $purchase->payment_details;
        $paymentDetails['ticket_ids'] = $ticketIds;
        $purchase->update(['payment_details' => $paymentDetails]);
    }

    /**
     * Generate and return QR code image dynamically.
     */
    public function generateQRCode($ticketId)
    {
        \Log::info('QR Code generation started', ['ticket_id' => $ticketId, 'user_id' => Auth::id(), 'auth_check' => Auth::check()]);
        
        // Check if user is authenticated
        if (!Auth::check()) {
            \Log::error('User not authenticated for QR code generation', ['ticket_id' => $ticketId]);
            abort(401, 'Authentication required');
        }
        
        $ticket = EventTicketInformation::with(['event', 'ticketPurchase'])
            ->where('id', $ticketId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        \Log::info('Ticket found', [
            'ticket_id' => $ticket->id,
            'event_id' => $ticket->event_id,
            'purchase_status' => $ticket->ticketPurchase->status,
            'has_qr_data' => !empty($ticket->qr_code_data)
        ]);

        // Ensure ticket belongs to paid purchase
        if ($ticket->ticketPurchase->status !== 'paid') {
            \Log::error('Ticket not paid', ['ticket_id' => $ticketId, 'status' => $ticket->ticketPurchase->status]);
            abort(403, 'This ticket is not valid.');
        }

        // Generate QR code data if not exists
        if (!$ticket->qr_code_data) {
            \Log::info('Generating QR code data for ticket', ['ticket_id' => $ticketId]);
            $qrData = $ticket->generateQRCodeData();
            $ticket->update(['qr_code_data' => $qrData]);
            \Log::info('QR code data generated and saved', ['ticket_id' => $ticketId, 'qr_data_length' => strlen($qrData)]);
        } else {
            \Log::info('Using existing QR code data', ['ticket_id' => $ticketId, 'qr_data_length' => strlen($ticket->qr_code_data)]);
        }

        try {
            // Check if BaconQrCode is available
            \Log::info('Checking BaconQrCode availability', ['class_exists' => class_exists('\BaconQrCode\Writer')]);
            
            if (class_exists('\BaconQrCode\Writer')) {
                \Log::info('Using BaconQrCode for QR generation');
                
                try {
                    // Try different BaconQrCode configurations
                    $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400, 1);
                    $renderer = new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, new \BaconQrCode\Renderer\Image\SvgImageBackEnd());
                    $writer = new \BaconQrCode\Writer($renderer);
                    
                    $qrCodeSvg = $writer->writeString($ticket->qr_code_data);
                    \Log::info('BaconQrCode SVG generated', ['svg_length' => strlen($qrCodeSvg)]);
                    
                    return response($qrCodeSvg, 200, [
                        'Content-Type' => 'image/svg+xml',
                        'Cache-Control' => 'public, max-age=3600',
                    ]);
                } catch (\Exception $baconError) {
                    \Log::error('BaconQrCode failed, falling back to simple SVG', [
                        'error' => $baconError->getMessage(),
                        'ticket_id' => $ticketId
                    ]);
                    
                    // Fall through to simple SVG fallback
                    $qrData = $ticket->qr_code_data;
                    $qrSvg = $this->generateSimpleQRSVG($qrData);
                    
                    \Log::info('Simple SVG QR generated as fallback', ['svg_length' => strlen($qrSvg)]);
                    
                    return response($qrSvg, 200, [
                        'Content-Type' => 'image/svg+xml',
                        'Cache-Control' => 'public, max-age=3600',
                    ]);
                }
            } else {
                \Log::info('BaconQrCode not available, using improved SVG fallback');
                
                // Fallback: Generate a proper QR-like SVG pattern
                $qrData = $ticket->qr_code_data;
                $qrSvg = $this->generateSimpleQRSVG($qrData);
                
                \Log::info('Improved SVG QR generated', ['svg_length' => strlen($qrSvg)]);
                
                return response($qrSvg, 200, [
                    'Content-Type' => 'image/svg+xml',
                    'Cache-Control' => 'public, max-age=3600',
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('QR Code generation failed', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return placeholder SVG if generation fails
            $placeholderSvg = '<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
                <rect width="200" height="200" fill="#f3f4f6"/>
                <rect x="20" y="20" width="160" height="160" fill="none" stroke="#9ca3af" stroke-width="2"/>
                <text x="100" y="100" text-anchor="middle" fill="#6b7280" font-family="Arial, sans-serif" font-size="12">QR Code</text>
                <text x="100" y="115" text-anchor="middle" fill="#6b7280" font-family="Arial, sans-serif" font-size="10">Error</text>
            </svg>';
            
            return response($placeholderSvg, 200, ['Content-Type' => 'image/svg+xml']);
        }
    }

    /**
     * Generate a proper QR code-like SVG pattern as fallback.
     */
    private function generateSimpleQRSVG(string $data): string
    {
        // Create a more QR-like pattern based on the data hash
        $hash = md5($data);
        $size = 200;
        $moduleSize = 4; // Smaller modules for better detail
        $modules = intval($size / $moduleSize);
        
        // Generate a pattern based on the hash
        $pattern = [];
        for ($i = 0; $i < $modules; $i++) {
            for ($j = 0; $j < $modules; $j++) {
                $index = ($i * $modules + $j) % strlen($hash);
                $pattern[$i][$j] = hexdec($hash[$index]) % 2;
            }
        }
        
        // Add proper QR code finder patterns (corner squares)
        $finderSize = 7;
        $finderPattern = [
            [1,1,1,1,1,1,1],
            [1,0,0,0,0,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,0,0,0,0,1],
            [1,1,1,1,1,1,1]
        ];
        
        // Top-left finder pattern
        for ($i = 0; $i < $finderSize; $i++) {
            for ($j = 0; $j < $finderSize; $j++) {
                $pattern[$i][$j] = $finderPattern[$i][$j];
            }
        }
        
        // Top-right finder pattern
        for ($i = 0; $i < $finderSize; $i++) {
            for ($j = 0; $j < $finderSize; $j++) {
                $pattern[$i][$modules - 1 - $j] = $finderPattern[$i][$j];
            }
        }
        
        // Bottom-left finder pattern
        for ($i = 0; $i < $finderSize; $i++) {
            for ($j = 0; $j < $finderSize; $j++) {
                $pattern[$modules - 1 - $i][$j] = $finderPattern[$i][$j];
            }
        }
        
        // Add timing patterns (horizontal and vertical lines)
        for ($i = 6; $i < $modules - 6; $i += 2) {
            $pattern[6][$i] = 1;
            $pattern[$i][6] = 1;
        }
        
        // Generate SVG
        $svg = '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="white"/>';
        
        for ($i = 0; $i < $modules; $i++) {
            for ($j = 0; $j < $modules; $j++) {
                if ($pattern[$i][$j]) {
                    $x = $j * $moduleSize;
                    $y = $i * $moduleSize;
                    $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $moduleSize . '" height="' . $moduleSize . '" fill="black"/>';
                }
            }
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }

    /**
     * Load ticket settings from R2 storage.
     */
    private function loadTicketSettingsFromR2($event): array
    {
        try {
            $eventDir = "events/{$event->id}";
            $disk = Storage::disk('r2');
            $settingsPath = $eventDir . '/ticket_settings.json';
            
            if ($disk->exists($settingsPath)) {
                $ticketSettingsJson = $disk->get($settingsPath);
                $ticketSettings = json_decode($ticketSettingsJson, true);       

                if (is_array($ticketSettings)) {
                    // Ensure ticket_colors are present with defaults if missing
                    if (!isset($ticketSettings['ticket_colors']) || empty($ticketSettings['ticket_colors'])) {
                        $ticketSettings['ticket_colors'] = [
                            'primary' => '#10b981',
                            'secondary' => '#f59e0b',
                            'accent' => '#059669',
                        ];
                    }
                    return $ticketSettings;
                }
                return [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load ticket settings from R2: ' . $e->getMessage());
        }

        // Return default settings if file doesn't exist or error occurs
        return [
            'ticket_colors' => [
                'primary' => '#10b981',
                'secondary' => '#f59e0b',
                'accent' => '#059669',
            ]
        ];
    }

    /**
     * Show individual ticket details with QR code.
     */
    public function showIndividualTicket($ticketId)
    {
        $ticket = EventTicketInformation::with(['user', 'event', 'ticketPurchase'])
            ->where('id', $ticketId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Ensure the ticket belongs to a paid purchase
        if ($ticket->ticketPurchase->status !== 'paid') {
            abort(403, 'This ticket is not valid.');
        }

        // Load ticket settings from R2 for color theming
        $ticketSettings = $this->loadTicketSettingsFromR2($ticket->event);
        $ticketColors = $ticketSettings['ticket_colors'] ?? [];
        
        // Set default colors if not specified
        $primaryColor = $ticketColors['primary'] ?? '#10b981';
        $secondaryColor = $ticketColors['secondary'] ?? '#f59e0b';
        $accentColor = $ticketColors['accent'] ?? '#059669';

        return view($this->ticketView('individual-ticket-details'), compact('ticket', 'primaryColor', 'secondaryColor', 'accentColor'));
    }

    /**
     * Handle Xendit webhook for payment status updates.
     */
    public function webhook(Request $request)
    {
        try {
            $payload = $request->all();
            
            Log::info('Xendit webhook received', ['payload' => $payload]);

            // Verify webhook signature if needed
            // $signature = $request->header('X-Xendit-Signature');
            // $expectedSignature = hash_hmac('sha256', $request->getContent(), config('services.xendit.webhook_token'));

            $externalId = $payload['external_id'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$externalId || !$status) {
                return response()->json(['message' => 'Missing required fields'], 400);
            }

            $purchase = TicketPurchase::where('purchase_id', $externalId)->first();

            if (!$purchase) {
                Log::warning('Purchase not found for webhook', ['external_id' => $externalId]);
                return response()->json(['message' => 'Purchase not found'], 404);
            }

            // Update purchase status based on Xendit status
            switch ($status) {
                case 'PAID':
                    $purchase->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_method' => $payload['payment_method'] ?? null,
                        'xendit_payment_id' => $payload['id'] ?? null,
                        'payment_details' => array_merge($purchase->payment_details ?? [], $payload),
                    ]);

                    // Update ticket sold count
                    $ticketType = $purchase->ticket_type; // Use accessor method
                    if ($ticketType) {
                        $ticketType->increment('sold_count', $purchase->quantity);
                    }

                    // Generate individual tickets with QR codes
                    $this->generateIndividualTickets($purchase);

                    // Send invoice email for successful payment
                    try { 
                        $this->sendInvoiceEmail($purchase, false); 
                    } catch (\Throwable $t) { 
                        Log::error('Send success invoice email failed: '.$t->getMessage()); 
                    }

                    // Broadcast real-time updates (don't fail the purchase if broadcasting fails)
                    try {
                        $this->broadcastTicketPurchaseUpdates($purchase->event, $purchase);
                    } catch (\Exception $e) {
                        Log::error('Failed to broadcast payment completion updates: ' . $e->getMessage());
                    }

                    Log::info('Payment completed successfully', [
                        'purchase_id' => $purchase->purchase_id,
                        'amount' => $purchase->total_amount
                    ]);
                    break;

                case 'EXPIRED':
                    $purchase->update(['status' => 'expired']);
                    Log::info('Payment expired', ['purchase_id' => $purchase->purchase_id]);
                    break;

                case 'FAILED':
                    $purchase->update(['status' => 'failed']);
                    
                    // Send invoice email for failed payment
                    try { 
                        $this->sendInvoiceEmailForFailedPayment($purchase, $payload); 
                    } catch (\Throwable $t) { 
                        Log::error('Send failed invoice email failed: '.$t->getMessage()); 
                    }
                    
                    Log::info('Payment failed', ['purchase_id' => $purchase->purchase_id]);
                    break;
            }

            return response()->json(['message' => 'Webhook processed successfully']);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage(), [
                'payload' => $request->all(),
                'exception' => $e->getTraceAsString()
            ]);

            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    /**
     * Show invoice for a specific ticket.
     */
    public function viewInvoice($purchaseId, $ticketId)
    {
        $isAdmin = \Illuminate\Support\Facades\Auth::guard('admin')->check();

        $purchaseQuery = TicketPurchase::with(['event', 'user'])
            ->where('purchase_id', $purchaseId)
            ->where('status', 'paid');

        if (!$isAdmin) {
            $purchaseQuery->where('user_id', Auth::id());
        }

        $purchase = $purchaseQuery->firstOrFail();

        $ticketQuery = EventTicketInformation::with(['event', 'ticketPurchase'])
            ->where('id', $ticketId)
            ->where('ticket_purchase_id', $purchase->id);

        if (!$isAdmin) {
            $ticketQuery->where('user_id', Auth::id());
        }

        $ticket = $ticketQuery->first();
        if (!$ticket) {
            // Fallback: load any ticket belonging to the purchase
            $ticket = EventTicketInformation::where('ticket_purchase_id', $purchase->id)->firstOrFail();
        }

        // Use the plain, print-friendly invoice template
        return view('events.tickets.invoice-plain', compact('purchase', 'ticket'));
    }

    /**
     * Broadcast real-time updates for ticket purchases
     */
    private function broadcastTicketPurchaseUpdates(Event $event, TicketPurchase $purchase)
    {
        try {
            // Get event creator and staff for dashboard updates
            $organizerIds = collect([$event->user_id]);
            
            // Add staff members
            $staffIds = $event->staff()->pluck('user_id');
            $organizerIds = $organizerIds->merge($staffIds)->unique();

            // Calculate updated stats for each organizer
            foreach ($organizerIds as $organizerId) {
                $user = User::find($organizerId);
                if ($user) {
                    $dashboardStats = $this->calculateUserDashboardStats($user);
                    
                    // Prepare sale and top events data
                    $saleData = $this->prepareSaleData($purchase);
                    $topEventsData = $this->getTopEventsData($user);
                    
                    // Broadcast dashboard stats update
                    broadcast(new DashboardStatsUpdated($organizerId, $dashboardStats));
                    
                    // Broadcast new sale data
                    broadcast(new DashboardStatsUpdated($organizerId, [
                        'sale' => $saleData,
                        'topEvents' => $topEventsData
                    ]));
                }
            }

            // Calculate updated event stats
            $eventStats = $this->calculateEventStats($event);
            broadcast(new EventStatsChanged($event->id, $eventStats));

            // Broadcast sales report updates for each organizer
            foreach ($organizerIds as $organizerId) {
                $salesData = $this->prepareSalesReportData($event, $purchase);
                $stats = $this->calculateEventStats($event);
                broadcast(new SalesReportUpdated($event->id, $organizerId, $salesData, $stats));
            }

            // Broadcast activity log
            $this->broadcastActivityLog($event, $purchase);

            // Trigger admin dashboard update
            try {
                $adminController = new \App\Http\Controllers\Admin\DashboardAnalyticsController();
                $adminController->triggerUpdate(null, 'ticket_purchase');
                
                // Also trigger admin sales report update
                $reportsController = new \App\Http\Controllers\Admin\ReportsController();
                $reportsController->triggerSalesUpdate(null);
            } catch (\Exception $e) {
                Log::error('Failed to trigger admin dashboard update: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Failed to broadcast ticket purchase updates: ' . $e->getMessage());
        }
    }

    /**
     * Calculate dashboard stats for a user
     */
    private function calculateUserDashboardStats(User $user)
    {
        // Get user's events (created by user OR where user is staff)
        $events = Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->pluck('id');

        // Calculate sales stats
        $totalRevenue = TicketPurchase::whereIn('event_id', $events)
            ->where('status', 'paid')
            ->sum('total_amount');

        $totalTicketsSold = TicketPurchase::whereIn('event_id', $events)
            ->where('status', 'paid')
            ->sum('quantity');

        $totalOrders = TicketPurchase::whereIn('event_id', $events)
            ->where('status', 'paid')
            ->count();

        $thisMonthRevenue = TicketPurchase::whereIn('event_id', $events)
            ->where('status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total_amount');

        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $conversionRate = 100; // Simplified for now
        $pendingRevenue = TicketPurchase::whereIn('event_id', $events)
            ->where('status', 'pending')
            ->sum('total_amount');

        return [
            'totalEvents' => $events->count(),
            'activeEvents' => Event::whereIn('id', $events)->where('status', 'published')->count(),
            'draftEvents' => Event::whereIn('id', $events)->where('status', 'draft')->count(),
            'upcomingEvents' => Event::whereIn('id', $events)->where('start_date', '>', now())->count(),
            'totalRevenue' => $totalRevenue,
            'totalTicketsSold' => $totalTicketsSold,
            'thisMonthRevenue' => $thisMonthRevenue,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'conversionRate' => $conversionRate,
            'pendingRevenue' => $pendingRevenue,
        ];
    }

    /**
     * Calculate event-specific stats
     */
    private function calculateEventStats(Event $event)
    {
        // Attendance stats
        $totalTickets = EventTicketInformation::where('event_id', $event->id)->count();
        $checkedInTickets = EventTicketInformation::where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->count();
        $attendanceRate = $totalTickets > 0 ? ($checkedInTickets / $totalTickets) * 100 : 0;

        // Sales stats
        $totalRevenue = TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->sum('total_amount');

        // Compute net and expected revenue (net minus platform fee), never negative, 0 if free
        $netAmount = (float) TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->sum(\DB::raw('COALESCE(net_amount, total_amount)'));
        $qricketFeePercentage = config('app.qricket_fee_percentage', 5);
        $qricketFee = max(0, $netAmount) * ($qricketFeePercentage / 100);
        $expectedRevenue = ($event->is_free || $netAmount <= 0) ? 0 : max(0, $netAmount - $qricketFee);

        $totalTicketsSold = TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->sum('quantity');

        $totalOrders = TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->count();

        // Calculate conversion rate (simplified - you might want to adjust this logic)
        $conversionRate = $totalOrders > 0 ? min(($totalTicketsSold / max($totalOrders, 1)) * 100, 100) : 0;

        // Sales trend data (last 30 days)
        $salesTrendData = $this->calculateSalesTrendData($event);

        // Ticket performance data
        $ticketPerformanceData = $this->calculateTicketPerformanceData($event);

        return [
            // Attendance stats
            'totalTickets' => $totalTickets,
            'checkedInTickets' => $checkedInTickets,
            'pendingTickets' => $totalTickets - $checkedInTickets,
            'attendanceRate' => round($attendanceRate, 1),
            // Sales stats
            'totalRevenue' => $totalRevenue,
            'expected_revenue' => $expectedRevenue,
            'totalTicketsSold' => $totalTicketsSold,
            'totalOrders' => $totalOrders,
            'conversionRate' => round($conversionRate, 1),
            // Additional data for real-time updates
            'salesTrendData' => $salesTrendData,
            'ticketPerformanceData' => $ticketPerformanceData,
        ];
    }

    /**
     * Calculate sales trend data for the last 30 days
     */
    private function calculateSalesTrendData(Event $event)
    {
        $salesTrendData = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $startOfDay = $date->copy()->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            $dayRevenue = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$startOfDay, $endOfDay])
                ->sum('total_amount');
                
            $dayTicketsSold = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->whereBetween('paid_at', [$startOfDay, $endOfDay])
                ->sum('quantity');
            
            $salesTrendData[] = [
                'display_date' => $date->format('M j'),
                'date' => $date->format('Y-m-d'),
                'revenue' => $dayRevenue,
                'tickets_sold' => $dayTicketsSold,
            ];
        }
        
        return $salesTrendData;
    }

    /**
     * Calculate ticket performance data
     */
    private function calculateTicketPerformanceData(Event $event)
    {
        // Get ticket types from the event
        $ticketTypes = \App\Models\EventTicket::where('event_id', $event->id)->get();
        $performanceData = [];
        
        foreach ($ticketTypes as $ticketType) {
            // Use the sold_count from the EventTicket table if available, otherwise calculate from purchases
            $totalSold = $ticketType->sold_count ?? 0;
            
            // If sold_count is 0, try to calculate from TicketPurchase records
            if ($totalSold == 0) {
                $totalSold = TicketPurchase::where('event_id', $event->id)
                    ->where('event_id', $ticketType->event_id)
                    ->where('status', 'paid')
                    ->sum('quantity');
            }
            
            $totalRevenue = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->sum('total_amount');
            
            $remaining = $ticketType->quantity ? $ticketType->quantity - $totalSold : null;
            $utilization = $ticketType->quantity ? ($totalSold / $ticketType->quantity) * 100 : 0;
            
            $performanceData[] = [
                'name' => $ticketType->name,
                'price' => $ticketType->price,
                'total_sold' => $totalSold,
                'remaining' => $remaining,
                'utilization' => min($utilization, 100),
                'total_revenue' => $totalRevenue,
            ];
        }
        
        return $performanceData;
    }

    /**
     * Prepare sale data for broadcasting
     */
    private function prepareSaleData(TicketPurchase $purchase)
    {
        $purchase->load(['user', 'event']);
        
        return [
            'id' => $purchase->id,
            'user' => [
                'first_name' => $purchase->user->first_name,
                'last_name' => $purchase->user->last_name,
            ],
            'event' => [
                'title' => $purchase->event->title,
            ],
            'ticket_type' => $this->getTicketTypeName($purchase),
            'quantity' => $purchase->quantity,
            'total_amount' => $purchase->total_amount,
            'created_at' => $purchase->created_at->toISOString(),
        ];
    }

    /**
     * Get top events data for broadcasting
     */
    private function getTopEventsData(User $user)
    {
        // Get user's events (created by user OR where user is staff)
        $events = Event::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereHas('staff', function($q) use ($user) {
                          $q->where('user_id', $user->id);
                      });
            })
            ->get();

        $topEvents = [];
        
        foreach ($events as $event) {
            $totalRevenue = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->sum('total_amount');

            $totalTicketsSold = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->sum('quantity');

            $totalOrders = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->count();

            if ($totalRevenue > 0) {
                $topEvents[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start_date' => $event->start_date->format('M j, Y'),
                    'total_revenue' => $totalRevenue,
                    'total_tickets_sold' => $totalTicketsSold,
                    'total_orders' => $totalOrders,
                ];
            }
        }

        // Sort by revenue and take top 5
        usort($topEvents, function($a, $b) {
            return $b['total_revenue'] <=> $a['total_revenue'];
        });

        return array_slice($topEvents, 0, 5);
    }

    /**
     * Broadcast activity log for ticket purchase
     */
    private function broadcastActivityLog(Event $event, TicketPurchase $purchase)
    {
        try {
            // Get event creator and staff for activity updates
            $organizerIds = collect([$event->user_id]);
            
            // Add staff members
            $staffIds = $event->staff()->pluck('user_id');
            $organizerIds = $organizerIds->merge($staffIds)->unique();

            $ticketTypeName = $this->getTicketTypeName($purchase);
            $activityData = [
                'id' => uniqid(),
                'action' => 'Ticket Purchase',
                'description' => "New ticket purchase: {$purchase->quantity}x {$ticketTypeName} for {$event->title}",
                'created_at' => now()->toISOString(),
            ];

            foreach ($organizerIds as $organizerId) {
                // Use a custom event for activity updates
                broadcast(new \App\Events\DashboardStatsUpdated($organizerId, [
                    'activity' => $activityData
                ]));
            }

        } catch (\Exception $e) {
            Log::error('Failed to broadcast activity log: ' . $e->getMessage());
        }
    }

    /**
     * Send invoice email to purchaser
     * @param TicketPurchase $purchase
     * @param bool $pending If true, send a pending invoice with payment link; otherwise send paid invoice with items
     */
    private function sendInvoiceEmail(TicketPurchase $purchase, bool $pending = false): void
    {
        $purchase->loadMissing(['event', 'user', 'ticketInformation']);
        $to = $purchase->customer_details['email'] ?? ($purchase->user->email ?? null);
        if (!$to) { return; }

        // Use a branded HTML mailable
        Mail::to($to)->send(new \App\Mail\TicketInvoiceMail($purchase, $pending));
    }

    /**
     * Send invoice email for failed payment
     */
    private function sendInvoiceEmailForFailedPayment(TicketPurchase $purchase, array $payload): void
    {
        try {
            $purchase->loadMissing(['event', 'user']);
            $to = $purchase->customer_details['email'] ?? ($purchase->user->email ?? null);
            if (!$to) { return; }

            $invoiceUrl = $payload['invoice_url'] ?? null;
            $amount = $purchase->total_amount;
            $currency = $purchase->currency;
            
            // Create a mock plan object for the invoice
            $planData = [
                'name' => 'Ticket Purchase Invoice',
                'price' => $amount,
                'currency' => $currency,
                'description' => 'Ticket purchase for ' . $purchase->event->title
            ];

            Mail::to($to)->send(new \App\Mail\InvoiceMail(
                $purchase->user, 
                $invoiceUrl, 
                $planData,
                'failed'
            ));

            Log::info('Failed payment invoice email sent successfully', [
                'purchase_id' => $purchase->purchase_id,
                'user_email' => $to,
                'amount' => $amount,
                'currency' => $currency
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send invoice email for failed payment', [
                'purchase_id' => $purchase->purchase_id,
                'error' => $e->getMessage()
            ]);
        }
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
        return Auth::id();
    }

    /**
     * Prepare sales report data for real-time updates
     */
    private function prepareSalesReportData(Event $event, TicketPurchase $purchase)
    {
        return [
            'purchase_id' => $purchase->id,
            'purchase_external_id' => $purchase->purchase_id,
            'buyer_name' => trim(($purchase->user->first_name ?? '') . ' ' . ($purchase->user->last_name ?? '')) ?: 'Unknown',
            'buyer_email' => $purchase->user->email ?? '',
            'ticket_type' => $this->getTicketTypeName($purchase),
            'quantity' => $purchase->quantity,
            'unit_price' => $purchase->unit_price,
            'total_amount' => $purchase->total_amount,
            'status' => $purchase->status,
            'paid_at' => $purchase->paid_at ? $purchase->paid_at->format('Y-m-d H:i:s') : null,
            'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Get ticket type name for a purchase
     */
    private function getTicketTypeName(TicketPurchase $purchase)
    {
        $ticketBreakdown = $purchase->payment_details['ticket_breakdown'] ?? [];
        
        if (!empty($ticketBreakdown)) {
            // For multiple ticket types, return a summary
            $names = [];
            foreach ($ticketBreakdown as $detail) {
                $ticketTypeName = 'Event Ticket';
                if (is_object($detail['ticket_type'])) {
                    $ticketTypeName = $detail['ticket_type']->name ?? 'Event Ticket';
                } elseif (is_array($detail['ticket_type'])) {
                    $ticketTypeName = $detail['ticket_type']['name'] ?? 'Event Ticket';
                } elseif (is_string($detail['ticket_type'])) {
                    $ticketTypeName = $detail['ticket_type'];
                }
                $names[] = $ticketTypeName;
            }
            return implode(', ', array_unique($names));
        }
        
        // Fallback for single ticket type
        return 'Event Ticket';
    }
}


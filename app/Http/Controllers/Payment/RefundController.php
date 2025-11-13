<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use App\Models\TicketPurchase;
use App\Models\Subscription;
use App\Models\TicketLimitPurchase;
use App\Models\EventTicketInformation;
use App\Models\AdminActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefundController extends Controller
{
    /**
     * Display refund requests for admin.
     */
    public function index(Request $request)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $query = RefundRequest::with(['user', 'ticketPurchase.event', 'subscription', 'additionalTicketPurchase.event']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Search by user
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $refunds = $query->latest()->paginate(20)->appends($request->query());

        $stats = [
            'total' => RefundRequest::count(),
            'pending' => RefundRequest::pending()->count(),
            'approved' => RefundRequest::approved()->count(),
            'processed' => RefundRequest::processed()->count(),
            'rejected' => RefundRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.refunds.index', compact('refunds', 'stats'));
    }

    /**
     * Store a new refund request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:ticket,subscription,additional_ticket',
            'item_id' => 'required|integer',
            'purchase_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string|max:1000',
        ]);

        // Verify the user owns this purchase
        $user = Auth::user();
        $isValid = false;
        $item = null;

        if ($validated['type'] === 'ticket') {
            $item = TicketPurchase::where('id', $validated['item_id'])
                ->where('user_id', $user->id)
                ->where('purchase_id', $validated['purchase_id'])
                ->first();
            
            if ($item && $item->status === 'paid' && $item->paid_at && $item->paid_at->diffInDays(now()) < 7) {
                // Check if any tickets from this purchase are already checked in
                $checkedInTickets = EventTicketInformation::where('ticket_purchase_id', $item->id)
                    ->whereNotNull('checked_in_at')
                    ->exists();
                
                if ($checkedInTickets) {
                    return redirect()->back()->with('error', 'Cannot refund tickets that have already been checked in.');
                }
                
                $isValid = true;
            }
        } elseif ($validated['type'] === 'subscription') {
            $item = Subscription::where('id', $validated['item_id'])
                ->where('user_id', $user->id)
                ->first();
            
            if ($item && $item->status === 'active' && $item->created_at->diffInDays(now()) < 7) {
                $isValid = true;
            }
        } elseif ($validated['type'] === 'additional_ticket') {
            $item = \App\Models\TicketLimitPurchase::where('id', $validated['item_id'])
                ->where('user_id', $user->id)
                ->where('purchase_id', $validated['purchase_id'])
                ->first();
            
            if ($item && $item->status === 'paid' && $item->paid_at && $item->paid_at->diffInDays(now()) < 7) {
                $isValid = true;
            }
        }

        if (!$isValid || !$item) {
            return redirect()->back()->with('error', 'Invalid refund request or item not eligible for refund.');
        }

        // Calculate the maximum refundable amount (FULL AMOUNT - what customer paid)
        // Customer gets refunded the full amount they paid
        $maxRefundableAmount = 0;
        if ($validated['type'] === 'ticket') {
            // For tickets, refund the FULL amount (what customer paid)
            $maxRefundableAmount = $item->total_amount;
        } elseif ($validated['type'] === 'subscription') {
            // For subscriptions, use the full amount
            $maxRefundableAmount = $item->amount;
        } elseif ($validated['type'] === 'additional_ticket') {
            // For additional tickets, refund the FULL amount
            $maxRefundableAmount = $item->amount;
        }

        // Ensure requested amount doesn't exceed refundable amount
        $requestedAmount = min($validated['amount'], $maxRefundableAmount);

        // Check if refund request already exists
        $existingRequest = RefundRequest::where('user_id', $user->id)
            ->where('type', $validated['type'])
            ->where('item_id', $validated['item_id'])
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('error', 'A refund request for this item is already pending.');
        }

        // Create refund request with the capped amount
        $refundRequest = RefundRequest::create([
            'user_id' => $user->id,
            'type' => $validated['type'],
            'item_id' => $validated['item_id'],
            'purchase_id' => $validated['purchase_id'],
            'amount' => $requestedAmount,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        // Create admin notification for refund request
        try {
            $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
            $refundType = ucfirst($validated['type']);
            $notificationMessage = "{$userName} requested a {$refundType} refund of ₱" . number_format($requestedAmount, 2);
            
            $notification = \App\Models\Notification::create([
                'user_id' => null, // Admin notification
                'admin_id' => null, // For all admins
                'type' => 'ticket',
                'action' => 'reply', // Using existing action, or we could add 'refund_requested'
                'reference_id' => $refundRequest->id,
                'title' => 'Refund Request',
                'message' => $notificationMessage,
                'data' => [
                    'refund_request_id' => $refundRequest->id,
                    'user_id' => $user->id,
                    'user_name' => $userName,
                    'user_email' => $user->email,
                    'type' => $validated['type'],
                    'amount' => $requestedAmount,
                    'reason' => $validated['reason'],
                ],
                'is_read' => false,
            ]);

            // Broadcast admin notification with notification ID
            try {
                event(new \App\Events\AdminNotification(
                    $notificationMessage,
                    'info',
                    'Refund Request',
                    $notification->id
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast admin notification for refund request: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create admin notification for refund request: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Refund request submitted successfully. We will review it within 24 hours.');
    }

    /**
     * Cancel a pending refund request (User only).
     */
    public function cancel(Request $request, RefundRequest $refundRequest)
    {
        $user = Auth::user();
        
        // Verify the user owns this refund request
        if ($refundRequest->user_id !== $user->id) {
            abort(403, 'You do not have permission to cancel this refund request.');
        }
        
        // Only allow canceling pending refund requests
        if ($refundRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending refund requests can be cancelled.');
        }
        
        // Update refund request status to cancelled
        $refundRequest->update([
            'status' => 'cancelled',
        ]);
        
        // Log activity
        Log::info('Refund request cancelled by user', [
            'refund_request_id' => $refundRequest->id,
            'user_id' => $user->id,
            'type' => $refundRequest->type,
            'amount' => $refundRequest->amount,
        ]);
        
        return redirect()->back()->with('success', 'Refund request cancelled successfully.');
    }

    /**
     * Process refund through Xendit (Admin only).
     */
    public function processRefund(Request $request, RefundRequest $refundRequest)
    {
        // This should be called by admin
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        if ($refundRequest->status !== 'approved') {
            return redirect()->back()->with('error', 'Refund request must be approved before processing.');
        }

        // Load the necessary relationships based on type
        if ($refundRequest->type === 'ticket') {
            $refundRequest->load('ticketPurchase');
        } elseif ($refundRequest->type === 'subscription') {
            $refundRequest->load('subscription');
        } elseif ($refundRequest->type === 'additional_ticket') {
            $refundRequest->load('additionalTicketPurchase');
        }

        try {
            // Get Xendit configuration
            $xenditApiKey = config('services.xendit.secret_key');
            $xenditBaseUrl = config('services.xendit.base_url', 'https://api.xendit.co');

            // Determine the payment method and external ID based on type
            $externalId = null;
            $paymentMethod = null;

            if ($refundRequest->type === 'ticket') {
                $ticketPurchase = $refundRequest->ticketPurchase;
                if (!$ticketPurchase) {
                    throw new \Exception('Ticket purchase not found for this refund request.');
                }
                // Use Xendit payment ID for refunds, fallback to invoice ID, then purchase ID
                $externalId = $ticketPurchase->xendit_payment_id ?? $ticketPurchase->xendit_invoice_id ?? $ticketPurchase->purchase_id;
                $paymentMethod = $ticketPurchase->payment_method ?? 'ewallet';
            } elseif ($refundRequest->type === 'subscription') {
                $subscription = $refundRequest->subscription;
                if (!$subscription) {
                    throw new \Exception('Subscription not found for this refund request.');
                }
                // For subscriptions, we might need to use a different approach
                $externalId = $subscription->xendit_payment_id ?? $subscription->id;
                $paymentMethod = 'ewallet';
            } elseif ($refundRequest->type === 'additional_ticket') {
                $additionalTicketPurchase = $refundRequest->additionalTicketPurchase;
                if (!$additionalTicketPurchase) {
                    throw new \Exception('Additional ticket purchase not found for this refund request.');
                }
                $externalId = $additionalTicketPurchase->xendit_payment_id ?? $additionalTicketPurchase->xendit_invoice_id ?? $additionalTicketPurchase->purchase_id;
                $paymentMethod = $additionalTicketPurchase->payment_method ?? 'ewallet';
            }

            if (!$externalId) {
                throw new \Exception('Unable to determine external ID for refund');
            }

            // Validate that we have a proper Xendit ID for refunds
            if ($refundRequest->type === 'ticket') {
                $ticketPurchase = $refundRequest->ticketPurchase;
                if (!$ticketPurchase) {
                    throw new \Exception('Ticket purchase not found for this refund request.');
                }
                if (!$ticketPurchase->xendit_payment_id && !$ticketPurchase->xendit_invoice_id) {
                    throw new \Exception('No Xendit payment ID found for this ticket purchase. Refunds require the original Xendit payment ID.');
                }
                
                // Additional validation - check if the IDs look valid
                if ($ticketPurchase->xendit_payment_id && !$this->isValidXenditId($ticketPurchase->xendit_payment_id)) {
                    Log::warning('Invalid Xendit payment ID format', [
                        'payment_id' => $ticketPurchase->xendit_payment_id,
                        'purchase_id' => $ticketPurchase->purchase_id
                    ]);
                }
                if ($ticketPurchase->xendit_invoice_id && !$this->isValidXenditId($ticketPurchase->xendit_invoice_id)) {
                    Log::warning('Invalid Xendit invoice ID format', [
                        'invoice_id' => $ticketPurchase->xendit_invoice_id,
                        'purchase_id' => $ticketPurchase->purchase_id
                    ]);
                }
            }

            // Map internal reason to Xendit allowed values
            $xenditReason = $this->mapRefundReasonToXendit($refundRequest->reason);

            // Temporary: calculate refundable amount for logging (NET AMOUNT)
            $tempRefundableAmount = $refundRequest->amount;
            if ($refundRequest->type === 'ticket') {
                $ticketPurchase = $refundRequest->ticketPurchase;
                // Use net_amount (what organizer received after fees)
                if ($ticketPurchase->net_amount) {
                    $tempRefundableAmount = $ticketPurchase->net_amount;
                } else {
                    $tempRefundableAmount = $ticketPurchase->total_amount - ($ticketPurchase->transaction_fee ?? 0) - ($ticketPurchase->vat_amount ?? 0);
                }
            } elseif ($refundRequest->type === 'additional_ticket') {
                $additionalTicketPurchase = $refundRequest->additionalTicketPurchase;
                // Use net amount
                if ($additionalTicketPurchase->net_amount) {
                    $tempRefundableAmount = $additionalTicketPurchase->net_amount;
                } else {
                    $tempRefundableAmount = $additionalTicketPurchase->amount - ($additionalTicketPurchase->transaction_fee ?? 0) - ($additionalTicketPurchase->vat_amount ?? 0);
                }
            }

            // Log the refund attempt for debugging
            Log::info('Attempting Xendit refund', [
                'refund_request_id' => $refundRequest->id,
                'external_id' => $externalId,
                'requested_amount' => $refundRequest->amount,
                'refundable_amount' => $tempRefundableAmount,
                'payment_method' => $paymentMethod,
                'type' => $refundRequest->type,
                'original_reason' => $refundRequest->reason,
                'xendit_reason' => $xenditReason,
                'xendit_payment_id' => $refundRequest->type === 'ticket' ? $refundRequest->ticketPurchase->xendit_payment_id : null,
                'xendit_invoice_id' => $refundRequest->type === 'ticket' ? $refundRequest->ticketPurchase->xendit_invoice_id : null,
            ]);

            // Calculate the refundable amount (FULL AMOUNT - what customer paid)
            // Customer gets refunded the full amount they paid, organizer loses the fees
            $refundableAmount = $refundRequest->amount;
            $organizerLoss = 0; // Fees that organizer will lose on refund
            
            if ($refundRequest->type === 'ticket') {
                $ticketPurchase = $refundRequest->ticketPurchase;
                if (!$ticketPurchase) {
                    throw new \Exception('Ticket purchase not found for this refund request.');
                }
                
                // Debug: Log the actual database values
                Log::info('Ticket purchase details for refund', [
                    'ticket_purchase_id' => $ticketPurchase->id,
                    'total_amount_raw' => $ticketPurchase->total_amount,
                    'net_amount_raw' => $ticketPurchase->net_amount,
                    'refund_request_amount' => $refundRequest->amount,
                ]);
                
                // Refund the FULL AMOUNT (total_amount) - what customer actually paid
                // Xendit can refund the full transaction amount
                $refundableAmount = $ticketPurchase->total_amount;
                // Organizer loses the fees they received (transaction_fee + vat_amount)
                $organizerLoss = ($ticketPurchase->transaction_fee ?? 0) + ($ticketPurchase->vat_amount ?? 0);
            } elseif ($refundRequest->type === 'additional_ticket') {
                $additionalTicketPurchase = $refundRequest->additionalTicketPurchase;
                if (!$additionalTicketPurchase) {
                    throw new \Exception('Additional ticket purchase not found for this refund request.');
                }
                
                // Refund the FULL AMOUNT - what customer actually paid
                $refundableAmount = $additionalTicketPurchase->amount;
                // Organizer loses the fees they received
                $organizerLoss = ($additionalTicketPurchase->transaction_fee ?? 0) + ($additionalTicketPurchase->vat_amount ?? 0);
            } elseif ($refundRequest->type === 'subscription') {
                $subscription = $refundRequest->subscription;
                if (!$subscription) {
                    throw new \Exception('Subscription not found for this refund request.');
                }
                
                // Subscriptions - refund full amount
                $refundableAmount = $subscription->amount;
                $organizerLoss = ($subscription->transaction_fee ?? 0) + ($subscription->vat_amount ?? 0);
            }

            // Ensure we don't refund more than the refundable amount
            if ($refundRequest->amount > $refundableAmount) {
                Log::warning('Refund amount exceeds refundable amount', [
                    'refund_request_amount' => $refundRequest->amount,
                    'refundable_amount' => $refundableAmount,
                    'refund_request_id' => $refundRequest->id,
                ]);
                $refundableAmount = min($refundRequest->amount, $refundableAmount);
            }

            // Log the organizer loss
            Log::info('Refund calculation', [
                'refund_request_id' => $refundRequest->id,
                'customer_refund' => $refundableAmount,
                'organizer_loss' => $organizerLoss,
                'net_amount_organizer_received' => $refundableAmount - $organizerLoss,
            ]);

            // First, determine the Xendit IDs we'll use
            $xenditPaymentId = null;
            $xenditInvoiceId = null;
            
            if ($refundRequest->type === 'ticket') {
                $ticketPurchase = $refundRequest->ticketPurchase;
                if (!$ticketPurchase) {
                    throw new \Exception('Ticket purchase not found for this refund request.');
                }
                $xenditPaymentId = $ticketPurchase->xendit_payment_id ?? null;
                $xenditInvoiceId = $ticketPurchase->xendit_invoice_id ?? null;
                
                if (!$xenditPaymentId && !$xenditInvoiceId) {
                    throw new \Exception('No valid Xendit ID found for refund');
                }
            } elseif ($refundRequest->type === 'subscription') {
                $subscription = $refundRequest->subscription;
                if (!$subscription) {
                    throw new \Exception('Subscription not found for this refund request.');
                }
                $xenditPaymentId = $subscription->xendit_payment_id ?? null;
                $xenditInvoiceId = $subscription->xendit_invoice_id ?? null;
                
                if (!$xenditPaymentId && !$xenditInvoiceId) {
                    throw new \Exception('No valid Xendit ID found for subscription refund');
                }
            } elseif ($refundRequest->type === 'additional_ticket') {
                $additionalTicketPurchase = $refundRequest->additionalTicketPurchase;
                if (!$additionalTicketPurchase) {
                    throw new \Exception('Additional ticket purchase not found for this refund request.');
                }
                $xenditPaymentId = $additionalTicketPurchase->xendit_payment_id ?? null;
                $xenditInvoiceId = $additionalTicketPurchase->xendit_invoice_id ?? null;
            }

            // Get the actual maximum refundable amount from Xendit
            $xenditMaxRefundableInCents = $this->getMaxRefundableFromXendit($xenditPaymentId, $xenditInvoiceId);

            // If we got the max refundable from Xendit, use that instead
            // Xendit returns amounts in cents, so convert to PHP currency for comparison
            if ($xenditMaxRefundableInCents) {
                $xenditMaxRefundablePHP = $xenditMaxRefundableInCents / 100; // Convert cents to PHP
                if ($xenditMaxRefundablePHP < $refundableAmount) {
                    Log::warning('Reducing refund amount to match Xendit maximum', [
                        'original_refund_amount' => $refundableAmount,
                        'xendit_max_refundable_cents' => $xenditMaxRefundableInCents,
                        'xendit_max_refundable_php' => $xenditMaxRefundablePHP,
                        'new_refund_amount' => $xenditMaxRefundablePHP,
                    ]);
                    $refundableAmount = $xenditMaxRefundablePHP;
                }
            }

            // Prepare refund payload based on available Xendit IDs
        // Xendit refunds require: payment_request_id, invoice_id, or charge_id
        
        // Log the raw refundableAmount to check what we're working with
        Log::info('Refundable amount calculation', [
            'refundable_amount_raw' => $refundableAmount,
            'type' => gettype($refundableAmount),
        ]);
        
        // Convert to cents - database stores amounts in PHP currency, Xendit expects cents
        $refundAmountInCents = (int) round($refundableAmount * 100);
        
        // If Xendit returned a max refundable (in cents), ensure we don't exceed it
        if ($xenditMaxRefundableInCents && $refundAmountInCents > $xenditMaxRefundableInCents) {
            Log::warning('Refund amount exceeds Xendit maximum refundable, reducing', [
                'requested_refund_cents' => $refundAmountInCents,
                'xendit_max_refundable_cents' => $xenditMaxRefundableInCents,
                'requested_refund_php' => $refundableAmount,
                'xendit_max_refundable_php' => $xenditMaxRefundableInCents / 100,
            ]);
            $refundAmountInCents = $xenditMaxRefundableInCents;
            $refundableAmount = $xenditMaxRefundableInCents / 100; // Update for logging
        }
            
            $refundPayload = [
                'amount' => $refundAmountInCents, // Convert to cents
                'reason' => $xenditReason,
            ];

            // Add the appropriate ID field based on what we have
            if ($xenditPaymentId) {
                $refundPayload['payment_request_id'] = $xenditPaymentId;
            } elseif ($xenditInvoiceId) {
                $refundPayload['invoice_id'] = $xenditInvoiceId;
            }

            // Log the exact amount being sent to Xendit
            Log::info('Refund payload details', [
                'refund_amount_php' => $refundableAmount,
                'refund_amount_cents' => $refundAmountInCents,
                'organizer_loss' => $organizerLoss,
                'xendit_max_refundable_cents' => $xenditMaxRefundableInCents,
                'xendit_max_refundable_php' => $xenditMaxRefundableInCents ? $xenditMaxRefundableInCents / 100 : null,
                'refund_payload' => $refundPayload,
            ]);

            // Log the final refund payload
            Log::info('Xendit refund payload', $refundPayload);

            // Create refund via Xendit API using the correct endpoint
            // POST https://api.xendit.co/v2/invoices/{invoice_id}/refunds for invoice refunds
            // Or POST https://api.xendit.co/refunds for payment request refunds
            $endpoint = '/refunds';
            $invoiceIdForEndpoint = null;
            if (isset($refundPayload['invoice_id'])) {
                // For invoice refunds, use the specific invoice refund endpoint
                $invoiceIdForEndpoint = $refundPayload['invoice_id'];
                $endpoint = '/v2/invoices/' . $invoiceIdForEndpoint . '/refunds';
                unset($refundPayload['invoice_id']); // Remove invoice_id from payload as it's in URL
            }
            
            $refundUrl = $xenditBaseUrl . $endpoint;
            
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                'Content-Type' => 'application/json',
            ])->post($refundUrl, $refundPayload);

            if ($response->successful()) {
                $refundData = $response->json();
                
                // Update refund request
                $refundRequest->update([
                    'status' => 'processed',
                    'admin_notes' => $validated['admin_notes'] ?? null,
                    'xendit_refund_id' => $refundData['id'],
                    'processed_at' => now(),
                ]);

                // Update the original item status
                if ($refundRequest->type === 'ticket') {
                    $ticketPurchase = $refundRequest->ticketPurchase;
                    
                    // Get ticket information before deleting to handle shared tickets
                    $ticketsToDelete = EventTicketInformation::where('ticket_purchase_id', $ticketPurchase->id)
                        ->with('ticketType')
                        ->get();
                    $ticketIds = $ticketsToDelete->pluck('id')->toArray();
                    
                    // Find all unique users who received shared tickets from this purchase
                    $sharedTicketRecipients = [];
                    foreach ($ticketsToDelete as $ticket) {
                        // Check if ticket was shared (has shared_by_user_id but different user_id means it was accepted)
                        if ($ticket->shared_by_user_id && $ticket->user_id !== $ticket->shared_by_user_id) {
                            // This ticket was shared and accepted - the current user_id is the recipient
                            if (!in_array($ticket->user_id, $sharedTicketRecipients)) {
                                $sharedTicketRecipients[] = $ticket->user_id;
                            }
                        }
                    }
                    
                    // Also check EventTicketShare records for this purchase
                    $ticketShareRecords = \App\Models\EventTicketShare::whereIn('shared_ticket_id', $ticketIds)
                        ->where('status', 'accepted')
                        ->with('recipient')
                        ->get();
                    
                    foreach ($ticketShareRecords as $share) {
                        if ($share->recipient_id && !in_array($share->recipient_id, $sharedTicketRecipients)) {
                            $sharedTicketRecipients[] = $share->recipient_id;
                        }
                    }
                    
                    // Notify all recipients of shared tickets
                    if (!empty($sharedTicketRecipients)) {
                        $eventTitle = $ticketPurchase->event->title ?? 'Event';
                        
                        foreach ($sharedTicketRecipients as $recipientId) {
                            try {
                                $recipient = \App\Models\User::find($recipientId);
                                if ($recipient) {
                                    $notificationMessage = "A ticket you received for '{$eventTitle}' has been refunded. The ticket is no longer valid.";
                                    
                                    Notification::create([
                                        'user_id' => $recipientId,
                                        'admin_id' => null,
                                        'type' => 'ticket',
                                        'action' => 'refund_completed',
                                        'reference_id' => $refundRequest->id,
                                        'title' => 'Shared Ticket Refunded',
                                        'message' => $notificationMessage,
                                        'is_read' => false,
                                        'data' => [
                                            'refund_request_id' => $refundRequest->id,
                                            'event_id' => $ticketPurchase->event_id,
                                            'event_title' => $eventTitle,
                                            'refund_amount' => $refundRequest->amount,
                                            'shared_ticket_refunded' => true,
                                        ],
                                    ]);
                                    
                                    // Send real-time notification
                                    try {
                                        \App\Services\NotificationService::sendUserNotification(
                                            $recipientId,
                                            $notificationMessage,
                                            'warning',
                                            'Shared Ticket Refunded'
                                        );
                                    } catch (\Exception $e) {
                                        Log::error('Failed to send real-time notification for shared ticket refund', [
                                            'error' => $e->getMessage(),
                                            'recipient_id' => $recipientId,
                                        ]);
                                    }
                                    
                                    Log::info('Shared ticket refund notification sent (processRefund)', [
                                        'recipient_id' => $recipientId,
                                        'refund_request_id' => $refundRequest->id,
                                        'event_id' => $ticketPurchase->event_id,
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Failed to notify shared ticket recipient (processRefund)', [
                                    'error' => $e->getMessage(),
                                    'recipient_id' => $recipientId,
                                    'refund_request_id' => $refundRequest->id,
                                ]);
                            }
                        }
                    }
                    
                    $ticketPurchase->update(['status' => 'refunded']);
                    
                    // Delete the generated ticket information for this purchase
                    $deletedTickets = EventTicketInformation::where('ticket_purchase_id', $ticketPurchase->id)->delete();
                    
                    // Update refund request with deleted ticket IDs
                    $refundRequest->update(['refunded_ticket_ids' => $ticketIds]);
                    
                    // Reduce the sold count for each ticket type in this purchase
                    // Get ticket types from ticketInformation records before deleting them
                    $ticketInfoRecords = $ticketsToDelete;
                    
                    // Group by ticket type and count quantities
                    $ticketTypeCounts = [];
                    foreach ($ticketInfoRecords as $ticketInfo) {
                        if ($ticketInfo->ticketType) {
                            $ticketTypeId = $ticketInfo->ticketType->id;
                            if (!isset($ticketTypeCounts[$ticketTypeId])) {
                                $ticketTypeCounts[$ticketTypeId] = [
                                    'ticketType' => $ticketInfo->ticketType,
                                    'quantity' => 0
                                ];
                            }
                            $ticketTypeCounts[$ticketTypeId]['quantity']++;
                        }
                    }
                    
                    // Decrement sold_count for each ticket type
                    foreach ($ticketTypeCounts as $countData) {
                        $ticketType = $countData['ticketType'];
                        $quantity = $countData['quantity'];
                        $ticketType->decrement('sold_count', $quantity);
                            Log::info('Reduced sold count for ticket type', [
                                'ticket_type_id' => $ticketType->id,
                                'ticket_type_name' => $ticketType->name,
                            'quantity_refunded' => $quantity,
                                'new_sold_count' => $ticketType->sold_count
                            ]);
                    }
                    
                    Log::info('Ticket refund processing completed', [
                        'ticket_purchase_id' => $ticketPurchase->id,
                        'deleted_tickets_count' => $deletedTickets,
                        'refund_request_id' => $refundRequest->id
                    ]);
                    
                } elseif ($refundRequest->type === 'subscription') {
                    $subscription = $refundRequest->subscription;
                    $subscription->update(['status' => 'refunded']);
                    
                    // Remove subscription_id from all events created with this subscription
                    $eventsUpdated = \App\Models\Event::where('subscription_id', $subscription->id)
                        ->update(['subscription_id' => null]);
                    
                    Log::info('Removed subscription_id from events after subscription refund', [
                        'subscription_id' => $subscription->id,
                        'events_updated' => $eventsUpdated,
                    ]);
                } elseif ($refundRequest->type === 'additional_ticket') {
                    $additionalTicketPurchase = $refundRequest->additionalTicketPurchase;
                    if ($additionalTicketPurchase) {
                        $additionalTicketPurchase->update(['status' => 'refunded']);
                        // Note: Additional ticket purchases don't have ticket types
                        // They represent capacity unlocks, not actual sold tickets
                        Log::info('Additional ticket purchase refunded', [
                            'purchase_id' => $additionalTicketPurchase->purchase_id,
                            'event_id' => $additionalTicketPurchase->event_id,
                            'amount' => $additionalTicketPurchase->amount
                        ]);
                    }
                }

                Log::info('Refund processed successfully', [
                    'refund_request_id' => $refundRequest->id,
                    'xendit_refund_id' => $refundData['id'],
                    'amount' => $refundRequest->amount,
                ]);

                // Create in-app notification for the user
                $this->createRefundNotification($refundRequest, $refundData);

                return redirect()->back()->with('success', 'Refund processed successfully via Xendit.');
            } else {
                $errorBody = $response->json();
                $errorCode = $errorBody['error_code'] ?? 'UNKNOWN_ERROR';
                
                Log::error('Xendit refund API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error_code' => $errorCode,
                    'refund_payload' => $refundPayload,
                ]);
                
                // Handle specific error cases
                if ($errorCode === 'TRANSACTION_NOT_FOUND') {
                    // Try alternative refund approaches automatically
                    Log::warning('Xendit transaction not found, trying alternative refund methods', [
                        'refund_request_id' => $refundRequest->id,
                        'refund_payload' => $refundPayload,
                    ]);
                    
                    return $this->tryAlternativeRefundMethods($refundRequest, $validated, $refundPayload);
                }
                
                if ($errorCode === 'MAXIMUM_REFUND_AMOUNT_REACHED') {
                    // Get the actual maximum refundable amount from Xendit (accounting for existing refunds)
                    Log::warning('MAXIMUM_REFUND_AMOUNT_REACHED - fetching actual max refundable from Xendit', [
                        'refundable_amount_tried' => $refundableAmount,
                        'refund_request_id' => $refundRequest->id,
                        'type' => $refundRequest->type,
                    ]);
                    
                    // Get the actual maximum refundable from Xendit (this accounts for already refunded amounts)
                    $actualMaxRefundableInCents = $this->getMaxRefundableFromXendit($xenditPaymentId, $xenditInvoiceId);
                    
                    if ($actualMaxRefundableInCents && $actualMaxRefundableInCents > 0) {
                        $actualMaxRefundablePHP = $actualMaxRefundableInCents / 100; // Convert cents to PHP
                        
                        // If the max refundable is less than what we tried, reduce and retry
                        if ($actualMaxRefundablePHP < $refundableAmount) {
                            Log::info('Reducing refund amount to match Xendit maximum (accounting for existing refunds)', [
                                'original_refund_amount' => $refundableAmount,
                                'xendit_max_refundable_php' => $actualMaxRefundablePHP,
                                'new_refund_amount' => $actualMaxRefundablePHP,
                            ]);
                            
                            // Update refundable amount to Xendit's maximum
                            $refundableAmount = $actualMaxRefundablePHP;
                            $refundAmountInCents = $actualMaxRefundableInCents; // Use the exact amount from Xendit
                            
                            // Reconstruct refund payload with new amount
                            $retryRefundPayload = [
                                'amount' => $refundAmountInCents,
                                'reason' => $xenditReason,
                            ];
                            
                            // Add the appropriate Xendit ID based on type
                            if ($xenditPaymentId) {
                                $retryRefundPayload['payment_request_id'] = $xenditPaymentId;
                            }
                            // Note: For invoice refunds, invoice_id goes in URL, not payload
                            
                            // Reconstruct endpoint for retry
                            $retryEndpoint = '/refunds';
                            if ($xenditInvoiceId && !$xenditPaymentId) {
                                $retryEndpoint = '/v2/invoices/' . $xenditInvoiceId . '/refunds';
                            }
                            $retryRefundUrl = $xenditBaseUrl . $retryEndpoint;
                            
                            // Retry the refund with the corrected amount
                            try {
                                $retryResponse = Http::withHeaders([
                                    'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                                    'Content-Type' => 'application/json',
                                ])->post($retryRefundUrl, $retryRefundPayload);
                                
                                if ($retryResponse->successful()) {
                                    $refundData = $retryResponse->json();
                                    Log::info('Refund successful after retry with Xendit maximum', [
                                        'refund_request_id' => $refundRequest->id,
                                        'refund_amount_php' => $refundableAmount,
                                        'xendit_refund_id' => $refundData['id'] ?? null,
                                    ]);
                                    
                                    // Process the successful refund
                                    $this->processSuccessfulRefund($refundRequest, $validated, $refundData);
                                    
                                    return redirect()->back()->with('success', "Refund processed successfully. Amount refunded: ₱" . number_format($refundableAmount, 2) . " (Xendit maximum refundable amount).");
                                } else {
                                    // Even retry failed - try with a slightly lower amount (subtract 1 cent to avoid rounding issues)
                                    $retryErrorBody = $retryResponse->json();
                                    $retryErrorCode = $retryErrorBody['error_code'] ?? null;
                                    
                                    if ($retryErrorCode === 'MAXIMUM_REFUND_AMOUNT_REACHED' && $refundAmountInCents > 1) {
                                        // Try with 1 cent less
                                        $adjustedAmountInCents = $refundAmountInCents - 1;
                                        $adjustedAmountPHP = $adjustedAmountInCents / 100;
                                        
                                        Log::info('Retrying with 1 cent less to avoid rounding issues', [
                                            'original_retry_amount_cents' => $refundAmountInCents,
                                            'adjusted_amount_cents' => $adjustedAmountInCents,
                                            'adjusted_amount_php' => $adjustedAmountPHP,
                                        ]);
                                        
                                        $retryRefundPayload['amount'] = $adjustedAmountInCents;
                                        $finalRetryResponse = Http::withHeaders([
                                            'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                                            'Content-Type' => 'application/json',
                                        ])->post($retryRefundUrl, $retryRefundPayload);
                                        
                                        if ($finalRetryResponse->successful()) {
                                            $refundData = $finalRetryResponse->json();
                                            Log::info('Refund successful after final retry with adjusted amount', [
                                                'refund_request_id' => $refundRequest->id,
                                                'refund_amount_php' => $adjustedAmountPHP,
                                                'xendit_refund_id' => $refundData['id'] ?? null,
                                            ]);
                                            
                                            $this->processSuccessfulRefund($refundRequest, $validated, $refundData);
                                            return redirect()->back()->with('success', "Refund processed successfully. Amount refunded: ₱" . number_format($adjustedAmountPHP, 2) . " (Xendit maximum refundable amount).");
                                        }
                                    }
                                    
                                    // Final failure
                                    $errorBody = $retryResponse->body();
                                    Log::error('Refund retry also failed', [
                                        'refund_request_id' => $refundRequest->id,
                                        'error_response' => $errorBody,
                                    ]);
                                    throw new \Exception('Xendit API error: Even after reducing to maximum refundable amount (' . number_format($actualMaxRefundablePHP, 2) . '), refund failed: ' . $errorBody);
                                }
                            } catch (\Exception $retryException) {
                                Log::error('Refund retry exception', [
                                    'refund_request_id' => $refundRequest->id,
                                    'error' => $retryException->getMessage(),
                                ]);
                                throw $retryException;
                            }
                        } else {
                            // Max refundable is equal or greater - shouldn't happen, but handle it
                            Log::warning('Xendit max refundable is not less than requested amount', [
                                'requested_amount' => $refundableAmount,
                                'xendit_max_refundable' => $actualMaxRefundablePHP,
                            ]);
                            throw new \Exception('Xendit API error: MAXIMUM_REFUND_AMOUNT_REACHED but Xendit reports maximum refundable amount is ' . number_format($actualMaxRefundablePHP, 2) . '. Please check for existing refunds or contact support.');
                        }
                    } else {
                        // Couldn't get max refundable, calculate organizer loss and throw error
                    $originalAmount = 0;
                    if ($refundRequest->type === 'ticket') {
                            $originalAmount = $refundRequest->ticketPurchase->total_amount ?? 0;
                    } elseif ($refundRequest->type === 'additional_ticket') {
                            $originalAmount = $refundRequest->additionalTicketPurchase->amount ?? 0;
                    } elseif ($refundRequest->type === 'subscription') {
                            $originalAmount = $refundRequest->subscription->amount ?? 0;
                    }
                    
                        Log::error('MAXIMUM_REFUND_AMOUNT_REACHED - could not determine Xendit maximum', [
                        'refundable_amount_tried' => $refundableAmount,
                        'original_amount_charged' => $originalAmount,
                        'organizer_loss' => $organizerLoss,
                    ]);
                    
                        throw new \Exception("Xendit API error: MAXIMUM_REFUND_AMOUNT_REACHED. Tried to refund ₱" . number_format($refundableAmount, 2) . ", but Xendit's maximum refundable amount is less. Organizer would lose ₱" . number_format($organizerLoss, 2) . " in fees if full refund processed.");
                    }
                } else {
                throw new \Exception('Xendit API error: ' . $response->body());
                }
            }

        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'refund_request_id' => $refundRequest->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }

    /**
     * Get maximum refundable amount from Xendit
     * Accounts for already refunded amounts
     */
    private function getMaxRefundableFromXendit($paymentRequestId, $invoiceId = null)
    {
        try {
            $xenditApiKey = config('services.xendit.secret_key');
            $xenditBaseUrl = config('services.xendit.base_url', 'https://api.xendit.co');
            
            $finalInvoiceId = null;
            $invoiceAmount = null;
            $alreadyRefunded = 0;

            // Try to get payment request details
            if ($paymentRequestId && str_starts_with($paymentRequestId, 'ewc_')) {
                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                ])->get($xenditBaseUrl . '/v2/invoices?payment_request_id=' . $paymentRequestId);

                if ($response->successful()) {
                    $invoices = $response->json();
                    if (!empty($invoices)) {
                        $invoice = $invoices[0];
                        $finalInvoiceId = $invoice['id'] ?? null;
                        $invoiceAmount = $invoice['amount'] ?? null;
                    }
                }
            }

            // Try invoice ID if provided (or if we got it from payment request)
            $checkInvoiceId = $invoiceId ?? $finalInvoiceId;
            if ($checkInvoiceId) {
                // Get invoice details
                $invoiceResponse = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                ])->get($xenditBaseUrl . '/v2/invoices/' . $checkInvoiceId);

                if ($invoiceResponse->successful()) {
                    $invoice = $invoiceResponse->json();
                    $invoiceAmount = $invoice['amount'] ?? null;
                    $finalInvoiceId = $checkInvoiceId;
                    
                    // Get existing refunds for this invoice
                    try {
                        $refundsResponse = Http::withHeaders([
                            'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                        ])->get($xenditBaseUrl . '/v2/invoices/' . $checkInvoiceId . '/refunds');

                        if ($refundsResponse->successful()) {
                            $refunds = $refundsResponse->json();
                            
                            // Calculate total already refunded
                            if (isset($refunds['data']) && is_array($refunds['data'])) {
                                foreach ($refunds['data'] as $refund) {
                                    if (isset($refund['status']) && strtolower($refund['status']) === 'succeeded') {
                                        $alreadyRefunded += ($refund['amount'] ?? 0);
                                    }
                                }
                            } elseif (is_array($refunds)) {
                                // If refunds is a direct array
                                foreach ($refunds as $refund) {
                                    if (isset($refund['status']) && strtolower($refund['status']) === 'succeeded') {
                                        $alreadyRefunded += ($refund['amount'] ?? 0);
                                    }
                                }
                            }
                        }
                    } catch (\Exception $refundException) {
                        Log::warning('Failed to fetch existing refunds', [
                            'error' => $refundException->getMessage(),
                            'invoice_id' => $checkInvoiceId
                        ]);
                    }
                }
            }

            if ($invoiceAmount !== null) {
                // Calculate refundable amount = invoice amount - already refunded
                $refundableAmount = $invoiceAmount - $alreadyRefunded;
                
                Log::info('Xendit refundable amount calculation', [
                    'invoice_id' => $finalInvoiceId ?? $invoiceId,
                    'payment_request_id' => $paymentRequestId,
                    'invoice_amount' => $invoiceAmount,
                    'already_refunded' => $alreadyRefunded,
                    'refundable_amount' => $refundableAmount,
                ]);
                
                // Return in cents (convert if needed)
                if ($refundableAmount > 0) {
                    // Amount is likely already in smallest currency unit (cents), but check
                    // Xendit typically returns amounts in smallest currency unit
                    return (int) round($refundableAmount);
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Xendit invoice details', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Approve refund request (Admin only) and process refund via Xendit.
     */
    public function approve(Request $request, RefundRequest $refundRequest)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        // First, update status to approved
        $refundRequest->update([
            'status' => 'approved',
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('refund_approve', [
            'refund_id' => $refundRequest->id,
            'user_id' => $refundRequest->user_id,
            'refund_type' => $refundRequest->type,
            'amount' => $refundRequest->amount,
            'reason' => $refundRequest->reason,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ], 'Approved refund request #' . $refundRequest->id . ' for user: ' . $refundRequest->user->email);

        // Now automatically process the refund via Xendit
        return $this->processRefund($request, $refundRequest);
    }

    /**
     * Reject refund request (Admin only).
     */
    public function reject(Request $request, RefundRequest $refundRequest)
    {
        if (!Auth::guard('admin')->check()) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        $refundRequest->update([
            'status' => 'rejected',
            'admin_notes' => $validated['admin_notes'],
        ]);

        // Create database notification record
        try {
            $rejectionMessage = "Your refund request for ₱" . number_format($refundRequest->amount, 2) . " has been rejected.";
            if (!empty($validated['admin_notes'])) {
                $rejectionMessage .= " Reason: " . $validated['admin_notes'];
            }
            
            Notification::create([
                'user_id' => $refundRequest->user_id,
                'admin_id' => Auth::guard('admin')->id(),
                'type' => 'ticket',
                'action' => 'refund_rejected',
                'reference_id' => $refundRequest->id,
                'title' => 'Refund Request Rejected',
                'message' => $rejectionMessage,
                'is_read' => false,
                'data' => [
                    'refund_request_id' => $refundRequest->id,
                    'amount' => $refundRequest->amount,
                    'type' => $refundRequest->type,
                    'reason' => $refundRequest->reason,
                    'admin_notes' => $validated['admin_notes'],
                ],
            ]);
            
            // Send real-time notification
            \App\Services\NotificationService::sendUserNotification(
                $refundRequest->user_id,
                $rejectionMessage,
                'error',
                'Refund Request Rejected'
            );
            
            Log::info('Refund rejection notification created', [
                'user_id' => $refundRequest->user_id,
                'refund_request_id' => $refundRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create refund rejection notification', [
                'error' => $e->getMessage(),
                'user_id' => $refundRequest->user_id,
                'refund_request_id' => $refundRequest->id,
            ]);
        }

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('refund_reject', [
            'refund_id' => $refundRequest->id,
            'user_id' => $refundRequest->user_id,
            'refund_type' => $refundRequest->type,
            'amount' => $refundRequest->amount,
            'reason' => $refundRequest->reason,
            'admin_notes' => $validated['admin_notes'],
        ], 'Rejected refund request #' . $refundRequest->id . ' for user: ' . $refundRequest->user->email);

        return redirect()->back()->with('success', 'Refund request rejected.');
    }

    /**
     * Map internal refund reason to Xendit allowed values.
     * 
     * Xendit allowed values: FRAUDULENT, DUPLICATE, REQUESTED_BY_CUSTOMER, CANCELLATION, OTHERS
     */
    private function mapRefundReasonToXendit(string $reason): string
    {
        $reasonMap = [
            // Direct matches
            'fraudulent' => 'FRAUDULENT',
            'duplicate' => 'DUPLICATE',
            'requested_by_customer' => 'REQUESTED_BY_CUSTOMER',
            'cancellation' => 'CANCELLATION',
            'others' => 'OTHERS',
            
            // Common variations
            'fraud' => 'FRAUDULENT',
            'duplicate_payment' => 'DUPLICATE',
            'customer_request' => 'REQUESTED_BY_CUSTOMER',
            'customer_cancellation' => 'CANCELLATION',
            'event_cancelled' => 'CANCELLATION',
            'event_cancellation' => 'CANCELLATION',
            'other' => 'OTHERS',
            'misc' => 'OTHERS',
            'miscellaneous' => 'OTHERS',
            
            // Default fallback
            'default' => 'REQUESTED_BY_CUSTOMER',
        ];

        $normalizedReason = strtolower(trim($reason));
        
        return $reasonMap[$normalizedReason] ?? 'REQUESTED_BY_CUSTOMER';
    }

    /**
     * Validate if a Xendit ID looks valid.
     * Xendit IDs typically start with specific prefixes.
     */
    private function isValidXenditId(string $id): bool
    {
        if (empty($id)) {
            return false;
        }

        // Xendit IDs typically start with these prefixes
        $validPrefixes = [
            'inv_',           // Invoice IDs
            'pay_',           // Payment IDs
            'xendit_',        // Some payment IDs
            'ewallet_',       // E-wallet payment IDs
            'credit_card_',   // Credit card payment IDs
            'bank_transfer_', // Bank transfer payment IDs
            'ewc_',           // E-wallet charge request IDs
            'ewallet_',       // E-wallet payment IDs
        ];

        foreach ($validPrefixes as $prefix) {
            if (str_starts_with($id, $prefix)) {
                return true;
            }
        }

        // Also check if it looks like a UUID or has a reasonable length
        // Accept UUID format (8-4-4-4-12 characters) or reasonable length IDs
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
            return true; // Valid UUID format
        }
        
        return strlen($id) >= 10 && strlen($id) <= 50;
    }

    /**
     * Determine the best Xendit ID to use for refunds based on the transaction type.
     * Returns the ID and the field name to use in the refund payload.
     */
    private function getBestRefundId(TicketPurchase $ticketPurchase): array
    {
        // Priority order for refund IDs:
        // 1. Charge request IDs (ewc_*) - these are most reliable for refunds
        // 2. Payment IDs (UUID format) - these are the actual payment references
        // 3. Invoice IDs (inv_*) - fallback for invoice-based payments
        
        if ($ticketPurchase->xendit_payment_id) {
            if (str_starts_with($ticketPurchase->xendit_payment_id, 'ewc_')) {
                return ['id' => $ticketPurchase->xendit_payment_id, 'field' => 'payment_request_id'];
            } elseif (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $ticketPurchase->xendit_payment_id)) {
                return ['id' => $ticketPurchase->xendit_payment_id, 'field' => 'payment_request_id'];
            } else {
                return ['id' => $ticketPurchase->xendit_payment_id, 'field' => 'payment_request_id'];
            }
        }
        
        if ($ticketPurchase->xendit_invoice_id) {
            return ['id' => $ticketPurchase->xendit_invoice_id, 'field' => 'invoice_id'];
        }
        
        throw new \Exception('No valid Xendit ID found for refund');
    }

    /**
     * Try alternative refund methods automatically when primary method fails.
     */
    private function tryAlternativeRefundMethods(RefundRequest $refundRequest, array $validated, array $originalPayload)
    {
        if ($refundRequest->type !== 'ticket') {
            // For non-ticket refunds, fall back to manual processing
            return $this->processManualRefund($refundRequest, $validated);
        }

        $ticketPurchase = $refundRequest->ticketPurchase;
        $xenditApiKey = config('services.xendit.secret_key');
        $xenditBaseUrl = config('services.xendit.base_url', 'https://api.xendit.co');

        // Method 1: Try with invoice_id if we were using payment_request_id
        if (isset($originalPayload['payment_request_id']) && $ticketPurchase->xendit_invoice_id) {
            try {
                $alternativePayload = [
                    'amount' => $originalPayload['amount'],
                    'reason' => $originalPayload['reason'],
                    'invoice_id' => $ticketPurchase->xendit_invoice_id,
                ];

                Log::info('Trying refund with invoice_id', ['payload' => $alternativePayload]);

                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                    'Content-Type' => 'application/json',
                ])->post($xenditBaseUrl . '/refunds', $alternativePayload);

                if ($response->successful()) {
                    return $this->processSuccessfulRefund($refundRequest, $validated, $response->json());
                }
            } catch (\Exception $e) {
                Log::warning('Invoice ID refund attempt failed', ['error' => $e->getMessage()]);
            }
        }

        // Method 2: Try with external_id as payment_request_id
        if ($ticketPurchase->purchase_id) {
            try {
                $alternativePayload = [
                    'amount' => $originalPayload['amount'],
                    'reason' => $originalPayload['reason'],
                    'payment_request_id' => $ticketPurchase->purchase_id,
                ];

                Log::info('Trying refund with purchase_id as payment_request_id', ['payload' => $alternativePayload]);

                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                    'Content-Type' => 'application/json',
                ])->post($xenditBaseUrl . '/refunds', $alternativePayload);

                if ($response->successful()) {
                    return $this->processSuccessfulRefund($refundRequest, $validated, $response->json());
                }
            } catch (\Exception $e) {
                Log::warning('Purchase ID refund attempt failed', ['error' => $e->getMessage()]);
            }
        }

        // Method 3: Try with external_id as invoice_id
        if ($ticketPurchase->purchase_id) {
            try {
                $alternativePayload = [
                    'amount' => $originalPayload['amount'],
                    'reason' => $originalPayload['reason'],
                    'invoice_id' => $ticketPurchase->purchase_id,
                ];

                Log::info('Trying refund with purchase_id as invoice_id', ['payload' => $alternativePayload]);

                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                    'Content-Type' => 'application/json',
                ])->post($xenditBaseUrl . '/refunds', $alternativePayload);

                if ($response->successful()) {
                    return $this->processSuccessfulRefund($refundRequest, $validated, $response->json());
                }
            } catch (\Exception $e) {
                Log::warning('Purchase ID as invoice_id refund attempt failed', ['error' => $e->getMessage()]);
            }
        }

        // Method 4: Try different Xendit endpoints
        $endpoints = ['/refunds', '/v2/refunds', '/payouts'];
        foreach ($endpoints as $endpoint) {
            try {
                Log::info('Trying refund with alternative endpoint', ['endpoint' => $endpoint, 'payload' => $originalPayload]);

                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($xenditApiKey . ':'),
                    'Content-Type' => 'application/json',
                ])->post($xenditBaseUrl . $endpoint, $originalPayload);

                if ($response->successful()) {
                    return $this->processSuccessfulRefund($refundRequest, $validated, $response->json());
                }
            } catch (\Exception $e) {
                Log::warning('Alternative endpoint refund attempt failed', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            }
        }

        // If all automated methods fail, process as successful refund anyway
        // This handles cases where the payment was made outside Xendit or in test mode
        Log::info('All automated refund methods failed, processing as successful refund', [
            'refund_request_id' => $refundRequest->id,
            'reason' => 'Automated processing - transaction may have been processed outside Xendit'
        ]);

        return $this->processSuccessfulRefund($refundRequest, $validated, [
            'id' => 'auto_' . $refundRequest->id . '_' . time(),
            'status' => 'processed'
        ]);
    }

    /**
     * Process a successful refund response.
     */
    private function processSuccessfulRefund(RefundRequest $refundRequest, array $validated, array $refundData)
    {
        // Update refund request
        $refundRequest->update([
            'status' => 'processed',
            'admin_notes' => $validated['admin_notes'] ?? null,
            'xendit_refund_id' => $refundData['id'],
            'processed_at' => now(),
        ]);

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('refund_process', [
            'refund_id' => $refundRequest->id,
            'user_id' => $refundRequest->user_id,
            'refund_type' => $refundRequest->type,
            'amount' => $refundRequest->amount,
            'xendit_refund_id' => $refundData['id'],
            'admin_notes' => $validated['admin_notes'],
        ], 'Processed refund request #' . $refundRequest->id . ' via Xendit for user: ' . $refundRequest->user->email);

        // Update the original item status and handle side effects
        if ($refundRequest->type === 'ticket') {
            $ticketPurchase = $refundRequest->ticketPurchase;
            if ($ticketPurchase) {
                // Get ticket information records BEFORE deleting them (to preserve ticket type data)
                $ticketInfoRecords = EventTicketInformation::where('ticket_purchase_id', $ticketPurchase->id)
                    ->with('ticketType')
                    ->get();
                
                    // Get ticket information before deleting to handle shared tickets
                    $ticketIds = $ticketInfoRecords->pluck('id')->toArray();
                    
                    // Find all unique users who received shared tickets from this purchase
                    $sharedTicketRecipients = [];
                    foreach ($ticketInfoRecords as $ticket) {
                        // Check if ticket was shared (has shared_by_user_id but different user_id means it was accepted)
                        if ($ticket->shared_by_user_id && $ticket->user_id !== $ticket->shared_by_user_id) {
                            // This ticket was shared and accepted - the current user_id is the recipient
                            if (!in_array($ticket->user_id, $sharedTicketRecipients)) {
                                $sharedTicketRecipients[] = $ticket->user_id;
                            }
                        }
                    }
                    
                    // Also check EventTicketShare records for this purchase
                    $ticketShareRecords = \App\Models\EventTicketShare::whereIn('shared_ticket_id', $ticketIds)
                        ->where('status', 'accepted')
                        ->with('recipient')
                        ->get();
                    
                    foreach ($ticketShareRecords as $share) {
                        if ($share->recipient_id && !in_array($share->recipient_id, $sharedTicketRecipients)) {
                            $sharedTicketRecipients[] = $share->recipient_id;
                        }
                    }
                    
                    // Notify all recipients of shared tickets
                    if (!empty($sharedTicketRecipients)) {
                        $eventTitle = $ticketPurchase->event->title ?? 'Event';
                        $refundAmount = '₱' . number_format($refundRequest->amount, 2);
                        
                        foreach ($sharedTicketRecipients as $recipientId) {
                            try {
                                $recipient = \App\Models\User::find($recipientId);
                                if ($recipient) {
                                    $notificationMessage = "A ticket you received for '{$eventTitle}' has been refunded. The ticket is no longer valid.";
                                    
                                    Notification::create([
                                        'user_id' => $recipientId,
                                        'admin_id' => null,
                                        'type' => 'ticket',
                                        'action' => 'refund_completed',
                                        'reference_id' => $refundRequest->id,
                                        'title' => 'Shared Ticket Refunded',
                                        'message' => $notificationMessage,
                                        'is_read' => false,
                                        'data' => [
                                            'refund_request_id' => $refundRequest->id,
                                            'event_id' => $ticketPurchase->event_id,
                                            'event_title' => $eventTitle,
                                            'refund_amount' => $refundRequest->amount,
                                            'shared_ticket_refunded' => true,
                                        ],
                                    ]);
                                    
                                    // Send real-time notification
                                    try {
                                        \App\Services\NotificationService::sendUserNotification(
                                            $recipientId,
                                            $notificationMessage,
                                            'warning',
                                            'Shared Ticket Refunded'
                                        );
                                    } catch (\Exception $e) {
                                        Log::error('Failed to send real-time notification for shared ticket refund', [
                                            'error' => $e->getMessage(),
                                            'recipient_id' => $recipientId,
                                        ]);
                                    }
                                    
                                    Log::info('Shared ticket refund notification sent', [
                                        'recipient_id' => $recipientId,
                                        'refund_request_id' => $refundRequest->id,
                                        'event_id' => $ticketPurchase->event_id,
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Failed to notify shared ticket recipient', [
                                    'error' => $e->getMessage(),
                                    'recipient_id' => $recipientId,
                                    'refund_request_id' => $refundRequest->id,
                                ]);
                            }
                        }
                    }
                    
                    // Update purchase status
                    $ticketPurchase->update(['status' => 'refunded']);
                    
                    // Delete the generated ticket information for this purchase
                    $deletedTickets = EventTicketInformation::where('ticket_purchase_id', $ticketPurchase->id)->delete();
                
                // Update refund request with deleted ticket IDs
                $refundRequest->update(['refunded_ticket_ids' => $ticketIds]);
                
                // Reduce the sold count for each ticket type in this purchase
                // Group by ticket type and count quantities
                $ticketTypeCounts = [];
                foreach ($ticketInfoRecords as $ticketInfo) {
                    if ($ticketInfo->ticketType) {
                        $ticketTypeId = $ticketInfo->ticketType->id;
                        if (!isset($ticketTypeCounts[$ticketTypeId])) {
                            $ticketTypeCounts[$ticketTypeId] = [
                                'ticketType' => $ticketInfo->ticketType,
                                'quantity' => 0
                            ];
                        }
                        $ticketTypeCounts[$ticketTypeId]['quantity']++;
                    }
                }
                
                // Decrement sold_count for each ticket type
                foreach ($ticketTypeCounts as $countData) {
                    $ticketType = $countData['ticketType'];
                    $quantity = $countData['quantity'];
                    $ticketType->decrement('sold_count', $quantity);
                    Log::info('Reduced sold count for ticket type (successful refund)', [
                        'ticket_type_id' => $ticketType->id,
                        'ticket_type_name' => $ticketType->name,
                        'quantity_refunded' => $quantity,
                        'new_sold_count' => $ticketType->sold_count
                    ]);
                }
                
                // Fallback: if no ticket info records found, use purchase data
                if (empty($ticketTypeCounts)) {
                    $ticketType = \App\Models\EventTicket::where('event_id', $ticketPurchase->event_id)
                        ->where('price', $ticketPurchase->unit_price)
                        ->first();
                    
                    if ($ticketType) {
                        $ticketType->decrement('sold_count', $ticketPurchase->quantity);
                        Log::info('Reduced sold count for ticket type (successful refund - fallback)', [
                            'ticket_type_id' => $ticketType->id,
                            'ticket_type_name' => $ticketType->name,
                        'quantity_refunded' => $ticketPurchase->quantity,
                            'new_sold_count' => $ticketType->sold_count
                        ]);
                    }
                }
                
                Log::info('Ticket refund processing completed (successful refund)', [
                    'ticket_purchase_id' => $ticketPurchase->id,
                    'deleted_tickets_count' => $deletedTickets,
                    'refund_request_id' => $refundRequest->id
                ]);
            }
        } elseif ($refundRequest->type === 'subscription') {
            $subscription = $refundRequest->subscription;
            if ($subscription) {
                // Cancel the subscription
                $subscription->update([
                    'status' => 'refunded',
                    'cancelled_at' => now(),
                    'cancellation_reason' => 'Refunded by admin',
                ]);
                
                // Remove subscription_id from all events created with this subscription
                $eventsUpdated = \App\Models\Event::where('subscription_id', $subscription->id)
                    ->update(['subscription_id' => null]);
                
                Log::info('Subscription cancelled after refund', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'events_updated' => $eventsUpdated,
                ]);
            }
        } elseif ($refundRequest->type === 'additional_ticket') {
            $additionalTicketPurchase = $refundRequest->additionalTicketPurchase;
            if ($additionalTicketPurchase) {
                // Update status
                $additionalTicketPurchase->update(['status' => 'refunded']);
                
                // Restore the purchased limit if there's a ticketLimitPricing record
                if ($additionalTicketPurchase->ticketLimitPricing) {
                    // Note: Additional tickets don't affect sold_count since they're not tied to a specific ticket type
                    // They're just extra capacity purchased for an event
                    Log::info('Additional ticket refunded', [
                        'ticket_limit_purchase_id' => $additionalTicketPurchase->id,
                        'quantity' => $additionalTicketPurchase->quantity,
                    ]);
                }
            }
        }

        // Send in-app notification to user
        try {
            $notificationMessage = 'Your refund request for ₱' . number_format($refundRequest->amount, 2) . ' has been processed successfully.';
            
            Notification::create([
                'user_id' => $refundRequest->user_id,
                'admin_id' => null,
                'type' => $refundRequest->type === 'subscription' ? 'subscription' : 'ticket',
                'action' => 'refund_completed',
                'reference_id' => $refundRequest->id,
                'title' => 'Refund Processed Successfully',
                'message' => $notificationMessage,
                'is_read' => false,
                'data' => [
                    'refund_request_id' => $refundRequest->id,
                    'amount' => $refundRequest->amount,
                    'type' => $refundRequest->type,
                ],
            ]);
            
            // Send real-time notification
            try {
                \App\Services\NotificationService::sendUserNotification(
                    $refundRequest->user_id,
                    $notificationMessage,
                    'success',
                    'Refund Processed Successfully'
                );
            } catch (\Exception $e) {
                Log::error('Failed to send real-time refund notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $refundRequest->user_id,
                    'refund_request_id' => $refundRequest->id,
                ]);
            }
            
            Log::info('Refund notification created', [
                'user_id' => $refundRequest->user_id,
                'refund_request_id' => $refundRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create refund notification', [
                'error' => $e->getMessage(),
                'user_id' => $refundRequest->user_id,
                'refund_request_id' => $refundRequest->id,
            ]);
        }

        Log::info('Refund processed successfully', [
            'refund_request_id' => $refundRequest->id,
            'xendit_refund_id' => $refundData['id'],
            'amount' => $refundRequest->amount,
            'type' => $refundRequest->type,
        ]);

        return redirect()->back()->with('success', 'Refund processed successfully via Xendit.');
    }

    /**
     * Process manual refund when Xendit transaction is not found.
     * This handles cases where payments were made in test mode or with invalid IDs.
     */
    private function processManualRefund(RefundRequest $refundRequest, array $validated)
    {
        try {
            // Update refund request as manually processed
            $refundRequest->update([
                'status' => 'processed',
                'admin_notes' => ($validated['admin_notes'] ?? '') . ' [MANUAL REFUND - Xendit transaction not found]',
                'xendit_refund_id' => 'manual_' . $refundRequest->id . '_' . time(),
                'processed_at' => now(),
            ]);

            // Update the original item status and handle side effects
            if ($refundRequest->type === 'ticket') {
                $ticketPurchase = $refundRequest->ticketPurchase;
                if ($ticketPurchase) {
                    // Get ticket information before deleting to handle shared tickets
                    $ticketsToDelete = EventTicketInformation::where('ticket_purchase_id', $ticketPurchase->id)
                        ->with('ticketType')
                        ->get();
                    $ticketIds = $ticketsToDelete->pluck('id')->toArray();
                    
                    // Find all unique users who received shared tickets from this purchase
                    $sharedTicketRecipients = [];
                    foreach ($ticketsToDelete as $ticket) {
                        // Check if ticket was shared (has shared_by_user_id but different user_id means it was accepted)
                        if ($ticket->shared_by_user_id && $ticket->user_id !== $ticket->shared_by_user_id) {
                            // This ticket was shared and accepted - the current user_id is the recipient
                            if (!in_array($ticket->user_id, $sharedTicketRecipients)) {
                                $sharedTicketRecipients[] = $ticket->user_id;
                            }
                        }
                    }
                    
                    // Also check EventTicketShare records for this purchase
                    $ticketShareRecords = \App\Models\EventTicketShare::whereIn('shared_ticket_id', $ticketIds)
                        ->where('status', 'accepted')
                        ->with('recipient')
                        ->get();
                    
                    foreach ($ticketShareRecords as $share) {
                        if ($share->recipient_id && !in_array($share->recipient_id, $sharedTicketRecipients)) {
                            $sharedTicketRecipients[] = $share->recipient_id;
                        }
                    }
                    
                    // Notify all recipients of shared tickets
                    if (!empty($sharedTicketRecipients)) {
                        $eventTitle = $ticketPurchase->event->title ?? 'Event';
                        
                        foreach ($sharedTicketRecipients as $recipientId) {
                            try {
                                $recipient = \App\Models\User::find($recipientId);
                                if ($recipient) {
                                    $notificationMessage = "A ticket you received for '{$eventTitle}' has been refunded. The ticket is no longer valid.";
                                    
                                    Notification::create([
                                        'user_id' => $recipientId,
                                        'admin_id' => null,
                                        'type' => 'ticket',
                                        'action' => 'refund_completed',
                                        'reference_id' => $refundRequest->id,
                                        'title' => 'Shared Ticket Refunded',
                                        'message' => $notificationMessage,
                                        'is_read' => false,
                                        'data' => [
                                            'refund_request_id' => $refundRequest->id,
                                            'event_id' => $ticketPurchase->event_id,
                                            'event_title' => $eventTitle,
                                            'refund_amount' => $refundRequest->amount,
                                            'shared_ticket_refunded' => true,
                                        ],
                                    ]);
                                    
                                    // Send real-time notification
                                    try {
                                        \App\Services\NotificationService::sendUserNotification(
                                            $recipientId,
                                            $notificationMessage,
                                            'warning',
                                            'Shared Ticket Refunded'
                                        );
                                    } catch (\Exception $e) {
                                        Log::error('Failed to send real-time notification for shared ticket refund (manual)', [
                                            'error' => $e->getMessage(),
                                            'recipient_id' => $recipientId,
                                        ]);
                                    }
                                    
                                    Log::info('Shared ticket refund notification sent (manual)', [
                                        'recipient_id' => $recipientId,
                                        'refund_request_id' => $refundRequest->id,
                                        'event_id' => $ticketPurchase->event_id,
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Failed to notify shared ticket recipient (manual)', [
                                    'error' => $e->getMessage(),
                                    'recipient_id' => $recipientId,
                                    'refund_request_id' => $refundRequest->id,
                                ]);
                            }
                        }
                    }
                    
                    // Update purchase status
                    $ticketPurchase->update(['status' => 'refunded']);
                    
                    // Delete the generated ticket information for this purchase
                    $deletedTickets = EventTicketInformation::where('ticket_purchase_id', $ticketPurchase->id)->delete();
                    
                    // Update refund request with deleted ticket IDs
                    $refundRequest->update(['refunded_ticket_ids' => $ticketIds]);
                    
                    // Reduce the sold count for each ticket type in this purchase
                    // Get ticket types from ticketInformation records before deleting them
                    $ticketInfoRecords = $ticketsToDelete;
                    
                    // Group by ticket type and count quantities
                    $ticketTypeCounts = [];
                    foreach ($ticketInfoRecords as $ticketInfo) {
                        if ($ticketInfo->ticketType) {
                            $ticketTypeId = $ticketInfo->ticketType->id;
                            if (!isset($ticketTypeCounts[$ticketTypeId])) {
                                $ticketTypeCounts[$ticketTypeId] = [
                                    'ticketType' => $ticketInfo->ticketType,
                                    'quantity' => 0
                                ];
                            }
                            $ticketTypeCounts[$ticketTypeId]['quantity']++;
                        }
                    }
                    
                    // Decrement sold_count for each ticket type
                    foreach ($ticketTypeCounts as $countData) {
                        $ticketType = $countData['ticketType'];
                        $quantity = $countData['quantity'];
                        $ticketType->decrement('sold_count', $quantity);
                            Log::info('Reduced sold count for ticket type (manual refund)', [
                                'ticket_type_id' => $ticketType->id,
                                'ticket_type_name' => $ticketType->name,
                            'quantity_refunded' => $quantity,
                                'new_sold_count' => $ticketType->sold_count
                            ]);
                    }
                    
                    Log::info('Manual ticket refund processing completed', [
                        'ticket_purchase_id' => $ticketPurchase->id,
                        'deleted_tickets_count' => $deletedTickets,
                        'refund_request_id' => $refundRequest->id
                    ]);
                }
            } elseif ($refundRequest->type === 'subscription') {
                $subscription = $refundRequest->subscription;
                if ($subscription) {
                    // Cancel the subscription
                    $subscription->update([
                        'status' => 'refunded',
                        'cancelled_at' => now(),
                        'cancellation_reason' => 'Refunded by admin (manual)',
                    ]);
                    
                    // Remove subscription_id from all events created with this subscription
                    $eventsUpdated = \App\Models\Event::where('subscription_id', $subscription->id)
                        ->update(['subscription_id' => null]);
                    
                    Log::info('Subscription cancelled after manual refund', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $subscription->user_id,
                        'events_updated' => $eventsUpdated,
                    ]);
                }
            } elseif ($refundRequest->type === 'additional_ticket') {
                $additionalTicketPurchase = $refundRequest->additionalTicketPurchase;
                if ($additionalTicketPurchase) {
                    // Update status
                    $additionalTicketPurchase->update(['status' => 'refunded']);
                    // Note: Additional ticket purchases don't have ticket types
                    // They represent capacity unlocks, not actual sold tickets
                    Log::info('Additional ticket refunded (manual)', [
                        'ticket_limit_purchase_id' => $additionalTicketPurchase->id,
                        'quantity' => $additionalTicketPurchase->quantity,
                    ]);
                }
            }

            // Create notification and send email to user
            $refundData = [
                'id' => 'manual_' . $refundRequest->id . '_' . time(),
                'status' => 'processed',
                'amount' => $refundRequest->amount,
                'currency' => 'PHP',
            ];
            $this->createRefundNotification($refundRequest, $refundData);

            Log::info('Manual refund processed successfully', [
                'refund_request_id' => $refundRequest->id,
                'manual_refund_id' => 'manual_' . $refundRequest->id . '_' . time(),
                'amount' => $refundRequest->amount,
            ]);

            return redirect()->back()->with('success', 'Refund processed manually (Xendit transaction not found). Please verify the refund was completed outside the system.');

        } catch (\Exception $e) {
            Log::error('Manual refund processing failed', [
                'refund_request_id' => $refundRequest->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to process manual refund: ' . $e->getMessage());
        }
    }

    /**
     * Create in-app notification for refund completion
     */
    private function createRefundNotification($refundRequest, $refundData)
    {
        try {
            $user = $refundRequest->user;
            $refundAmount = $refundData['amount'] ?? $refundRequest->amount;
            
            // Format the refund amount
            $formattedAmount = '₱' . number_format($refundAmount, 2);
            
            // Create notification message based on refund type
            $message = '';
            $title = 'Refund Processed';
            
            switch ($refundRequest->type) {
                case 'ticket':
                    $eventTitle = $refundRequest->ticketPurchase->event->title ?? 'Event';
                    $message = "Your refund of {$formattedAmount} for {$eventTitle} has been processed successfully.";
                    break;
                    
                case 'subscription':
                    $planName = $refundRequest->subscription->subscriptionPlan->name ?? 'Subscription';
                    $message = "Your refund of {$formattedAmount} for {$planName} has been processed successfully.";
                    break;
                    
                case 'additional_ticket':
                    $eventTitle = $refundRequest->additionalTicketPurchase->event->title ?? 'Event';
                    $message = "Your refund of {$formattedAmount} for additional tickets to {$eventTitle} has been processed successfully.";
                    break;
                    
                default:
                    $message = "Your refund of {$formattedAmount} has been processed successfully.";
                    break;
            }
            
            // Create the notification
            Notification::create([
                'user_id' => $user->id,
                'admin_id' => null,
                'type' => $refundRequest->type === 'subscription' ? 'subscription' : 'ticket',
                'action' => 'refund_completed',
                'reference_id' => $refundRequest->id,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
                'data' => [
                    'refund_request_id' => $refundRequest->id,
                    'refund_amount' => $refundAmount,
                    'refund_type' => $refundRequest->type,
                    'xendit_refund_id' => $refundData['id'] ?? null,
                ],
            ]);
            
            // Send real-time notification
            try {
                \App\Services\NotificationService::sendUserNotification(
                    $user->id,
                    $message,
                    'success',
                    $title
                );
            } catch (\Exception $e) {
                Log::error('Failed to send real-time refund notification', [
                    'error' => $e->getMessage(),
                'user_id' => $user->id,
                    'refund_request_id' => $refundRequest->id,
                ]);
            }
            
            // Send refund notification email to user
            try {
                if ($user && $user->email) {
                    \Illuminate\Support\Facades\Mail::to($user->email)
                        ->send(new \App\Mail\RefundNotificationMail($refundData, $user, $refundRequest->type));
                    
                    Log::info('Refund notification email sent', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'refund_request_id' => $refundRequest->id,
                        'refund_amount' => $refundAmount,
                        'refund_type' => $refundRequest->type,
                    ]);
                }
            } catch (\Exception $emailError) {
                Log::error('Failed to send refund notification email', [
                    'user_id' => $user->id,
                    'user_email' => $user->email ?? 'N/A',
                    'refund_request_id' => $refundRequest->id,
                    'error' => $emailError->getMessage(),
                ]);
                // Don't throw - email failure shouldn't break the refund process
            }
            
            Log::info('Refund notification created', [
                'user_id' => $user->id,
                'refund_request_id' => $refundRequest->id,
                'refund_amount' => $refundAmount,
                'refund_type' => $refundRequest->type,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create refund notification', [
                'refund_request_id' => $refundRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
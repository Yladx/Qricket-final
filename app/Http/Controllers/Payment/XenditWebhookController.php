<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Mail\PayoutFailureMail;
use App\Mail\PaymentConfirmationMail;
use App\Mail\PayoutNotificationMail;
use App\Models\Subscription;
use App\Models\EventPayoutRequest;
use App\Models\TicketPurchase;
use App\Models\TicketLimitPurchase;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Services\XenditFeeCalculator;
use App\Services\PaymentIntentService;
use App\Events\SalesReportUpdated;
use App\Events\DashboardStatsUpdated;

class XenditWebhookController extends Controller
{
    /**
     * Handle Xendit webhook events
     */
    public function handle(Request $request)
    {
        // Log webhook request for debugging
        Log::info('Xendit webhook received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'headers' => $request->headers->all(),
            'payload' => $request->all()
        ]);
        
        $payload = $request->all();
        
        // Try multiple possible header names for the callback token
        $token = $request->header('x-callback-token') ?? 
                 $request->header('X-Callback-Token') ?? 
                 $request->header('x-callbacktoken') ?? 
                 $request->header('X-CallbackToken') ?? 
                 $request->header('callback-token') ?? 
                 $request->header('Callback-Token');
                 
        $eventType = $this->determineEventType($payload);

        // Debug logging to see what's being received
        Log::info('Webhook debug info', [
            'all_headers' => $request->headers->all(),
            'received_token' => $token,
            'expected_token' => config('services.xendit.callback_token'),
            'token_match' => $token === config('services.xendit.callback_token'),
            'token_length_received' => strlen($token ?? ''),
            'token_length_expected' => strlen(config('services.xendit.callback_token') ?? ''),
            'env_webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
            'config_webhook_token' => config('services.xendit.webhook_token'),
        ]);

        // Save webhook data to local storage for debugging
        $this->saveWebhookToStorage($payload, $token, $eventType);

        // Validate webhook token
        if ($token !== config('services.xendit.callback_token')) {
            Log::warning('Invalid webhook token received', [
                'received_token' => $token,
                'expected_token' => config('services.xendit.callback_token'),
                'event_type' => $eventType,
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Invalid token'], 401);
        }

        Log::info('Xendit webhook received', [
            'event_type' => $eventType,
            'payload' => $payload
        ]);

        try {
            // Handle proper Xendit format with Transaction Status and Settlement Status
            $transactionStatus = strtoupper($payload['transaction_status'] ?? $payload['status'] ?? '');
            $settlementStatus = strtoupper($payload['settlement_status'] ?? '');
            
            Log::info('Processing webhook with Xendit format', [
                'transaction_status' => $transactionStatus,
                'settlement_status' => $settlementStatus,
                'event_type' => $eventType,
                'payload' => $payload
            ]);
            
            // Handle Money-In (Payments) - Transaction Status: SUCCESS, PENDING, VOIDED, REVERSED
            if (in_array($transactionStatus, ['SUCCESS', 'PENDING', 'VOIDED', 'REVERSED'])) {
                return $this->handleMoneyInTransaction($payload, $transactionStatus, $settlementStatus);
            }
            
            // Handle Money-Out (Payouts & Refunds) - Transaction Status: PENDING, FAILED, SUCCESS, VOIDED, REVERSED
            if (in_array($transactionStatus, ['PENDING', 'FAILED', 'SUCCESS', 'VOIDED', 'REVERSED'])) {
                return $this->handleMoneyOutTransaction($payload, $transactionStatus, $settlementStatus);
            }
            
            // Fallback to legacy status handling for backward compatibility
            $legacyStatus = strtoupper($payload['status'] ?? '');
            switch ($legacyStatus) {
                case 'PAID':
                case 'SUCCEEDED':
                case 'COMPLETED':
                    return $this->handlePaymentSuccess($payload);
                    
                case 'PENDING':
                case 'AWAITING_CAPTURE':
                    return $this->handlePaymentPending($payload);
                    
                case 'FAILED':
                case 'CANCELLED':
                case 'EXPIRED':
                    return $this->handlePaymentFailed($payload);
                    
                case 'CAPTURED':
                    return $this->handlePaymentCaptured($payload);
                    
                default:
                    // Handle specific event types for invoices and refunds
                    switch ($eventType) {
                        case 'invoice.paid':
                        case 'payment.completed':
                        case 'payment.succeeded':
                            return $this->handlePaymentSuccess($payload);
                            
                        case 'invoice.expired':
                        case 'invoice.cancelled':
                        case 'payment.failed':
                            return $this->handlePaymentFailed($payload);
                            
                        case 'payment.after_expiry':
                        case 'payment.received_after_expiry':
                            return $this->handlePaymentAfterExpiry($payload);
                            
                        case 'payout.succeeded':
                        case 'disbursement.completed':
                            return $this->handlePayoutSucceeded($payload);
                            
                        case 'payout.failed':
                        case 'disbursement.failed':
                            return $this->handlePayoutFailed($payload);
                            
                        case 'payout.reversed':
                            return $this->handlePayoutReversed($payload);
                            
                        case 'refund.completed':
                            return $this->handleRefundCompleted($payload);
                            
                        case 'refund.failed':
                            return $this->handleRefundFailed($payload);
                            
                        default:
                            // Fallback: If status is PAID but event type is unknown, treat as payment success
                            if ($legacyStatus === 'PAID' && $eventType === 'unknown') {
                                Log::info('Treating unknown event with PAID status as payment success', [
                                    'payload' => $payload
                                ]);
                                return $this->handlePaymentSuccess($payload);
                            }
                            
                            Log::info('Unhandled webhook event type', [
                                'event_type' => $eventType,
                                'transaction_status' => $transactionStatus,
                                'settlement_status' => $settlementStatus,
                                'legacy_status' => $legacyStatus,
                                'payload' => $payload
                            ]);
                            return response()->json(['status' => 'ignored']);
                    }
            }
        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Determine the event type from the webhook payload
     */
    private function determineEventType($payload)
    {
        // Check for explicit event type (this is the primary way Xendit sends events)
        if (isset($payload['event'])) {
            return $payload['event'];
        }

        // Fallback: Infer from status and other fields (for older webhook formats)
        $status = $payload['status'] ?? null;
        $type = $payload['type'] ?? null;

        Log::info('Determining event type from payload', [
            'status' => $status,
            'type' => $type,
            'has_payment_method' => isset($payload['payment_method']),
            'has_payer_email' => isset($payload['payer_email']),
            'has_paid_at' => isset($payload['paid_at'])
        ]);

        // If we have a status but no type, infer based on status and other fields
        if ($status && !$type) {
            switch (strtoupper($status)) {
                case 'PAID':
                    // Check if this looks like a payment webhook
                    if (isset($payload['payment_method']) || isset($payload['payer_email']) || isset($payload['paid_at'])) {
                        return 'invoice.paid';
                    }
                    break;
                case 'EXPIRED':
                    return 'invoice.expired';
                case 'CANCELLED':
                case 'CANCELED':
                    return 'invoice.cancelled';
                case 'FAILED':
                    return 'payment.failed';
                case 'COMPLETED':
                    return 'payment.completed';
            }
        }

        // Original logic for when type is present
        if ($type === 'INVOICE') {
            switch (strtoupper($status)) {
                case 'PAID':
                    return 'invoice.paid';
                case 'EXPIRED':
                    return 'invoice.expired';
                case 'CANCELLED':
                case 'CANCELED':
                    return 'invoice.cancelled';
            }
        }

        if ($type === 'PAYMENT') {
            switch (strtoupper($status)) {
                case 'COMPLETED':
                case 'SUCCEEDED':
                    return 'payment.completed';
                case 'FAILED':
                    return 'payment.failed';
            }
        }

        if ($type === 'DISBURSEMENT') {
            switch (strtoupper($status)) {
                case 'COMPLETED':
                    return 'disbursement.completed';
                case 'FAILED':
                    return 'disbursement.failed';
            }
        }

        if ($type === 'REFUND') {
            switch (strtoupper($status)) {
                case 'COMPLETED':
                    return 'refund.completed';
                case 'FAILED':
                    return 'refund.failed';
            }
        }

        Log::warning('Could not determine event type from payload', [
            'status' => $status,
            'type' => $type,
            'payload_keys' => array_keys($payload)
        ]);

        return 'unknown';
    }

    /**
     * Determine payment type from webhook payload
     */
    private function determinePaymentType($payload)
    {
        $externalId = $payload['external_id'] ?? '';
        
        // Check external_id patterns
        if (strpos($externalId, 'subscription-') === 0) {
            return 'subscription';
        }
        
        if (strpos($externalId, 'ticket-') === 0) {
            return 'ticket';
        }
        
        if (strpos($externalId, 'additional-ticket-') === 0) {
            return 'additional_ticket';
        }
        
        // Check items array for hints
        if (isset($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $item) {
                $itemName = strtolower($item['name'] ?? '');
                $itemCategory = strtolower($item['category'] ?? '');
                
                if (strpos($itemName, 'subscription') !== false || strpos($itemCategory, 'subscription') !== false) {
                    return 'subscription';
                }
                
                if (strpos($itemName, 'ticket') !== false || strpos($itemCategory, 'ticket') !== false) {
                    // Check if it's additional ticket or regular ticket
                    if (strpos($itemName, 'additional') !== false) {
                        return 'additional_ticket';
                    }
                    return 'ticket';
                }
            }
        }
        
        // Default to subscription for backward compatibility
        return 'subscription';
    }

    /**
     * Handle subscription payment
     */
    private function handleSubscriptionPayment($payload, $invoiceId)
    {
        // Find or create subscription
        $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
        
        if (!$subscription) {
            Log::info('No existing subscription found, creating from webhook', [
                'invoice_id' => $invoiceId
            ]);
            $subscription = $this->createSubscriptionFromWebhook($payload);
        } else {
            Log::info('Found existing subscription', [
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoiceId,
                'current_status' => $subscription->status
            ]);
        }

        if (!$subscription) {
            Log::error('Failed to create or find subscription for paid invoice', [
                'invoice_id' => $invoiceId,
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        // Check if payment is already processed
        if ($subscription->payment_status === 'paid') {
            Log::info('Payment already processed for subscription', [
                'subscription_id' => $subscription->id,
                'xendit_invoice_id' => $invoiceId
            ]);
            return response()->json(['status' => 'already_processed']);
        }

        // Update subscription status
        try {
            // Calculate fees for the payment method
            $paymentMethod = $payload['payment_method'] ?? 'CREDIT_CARD';
            $amount = $payload['amount'] ?? $payload['paid_amount'] ?? $subscription->amount ?? 0;
            $fees = XenditFeeCalculator::calculateFees($amount, $paymentMethod);
            
            $subscription->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                'starts_at' => now(),
                'expires_at' => $subscription->getPlan()?->slug === 'intermediate' ? null : now()->addMonth(),
                'start_date' => now(),
                'end_date' => $subscription->getPlan()?->slug === 'intermediate' ? null : now()->addMonth(),
                'paid_at' => now(),
                'payment_method' => $paymentMethod,
                'transaction_fee' => $fees['transaction_fee'],
                'vat_amount' => $fees['vat_amount'],
                'net_amount' => $fees['net_amount'],
                'total_fees' => $fees['total_fees'],
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'payment_completed_at' => now(),
                    'payment_method' => $payload['payment_method'] ?? 'unknown',
                    'ewallet_type' => $payload['ewallet_type'] ?? null,
                    'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                    'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                    'fee_breakdown' => $fees,
                ]),
            ]);

            Log::info('Subscription payment status updated successfully', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'payment_status' => 'paid',
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                'xendit_invoice_id' => $subscription->xendit_invoice_id,
                'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                'paid_at' => $payload['paid_at'] ?? null,
                'payment_method' => $payload['payment_method'] ?? 'unknown',
                'ewallet_type' => $payload['ewallet_type'] ?? 'unknown',
                'refund_ready' => 'Yes - xendit_payment_id stored for refunds'
            ]);

            // Send payment confirmation email
            if ($subscription->user) {
                $this->sendPaymentConfirmationEmail($subscription);
                // Xendit handles invoice email notifications automatically
            }

            // Trigger admin sales report update with full metrics (this already broadcasts AdminDashboardUpdated with proper metrics)
            try {
                Log::info('[XenditWebhook] Triggering admin sales update for subscription payment');
                $reportsController = new \App\Http\Controllers\Admin\ReportsController();
                $reportsController->triggerSalesUpdate(null);
                Log::info('[XenditWebhook] ✅ Admin sales update triggered successfully for subscription');
            } catch (\Exception $e) {
                Log::error('Failed to trigger admin sales update after subscription payment: ' . $e->getMessage());
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Failed to update subscription payment status', [
                'subscription_id' => $subscription->id,
                'xendit_invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update subscription'], 500);
        }
    }

    /**
     * Handle ticket payment
     */
    private function handleTicketPayment($payload, $invoiceId)
    {
        Log::info('Processing ticket payment', [
            'invoice_id' => $invoiceId,
            'external_id' => $payload['external_id'] ?? 'unknown'
        ]);

        // Find ticket purchase by external_id or xendit_invoice_id
        $ticketPurchase = TicketPurchase::where('xendit_invoice_id', $invoiceId)
            ->orWhere('purchase_id', $payload['external_id'] ?? '')
            ->first();

        if (!$ticketPurchase) {
            Log::error('Ticket purchase not found for paid invoice', [
                'invoice_id' => $invoiceId,
                'external_id' => $payload['external_id'] ?? 'unknown',
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Ticket purchase not found'], 404);
        }

        // Check if payment is already processed
        if ($ticketPurchase->status === 'paid') {
            Log::info('Payment already processed for ticket purchase', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'xendit_invoice_id' => $invoiceId
            ]);
            return response()->json(['status' => 'already_processed']);
        }

        // Update ticket purchase status
        try {
            // Calculate fees for the payment method
            $paymentMethod = $payload['payment_method'] ?? 'CREDIT_CARD';
            $amount = $payload['amount'] ?? $payload['paid_amount'] ?? $ticketPurchase->total_amount ?? 0;
            $fees = XenditFeeCalculator::calculateFees($amount, $paymentMethod);
            
            $ticketPurchase->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $paymentMethod,
                'transaction_fee' => $fees['transaction_fee'],
                'vat_amount' => $fees['vat_amount'],
                'net_amount' => $fees['net_amount'],
                'total_fees' => $fees['total_fees'],
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                'payment_details' => array_merge($ticketPurchase->payment_details ?? [], [
                    'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                    'payment_method' => $payload['payment_method'] ?? 'unknown',
                    'ewallet_type' => $payload['ewallet_type'] ?? null,
                    'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                    'webhook_processed_at' => now(),
                    'fee_breakdown' => $fees,
                ]),
            ]);

            Log::info('Ticket purchase payment status updated successfully', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'user_id' => $ticketPurchase->user_id,
                'event_id' => $ticketPurchase->event_id,
                'status' => 'paid',
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                'xendit_invoice_id' => $ticketPurchase->xendit_invoice_id,
                'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                'payment_method' => $payload['payment_method'] ?? 'unknown',
                'ewallet_type' => $payload['ewallet_type'] ?? 'unknown',
                'refund_ready' => 'Yes - xendit_payment_id stored for refunds'
            ]);

            // Update sold count for ticket types
            $this->updateTicketSoldCount($ticketPurchase);

            // Generate individual tickets with QR codes
            $this->generateIndividualTickets($ticketPurchase);

            // Xendit handles invoice email notifications automatically

            // Send confirmation email for ticket purchase
            try {
                if ($ticketPurchase->user && $ticketPurchase->user->email) {
                    // Create plan details for ticket purchase
                    $planDetails = [
                        'name' => 'Event Ticket Purchase',
                        'price' => '₱' . number_format($ticketPurchase->total_amount, 2),
                    ];
                    
                    Mail::to($ticketPurchase->user->email)
                        ->send(new PaymentConfirmationMail($ticketPurchase, $planDetails));
                    
                    Log::info('Ticket purchase confirmation email sent', [
                        'ticket_purchase_id' => $ticketPurchase->id,
                        'user_email' => $ticketPurchase->user->email,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send ticket purchase confirmation email', [
                    'ticket_purchase_id' => $ticketPurchase->id,
                    'user_email' => $ticketPurchase->user->email ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }

            // Trigger admin sales report update with full metrics
            try {
                $reportsController = new \App\Http\Controllers\Admin\ReportsController();
                $reportsController->triggerSalesUpdate(null);
            } catch (\Exception $e) {
                Log::error('Failed to trigger admin sales update after ticket payment: ' . $e->getMessage());
            }

            // Trigger real-time updates for organizers
            $this->triggerTicketPurchaseUpdates($ticketPurchase);

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Failed to update ticket purchase payment status', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'xendit_invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update ticket purchase'], 500);
        }
    }

    /**
     * Handle additional ticket payment
     */
    private function handleAdditionalTicketPayment($payload, $invoiceId)
    {
        Log::info('Processing additional ticket payment', [
            'invoice_id' => $invoiceId,
            'external_id' => $payload['external_id'] ?? 'unknown'
        ]);

        // Find additional ticket purchase by external_id or xendit_invoice_id
        $additionalTicketPurchase = TicketLimitPurchase::where('xendit_invoice_id', $invoiceId)
            ->orWhere('purchase_id', $payload['external_id'] ?? '')
            ->first();

        if (!$additionalTicketPurchase) {
            Log::error('Additional ticket purchase not found for paid invoice', [
                'invoice_id' => $invoiceId,
                'external_id' => $payload['external_id'] ?? 'unknown',
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Additional ticket purchase not found'], 404);
        }

        // Check if payment is already processed
        if ($additionalTicketPurchase->status === 'paid') {
            Log::info('Payment already processed for additional ticket purchase', [
                'additional_ticket_purchase_id' => $additionalTicketPurchase->id,
                'xendit_invoice_id' => $invoiceId
            ]);
            return response()->json(['status' => 'already_processed']);
        }

        // Update additional ticket purchase status
        try {
            // Calculate fees for the payment method
            $paymentMethod = $payload['payment_method'] ?? 'CREDIT_CARD';
            $amount = $payload['amount'] ?? $payload['paid_amount'] ?? $additionalTicketPurchase->amount ?? 0;
            $fees = XenditFeeCalculator::calculateFees($amount, $paymentMethod);
            
            $additionalTicketPurchase->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $paymentMethod,
                'transaction_fee' => $fees['transaction_fee'],
                'vat_amount' => $fees['vat_amount'],
                'net_amount' => $fees['net_amount'],
                'total_fees' => $fees['total_fees'],
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                'payment_details' => array_merge($additionalTicketPurchase->payment_details ?? [], [
                    'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                    'payment_method' => $payload['payment_method'] ?? 'unknown',
                    'ewallet_type' => $payload['ewallet_type'] ?? null,
                    'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                    'webhook_processed_at' => now(),
                    'fee_breakdown' => $fees,
                ]),
            ]);

            Log::info('Additional ticket purchase payment status updated successfully', [
                'additional_ticket_purchase_id' => $additionalTicketPurchase->id,
                'user_id' => $additionalTicketPurchase->user_id,
                'status' => 'paid',
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                'xendit_invoice_id' => $additionalTicketPurchase->xendit_invoice_id,
                'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                'payment_method' => $payload['payment_method'] ?? 'unknown',
                'ewallet_type' => $payload['ewallet_type'] ?? 'unknown',
                'refund_ready' => 'Yes - xendit_payment_id stored for refunds'
            ]);

            // Send confirmation email
            try {
                if ($additionalTicketPurchase->user && $additionalTicketPurchase->user->email) {
                    Mail::to($additionalTicketPurchase->user->email)
                        ->send(new \App\Mail\AdditionalTicketPurchaseConfirmation($additionalTicketPurchase));
                    
                    Log::info('Additional ticket purchase confirmation email sent', [
                        'additional_ticket_purchase_id' => $additionalTicketPurchase->id,
                        'user_email' => $additionalTicketPurchase->user->email,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send additional ticket purchase confirmation email', [
                    'additional_ticket_purchase_id' => $additionalTicketPurchase->id,
                    'user_email' => $additionalTicketPurchase->user->email ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }

            // Trigger admin sales report update (this already broadcasts AdminDashboardUpdated with proper metrics)
            try {
                $reportsController = new \App\Http\Controllers\Admin\ReportsController();
                $reportsController->triggerSalesUpdate(null);
            } catch (\Exception $e) {
                Log::error('Failed to trigger admin sales update after additional ticket purchase: ' . $e->getMessage());
            }

            // Broadcast sales report updates for organizer dashboards if event exists
            try {
                $event = $additionalTicketPurchase->event;
                if ($event) {
                    $organizerId = $event->user_id;
                    if ($organizerId) {
                        // Prepare sales data
                        $salesData = [
                            'purchase_id' => $additionalTicketPurchase->id,
                            'type' => 'additional_ticket',
                            'quantity' => $additionalTicketPurchase->ticket_quantity ?? 0,
                            'amount' => $additionalTicketPurchase->amount,
                            'total_amount' => $additionalTicketPurchase->amount,
                            'status' => 'paid',
                            'paid_at' => $additionalTicketPurchase->paid_at ? $additionalTicketPurchase->paid_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                            'created_at' => $additionalTicketPurchase->created_at->format('Y-m-d H:i:s'),
                        ];

                        // Calculate event stats with expected_revenue (clamped)
                        $netAmount = (float) $event->ticketPurchases()->where('status','paid')->sum(\DB::raw('COALESCE(net_amount,total_amount)'));
                        $feePct = config('app.qricket_fee_percentage', 5);
                        $fee = max(0, $netAmount) * ($feePct / 100);
                        $expected = ($event->is_free || $netAmount <= 0) ? 0 : max(0, $netAmount - $fee);
                        $eventStats = [
                            'total_tickets_sold' => $event->ticketInformation()->count(),
                            'total_revenue' => $event->ticketPurchases()->where('status', 'paid')->sum('total_amount'),
                            'total_orders' => $event->ticketPurchases()->where('status', 'paid')->count(),
                            'expected_revenue' => $expected,
                        ];

                        // Broadcast to organizer dashboard
                        Log::info('[XenditWebhook] Broadcasting SalesReportUpdated for additional ticket', [
                            'event_id' => $event->id,
                            'organizer_id' => $organizerId,
                            'sales_data' => $salesData,
                            'event_stats' => $eventStats
                        ]);
                        try {
                            broadcast(new \App\Events\SalesReportUpdated($event->id, $organizerId, $salesData, $eventStats));
                            Log::info('[XenditWebhook] ✅ SalesReportUpdated broadcast sent successfully for additional ticket');
                        } catch (\Exception $e) {
                            Log::error('[XenditWebhook] ❌ Failed to broadcast SalesReportUpdated for additional ticket', [
                                'error' => $e->getMessage()
                            ]);
                        }
                        
                        // Also broadcast DashboardStatsUpdated for organizer dashboard
                        try {
                            $user = \App\Models\User::find($organizerId);
                            if ($user) {
                                $dashboardStats = $this->calculateOrganizerDashboardStats($user);
                                Log::info('[XenditWebhook] Broadcasting DashboardStatsUpdated for additional ticket', [
                                    'organizer_id' => $organizerId,
                                    'stats' => $dashboardStats
                                ]);
                                broadcast(new \App\Events\DashboardStatsUpdated($organizerId, $dashboardStats));
                                Log::info('[XenditWebhook] ✅ DashboardStatsUpdated broadcast sent successfully for additional ticket');
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to broadcast organizer dashboard stats: ' . $e->getMessage());
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Failed to broadcast additional ticket purchase updates: ' . $e->getMessage());
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Failed to update additional ticket purchase payment status', [
                'additional_ticket_purchase_id' => $additionalTicketPurchase->id,
                'xendit_invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update additional ticket purchase'], 500);
        }
    }

    /**
     * Trigger real-time updates for ticket purchase
     */
    private function triggerTicketPurchaseUpdates($ticketPurchase)
    {
        try {
            Log::info('[XenditWebhook] triggerTicketPurchaseUpdates called', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'event_id' => $ticketPurchase->event_id,
                'status' => $ticketPurchase->status
            ]);
            
            $event = $ticketPurchase->event;
            if (!$event) {
                Log::warning('Event not found for ticket purchase updates', [
                    'ticket_purchase_id' => $ticketPurchase->id,
                    'event_id' => $ticketPurchase->event_id
                ]);
                return;
            }
            
            Log::info('[XenditWebhook] Event found for updates', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'event_user_id' => $event->user_id
            ]);

            // Get organizer IDs (event creator and staff)
            $organizerIds = collect([$event->user_id])->filter();
            $staffIds = $event->staff()->pluck('user_id');
            $organizerIds = $organizerIds->merge($staffIds)->unique()->values();
            
            Log::info('[XenditWebhook] Organizer IDs collected', [
                'event_creator_id' => $event->user_id,
                'staff_ids' => $staffIds->toArray(),
                'all_organizer_ids' => $organizerIds->toArray(),
                'total_organizers' => $organizerIds->count()
            ]);

            // Prepare sales data
            $salesData = [
                'purchase_id' => $ticketPurchase->id,
                'purchase_external_id' => $ticketPurchase->purchase_id,
                'buyer_name' => trim(($ticketPurchase->user->first_name ?? '') . ' ' . ($ticketPurchase->user->last_name ?? '')) ?: 'Unknown',
                'buyer_email' => $ticketPurchase->user->email ?? '',
                // Ticket type relation may not exist; derive label from unit_price
                'ticket_type' => isset($ticketPurchase->unit_price)
                    ? ('Ticket (₱' . number_format($ticketPurchase->unit_price, 2) . ')')
                    : 'Ticket',
                'quantity' => $ticketPurchase->quantity,
                'unit_price' => $ticketPurchase->unit_price,
                'total_amount' => $ticketPurchase->total_amount,
                'status' => $ticketPurchase->status,
                'paid_at' => $ticketPurchase->paid_at ? $ticketPurchase->paid_at->format('Y-m-d H:i:s') : null,
                'created_at' => $ticketPurchase->created_at->format('Y-m-d H:i:s'),
            ];

            // Calculate event stats
            $netAmount = (float) $event->ticketPurchases()->where('status','paid')->sum(\DB::raw('COALESCE(net_amount,total_amount)'));
            $feePct = config('app.qricket_fee_percentage', 5);
            $fee = max(0, $netAmount) * ($feePct / 100);
            $expected = ($event->is_free || $netAmount <= 0) ? 0 : max(0, $netAmount - $fee);
            $eventStats = [
                'total_tickets_sold' => $event->ticketInformation()->count(),
                'total_revenue' => $event->ticketPurchases()->where('status', 'paid')->sum('total_amount'),
                'total_orders' => $event->ticketPurchases()->where('status', 'paid')->count(),
                'expected_revenue' => $expected,
            ];

            // Broadcast sales report updates and dashboard stats for each organizer
            foreach ($organizerIds as $organizerId) {
                Log::info('[XenditWebhook] Broadcasting SalesReportUpdated', [
                    'event_id' => $event->id,
                    'organizer_id' => $organizerId,
                    'sales_data' => $salesData,
                    'event_stats' => $eventStats
                ]);
                
                // Broadcast sales report update
                try {
                broadcast(new \App\Events\SalesReportUpdated($event->id, $organizerId, $salesData, $eventStats));
                    Log::info('[XenditWebhook] ✅ SalesReportUpdated broadcast sent successfully', [
                        'event_id' => $event->id,
                        'organizer_id' => $organizerId
                    ]);
                } catch (\Exception $e) {
                    Log::error('[XenditWebhook] ❌ Failed to broadcast SalesReportUpdated', [
                        'error' => $e->getMessage(),
                        'event_id' => $event->id,
                        'organizer_id' => $organizerId
                    ]);
                }
                
                // Broadcast dashboard stats update
                try {
                    $user = \App\Models\User::find($organizerId);
                    if ($user) {
                        $dashboardStats = $this->calculateOrganizerDashboardStats($user);
                        Log::info('[XenditWebhook] Broadcasting DashboardStatsUpdated', [
                            'organizer_id' => $organizerId,
                            'stats' => $dashboardStats
                        ]);
                        broadcast(new \App\Events\DashboardStatsUpdated($organizerId, $dashboardStats));
                        Log::info('[XenditWebhook] ✅ DashboardStatsUpdated broadcast sent successfully', [
                            'organizer_id' => $organizerId
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to broadcast organizer dashboard stats: ' . $e->getMessage());
                }
            }

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

            Log::info('[XenditWebhook] ✅ Real-time updates triggered for ticket purchase', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'event_id' => $event->id,
                'organizer_count' => $organizerIds->count(),
                'organizer_ids' => $organizerIds->toArray(),
                'channels_broadcasted' => [
                    'SalesReportUpdated' => $organizerIds->count() . ' times',
                    'DashboardStatsUpdated' => $organizerIds->count() . ' times',
                    'AdminDashboardUpdated' => '1 time (via triggerSalesUpdate)'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to trigger real-time updates for ticket purchase', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle invoice.expired event
     */
    private function handleInvoiceExpired($payload)
    {
        $subscription = Subscription::where('xendit_invoice_id', $payload['id'])->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'expired',
                'payment_status' => 'expired',
            ]);

            Log::info('Subscription marked as expired', [
                'subscription_id' => $subscription->id,
                'xendit_invoice_id' => $payload['id']
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle invoice.cancelled event
     */
    private function handleInvoiceCancelled($payload)
    {
        $subscription = Subscription::where('xendit_invoice_id', $payload['id'])->first();
        
        if ($subscription) {
            $subscription->update([
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
            ]);

            Log::info('Subscription marked as cancelled', [
                'subscription_id' => $subscription->id,
                'xendit_invoice_id' => $payload['id']
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payment.completed event
     */
    private function handlePaymentCompleted($payload)
    {
        Log::info('Payment completed event received', [
            'payment_id' => $payload['id'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? null
        ]);

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payment.after_expiry event
     */
    private function handlePaymentAfterExpiry($payload)
    {
        Log::info('Processing payment received after expiry', [
            'payment_id' => $payload['id'] ?? 'unknown',
            'external_id' => $payload['external_id'] ?? 'unknown',
            'status' => $payload['status'] ?? 'unknown',
            'amount' => $payload['amount'] ?? 'unknown',
            'paid_amount' => $payload['paid_amount'] ?? 'unknown'
        ]);

        // Extract invoice ID from different possible locations
        $invoiceId = $payload['id'] ?? $payload['external_id'] ?? null;
        
        if (!$invoiceId) {
            Log::error('No invoice ID found in payment after expiry payload', ['payload' => $payload]);
            return response()->json(['error' => 'No invoice ID found'], 400);
        }

        // Find existing subscription
        $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
        
        if (!$subscription) {
            // Create new subscription if none exists
            $subscription = $this->createSubscriptionFromWebhook($payload);
        }

        if (!$subscription) {
            Log::error('Failed to create or find subscription for payment after expiry', [
                'invoice_id' => $invoiceId,
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        // Update subscription status to active (payment was received)
        try {
            $subscription->update([
                'status' => 'active',
                'payment_status' => 'paid',
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
            ]);

            Log::info('Subscription activated after late payment', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'payment_status' => 'paid',
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                'paid_at' => $payload['paid_at'] ?? null
            ]);

            // Send payment confirmation email
            if ($subscription->user) {
                $this->sendPaymentConfirmationEmail($subscription);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Failed to update subscription for late payment', [
                'subscription_id' => $subscription->id,
                'xendit_invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update subscription'], 500);
        }
    }

    /**
     * Handle payout.succeeded event (renamed from disbursement.completed)
     */
    private function handlePayoutSucceeded($payload)
    {
        Log::info('Payout succeeded event received', [
            'payout_id' => $payload['id'] ?? null,
            'external_id' => $payload['external_id'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? null
        ]);

        try {
            $payoutId = $payload['id'] ?? null;
            $external = $payload['external_id'] ?? $payload['reference_id'] ?? null;

            $payout = null;
            if ($payoutId) {
                $payout = \App\Models\EventPayoutRequest::where('notes', 'like', "%Payout ID: {$payoutId}%")->first();
            }
            if (!$payout && $external && str_starts_with($external, 'payout_')) {
                $payoutId = (int) str_replace('payout_', '', explode('_', $external)[1] ?? '');
                $payout = \App\Models\EventPayoutRequest::find($payoutId);
            }

            if ($payout) {
                $payout->update([
                    'status' => 'paid',
                    'processed_at' => now(),
                    'notes' => $payout->notes . "\n\nPayout succeeded via Xendit webhook"
                ]);
                
                Log::info('Payout marked as paid via webhook', [
                    'payout_id' => $payout->id,
                    'payout_transaction_id' => $payoutId,
                    'amount' => $payout->net_amount
                ]);
                
                // Send email notification to organizer
                try {
                    $event = $payout->event;
                    $organizer = $event->user;
                    
                    if ($organizer && $organizer->email) {
                        Mail::to($organizer->email)
                            ->send(new PayoutNotificationMail($payout, $event, $organizer));
                        
                        Log::info('Payout notification email sent', [
                            'payout_id' => $payout->id,
                            'organizer_email' => $organizer->email,
                            'event_title' => $event->title
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send payout notification email', [
                        'payout_id' => $payout->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                try { event(new \App\Events\AdminMetricsPing('payout:paid')); } catch (\Throwable $e) {}
            } else {
                Log::warning('Payout not found for payout.succeeded', ['id' => $payoutId, 'external_id' => $external]);
            }
        } catch (\Throwable $e) {
            Log::error('Error handling payout.succeeded', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payout.failed event (renamed from disbursement.failed)
     */
    private function handlePayoutFailed($payload)
    {
        Log::info('Payout failed event received', [
            'payout_id' => $payload['id'] ?? null,
            'external_id' => $payload['external_id'] ?? $payload['reference_id'] ?? null,
            'failure_reason' => $payload['failure_reason'] ?? null,
            'failure_code' => $payload['failure_code'] ?? null,
        ]);

        try {
            $payoutId = $payload['id'] ?? null;
            $external = $payload['external_id'] ?? $payload['reference_id'] ?? null;
            $reason = $payload['failure_reason'] ?? $payload['failure_code'] ?? 'Unknown reason';

            $payout = null;
            if ($payoutId) {
                $payout = \App\Models\EventPayoutRequest::where('notes', 'like', "%Payout ID: {$payoutId}%")->first();
            }
            if (!$payout && $external && str_starts_with($external, 'payout_')) {
                $payoutId = (int) str_replace('payout_', '', explode('_', $external)[1] ?? '');
                $payout = \App\Models\EventPayoutRequest::find($payoutId);
            }

            if ($payout) {
                $payout->update([
                    'status' => 'rejected',
                    'notes' => $payout->notes . "\n\nPayout failed via Xendit webhook: {$reason}"
                ]);
                
                Log::info('Payout marked as rejected due to payout failure', [
                    'payout_id' => $payout->id,
                    'payout_transaction_id' => $payoutId,
                    'failure_reason' => $reason
                ]);
                
                // Send failure notification email to organizer
                try {
                    $event = $payout->event;
                    $organizer = $event->user;
                    
                    if ($organizer && $organizer->email) {
                        Mail::to($organizer->email)
                            ->send(new PayoutFailureMail($payout, $event, $organizer, $reason));
                        
                        Log::info('Payout failure notification email sent', [
                            'payout_id' => $payout->id,
                            'organizer_email' => $organizer->email,
                            'event_title' => $event->title,
                            'failure_reason' => $reason
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send payout failure notification email', [
                        'payout_id' => $payout->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                try { event(new \App\Events\AdminMetricsPing('payout:failed')); } catch (\Throwable $e) {}
            } else {
                Log::warning('Payout not found for payout.failed', ['id' => $payoutId, 'external_id' => $external]);
            }
        } catch (\Throwable $e) {
            Log::error('Error handling payout.failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle refund.completed event
     */
    private function handleRefundCompleted($payload)
    {
        Log::info('Refund completed event received', [
            'refund_id' => $payload['id'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? null,
            'external_id' => $payload['external_id'] ?? null,
        ]);

        try {
            // Try to find the user associated with this refund
            $user = null;
            $refundType = 'general';
            
            // Check if it's a subscription refund
            if (isset($payload['external_id']) && str_starts_with($payload['external_id'], 'subscription-')) {
                $subscription = Subscription::where('xendit_invoice_id', $payload['external_id'])->first();
                if ($subscription) {
                    $user = $subscription->user;
                    $refundType = 'subscription';
                }
            }
            
            // Check if it's a ticket refund
            if (!$user && isset($payload['external_id']) && str_starts_with($payload['external_id'], 'ticket-')) {
                $ticketPurchase = TicketPurchase::where('purchase_id', $payload['external_id'])->first();
                if ($ticketPurchase) {
                    $user = $ticketPurchase->user;
                    $refundType = 'ticket';
                }
            }
            
            // Check if it's an additional ticket refund
            if (!$user && isset($payload['external_id']) && str_starts_with($payload['external_id'], 'additional-ticket-')) {
                $additionalTicketPurchase = TicketLimitPurchase::where('purchase_id', $payload['external_id'])->first();
                if ($additionalTicketPurchase) {
                    $user = $additionalTicketPurchase->user;
                    $refundType = 'additional ticket';
                }
            }

            // Update RefundRequest status to 'completed'
            if ($user) {
                $itemId = null;
                
                if ($refundType === 'subscription') {
                    $subscription = Subscription::where('xendit_invoice_id', $payload['external_id'])->first();
                    $itemId = $subscription->id ?? null;
                } elseif ($refundType === 'ticket') {
                    $ticketPurchase = TicketPurchase::where('purchase_id', $payload['external_id'])->first();
                    $itemId = $ticketPurchase->id ?? null;
                } elseif ($refundType === 'additional ticket') {
                    $additionalTicketPurchase = TicketLimitPurchase::where('purchase_id', $payload['external_id'])->first();
                    $itemId = $additionalTicketPurchase->id ?? null;
                }

                if ($itemId) {
                    $refundRequest = RefundRequest::where('user_id', $user->id)
                        ->where('type', $refundType === 'additional ticket' ? 'additional_ticket' : $refundType)
                        ->where('item_id', $itemId)
                        ->where('status', 'approved')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($refundRequest) {
                        $refundRequest->update([
                            'status' => 'completed',
                            'xendit_refund_id' => $payload['id'] ?? null,
                            'refunded_at' => now()
                        ]);
                        
                        Log::info('RefundRequest updated to completed', [
                            'refund_request_id' => $refundRequest->id,
                            'refund_id' => $payload['id'] ?? null,
                            'xendit_refund_id' => $payload['id'] ?? null
                        ]);
                        
                        // Create in-app notification for the user
                        $this->createRefundNotification($user, $refundRequest, $payload);
                        
                        // Handle side effects: invalidate tickets, cancel subscriptions
                        $refundRequest->load(['ticketPurchase', 'subscription', 'additionalTicketPurchase']);
                        
                        if ($refundRequest->type === 'ticket' && $refundRequest->ticketPurchase) {
                            $ticketPurchase = $refundRequest->ticketPurchase;
                            // Update purchase status
                            $ticketPurchase->update(['status' => 'refunded']);
                            
                            // Invalidate ticket information
                            $ticketPurchase->ticketInformation()->update(['is_valid' => false]);
                            
                            // Restore sold_count for event tickets
                            if ($ticketPurchase->event) {
                                $eventTickets = $ticketPurchase->event->eventTickets()
                                    ->where('price', $ticketPurchase->unit_price)
                                    ->get();
                                
                                foreach ($eventTickets as $eventTicket) {
                                    $eventTicket->decrement('sold_count', $ticketPurchase->quantity);
                                }
                                
                                Log::info('Ticket counts restored after refund (webhook)', [
                                    'event_id' => $ticketPurchase->event_id,
                                    'quantity_refunded' => $ticketPurchase->quantity,
                                ]);
                            }
                        } elseif ($refundRequest->type === 'subscription' && $refundRequest->subscription) {
                            $subscription = $refundRequest->subscription;
                            // Cancel the subscription
                            $subscription->update([
                                'status' => 'refunded',
                                'cancelled_at' => now(),
                                'cancellation_reason' => 'Refunded via webhook',
                            ]);
                            
                            Log::info('Subscription cancelled after refund (webhook)', [
                                'subscription_id' => $subscription->id,
                                'user_id' => $subscription->user_id,
                            ]);
                        } elseif ($refundRequest->type === 'additional_ticket' && $refundRequest->additionalTicketPurchase) {
                            $additionalTicketPurchase = $refundRequest->additionalTicketPurchase;
                            // Update status
                            $additionalTicketPurchase->update(['status' => 'refunded']);
                            
                            Log::info('Additional ticket refunded (webhook)', [
                                'ticket_limit_purchase_id' => $additionalTicketPurchase->id,
                                'quantity' => $additionalTicketPurchase->quantity,
                            ]);
                        }
                    }
                }
            }

            // Send refund notification email if user found
            if ($user && $user->email) {
                Mail::to($user->email)
                    ->send(new \App\Mail\RefundNotificationMail($payload, $user, $refundType));
                
                Log::info('Refund notification email sent', [
                    'refund_id' => $payload['id'] ?? null,
                    'user_email' => $user->email,
                    'refund_type' => $refundType,
                    'amount' => $payload['amount'] ?? null,
                ]);
            } else {
                Log::warning('No user found for refund notification', [
                    'refund_id' => $payload['id'] ?? null,
                    'external_id' => $payload['external_id'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send refund notification email', [
                'refund_id' => $payload['id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle refund.failed event
     */
    private function handleRefundFailed($payload)
    {
        Log::info('Refund failed event received', [
            'refund_id' => $payload['id'] ?? null,
            'failure_reason' => $payload['failure_reason'] ?? null,
            'external_id' => $payload['external_id'] ?? null,
        ]);

        try {
            // Try to find the user associated with this refund
            $user = null;
            $refundType = 'general';
            
            // Check if it's a subscription refund
            if (isset($payload['external_id']) && str_starts_with($payload['external_id'], 'subscription-')) {
                $subscription = Subscription::where('xendit_invoice_id', $payload['external_id'])->first();
                if ($subscription) {
                    $user = $subscription->user;
                    $refundType = 'subscription';
                }
            }
            
            // Check if it's a ticket refund
            if (!$user && isset($payload['external_id']) && str_starts_with($payload['external_id'], 'ticket-')) {
                $ticketPurchase = TicketPurchase::where('purchase_id', $payload['external_id'])->first();
                if ($ticketPurchase) {
                    $user = $ticketPurchase->user;
                    $refundType = 'ticket';
                }
            }
            
            // Check if it's an additional ticket refund
            if (!$user && isset($payload['external_id']) && str_starts_with($payload['external_id'], 'additional-ticket-')) {
                $additionalTicketPurchase = TicketLimitPurchase::where('purchase_id', $payload['external_id'])->first();
                if ($additionalTicketPurchase) {
                    $user = $additionalTicketPurchase->user;
                    $refundType = 'additional ticket';
                }
            }

            // Update RefundRequest status to 'failed'
            if ($user) {
                $itemId = null;
                
                if ($refundType === 'subscription') {
                    $subscription = Subscription::where('xendit_invoice_id', $payload['external_id'])->first();
                    $itemId = $subscription->id ?? null;
                } elseif ($refundType === 'ticket') {
                    $ticketPurchase = TicketPurchase::where('purchase_id', $payload['external_id'])->first();
                    $itemId = $ticketPurchase->id ?? null;
                } elseif ($refundType === 'additional ticket') {
                    $additionalTicketPurchase = TicketLimitPurchase::where('purchase_id', $payload['external_id'])->first();
                    $itemId = $additionalTicketPurchase->id ?? null;
                }

                if ($itemId) {
                    $refundRequest = RefundRequest::where('user_id', $user->id)
                        ->where('type', $refundType === 'additional ticket' ? 'additional_ticket' : $refundType)
                        ->where('item_id', $itemId)
                        ->where('status', 'approved')
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($refundRequest) {
                        $refundRequest->update([
                            'status' => 'failed',
                            'xendit_refund_id' => $payload['id'] ?? null,
                            'failure_reason' => $payload['failure_reason'] ?? 'Refund processing failed'
                        ]);
                        
                        Log::info('RefundRequest updated to failed', [
                            'refund_request_id' => $refundRequest->id,
                            'refund_id' => $payload['id'] ?? null,
                            'failure_reason' => $payload['failure_reason'] ?? null
                        ]);
                    }
                }
            }

            // Send refund failure notification email if user found
            if ($user && $user->email) {
                $failurePayload = array_merge($payload, [
                    'status' => 'FAILED',
                    'reason' => $payload['failure_reason'] ?? 'Unknown failure reason'
                ]);
                
                Mail::to($user->email)
                    ->send(new \App\Mail\RefundNotificationMail($failurePayload, $user, $refundType));
                
                Log::info('Refund failure notification email sent', [
                    'refund_id' => $payload['id'] ?? null,
                    'user_email' => $user->email,
                    'refund_type' => $refundType,
                    'failure_reason' => $payload['failure_reason'] ?? null,
                ]);
            } else {
                Log::warning('No user found for refund failure notification', [
                    'refund_id' => $payload['id'] ?? null,
                    'external_id' => $payload['external_id'] ?? null,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send refund failure notification email', [
                'refund_id' => $payload['id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Verify payment status with Xendit API
     */
    private function verifyPaymentStatusWithXendit($invoiceId)
    {
        try {
            Log::info('Verifying payment status with Xendit API', ['invoice_id' => $invoiceId]);

            $response = Http::withBasicAuth(config('services.xendit.secret_key'), '')
                ->timeout(30)
                ->get("https://api.xendit.co/v2/invoices/{$invoiceId}");

            if ($response->successful()) {
                $invoiceData = $response->json();
                $status = $invoiceData['status'] ?? 'unknown';
                
                Log::info('Payment status verified with Xendit', [
                    'invoice_id' => $invoiceId,
                    'status' => $status,
                    'amount' => $invoiceData['amount'] ?? null,
                    'currency' => $invoiceData['currency'] ?? null
                ]);
                
                return strtoupper($status);
            }

            Log::warning('Failed to verify payment status with Xendit', [
                'invoice_id' => $invoiceId,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'response_headers' => $response->headers()
            ]);

            // If we can't verify with API, try to infer from webhook data
            return 'unknown';

        } catch (\Exception $e) {
            Log::error('Exception while verifying payment status with Xendit', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 'unknown';
        }
    }

    /**
     * Create subscription from webhook payload
     */
    private function createSubscriptionFromWebhook($payload)
    {
        try {
            // Extract user information from different possible locations
            $userEmail = $payload['payer_email'] ?? $payload['customer']['email'] ?? null;
            
            // Extract customer name from various possible locations
            $firstName = $payload['customer']['given_names'] ?? $payload['customer']['first_name'] ?? '';
            $lastName = $payload['customer']['surname'] ?? $payload['customer']['last_name'] ?? '';
            
            // If no separate first/last name, try to split full name
            if (empty($firstName) && empty($lastName)) {
                $fullName = $payload['customer']['name'] ?? $payload['customer']['full_name'] ?? '';
                if (!empty($fullName)) {
                    $nameParts = explode(' ', trim($fullName), 2);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';
                }
            }

            // Ensure we have at least some name data
            if (empty($firstName) && empty($lastName)) {
                $firstName = 'Unknown';
                $lastName = 'User';
            }

            $fullName = trim($firstName . ' ' . $lastName);

            Log::info('Extracted user data from webhook', [
                'email' => $userEmail,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'full_name' => $fullName
            ]);

            // Find or create user
            $user = null;
            if ($userEmail) {
                $user = User::where('email', $userEmail)->first();
                
                if (!$user) {
                    // Create user if doesn't exist
                    $user = User::create([
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $userEmail,
                        'password' => bcrypt(Str::random(12)), // Temporary password
                    ]);
                    
                    Log::info('User created from webhook', [
                        'user_id' => $user->id,
                        'email' => $userEmail,
                        'name' => $fullName
                    ]);
                } else {
                    // Update existing user's name if it was incomplete
                    if (empty($user->first_name) || empty($user->last_name)) {
                        $user->update([
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                        ]);
                        
                        Log::info('Updated existing user name from webhook', [
                            'user_id' => $user->id,
                            'email' => $userEmail,
                            'name' => $fullName
                        ]);
                    }
                }
            } else {
                // Try to find user by external_id pattern or user_id
                $user = $this->findUserFromWebhookData($payload);
            }

            // If still no user found, create a placeholder user with generated email
            if (!$user) {
                $generatedEmail = 'user_' . time() . '_' . rand(1000, 9999) . '@webhook.local';
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $generatedEmail,
                    'password' => bcrypt(Str::random(12)),
                ]);
                
                Log::info('Created placeholder user for webhook', [
                    'user_id' => $user->id,
                    'email' => $generatedEmail,
                    'name' => $fullName
                ]);
            }

            // Determine plan from amount or items array
            $amount = $payload['amount'] ?? $payload['paid_amount'] ?? null;
            $planSlug = $this->inferPlanFromAmount($amount);
            
            // If we have items array, try to get plan from there
            if (isset($payload['items']) && is_array($payload['items']) && !empty($payload['items'])) {
                $item = $payload['items'][0]; // Get first item
                $itemName = strtolower($item['name'] ?? '');
                
                if (strpos($itemName, 'basic') !== false) {
                    $planSlug = 'basic';
                } elseif (strpos($itemName, 'pro') !== false) {
                    $planSlug = 'pro';
                } elseif (strpos($itemName, 'intermediate') !== false) {
                    $planSlug = 'intermediate';
                } elseif (strpos($itemName, 'free') !== false) {
                    $planSlug = 'free';
                }
                
                Log::info('Plan determined from items array', [
                    'item_name' => $item['name'] ?? 'unknown',
                    'inferred_plan' => $planSlug
                ]);
            }
            
            // Find the subscription plan by slug
            $subscriptionPlan = \App\Models\SubscriptionPlan::where('slug', $planSlug)->first();
            if (!$subscriptionPlan) {
                Log::error('Subscription plan not found', ['plan_slug' => $planSlug]);
                return null;
            }
            
            // Extract invoice ID from various locations
            $invoiceId = $payload['id'] ?? $payload['external_id'] ?? null;
            
            // Extract payment ID from various locations
            $paymentId = $payload['payment_id'] ?? $payload['id'] ?? null;

            // Extract currency
            $currency = $payload['currency'] ?? 'PHP';

            $subscription = Subscription::create([
                'user_id' => $user->id, // Always ensure we have a valid user_id
                'subscription_plan_id' => $subscriptionPlan->id,
                'plan_id' => $planSlug, // Legacy field
                'status' => 'active',
                'xendit_invoice_id' => $invoiceId,
                'xendit_payment_id' => $paymentId,
                'amount' => $amount,
                'currency' => $currency,
                'start_date' => now(),
                'end_date' => $subscriptionPlan->slug === 'intermediate' ? null : now()->addMonth(),
                'starts_at' => now(),
                'expires_at' => $subscriptionPlan->slug === 'intermediate' ? null : now()->addMonth(),
                'payment_status' => 'paid',
                'payment_method' => 'xendit',
                'payment_reference' => $paymentId,
            ]);

            Log::info('Subscription created from webhook', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'subscription_plan_id' => $subscription->subscription_plan_id,
                'plan_slug' => $subscription->plan_id,
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => $payload['payment_method'] ?? 'unknown',
                'ewallet_type' => $payload['ewallet_type'] ?? 'unknown'
            ]);

            return $subscription;

        } catch (\Exception $e) {
            Log::error('Failed to create subscription from webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload
            ]);
            return null;
        }
    }

    /**
     * Infer plan from amount
     */
    private function inferPlanFromAmount($amount)
    {
        $planSlug = match((float)$amount) {
            0.0, 0 => 'free',
            // New peso amounts
            299.0, 299 => 'basic',
            799.0, 799 => 'pro',
            // Legacy USD amounts (for backward compatibility)
            10.0, 10 => 'basic',
            25.0, 25 => 'pro',
            // Handle amounts in different currencies (e.g., IDR)
            15000.0, 15000 => 'basic', // IDR equivalent (~$10)
            37500.0, 37500 => 'pro', // IDR equivalent (~$25)
            default => 'unknown'
        };

        Log::info('Plan inferred from amount', [
            'amount' => $amount,
            'inferred_plan' => $planSlug
        ]);

        return $planSlug;
    }

    /**
     * Send payment confirmation email
     */
    private function sendPaymentConfirmationEmail($subscription)
    {
        try {
            $plan = $this->getPlanDetails($subscription->plan_id);
            
            Mail::to($subscription->user->email)
                ->send(new PaymentConfirmationMail($subscription, $plan));
            
            Log::info('Payment confirmation email sent successfully', [
                'user_id' => $subscription->user_id,
                'user_email' => $subscription->user->email,
                'subscription_id' => $subscription->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email', [
                'user_id' => $subscription->user_id,
                'user_email' => $subscription->user->email,
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get plan details
     */
    private function getPlanDetails($planSlug)
    {
        $plan = SubscriptionPlan::where('slug', $planSlug)->first();
        
        if ($plan) {
            return [
                'id' => $plan->slug,
                'name' => $plan->name,
                'price' => $plan->price,
            ];
        }
        
        // Fallback for unknown plans
        return [
            'id' => $planSlug,
            'name' => ucfirst($planSlug) . ' Plan',
            'price' => 0,
        ];
    }

    /**
     * Save webhook data to local storage for debugging
     */
    private function saveWebhookToStorage($payload, $token, $eventType)
    {
        try {
            // Create webhooks directory if it doesn't exist
            $webhooksDir = storage_path('app/webhooks');
            if (!file_exists($webhooksDir)) {
                mkdir($webhooksDir, 0755, true);
            }

            // Create a filename with timestamp and event type
            $timestamp = now()->format('Y-m-d_H-i-s');
            $status = $payload['status'] ?? 'unknown';
            $invoiceId = $payload['id'] ?? 'unknown';
            $filename = "webhook_{$timestamp}_{$eventType}_{$status}_{$invoiceId}.json";
            $filepath = $webhooksDir . '/' . $filename;

            // Prepare data to save
            $dataToSave = [
                'webhook_data' => $payload,
                'event_type' => $eventType,
                'headers' => [
                    'x-callback-token' => $token,
                    'user-agent' => request()->header('User-Agent'),
                    'content-type' => request()->header('Content-Type'),
                ],
                'received_at' => now()->toISOString(),
                'file_info' => [
                    'filename' => $filename,
                    'saved_at' => now()->toISOString(),
                ]
            ];

            // Save to JSON file
            file_put_contents($filepath, json_encode($dataToSave, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Log::info('Webhook data saved to local storage', [
                'filepath' => $filepath,
                'event_type' => $eventType,
                'status' => $status,
                'invoice_id' => $invoiceId
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save webhook data to storage', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
        }
    }

    /**
     * Find user from webhook data
     */
    private function findUserFromWebhookData($payload)
    {
        try {
            // Try to find user by external_id if it contains user information
            $externalId = $payload['external_id'] ?? '';
            if (strpos($externalId, 'subscription-') === 0) {
                // Extract user ID from external_id if it follows a pattern
                $parts = explode('-', $externalId);
                if (count($parts) >= 2) {
                    $potentialUserId = $parts[1];
                    Log::info('Attempting to find user by external_id pattern', [
                        'external_id' => $externalId,
                        'potential_user_id' => $potentialUserId
                    ]);
                }
            }

            // Try to find user by user_id if present
            $xenditUserId = $payload['user_id'] ?? null;
            if ($xenditUserId) {
                Log::info('Attempting to find user by Xendit user_id', [
                    'xendit_user_id' => $xenditUserId
                ]);
                
                // You might want to store Xendit user_id in your users table
                // For now, we'll just log it
            }

            // Try to find user by looking for existing subscriptions with this invoice
            $invoiceId = $payload['id'] ?? $payload['external_id'] ?? null;
            if ($invoiceId) {
                $existingSubscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
                if ($existingSubscription && $existingSubscription->user) {
                    Log::info('Found existing user from subscription', [
                        'user_id' => $existingSubscription->user->id,
                        'invoice_id' => $invoiceId
                    ]);
                    return $existingSubscription->user;
                }
            }

            // If no user found, create a placeholder user
            Log::info('No existing user found, will create placeholder user');
            return null;

        } catch (\Exception $e) {
            Log::error('Error finding user from webhook data', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return null;
        }
    }

    /**
     * Unified payment success handler - handles all successful payment types
     */
    private function handlePaymentSuccess($payload)
    {
        Log::info('Processing unified payment success', [
            'payload' => $payload
        ]);

        $invoiceId = $payload['id'] ?? $payload['external_id'] ?? null;
        if (!$invoiceId) {
            Log::warning('No invoice ID found in payment success payload', ['payload' => $payload]);
            return response()->json(['error' => 'No invoice ID'], 400);
        }

        // Verify payment status with Xendit API
        $verifiedStatus = $this->verifyPaymentStatusWithXendit($invoiceId);
        if ($verifiedStatus !== 'PAID' && $verifiedStatus !== 'SUCCEEDED' && $verifiedStatus !== 'COMPLETED') {
            Log::warning('Payment verification failed', [
                'invoice_id' => $invoiceId,
                'verified_status' => $verifiedStatus,
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Payment verification failed'], 400);
        }

        // Determine payment type and delegate to specific handler
        $paymentType = $this->determinePaymentType($payload);
        
        Log::info('Determined payment type for success', [
            'payment_type' => $paymentType,
            'external_id' => $payload['external_id'] ?? 'unknown'
        ]);

        switch ($paymentType) {
            case 'subscription':
                // Try from cache intent first
                $intent = (new PaymentIntentService())->get($invoiceId);
                if (!empty($intent) && ($intent['type'] ?? null) === 'subscription') {
                    $user = User::find($intent['user_id'] ?? 0);
                    $plan = SubscriptionPlan::find($intent['plan_id'] ?? 0);
                    if ($user && $plan) {
                        if ($user->currentSubscription()) {
                            $user->currentSubscription()->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                        }
                        
                        // Calculate fees for the payment method
                        $paymentMethod = $payload['payment_method'] ?? 'CREDIT_CARD';
                        $amount = $payload['amount'] ?? $payload['paid_amount'] ?? ($intent['final_price'] ?? $plan->price);
                        $fees = \App\Services\XenditFeeCalculator::calculateFees($amount, $paymentMethod);
                        
                        $subscription = Subscription::create([
                            'user_id' => $user->id,
                            'subscription_plan_id' => $plan->id,
                            'plan_id' => $plan->slug,
                            'status' => 'active',
                            'starts_at' => now(),
                            'expires_at' => $plan->slug === 'intermediate' ? null : now()->addMonth(),
                            'payment_method' => $paymentMethod,
                            'payment_status' => 'paid',
                            'amount' => $intent['final_price'] ?? $plan->price,
                            'currency' => $plan->currency,
                            'transaction_fee' => $fees['transaction_fee'],
                            'vat_amount' => $fees['vat_amount'],
                            'net_amount' => $fees['net_amount'],
                            'total_fees' => $fees['total_fees'],
                            'start_date' => now(),
                            'end_date' => $plan->slug === 'intermediate' ? null : now()->addMonth(),
                            'discount_code' => $intent['discount_code'] ?? null,
                            'xendit_invoice_id' => $invoiceId,
                            'xendit_payment_id' => $payload['payment_id'] ?? $payload['data']['payment_id'] ?? null,
                            'metadata' => [
                                'original_price' => $intent['original_price'] ?? $plan->price,
                                'created_from' => 'xendit_webhook_cache',
                                'fee_breakdown' => $fees,
                            ],
                        ]);
                        
                        // Record discount usage if discount was applied
                        if (!empty($intent['discount_code'])) {
                            try {
                                $discount = \App\Models\Discount::where('code', $intent['discount_code'])->first();
                                if ($discount) {
                                    $discount->incrementUsage();
                                    $discount->recordUsage(
                                        $user->id,
                                        'subscription',
                                        $subscription->id,
                                        Subscription::class,
                                        ($intent['original_price'] ?? $plan->price) - ($intent['final_price'] ?? $plan->price),
                                        $intent['original_price'] ?? $plan->price
                                    );
                                }
                            } catch (\Exception $e) {
                                Log::error('Failed to record discount usage in webhook cache intent: ' . $e->getMessage());
                            }
                        }
                        
                        (new PaymentIntentService())->forget($invoiceId);
                        Log::info('Subscription finalized from unified webhook via cache intent with fees calculated', ['subscription_id' => $subscription->id]);
                        return response()->json(['status' => 'success']);
                    }
                }
                return $this->handleSubscriptionPayment($payload, $invoiceId);
            case 'ticket':
                // Try finalize from cache intent first
                $intent = (new PaymentIntentService())->get($invoiceId);
                if (!empty($intent) && ($intent['type'] ?? null) === 'ticket') {
                    $purchase = TicketPurchase::where('purchase_id', $intent['purchase_id'] ?? '')->first();
                    if ($purchase) {
                        // Calculate fees for the payment method
                        $paymentMethod = $payload['payment_method'] ?? 'CREDIT_CARD';
                        $amount = $payload['amount'] ?? $payload['paid_amount'] ?? $purchase->total_amount ?? 0;
                        $fees = \App\Services\XenditFeeCalculator::calculateFees($amount, $paymentMethod);
                        
                        $purchase->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                            'payment_method' => $paymentMethod,
                            'transaction_fee' => $fees['transaction_fee'],
                            'vat_amount' => $fees['vat_amount'],
                            'net_amount' => $fees['net_amount'],
                            'total_fees' => $fees['total_fees'],
                            'xendit_invoice_id' => $invoiceId,
                            'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                            'payment_details' => array_merge($purchase->payment_details ?? [], [
                                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                                'payment_method' => $payload['payment_method'] ?? 'unknown',
                                'ewallet_type' => $payload['ewallet_type'] ?? null,
                                'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                                'webhook_processed_at' => now(),
                                'fee_breakdown' => $fees,
                            ]),
                        ]);
                        // Update sold count and generate tickets
                        try { $ticketType = $purchase->ticket_type; if ($ticketType) { $ticketType->increment('sold_count', $purchase->quantity); } } catch (\Throwable $t) { Log::warning('Update sold count failed', ['e'=>$t->getMessage()]); }
                        try { $this->generateIndividualTickets($purchase); } catch (\Throwable $t) { Log::warning('Generate tickets failed', ['e'=>$t->getMessage()]); }
                        (new PaymentIntentService())->forget($invoiceId);
                        Log::info('Ticket purchase finalized via cache intent');
                        
                        // Trigger admin sales report update
                        try {
                            Log::info('[XenditWebhook] Triggering admin sales update for ticket purchase (cache intent)');
                            $reportsController = new \App\Http\Controllers\Admin\ReportsController();
                            $reportsController->triggerSalesUpdate(null);
                            Log::info('[XenditWebhook] ✅ Admin sales update triggered successfully (cache intent)');
                        } catch (\Exception $e) {
                            Log::error('Failed to trigger admin sales update after ticket payment (cache intent): ' . $e->getMessage());
                        }

                        // Trigger real-time updates for organizers
                        Log::info('[XenditWebhook] Triggering ticket purchase updates (cache intent)', [
                            'ticket_purchase_id' => $purchase->id,
                            'event_id' => $purchase->event_id
                        ]);
                        $this->triggerTicketPurchaseUpdates($purchase);
                        
                        return response()->json(['status' => 'success']);
                    }
                }
                return $this->handleTicketPayment($payload, $invoiceId);
            case 'additional_ticket':
                // Try finalize from cache intent first
                $intent = (new PaymentIntentService())->get($invoiceId);
                if (!empty($intent) && ($intent['type'] ?? null) === 'additional_ticket') {
                    $purchase = TicketLimitPurchase::where('purchase_id', $intent['purchase_id'] ?? '')->first();
                    if ($purchase) {
                        // Calculate fees for the payment method
                        $paymentMethod = $payload['payment_method'] ?? 'CREDIT_CARD';
                        $amount = $payload['amount'] ?? $payload['paid_amount'] ?? $purchase->amount ?? 0;
                        $fees = \App\Services\XenditFeeCalculator::calculateFees($amount, $paymentMethod);
                        
                        $purchase->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                            'xendit_invoice_id' => $invoiceId,
                            'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                            'payment_method' => $paymentMethod,
                            'transaction_fee' => $fees['transaction_fee'],
                            'vat_amount' => $fees['vat_amount'],
                            'net_amount' => $fees['net_amount'],
                            'total_fees' => $fees['total_fees'],
                            'payment_details' => array_merge($purchase->payment_details ?? [], [
                                'xendit_payment_id' => $payload['payment_id'] ?? $payload['id'] ?? null,
                                'payment_method' => $payload['payment_method'] ?? 'unknown',
                                'ewallet_type' => $payload['ewallet_type'] ?? null,
                                'paid_amount' => $payload['paid_amount'] ?? $payload['amount'] ?? null,
                                'webhook_processed_at' => now(),
                                'fee_breakdown' => $fees,
                            ]),
                        ]);
                        (new PaymentIntentService())->forget($invoiceId);
                        Log::info('Additional ticket purchase finalized via cache intent with fees calculated');
                        
                        // Trigger admin sales report update
                        try {
                            Log::info('[XenditWebhook] Triggering admin sales update for additional ticket (cache intent)');
                            $reportsController = new \App\Http\Controllers\Admin\ReportsController();
                            $reportsController->triggerSalesUpdate(null);
                            Log::info('[XenditWebhook] ✅ Admin sales update triggered successfully for additional ticket (cache intent)');
                        } catch (\Exception $e) {
                            Log::error('Failed to trigger admin sales update after additional ticket purchase (cache intent): ' . $e->getMessage());
                        }

                        // Broadcast sales report updates for organizer dashboards if event exists
                        try {
                            $event = $purchase->event;
                            if ($event) {
                                $organizerId = $event->user_id;
                                if ($organizerId) {
                                    // Prepare sales data
                                    $salesData = [
                                        'purchase_id' => $purchase->id,
                                        'type' => 'additional_ticket',
                                        'quantity' => $purchase->ticket_quantity ?? 0,
                                        'amount' => $purchase->amount,
                                        'total_amount' => $purchase->amount,
                                        'status' => 'paid',
                                        'paid_at' => $purchase->paid_at ? $purchase->paid_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                                        'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
                                    ];

                                    // Calculate event stats
                                    $eventStats = [
                                        'total_tickets_sold' => $event->ticketInformation()->count(),
                                        'total_revenue' => $event->ticketPurchases()->where('status', 'paid')->sum('total_amount'),
                                        'total_orders' => $event->ticketPurchases()->where('status', 'paid')->count(),
                                    ];

                                    // Broadcast to organizer dashboard
                                    Log::info('[XenditWebhook] Broadcasting SalesReportUpdated for additional ticket (cache intent)', [
                                        'event_id' => $event->id,
                                        'organizer_id' => $organizerId,
                                        'sales_data' => $salesData,
                                        'event_stats' => $eventStats
                                    ]);
                                    try {
                                        broadcast(new \App\Events\SalesReportUpdated($event->id, $organizerId, $salesData, $eventStats));
                                        Log::info('[XenditWebhook] ✅ SalesReportUpdated broadcast sent successfully for additional ticket (cache intent)');
                                    } catch (\Exception $e) {
                                        Log::error('[XenditWebhook] ❌ Failed to broadcast SalesReportUpdated for additional ticket (cache intent)', [
                                            'error' => $e->getMessage()
                                        ]);
                                    }
                                    
                                    // Also broadcast DashboardStatsUpdated for organizer dashboard
                                    try {
                                        $user = \App\Models\User::find($organizerId);
                                        if ($user) {
                                            $dashboardStats = $this->calculateOrganizerDashboardStats($user);
                                            Log::info('[XenditWebhook] Broadcasting DashboardStatsUpdated for additional ticket (cache intent)', [
                                                'organizer_id' => $organizerId,
                                                'stats' => $dashboardStats
                                            ]);
                                            broadcast(new \App\Events\DashboardStatsUpdated($organizerId, $dashboardStats));
                                            Log::info('[XenditWebhook] ✅ DashboardStatsUpdated broadcast sent successfully for additional ticket (cache intent)');
                                        }
                                    } catch (\Exception $e) {
                                        Log::error('Failed to broadcast organizer dashboard stats (cache intent): ' . $e->getMessage());
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('Failed to broadcast additional ticket purchase updates (cache intent): ' . $e->getMessage());
                        }
                        
                        return response()->json(['status' => 'success']);
                    }
                }
                return $this->handleAdditionalTicketPayment($payload, $invoiceId);
            default:
                Log::warning('Unknown payment type for success, defaulting to subscription handling', [
                    'payment_type' => $paymentType,
                    'payload' => $payload
                ]);
                return $this->handleSubscriptionPayment($payload, $invoiceId);
        }
    }

    /**
     * Unified payment pending handler
     */
    private function handlePaymentPending($payload)
    {
        Log::info('Processing unified payment pending', [
            'payload' => $payload
        ]);

        $invoiceId = $payload['id'] ?? $payload['external_id'] ?? null;
        if (!$invoiceId) {
            Log::warning('No invoice ID found in payment pending payload', ['payload' => $payload]);
            return response()->json(['error' => 'No invoice ID'], 400);
        }

        // Log pending payment for monitoring
        Log::info('Payment is pending', [
            'invoice_id' => $invoiceId,
            'external_id' => $payload['external_id'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? null
        ]);

        return response()->json(['status' => 'pending_logged']);
    }

    /**
     * Unified payment failed handler
     */
    private function handlePaymentFailed($payload)
    {
        Log::info('Processing unified payment failure', [
            'payload' => $payload
        ]);

        $invoiceId = $payload['id'] ?? $payload['external_id'] ?? null;
        if (!$invoiceId) {
            Log::warning('No invoice ID found in payment failure payload', ['payload' => $payload]);
            return response()->json(['error' => 'No invoice ID'], 400);
        }

        // Log failed payment for monitoring
        Log::warning('Payment failed', [
            'invoice_id' => $invoiceId,
            'external_id' => $payload['external_id'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? null,
            'failure_reason' => $payload['failure_reason'] ?? 'Unknown'
        ]);

        // Xendit handles invoice email notifications automatically

        return response()->json(['status' => 'failure_logged']);
    }

    /**
     * Unified payment captured handler
     */
    private function handlePaymentCaptured($payload)
    {
        Log::info('Processing unified payment captured', [
            'payload' => $payload
        ]);

        $invoiceId = $payload['id'] ?? $payload['external_id'] ?? null;
        if (!$invoiceId) {
            Log::warning('No invoice ID found in payment captured payload', ['payload' => $payload]);
            return response()->json(['error' => 'No invoice ID'], 400);
        }

        // Log captured payment for monitoring
        Log::info('Payment captured', [
            'invoice_id' => $invoiceId,
            'external_id' => $payload['external_id'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? null
        ]);

        return response()->json(['status' => 'captured_logged']);
    }

    /**
     * Handle Money-In transactions (Payments) with proper Xendit format
     */
    private function handleMoneyInTransaction($payload, $transactionStatus, $settlementStatus)
    {
        Log::info('Processing Money-In transaction', [
            'transaction_status' => $transactionStatus,
            'settlement_status' => $settlementStatus,
            'payload' => $payload
        ]);

        switch ($transactionStatus) {
            case 'SUCCESS':
                // Payment success - funds captured
                if ($settlementStatus === 'PENDING') {
                    Log::info('Payment success - funds captured, settlement pending', [
                        'transaction_id' => $payload['id'] ?? null,
                        'amount' => $payload['amount'] ?? null
                    ]);
                    return $this->handlePaymentSuccess($payload);
                } elseif (in_array($settlementStatus, ['SETTLED', 'EARLY_SETTLED'])) {
                    Log::info('Payment success - settlement complete', [
                        'transaction_id' => $payload['id'] ?? null,
                        'settlement_status' => $settlementStatus,
                        'amount' => $payload['amount'] ?? null
                    ]);
                    return $this->handlePaymentSuccess($payload);
                }
                break;

            case 'PENDING':
                // Payment pending
                Log::info('Payment pending', [
                    'transaction_id' => $payload['id'] ?? null,
                    'amount' => $payload['amount'] ?? null
                ]);
                return $this->handlePaymentPending($payload);

            case 'VOIDED':
                // Payment cancelled before settlement
                Log::info('Payment voided before settlement', [
                    'transaction_id' => $payload['id'] ?? null,
                    'amount' => $payload['amount'] ?? null
                ]);
                return $this->handlePaymentFailed($payload);

            case 'REVERSED':
                // Payment reversed after settlement
                Log::info('Payment reversed after settlement', [
                    'transaction_id' => $payload['id'] ?? null,
                    'settlement_status' => $settlementStatus,
                    'amount' => $payload['amount'] ?? null
                ]);
                return $this->handlePaymentFailed($payload);
        }

        return response()->json(['status' => 'money_in_processed']);
    }

    /**
     * Handle Money-Out transactions (Payouts & Refunds) with proper Xendit format
     */
    private function handleMoneyOutTransaction($payload, $transactionStatus, $settlementStatus)
    {
        Log::info('Processing Money-Out transaction', [
            'transaction_status' => $transactionStatus,
            'settlement_status' => $settlementStatus,
            'payload' => $payload
        ]);

        // Determine if this is a payout or refund based on payload
        $isPayout = $this->isPayoutTransaction($payload);
        $isRefund = $this->isRefundTransaction($payload);

        switch ($transactionStatus) {
            case 'PENDING':
                // Payout/Refund created, processing
                Log::info('Payout/Refund pending', [
                    'transaction_id' => $payload['id'] ?? null,
                    'is_payout' => $isPayout,
                    'is_refund' => $isRefund,
                    'amount' => $payload['amount'] ?? null
                ]);
                return response()->json(['status' => 'money_out_pending']);

            case 'SUCCESS':
                // Payout/Refund success
                if ($settlementStatus === 'SETTLED') {
                    Log::info('Payout/Refund success - settlement complete', [
                        'transaction_id' => $payload['id'] ?? null,
                        'is_payout' => $isPayout,
                        'is_refund' => $isRefund,
                        'amount' => $payload['amount'] ?? null
                    ]);
                    
                    if ($isPayout) {
                        return $this->handlePayoutSucceeded($payload);
                    } elseif ($isRefund) {
                        return $this->handleRefundCompleted($payload);
                    }
                }
                break;

            case 'FAILED':
                // Payout/Refund failed
                Log::info('Payout/Refund failed', [
                    'transaction_id' => $payload['id'] ?? null,
                    'is_payout' => $isPayout,
                    'is_refund' => $isRefund,
                    'failure_reason' => $payload['failure_reason'] ?? 'Unknown',
                    'amount' => $payload['amount'] ?? null
                ]);
                
                if ($isPayout) {
                    return $this->handlePayoutFailed($payload);
                } elseif ($isRefund) {
                    return $this->handleRefundFailed($payload);
                }
                break;

            case 'VOIDED':
                // Payout/Refund cancelled before settlement
                Log::info('Payout/Refund voided before settlement', [
                    'transaction_id' => $payload['id'] ?? null,
                    'is_payout' => $isPayout,
                    'is_refund' => $isRefund,
                    'amount' => $payload['amount'] ?? null
                ]);
                return response()->json(['status' => 'money_out_voided']);

            case 'REVERSED':
                // Payout/Refund reversed after settlement
                Log::info('Payout/Refund reversed after settlement', [
                    'transaction_id' => $payload['id'] ?? null,
                    'is_payout' => $isPayout,
                    'is_refund' => $isRefund,
                    'settlement_status' => $settlementStatus,
                    'amount' => $payload['amount'] ?? null
                ]);
                return response()->json(['status' => 'money_out_reversed']);
        }

        return response()->json(['status' => 'money_out_processed']);
    }

    /**
     * Determine if this is a payout transaction
     */
    private function isPayoutTransaction($payload)
    {
        $externalId = $payload['external_id'] ?? $payload['reference_id'] ?? '';
        $description = $payload['description'] ?? '';
        
        return str_contains($externalId, 'payout_') || 
               str_contains($description, 'payout') ||
               str_contains($description, 'disbursement');
    }

    /**
     * Determine if this is a refund transaction
     */
    private function isRefundTransaction($payload)
    {
        $externalId = $payload['external_id'] ?? $payload['reference_id'] ?? '';
        $description = $payload['description'] ?? '';
        
        return str_contains($externalId, 'refund_') || 
               str_contains($description, 'refund');
    }

    /**
     * Handle payout.reversed event
     */
    private function handlePayoutReversed($payload)
    {
        Log::info('Payout reversed event received', [
            'payout_id' => $payload['id'] ?? null,
            'external_id' => $payload['external_id'] ?? $payload['reference_id'] ?? null,
            'amount' => $payload['amount'] ?? null,
            'currency' => $payload['currency'] ?? null
        ]);

        try {
            $payoutId = $payload['id'] ?? null;
            $external = $payload['external_id'] ?? $payload['reference_id'] ?? null;

            $payout = null;
            if ($payoutId) {
                $payout = \App\Models\EventPayoutRequest::where('notes', 'like', "%Payout ID: {$payoutId}%")->first();
            }
            if (!$payout && $external && str_starts_with($external, 'payout_')) {
                $payoutId = (int) str_replace('payout_', '', explode('_', $external)[1] ?? '');
                $payout = \App\Models\EventPayoutRequest::find($payoutId);
            }

            if ($payout) {
                $payout->update([
                    'status' => 'reversed',
                    'notes' => $payout->notes . "\n\nPayout reversed via Xendit webhook"
                ]);
                
                Log::info('Payout marked as reversed via webhook', [
                    'payout_id' => $payout->id,
                    'payout_transaction_id' => $payoutId,
                    'amount' => $payout->net_amount
                ]);
                
                // Send reversal notification email to organizer
                try {
                    $event = $payout->event;
                    $organizer = $event->user;
                    
                    if ($organizer && $organizer->email) {
                        Mail::to($organizer->email)
                            ->send(new PayoutFailureMail($payout, $event, $organizer, 'Payout was reversed'));
                        
                        Log::info('Payout reversal notification email sent', [
                            'payout_id' => $payout->id,
                            'organizer_email' => $organizer->email,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send payout reversal notification email', [
                        'payout_id' => $payout->id,
                        'organizer_email' => $organizer->email ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
                
                try { event(new \App\Events\AdminMetricsPing('payout:reversed')); } catch (\Throwable $e) {}
            } else {
                Log::warning('Payout not found for payout.reversed', ['id' => $payoutId, 'external_id' => $external]);
            }
        } catch (\Throwable $e) {
            Log::error('Error handling payout.reversed', ['error' => $e->getMessage()]);
        }

        return response()->json(['status' => 'success']);
    }


    /**
     * Find user from payment payload
     */
    private function findUserFromPayment($payload)
    {
        $invoiceId = $payload['id'] ?? $payload['external_id'] ?? null;
        
        // Try subscription first
        $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
        if ($subscription && $subscription->user) {
            return $subscription->user;
        }

        // Try ticket purchase
        $ticketPurchase = TicketPurchase::where('xendit_invoice_id', $invoiceId)
            ->orWhere('purchase_id', $payload['external_id'] ?? '')
            ->first();
        if ($ticketPurchase && $ticketPurchase->user) {
            return $ticketPurchase->user;
        }

        // Try additional ticket purchase
        $additionalTicketPurchase = TicketLimitPurchase::where('xendit_invoice_id', $invoiceId)
            ->orWhere('purchase_id', $payload['external_id'] ?? '')
            ->first();
        if ($additionalTicketPurchase && $additionalTicketPurchase->user) {
            return $additionalTicketPurchase->user;
        }

        return null;
    }

    /**
     * Update sold count for ticket types
     */
    private function updateTicketSoldCount($ticketPurchase)
    {
        try {
            $ticketBreakdown = $ticketPurchase->payment_details['ticket_breakdown'] ?? [];
            
            if (!empty($ticketBreakdown)) {
                // Handle multiple ticket types
                foreach ($ticketBreakdown as $detail) {
                    $ticketTypeId = null;
                    if (is_object($detail['ticket_type'])) {
                        $ticketTypeId = $detail['ticket_type']->id;
                    } elseif (is_array($detail['ticket_type'])) {
                        $ticketTypeId = $detail['ticket_type']['id'] ?? null;
                    }
                    
                    if ($ticketTypeId) {
                        \App\Models\EventTicket::where('id', $ticketTypeId)
                            ->increment('sold_count', $detail['quantity']);
                    }
                }
            } else {
                // Fallback for single ticket type
                $ticketType = $ticketPurchase->ticket_type;
                if ($ticketType) {
                    $ticketType->increment('sold_count', $ticketPurchase->quantity);
                }
            }
            
            Log::info('Ticket sold count updated', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'quantity' => $ticketPurchase->quantity
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update ticket sold count', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate individual tickets with QR codes
     */
    private function generateIndividualTickets($ticketPurchase)
    {
        try {
            $tickets = [];
            $ticketBreakdown = $ticketPurchase->payment_details['ticket_breakdown'] ?? [];
            
            if (empty($ticketBreakdown)) {
                // Fallback for single ticket type (backward compatibility)
                // Try to get the first ticket type from the event
                $firstTicketType = \App\Models\EventTicket::where('event_id', $ticketPurchase->event_id)->first();
                $ticketTypeId = $firstTicketType ? $firstTicketType->id : null;
                
                for ($i = 1; $i <= $ticketPurchase->quantity; $i++) {
                    \App\Models\EventTicketInformation::create([
                        'user_id' => $ticketPurchase->user_id,
                        'event_id' => $ticketPurchase->event_id,
                        'ticket_type_id' => $ticketTypeId,
                        'ticket_purchase_id' => $ticketPurchase->id,
                        'qr_code_data' => null, // Will be generated when ticket is first accessed
                    ]);
                }
            } else {
                // Handle multiple ticket types
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
                            'user_id' => $ticketPurchase->user_id,
                            'event_id' => $ticketPurchase->event_id,
                            'ticket_type_id' => $ticketTypeId,
                            'ticket_purchase_id' => $ticketPurchase->id,
                            'qr_code_data' => null, // Will be generated when ticket is first accessed
                        ]);
                    }
                }
            }
            
            // Generate QR code data for each ticket after insertion
            $insertedTickets = \App\Models\EventTicketInformation::where('ticket_purchase_id', $ticketPurchase->id)
                ->orderBy('id', 'desc')
                ->limit($ticketPurchase->quantity)
                ->get();
                
            $ticketIds = [];
            foreach ($insertedTickets as $ticket) {
                $ticketModel = \App\Models\EventTicketInformation::find($ticket->id);
                if ($ticketModel) {
                    $ticketModel->update(['qr_code_data' => $ticketModel->generateQRCodeData()]);
                    $ticketIds[] = $ticket->id;
                }
            }
            
            // Store ticket IDs in payment details
            $paymentDetails = $ticketPurchase->payment_details;
            $paymentDetails['ticket_ids'] = $ticketIds;
            $ticketPurchase->update(['payment_details' => $paymentDetails]);
            
            Log::info('Individual tickets generated successfully', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'tickets_generated' => count($ticketIds)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to generate individual tickets', [
                'ticket_purchase_id' => $ticketPurchase->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create in-app notification for refund completion
     */
    private function createRefundNotification($user, $refundRequest, $payload)
    {
        try {
            $refundAmount = $payload['amount'] ?? $refundRequest->amount;
            $currency = $payload['currency'] ?? 'PHP';
            
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
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'refund_completed',
                'title' => $title,
                'message' => $message,
                'data' => [
                    'refund_request_id' => $refundRequest->id,
                    'refund_amount' => $refundAmount,
                    'refund_type' => $refundRequest->type,
                    'xendit_refund_id' => $payload['id'] ?? null,
                ],
                'read_at' => null,
            ]);
            
            Log::info('Refund notification created', [
                'user_id' => $user->id,
                'refund_request_id' => $refundRequest->id,
                'refund_amount' => $refundAmount,
                'refund_type' => $refundRequest->type,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create refund notification', [
                'user_id' => $user->id,
                'refund_request_id' => $refundRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate organizer dashboard stats
     */
    private function calculateOrganizerDashboardStats($user)
    {
        try {
            // Get user's events (created by user OR where user is staff)
            $events = \App\Models\Event::where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhereHas('staff', function($q) use ($user) {
                              $q->where('user_id', $user->id);
                          });
                })
                ->pluck('id');

            // Calculate sales stats
            $totalRevenue = \App\Models\TicketPurchase::whereIn('event_id', $events)
                ->where('status', 'paid')
                ->sum('total_amount');

            $totalTicketsSold = \App\Models\TicketPurchase::whereIn('event_id', $events)
                ->where('status', 'paid')
                ->sum('quantity');

            $totalOrders = \App\Models\TicketPurchase::whereIn('event_id', $events)
                ->where('status', 'paid')
                ->count();

            $thisMonthRevenue = \App\Models\TicketPurchase::whereIn('event_id', $events)
                ->where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total_amount');

            return [
                'totalRevenue' => $totalRevenue,
                'totalTicketsSold' => $totalTicketsSold,
                'thisMonthRevenue' => $thisMonthRevenue,
                'totalOrders' => $totalOrders,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to calculate organizer dashboard stats: ' . $e->getMessage());
            return [
                'totalRevenue' => 0,
                'totalTicketsSold' => 0,
                'thisMonthRevenue' => 0,
                'totalOrders' => 0,
            ];
        }
    }
}
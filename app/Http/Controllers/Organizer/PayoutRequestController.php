<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPayoutRequest;
use App\Models\TicketPurchase;
use App\Services\XenditDisbursementService;
use App\Services\XenditPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutRequestController extends Controller
{
    public function show(Event $event)
    {
        // Check if user has access to this event
        $hasAccess = Auth::guard('admin')->check() ||
            $event->user_id === Auth::id() || 
            $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        $payout = EventPayoutRequest::where('event_id', $event->id)->orderBy('created_at', 'desc')->first();
        $canPayout = $event->end_date && now()->gte($event->end_date) && $event->user_id === Auth::id();
        
        return view('organizer.events.payout', compact('event', 'payout', 'canPayout'));
    }

    public function calculatePayout(Event $event)
    {
        // Check if user has access to this event
        $hasAccess = Auth::guard('admin')->check() ||
            $event->user_id === Auth::id() || 
            $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Calculate payout base from PAID ticket purchases only
        // IMPORTANT: Use net_amount (after Xendit gateway fees) as the base
        // We then apply the Qricket service fee on top of this net base
        $totalEarnings = TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->sum(DB::raw('COALESCE(net_amount, total_amount)')) ?? 0;
        
        // Log for debugging
        $paidPurchases = TicketPurchase::where('event_id', $event->id)->where('status', 'paid')->get();
        $debugDetails = $paidPurchases->map(function($purchase) {
            return [
                'id' => $purchase->id,
                'total_amount' => $purchase->total_amount,
                'net_amount' => $purchase->net_amount,
                'purchase_id' => $purchase->purchase_id
            ];
        });
        
        \Log::info('Payout Calculation Debug', [
            'event_id' => $event->id,
            'event_title' => $event->title,
            'total_paid_tickets' => $paidPurchases->count(),
            'payout_base_net_sum' => $totalEarnings,
            'sum_total_amount' => $paidPurchases->sum('total_amount'),
            'sum_net_amount' => $paidPurchases->sum(function($p) { return $p->net_amount ?? $p->total_amount; }),
            'purchases' => $debugDetails
        ]);

        // Calculate Qricket fee and net amount
        // Payoutable amount base is the NET total (after Xendit gateway fees)
        $payoutableAmount = $totalEarnings;
        
        // Get Qricket fee percentage from config (default 5%)
        $qricketFeePercentage = config('app.qricket_fee_percentage', 5);
        $qricketFee = $payoutableAmount * ($qricketFeePercentage / 100);
        $netAmount = $payoutableAmount - $qricketFee;

        // Check if there's already a pending payout request
        $existingRequest = EventPayoutRequest::where('event_id', $event->id)
            ->where('status', 'pending')
            ->first();

        return response()->json([
            'success' => true,
            'total_earnings' => $totalEarnings ?? 0,
            'qricket_fee_percentage' => $qricketFeePercentage,
            'qricket_fee' => $qricketFee,
            'net_amount' => $netAmount,
            'has_existing_request' => $existingRequest ? true : false,
            'existing_request' => $existingRequest,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'end_date' => $event->end_date,
            ]
        ]);
    }

    public function requestPayout(Request $request, Event $event)
    {
        // Check if user has access to this event
        $hasAccess = Auth::guard('admin')->check() ||
            $event->user_id === Auth::id() || 
            $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Validate request
        $request->validate([
            'total_earnings' => 'required|numeric|min:0',
            'qricket_fee' => 'required|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if there's already a pending payout request
        $existingRequest = EventPayoutRequest::where('event_id', $event->id)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending payout request for this event.'
            ], 400);
        }

        // Create payout request
        $payoutRequest = EventPayoutRequest::create([
            'event_id' => $event->id,
            'organizer_id' => $event->user_id,
            'total_earnings' => $request->total_earnings,
            'qricket_fee' => $request->qricket_fee,
            'net_amount' => $request->net_amount,
            'status' => 'pending',
            'notes' => $request->notes,
            'requested_at' => now(),
        ]);

        // Create admin notification for payout request
        try {
            $organizer = \App\Models\User::find($event->user_id);
            $organizerName = $organizer ? (trim(($organizer->first_name ?? '') . ' ' . ($organizer->last_name ?? '')) ?: $organizer->email) : 'Unknown Organizer';
            $notificationMessage = "{$organizerName} requested a payout of ₱" . number_format($request->net_amount, 2) . " for event '{$event->title}'";
            
            $notification = \App\Models\Notification::create([
                'user_id' => null, // Admin notification
                'admin_id' => null, // For all admins
                'type' => 'event',
                'action' => 'payout_processed', // Using existing action
                'reference_id' => $payoutRequest->id,
                'title' => 'Payout Request',
                'message' => $notificationMessage,
                'data' => [
                    'payout_request_id' => $payoutRequest->id,
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'organizer_id' => $event->user_id,
                    'organizer_name' => $organizerName,
                    'net_amount' => $request->net_amount,
                    'total_earnings' => $request->total_earnings,
                    'qricket_fee' => $request->qricket_fee,
                ],
                'is_read' => false,
            ]);

            // Broadcast admin notification with notification ID
            try {
                event(new \App\Events\AdminNotification(
                    $notificationMessage,
                    'info',
                    'Payout Request',
                    $notification->id
                ));
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast admin notification for payout request: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create admin notification for payout request: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Payout request submitted successfully!',
            'payout_request' => $payoutRequest
        ]);
    }

    public function getPayoutStatus(Event $event)
    {
        // Check if user has access to this event
        $hasAccess = Auth::guard('admin')->check() ||
            $event->user_id === Auth::id() || 
            $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        $payoutRequest = EventPayoutRequest::where('event_id', $event->id)
            ->orderBy('created_at', 'desc')
            ->first();

        return response()->json([
            'payout_request' => $payoutRequest
        ]);
    }

    public function collectPayout(Request $request, Event $event)
    {
        // Check if user has access to this event
        $hasAccess = Auth::guard('admin')->check() ||
            $event->user_id === Auth::id() || 
            $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Validate request - we need bank details for disbursement
        $request->validate([
            'bank_code' => 'required|string|max:50',
            'account_number' => 'required|string|max:20',
            'account_name' => 'required|string|max:100',
        ]);

        // Find the released payout request
        $payoutRequest = EventPayoutRequest::where('event_id', $event->id)
            ->where('status', 'released')
            ->first();

        if (!$payoutRequest) {
            return response()->json([
                'success' => false,
                'message' => 'No released payout request found for this event.'
            ], 400);
        }

        try {
            // Use Xendit disbursement service to send money to user's account
            $xenditService = new XenditDisbursementService();
            
            $externalId = 'payout_' . $payoutRequest->id . '_' . time();
            $description = "Payout for event: {$event->title}";
            
            // Process disbursement through Xendit - this sends money TO the user's account
            $disbursementId = $xenditService->disburse(
                $externalId,
                $payoutRequest->net_amount,
                $request->bank_code,
                $request->account_number,
                $request->account_name,
                $description
            );

            // Update payout request to paid status
            $payoutRequest->update([
                'status' => 'paid',
                'processed_at' => now(),
                'notes' => $payoutRequest->notes . "\n\nPayout processed via Xendit Disbursement:\nDisbursement ID: " . $disbursementId . "\nBank: " . $request->bank_code . "\nAccount: " . $request->account_number . "\nName: " . $request->account_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payout processed successfully! The amount will be transferred to your account within 1-2 business days.',
                'payout_request' => $payoutRequest,
                'disbursement_id' => $disbursementId
            ]);

        } catch (\Exception $e) {
            Log::error('Xendit disbursement failed', [
                'payout_request_id' => $payoutRequest->id,
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process payout. Please try again or contact support.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function payoutSuccess(Event $event)
    {
        // Check if user has access to this event
        $hasAccess = Auth::guard('admin')->check() ||
            $event->user_id === Auth::id() || 
            $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        $payoutRequest = EventPayoutRequest::where('event_id', $event->id)
            ->where('status', 'released')
            ->first();

        if (!$payoutRequest) {
            return redirect()->route('organizer.events.payout', $event)
                ->with('error', 'Payout request not found.');
        }

        // Get invoice ID from notes
        $notes = $payoutRequest->notes ?? '';
        preg_match('/Invoice ID: ([a-zA-Z0-9\-_]+)/', $notes, $matches);
        $invoiceId = $matches[1] ?? null;

        if ($invoiceId) {
            try {
                $xenditService = new XenditPaymentService();
                $invoiceStatus = $xenditService->getInvoiceStatus($invoiceId);
                
                if ($invoiceStatus['status'] === 'PAID') {
                    // Update payout request to paid status
                    $payoutRequest->update([
                        'status' => 'paid',
                        'processed_at' => now(),
                        'notes' => $payoutRequest->notes . "\n\nPayment completed via Xendit payment page on " . now()->format('Y-m-d H:i:s')
                    ]);

                    return redirect()->route('organizer.events.payout', $event)
                        ->with('success', 'Payout collected successfully! The amount will be transferred to your account within 1-2 business days.');
                }
            } catch (\Exception $e) {
                Log::error('Failed to verify payment status', [
                    'invoice_id' => $invoiceId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('organizer.events.payout', $event)
            ->with('info', 'Payment is being processed. You will be notified once it\'s completed.');
    }

    public function payoutFailure(Event $event)
    {
        // Check if user has access to this event
        $hasAccess = Auth::guard('admin')->check() ||
            $event->user_id === Auth::id() || 
            $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        return redirect()->route('organizer.events.payout', $event)
            ->with('error', 'Payment was not completed. Please try again or contact support if the issue persists.');
    }
}
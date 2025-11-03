<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;

use App\Models\Event;
use App\Models\EventPayoutRequest;
use App\Models\TicketPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventPayoutController extends Controller
{
    public function show(Event $event)
    {
        // Check if user has access to this event
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Get the latest payout request for this event
        $payoutRequest = EventPayoutRequest::where('event_id', $event->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Check if user can request payout (event ended and user is owner or admin)
        $canPayout = $event->end_date && now()->gte($event->end_date) && ($isOwner || $isAdminEvent);

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.payout', compact('event', 'payoutRequest', 'canPayout', 'isAdmin', 'layoutType'));
    }

    public function request(Event $event, Request $request)
    {
        $this->authorize('update', $event);
        $payout = EventPayout::where('event_id',$event->id)->firstOrFail();
        // No banking details needed at request time; just move to under_review
        $payout->update(['status' => 'under_review']);
        try { event(new \App\Events\AdminMetricsPing('payout:requested')); } catch (\Throwable $e) {}
        return back()->with('success','Payout requested and sent to admin for review.');
    }

    public function collect(Event $event)
    {
        $this->authorize('update', $event);
        $payout = EventPayout::where('event_id',$event->id)->firstOrFail();
        abort_unless($payout->status === 'approved', 403);
        
        // Validate method and destination provided at collection time
        $method = request('method');
        try {
            if ($method === 'bank') {
                $bankCode = (string) request('bank_code');
                $acctNum = (string) request('account_number');
                $acctName = (string) request('account_name');
                if (!$bankCode || !$acctNum || !$acctName) {
                    return back()->with('error','Please provide bank details to proceed.');
                }
                // Save destination for record
                $payout->update([
                    'destination_bank_code' => $bankCode,
                    'destination_account_number' => $acctNum,
                    'destination_account_name' => $acctName,
                ]);
                // Queue disbursement via Xendit (bank transfer)
                $disbId = app(\App\Services\XenditDisbursementService::class)->disburse(
                    externalId: 'payout_'.$payout->id,
                    amount: (float)$payout->net_amount,
                    bankCode: $bankCode,
                    accountNumber: $acctNum,
                    accountName: $acctName,
                    description: 'Event payout #'.$payout->id.' for '.$event->title
                );
                $payout->update(['status'=>'processing','xendit_disbursement_id'=>$disbId]);
                event(new \App\Events\AdminMetricsPing('payout:queued'));
                return back()->with('success','Payout is being processed.');
            }

            if (in_array($method, ['gcash','maya','ewallet'], true)) {
                // Placeholder: Implement Xendit e-wallet disbursement here
                throw new \RuntimeException('E-wallet payout is not configured yet. Please use bank transfer.');
            }

            return back()->with('error','Select a payout method to continue.');
        } catch (\Throwable $e) {
            $payout->update(['status'=>'failed','failure_reason'=>$e->getMessage()]);
            return back()->with('error','Failed to queue payout: '.$e->getMessage());
        }
    }
}



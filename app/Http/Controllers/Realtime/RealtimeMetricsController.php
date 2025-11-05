<?php

namespace App\Http\Controllers\Realtime;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TicketPurchase;
use App\Models\TicketLimitPurchase;
use App\Models\Subscription;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class RealtimeMetricsController extends Controller
{
    public function metrics(Request $request)
    {
        $scope = $request->query('scope');
        $eventId = (int) $request->query('event_id');

        switch ($scope) {
            case 'admin_dashboard':
                return response()->json($this->adminDashboard());
            case 'admin_sales':
                return response()->json($this->adminSales());
            case 'organizer_dashboard':
                return response()->json($this->organizerDashboard());
            case 'event_overview':
            case 'event_sales':
                if ($eventId > 0) {
                    return response()->json($this->eventSales($eventId));
                }
                return response()->json(['error' => 'event_id required'], 422);
            default:
                return response()->json(['error' => 'invalid scope'], 400);
        }
    }

    private function adminDashboard(): array
    {
        $ticketRevenue = (float) TicketPurchase::where('status','paid')->sum('total_amount');
        $ticketNet = (float) TicketPurchase::where('status','paid')->sum(DB::raw('COALESCE(net_amount,total_amount)'));
        $subsRevenue = (float) Subscription::where('payment_status','paid')->sum('amount');
        $subsNet = (float) Subscription::where('payment_status','paid')->sum(DB::raw('COALESCE(net_amount,amount)'));
        $addlRevenue = (float) TicketLimitPurchase::where('status','paid')->sum('amount');
        $addlNet = (float) TicketLimitPurchase::where('status','paid')->sum(DB::raw('COALESCE(net_amount,amount)'));

        return [
            'commerce' => [
                'gross' => $ticketRevenue + $subsRevenue + $addlRevenue,
                'net' => $ticketNet + $subsNet + $addlNet,
            ]
        ];
    }

    private function adminSales(): array
    {
        $adminRevenuePercentage = (float) config('app.admin_revenue_percentage', 5.0);
        
        $ticketRevenue = (float) TicketPurchase::where('status','paid')->sum('total_amount');
        $ticketNet = (float) TicketPurchase::where('status','paid')->sum(DB::raw('COALESCE(net_amount,total_amount)'));
        $payoutPaidTotal = (float) \App\Models\EventPayoutRequest::where('status','paid')->sum('net_amount');
        $payoutProcessedTotal = (float) \App\Models\EventPayoutRequest::whereIn('status',['approved','released','paid'])->sum('net_amount');
        $totalRefundedAmount = (float) \App\Models\RefundRequest::where('status','processed')->sum('amount');

        // Get all events with revenue (for detailed table) - return all, not paginated for real-time updates
        $allEventsData = TicketPurchase::where('status','paid')
            ->get()
            ->groupBy('event_id')
            ->map(function($purchases, $eventId) use ($adminRevenuePercentage) {
                $event = Event::find($eventId);
                $totalRevenue = (float) $purchases->sum('total_amount');
                $netRevenue = (float) $purchases->sum(function($p) {
                    return $p->net_amount ?? $p->total_amount;
                });
                $adminEarnings = (float) $netRevenue * ($adminRevenuePercentage / 100);
                
                return [
                    'event_id' => $eventId,
                    'title' => $event?->title ?? ('Event #'.$eventId),
                    'total_revenue' => $totalRevenue,
                    'net_revenue' => $netRevenue,
                    'admin_earnings' => $adminEarnings,
                    'orders' => (int) $purchases->count(),
                    'start_date' => $event?->start_date?->format('M d, Y') ?? 'N/A',
                    'status' => $event?->status ?? 'unknown',
                ];
            })
            ->sortByDesc('total_revenue')
            ->values()
            ->toArray();

        // Get top events for widget (first 8)
        $topEvents = collect($allEventsData)->take(8)->map(function($event) {
            return [
                'title' => $event['title'],
                'orders' => $event['orders'],
                'revenue' => $event['total_revenue'],
            ];
        })->toArray();

        // Subscription users for detailed table - return all for real-time updates (not paginated)
        $subscriptionUsers = \App\Models\Subscription::with(['user', 'plan'])
            ->where('payment_status', 'paid')
            ->where('status','!=','refunded')
            ->orderByDesc('created_at')
            ->get()
            ->map(function($subscription) {
                return [
                    'id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'name' => $subscription->user?->full_name ?? 'Unknown User',
                    'email' => $subscription->user?->email ?? 'N/A',
                    'plan_name' => $subscription->plan?->name ?? 'Unknown Plan',
                    'amount' => (float)$subscription->amount,
                    'status' => $subscription->status,
                    'created_at' => $subscription->created_at->format('M d, Y'),
                    'subscribed_at' => $subscription->created_at->format('M d, Y'),
                ];
            })
            ->toArray();

        return [
            'ticketRevenue' => $ticketRevenue,
            'ticketNetRevenue' => $ticketNet,
            'payoutPaidTotal' => $payoutPaidTotal,
            'payoutProcessedTotal' => $payoutProcessedTotal,
            'totalRefundedAmount' => $totalRefundedAmount,
            'allEventsRevenue' => $allEventsData, // Full list of events with revenue
            'topEvents' => $topEvents, // Top 8 events for widget
            'subscriptionUsers' => $subscriptionUsers, // Full list of subscription users
        ];
    }

    private function organizerDashboard(): array
    {
        // Minimal organizer-level snapshot; expand as needed
        $events = Event::where('user_id', auth()->id())->pluck('id');
        if ($events->isEmpty()) {
            return [ 'net' => 0.0 ];
        }
        $ticketNet = (float) TicketPurchase::whereIn('event_id', $events)->where('status','paid')->sum(DB::raw('COALESCE(net_amount,total_amount)'));
        $addlNet = (float) TicketLimitPurchase::whereIn('event_id', $events)->where('status','paid')->sum(DB::raw('COALESCE(net_amount,amount)'));
        $subsNet = 0.0; // not tied to single organizer in current schema
        return [ 'net' => $ticketNet + $addlNet + $subsNet ];
    }

    private function eventSales(int $eventId): array
    {
        $event = Event::find($eventId);
        $ticketRevenue = (float) TicketPurchase::where('event_id',$eventId)->where('status','paid')->sum('total_amount');
        $ticketNet = (float) TicketPurchase::where('event_id',$eventId)->where('status','paid')->sum(DB::raw('COALESCE(net_amount,total_amount)'));
        // Exclude additional ticket-limit purchases from expected revenue; not part of ticket sales
        $addlNet = 0.0;
        
        // Calculate expected revenue: net minus Qricket platform fee, clamped to 0
        // If event is free or net is zero/non-positive, expected revenue is 0
        $netAmountTotal = max(0, $ticketNet);
        if ($event && ($event->is_free || $netAmountTotal <= 0)) {
            $expectedRevenue = 0;
        } else {
            $qricketFeePercentage = config('app.qricket_fee_percentage', 5);
            $qricketFee = $netAmountTotal * ($qricketFeePercentage / 100);
            $expectedRevenue = max(0, $netAmountTotal - $qricketFee);
        }
        
        return [
            'event_id' => $eventId,
            'ticketRevenue' => $ticketRevenue,
            'ticketNetRevenue' => $ticketNet, // Keep for backward compatibility
            'expectedRevenue' => $expectedRevenue, // Add expected revenue field
            'additionalNetRevenue' => $addlNet,
        ];
    }
}



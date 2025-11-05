<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminDashboardUpdated;
use App\Http\Controllers\Controller;
use App\Models\TicketPurchase;
use App\Models\Subscription;
use App\Models\Event;
use App\Models\TicketLimitPricing;
use App\Models\TicketLimitPurchase;
use App\Models\RefundRequest;
use App\Models\AdminActivityLog;
use App\Services\XlsxGenerator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function sales()
    {
        // Admin cut configuration (for UI summaries below)
        $adminRevenuePercentage = (float) config('app.admin_revenue_percentage', 5.0);

        // Ticket sales aggregates - use net_amount if available, fallback to total_amount
        $ticketPurchases = TicketPurchase::where('status','paid')->get();
        $ticketRevenue = (float) $ticketPurchases->sum('total_amount');
        $ticketNetRevenue = (float) $ticketPurchases->sum(function($p) {
            $net = $p->net_amount ?? $p->total_amount;
            return max(0, (float) $net);
        });
        $ticketAdminEarnings = (float) $ticketNetRevenue * ($adminRevenuePercentage / 100);
        $ticketOrders = (int) $ticketPurchases->count();
        $ticketPending = 0; // deprecated in UI
        // Free tickets sold KPI
        $freeTicketsSold = (int) TicketPurchase::where('status','paid')->where('unit_price',0)->sum('quantity');

        // Payout aggregates
        $payoutPaidTotal = (float) \App\Models\EventPayoutRequest::where('status','paid')->sum('net_amount');
        $payoutProcessedTotal = (float) \App\Models\EventPayoutRequest::whereIn('status',['approved','released','paid'])->sum('net_amount');

        // Refund totals (processed only)
        $refundProcessed = RefundRequest::where('status','processed');
        $totalRefundedAmount = (float) $refundProcessed->sum('amount');
        $totalRefundedCount = (int) RefundRequest::where('status','processed')->count();
        $refundTicketsCount = (int) RefundRequest::where('status','processed')->where('type','ticket')->count();
        $refundSubsCount = (int) RefundRequest::where('status','processed')->where('type','subscription')->count();
        $refundAddlCount = (int) RefundRequest::where('status','processed')->where('type','additional_ticket')->count();

        // Ticket trend last 30 days
        $start = Carbon::now()->subDays(29)->startOfDay();
        $ticketTrend = TicketPurchase::selectRaw('DATE(paid_at) as d, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->where('status','paid')
            ->whereNotNull('paid_at')
            ->where('paid_at','>=',$start)
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');
        $ticketTrendLabels = [];
        $ticketTrendRevenue = [];
        $ticketTrendOrders = [];
        for ($i=29; $i>=0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $ticketTrendLabels[] = $day;
            $ticketTrendRevenue[] = (float) ($ticketTrend[$day]->revenue ?? 0);
            $ticketTrendOrders[] = (int) ($ticketTrend[$day]->orders ?? 0);
        }

        // Top events by ticket revenue
        $topEventsData = TicketPurchase::where('status','paid')
            ->get()
            ->groupBy('event_id')
            ->map(function($purchases, $eventId) {
                $event = Event::find($eventId);
                return [
                    'event_id' => $eventId,
                    'title' => $event?->title ?? ('Event #'.$eventId),
                    'revenue' => (float) $purchases->sum('total_amount'),
                    'orders' => (int) $purchases->count(),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5);

        // Subscription aggregates - be tolerant of payment_status column
        $subscriptionsAll = Subscription::with('plan')
            ->where('status','!=','refunded')
            ->get();
        // Revenue should reflect paid transactions only
        $paidSubs = $subscriptionsAll->filter(function($s){
            return ($s->payment_status ?? 'paid') === 'paid';
        });
        $subRevenue = (float) $paidSubs->sum('amount');
        $subNetRevenue = (float) $paidSubs->sum(function($s) {
            return $s->net_amount ?? $s->amount;
        });
        $subAdminEarnings = (float) $subNetRevenue * ($adminRevenuePercentage / 100);
        $activeSubs = (int) $subscriptionsAll->where('status','active')->count();

        // Additional tickets revenue already computed later; predefine here as 0 to build total, update after ticket-limit section
        $ticketLimitRevenue = 0.0;
        $ticketLimitNetRevenue = 0.0;
        $ticketLimitAdminEarnings = 0.0;

        // All events with revenue (for detailed table) - with pagination
        // Get all purchases and group by event_id to calculate net amounts and admin earnings
        $allEventsData = TicketPurchase::where('status','paid')
            ->get()
            ->groupBy('event_id')
            ->map(function($purchases, $eventId) use ($adminRevenuePercentage) {
                $event = Event::find($eventId);
                $totalRevenue = (float) $purchases->sum('total_amount');
                $netRevenue = (float) $purchases->sum(function($p) {
                    $net = $p->net_amount ?? $p->total_amount;
                    return max(0, (float) $net);
                });
                $adminEarnings = (float) max(0, $netRevenue) * ($adminRevenuePercentage / 100);
                
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
            ->values();
            
        // Convert to paginated collection manually
        $page = request()->get('page', 1);
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $total = $allEventsData->count();
        $items = $allEventsData->slice($offset, $perPage)->values();
        
        // Create a LengthAwarePaginator manually
        $allEventsRevenue = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        // Get all subscription plans (even those with no subscriptions)
        $allPlans = \App\Models\SubscriptionPlan::all();
        
        // Get subscription data for plans that have subscriptions (paid totals + total users not refunded)
        $subscriptionData = $subscriptionsAll
            ->groupBy('subscription_plan_id')
            ->map(function($subsForPlan) {
                $paid = $subsForPlan->filter(function($s){ return ($s->payment_status ?? 'paid') === 'paid'; });
                return [
                    'total_revenue' => (float) $paid->sum('amount'),
                    'total_subscriptions' => (int) $subsForPlan->count(),
                    'active_subscriptions' => (int) $subsForPlan->where('status','active')->count(),
                ];
            });
        
        // Create cards for all plans, showing 0 for plans with no subscriptions
        $subscriptionPlans = $allPlans->map(function($plan) use ($subscriptionData) {
            $data = $subscriptionData->get($plan->id, [
                'total_revenue' => 0,
                'total_subscriptions' => 0,
                'active_subscriptions' => 0,
            ]);
            
            return [
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'plan_price' => $plan->price,
                'total_revenue' => $data['total_revenue'],
                'total_subscriptions' => $data['total_subscriptions'],
                'active_subscriptions' => $data['active_subscriptions'],
            ];
        })
        // Remove plans we don't sell directly (e.g., Free or deprecated Enterprise)
        ->reject(function($plan){
            $name = strtolower((string)($plan['plan_name'] ?? ''));
            return $name === 'free' || $name === 'enterprise';
        })
        ->sortByDesc('total_revenue')->values();

        // Additional Ticket Settings (Ticket Limit Purchases) - use net_amount
        $additionalTicketPurchases = TicketLimitPurchase::where('status','paid')->get();
        $ticketLimitRevenue = (float) $additionalTicketPurchases->sum('amount');
        $ticketLimitNetRevenue = (float) $additionalTicketPurchases->sum(function($p) {
            return $p->net_amount ?? $p->amount;
        });
        $ticketLimitAdminEarnings = (float) $ticketLimitNetRevenue * ($adminRevenuePercentage / 100);
        $additionalTicketsPurchased = (int) $additionalTicketPurchases->sum('additional_tickets');
        $additionalTicketOrders = (int) $additionalTicketPurchases->count();

        // Build quick lookups for pricing plans
        $pricingByName = TicketLimitPricing::all()->keyBy(function($p){ return strtolower($p->name); });
        $pricingById = TicketLimitPricing::all()->keyBy('id');

        // Per-pricing plan aggregates: include all pricing rows, even with zero purchases
        $purchasesByPlanId = $additionalTicketPurchases->groupBy('ticket_limit_pricing_id');
        $ticketLimitPlans = TicketLimitPricing::all()->map(function($plan) use ($purchasesByPlanId, $adminRevenuePercentage){
            $purchases = $purchasesByPlanId->get($plan->id, collect());
            $totalRevenue = (float) $purchases->sum('amount');
            $netRevenue = (float) $purchases->sum(function ($p) { return $p->net_amount ?? $p->amount; });
            return [
                'plan_name' => $plan->name,
                'plan_price' => (float) $plan->price,
                'currency' => $plan->currency,
                'total_revenue' => $totalRevenue,
                'net_revenue' => $netRevenue,
                'admin_earnings' => $netRevenue * ($adminRevenuePercentage / 100),
                'total_purchases' => (int) $purchases->count(),
                'total_tickets' => (int) $purchases->sum('additional_tickets'),
            ];
        })->sortByDesc('total_revenue')->values();

        // Additional tickets grouped by event (for tab table) - calculate net amounts
        $ticketLimitByEvent = $additionalTicketPurchases
            ->groupBy('event_id')
            ->map(function($purchases, $eventId) use ($adminRevenuePercentage) {
                $event = Event::find($eventId);
                $totalRevenue = (float) $purchases->sum('amount');
                $netRevenue = (float) $purchases->sum(function($p) {
                    return $p->net_amount ?? $p->amount;
                });
                return [
                    'event_id' => $eventId,
                    'title' => $event?->title ?? ('Event #'.$eventId),
                    'orders' => (int) $purchases->count(),
                    'tickets' => (int) $purchases->sum('additional_tickets'),
                    'revenue' => $totalRevenue,
                    'net_revenue' => $netRevenue,
                    'admin_earnings' => $netRevenue * ($adminRevenuePercentage / 100),
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // Build additional tickets summary for report and excel
        $additionalTicketsSummary = [
            'total_revenue' => $ticketLimitRevenue,
            'net_revenue' => $ticketLimitNetRevenue,
            'total_orders' => $additionalTicketOrders,
            'total_tickets' => $additionalTicketsPurchased,
            'admin_revenue' => $ticketLimitAdminEarnings,
        ];

        // Grand totals for UI - use total amounts for display, but calculate admin earnings from net
        $grandRevenue = (float) ($ticketRevenue + $subRevenue + $ticketLimitRevenue);
        $grandNetRevenue = (float) ($ticketNetRevenue + $subNetRevenue + $ticketLimitNetRevenue);
        $grandAdminEarnings = (float) ($ticketAdminEarnings + $subAdminEarnings + $ticketLimitAdminEarnings);

        // Admin earnings rule: 5% of ticket net, plus full net of subs and additional tickets
        $adminEarningsTotal = (float) (($ticketNetRevenue * ($adminRevenuePercentage / 100)) + $subNetRevenue + $ticketLimitNetRevenue);

        // Subscription users for detailed table - with pagination
        $subscriptionUsers = Subscription::with(['user', 'plan'])
            ->where('payment_status', 'paid')
            ->where('status','!=','refunded')
            ->orderByDesc('created_at')
            ->paginate(20); // Paginate with 20 users per page
            
        // Transform the paginated results
        $subscriptionUsers->getCollection()->transform(function($subscription){
            return [
                'user_id' => $subscription->user_id,
                'name' => $subscription->user?->full_name ?? 'Unknown User',
                'email' => $subscription->user?->email ?? 'N/A',
                'plan_name' => $subscription->plan?->name ?? 'Unknown Plan',
                'amount' => (float)$subscription->amount,
                'status' => $subscription->status,
                'created_at' => $subscription->created_at->format('M d, Y'),
            ];
        });

        // Count all paid, non-refunded subscriptions
        $subscriptionCount = (int) Subscription::where('payment_status','paid')
            ->where('status','!=','refunded')
            ->count();

        // Subscription trend last 30 days
        $subTrend = Subscription::selectRaw('DATE(updated_at) as d, SUM(amount) as revenue, COUNT(*) as cnt')
            ->where('payment_status','paid')
            ->where('status','!=','refunded')
            ->where('updated_at','>=',$start)
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');
        $subTrendLabels = [];
        $subTrendRevenue = [];
        for ($i=29; $i>=0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $subTrendLabels[] = $day;
            $subTrendRevenue[] = (float) ($subTrend[$day]->revenue ?? 0);
        }

        return view('admin.reports.sales', [
            'ticketRevenue' => $ticketRevenue,
            'ticketNetRevenue' => $ticketNetRevenue,
            'ticketAdminEarnings' => $ticketAdminEarnings,
            'ticketOrders' => $ticketOrders,
            'ticketPending' => $ticketPending,
            'freeTicketsSold' => $freeTicketsSold,
            'payoutPaidTotal' => $payoutPaidTotal,
            'payoutProcessedTotal' => $payoutProcessedTotal,
            'totalRefundedAmount' => $totalRefundedAmount,
            'totalRefundedCount' => $totalRefundedCount,
            'ticketTrendLabels' => $ticketTrendLabels,
            'ticketTrendRevenue' => $ticketTrendRevenue,
            'ticketTrendOrders' => $ticketTrendOrders,
            'topEvents' => $topEventsData,
            'subRevenue' => $subRevenue,
            'subNetRevenue' => $subNetRevenue,
            'subAdminEarnings' => $subAdminEarnings,
            'activeSubs' => $activeSubs,
            'subTrendLabels' => $subTrendLabels,
            'subTrendRevenue' => $subTrendRevenue,
            'allEventsRevenue' => $allEventsRevenue,
            'subscriptionPlans' => $subscriptionPlans,
            'subscriptionUsers' => $subscriptionUsers,
            'subscriptionCount' => $subscriptionCount,
            'ticketLimitRevenue' => $ticketLimitRevenue,
            'ticketLimitNetRevenue' => $ticketLimitNetRevenue,
            'ticketLimitAdminEarnings' => $ticketLimitAdminEarnings,
            'additionalTicketsPurchased' => $additionalTicketsPurchased,
            'ticketLimitPlans' => $ticketLimitPlans,
            'ticketLimitByEvent' => $ticketLimitByEvent,
            'additionalTicketOrders' => $additionalTicketOrders,
            'grandRevenue' => $grandRevenue,
            'grandNetRevenue' => $grandNetRevenue,
            'grandAdminEarnings' => $grandAdminEarnings,
            'adminEarningsTotal' => $adminEarningsTotal,
            'adminRevenuePercentage' => $adminRevenuePercentage,
        ]);
    }

    /**
     * Trigger admin sales report update event
     */
    public function triggerSalesUpdate($adminId = null)
    {
        try {
            // Get current sales data
            $salesData = $this->getSalesDataForRealtime();
            
            // Also get net revenue and additional metrics for admin dashboard
            $salesData['ticketNetRevenue'] = (float) TicketPurchase::where('status','paid')->sum(\DB::raw('COALESCE(net_amount,total_amount)'));
            $salesData['subNetRevenue'] = (float) Subscription::where('payment_status','paid')->sum(\DB::raw('COALESCE(net_amount,amount)'));
            $salesData['ticketLimitNetRevenue'] = (float) TicketLimitPurchase::where('status','paid')->sum(\DB::raw('COALESCE(net_amount,amount)'));
            
            // Calculate admin earnings (5% of net revenue)
            $adminRevenuePercentage = config('app.admin_revenue_percentage', 5.0);
            $salesData['adminEarningsTotal'] = ($salesData['ticketNetRevenue'] + $salesData['subNetRevenue'] + $salesData['ticketLimitNetRevenue']) * ($adminRevenuePercentage / 100);
            
            // Broadcast to all admins or specific admin
            if ($adminId) {
                broadcast(new AdminDashboardUpdated($adminId, ['metrics' => $salesData, 'type' => 'sales_update'], 'sales_update'));
            } else {
                // Broadcast to all admins
                broadcast(new AdminDashboardUpdated('all', ['metrics' => $salesData, 'type' => 'sales_update'], 'sales_update'));
            }
            
            return response()->json(['message' => 'Admin sales report update triggered', 'data' => $salesData]);
        } catch (\Exception $e) {
            \Log::error('Failed to trigger admin sales update: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to trigger update'], 500);
        }
    }

    /**
     * Get sales data for real-time updates
     */
    private function getSalesDataForRealtime()
    {
        // Get current metrics
        $ticketRevenue = (float) TicketPurchase::where('status','paid')->sum('total_amount');
        $ticketOrders = (int) TicketPurchase::where('status','paid')->count();
        $ticketPending = (int) TicketPurchase::where('status','pending')->count();
        
        // Subscription metrics
        $subRevenue = (float) Subscription::where('payment_status','paid')->sum('amount');
        $activeSubs = (int) Subscription::where('status','active')->count();
        $totalSubscriptions = (int) Subscription::where('payment_status','paid')->count();
        
        // Additional ticket metrics
        $ticketLimitRevenue = (float) TicketLimitPurchase::where('status','paid')->sum('amount');
        $additionalTicketsPurchased = (int) TicketLimitPurchase::where('status','paid')->sum('additional_tickets');
        $additionalTicketOrders = (int) TicketLimitPurchase::where('status','paid')->count();
        
        $grandRevenue = (float) ($ticketRevenue + $subRevenue + $ticketLimitRevenue);

        // Get top events (return top 8 for widget)
        $topEvents = TicketPurchase::selectRaw('event_id, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->where('status','paid')
            ->groupBy('event_id')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get()
            ->map(function($row){
                $event = Event::find($row->event_id);
                return [
                    'event_id' => $row->event_id,
                    'title' => $event?->title ?? ('Event #'.$row->event_id),
                    'revenue' => (float)$row->revenue,
                    'orders' => (int)$row->orders,
                ];
            })
            ->values()
            ->toArray();

        return [
            'ticketRevenue' => $ticketRevenue,
            'ticketOrders' => $ticketOrders,
            'ticketPending' => $ticketPending,
            'subRevenue' => $subRevenue,
            'activeSubs' => $activeSubs,
            'totalSubscriptions' => $totalSubscriptions,
            'ticketLimitRevenue' => $ticketLimitRevenue,
            'additionalTicketsPurchased' => $additionalTicketsPurchased,
            'additionalTicketOrders' => $additionalTicketOrders,
            'grandRevenue' => $grandRevenue,
            'topEvents' => $topEvents, // Include top events in broadcast
            'timestamp' => now()->toISOString(),
        ];
    }

    public function generateSalesReport(Request $request)
    {
        $request->validate([
            'dateRange' => 'required|string',
            'reportType' => 'required|string',
            'eventFilter' => 'nullable|string',
            'format' => 'required|string',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
        ]);

        // Calculate date range
        $dateRange = $this->calculateDateRange($request->dateRange, $request->startDate, $request->endDate);
        
        // Log admin activity
        Auth::guard('admin')->user()->logActivity('report_generate', [
            'report_type' => $request->reportType,
            'date_range' => $request->dateRange,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
            'event_filter' => $request->eventFilter,
            'format' => $request->format,
            'calculated_date_range' => $dateRange['label'],
        ], 'Generated sales report: ' . $request->reportType . ' (' . $request->format . ')');
        
        // Build query based on filters
        $reportData = $this->buildReportData($request->reportType, $dateRange, $request->eventFilter);
        
        // Generate file based on format
        return $this->generateFile($reportData, $request->format, $dateRange, $request->reportType);
    }

    private function calculateDateRange($dateRange, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();
        
        switch ($dateRange) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                    'label' => 'Today'
                ];
            case 'week':
                return [
                    'start' => $now->copy()->startOfWeek(),
                    'end' => $now->copy()->endOfWeek(),
                    'label' => 'This Week'
                ];
            case 'month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                    'label' => 'This Month'
                ];
            case 'year':
                return [
                    'start' => $now->copy()->startOfYear(),
                    'end' => $now->copy()->endOfYear(),
                    'label' => 'This Year'
                ];
            case 'custom':
                return [
                    'start' => Carbon::parse($startDate)->startOfDay(),
                    'end' => Carbon::parse($endDate)->endOfDay(),
                    'label' => 'Custom Range'
                ];
            default:
                return [
                    'start' => $now->copy()->subDays(365)->startOfDay(), // Look back 1 year by default
                    'end' => $now->copy()->endOfDay(),
                    'label' => 'Last Year'
                ];
        }
    }

    private function buildReportData($reportType, $dateRange, $eventFilter)
    {
        // Calculate admin revenue percentage
        $adminRevenuePercentage = config('app.admin_revenue_percentage', 5.0);
        
        $data = [
            'summary' => [],
            'details' => [
                'tickets' => [],
                'subscriptions' => [],
                'admin_revenue' => []
            ],
            'dateRange' => $dateRange['label'],
            'admin_revenue_percentage' => $adminRevenuePercentage
        ];

        // Event sales data
        if ($reportType === 'all' || $reportType === 'events') {
            // Eager load event and its owner (user)
            $eventQuery = TicketPurchase::with(['event.user', 'user'])
                ->where('status', 'paid');

            // More flexible date filtering - check both created_at and paid_at
            $eventQuery->where(function($q) use ($dateRange) {
                $q->whereBetween('paid_at', [$dateRange['start'], $dateRange['end']])
                  ->orWhere(function($subQ) use ($dateRange) {
                      $subQ->whereNull('paid_at')
                           ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                  });
            });

            if ($eventFilter && $eventFilter !== 'all') {
                $eventQuery->whereHas('event', function($q) use ($eventFilter) {
                    $q->where('status', $eventFilter);
                });
            }

            $eventData = $eventQuery->get();
            
            $eventSummary = [
                'total_revenue' => $eventData->sum('total_amount'),
                'net_revenue' => $eventData->sum(function($purchase) {
                    return $purchase->net_amount ?? $purchase->total_amount;
                }),
                'total_fees' => $eventData->sum(function($purchase) {
                    return ($purchase->transaction_fee ?? 0) + ($purchase->vat_amount ?? 0);
                }),
                'transaction_fees' => $eventData->sum('transaction_fee'),
                'vat_amount' => $eventData->sum('vat_amount'),
                'admin_revenue' => $eventData->sum(function($purchase) use ($adminRevenuePercentage) {
                    $netAmount = $purchase->net_amount ?? $purchase->total_amount;
                    return $netAmount * ($adminRevenuePercentage / 100);
                }),
                'total_orders' => $eventData->count(),
                'pending_orders' => TicketPurchase::where('status', 'pending')->count()
            ];

            $data['summary']['tickets'] = $eventSummary;

            // Event revenue summary - group by event
            $eventRevenueData = $eventData->groupBy('event_id')->map(function ($purchases) use ($adminRevenuePercentage) {
                $firstPurchase = $purchases->first();
                $event = $firstPurchase->event;
                
                $totalRevenue = $purchases->sum('total_amount');
                $netRevenue = $purchases->sum(function($purchase) {
                    return $purchase->net_amount ?? $purchase->total_amount;
                });
                $totalFees = $purchases->sum(function($purchase) {
                    return ($purchase->transaction_fee ?? 0) + ($purchase->vat_amount ?? 0);
                });
                
                return [
                    'event_id' => $event?->id ?? 0,
                    'event_title' => $event?->title ?? 'Unknown Event',
                    'event_date' => $event?->start_date?->format('Y-m-d H:i:s') ?? '',
                    'event_status' => ucfirst($event?->status ?? 'Unknown'),
                    'organizer_name' => ($event?->user?->full_name) ?? ($event?->user?->name) ?? 'Unknown Organizer',
                    'total_revenue' => $totalRevenue,
                    'net_revenue' => $netRevenue,
                    'total_fees' => $totalFees,
                    'transaction_fees' => $purchases->sum('transaction_fee'),
                    'vat_amount' => $purchases->sum('vat_amount'),
                    'admin_revenue' => $netRevenue * ($adminRevenuePercentage / 100),
                    'total_orders' => $purchases->count(),
                    'paid_orders' => $purchases->where('status', 'paid')->count(),
                    'pending_orders' => $purchases->where('status', 'pending')->count(),
                    'average_order_value' => $purchases->avg('total_amount'),
                    'first_sale_date' => $purchases->min('created_at')?->format('Y-m-d H:i:s') ?? '',
                    'last_sale_date' => $purchases->max('created_at')?->format('Y-m-d H:i:s') ?? ''
                ];
            })->values();

            $data['details']['events'] = $eventRevenueData->toArray();

            // Ticket sales detail rows (for Ticket Sales worksheet)
            $ticketSalesRows = TicketPurchase::with(['user','event'])
                ->where('status','paid')
                ->where(function($q) use ($dateRange) {
                    $q->whereBetween('paid_at', [$dateRange['start'], $dateRange['end']])
                      ->orWhere(function($subQ) use ($dateRange) {
                          $subQ->whereNull('paid_at')
                               ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                      });
                })
                ->get();

            foreach ($ticketSalesRows as $row) {
                $data['details']['ticket_sales'][] = [
                    'id' => $row->id,
                    'purchase_id' => $row->id,
                    'buyer' => trim(($row->user->first_name ?? '') . ' ' . ($row->user->last_name ?? '')) ?: 'Unknown',
                    'buyer_email' => $row->user->email ?? '—',
                    'event_title' => $row->event?->title ?? 'Unknown Event',
                    'ticket_type' => isset($row->unit_price) ? ('Ticket (₱' . number_format($row->unit_price, 2) . ')') : 'Ticket',
                    'status' => $row->status,
                    'quantity' => (int) ($row->quantity ?? 0),
                    'total_amount' => (float) ($row->total_amount ?? 0),
                    'transaction_fee' => (float) ($row->transaction_fee ?? 0),
                    'vat_amount' => (float) ($row->vat_amount ?? 0),
                    'net_amount' => (float) ($row->net_amount ?? ($row->total_amount ?? 0)),
                    'payment_method' => $row->payment_method ?? '',
                    'paid_at' => optional($row->paid_at)->format('Y-m-d H:i:s') ?? '',
                    'created_at' => $row->created_at?->format('Y-m-d H:i:s') ?? '',
                ];
            }
        }

        // Subscription data
        if ($reportType === 'all' || $reportType === 'subscriptions') {
            $subQuery = Subscription::with(['user', 'plan'])
                ->where('payment_status', 'paid');

            // More flexible date filtering for subscriptions
            $subQuery->where(function($q) use ($dateRange) {
                $q->whereBetween('updated_at', [$dateRange['start'], $dateRange['end']])
                  ->orWhereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            });

            $subData = $subQuery->get();
            
            $subSummary = [
                'total_revenue' => $subData->sum('amount'),
                'net_revenue' => $subData->sum(function($subscription) {
                    return $subscription->net_amount ?? $subscription->amount;
                }),
                'total_fees' => $subData->sum(function($subscription) {
                    return ($subscription->transaction_fee ?? 0) + ($subscription->vat_amount ?? 0);
                }),
                'transaction_fees' => $subData->sum('transaction_fee'),
                'vat_amount' => $subData->sum('vat_amount'),
                'total_subscriptions' => $subData->count(),
                'active_subscriptions' => $subData->where('status', 'active')->count()
            ];

            $data['summary']['subscriptions'] = $subSummary;

            // Subscription details with enhanced information
            foreach ($subData as $subscription) {
                $data['details']['subscriptions'][] = [
                    'subscription_id' => $subscription->id,
                    'customer_name' => $subscription->user?->full_name ?? 'Unknown Customer',
                    'customer_email' => $subscription->user?->email ?? '',
                    'plan_name' => $subscription->plan?->name ?? 'Unknown Plan',
                    'plan_duration' => $subscription->plan?->duration ?? 'N/A',
                    'gross_amount' => $subscription->amount,
                    'net_amount' => $subscription->net_amount ?? $subscription->amount,
                    'transaction_fee' => $subscription->transaction_fee ?? 0,
                    'vat_amount' => $subscription->vat_amount ?? 0,
                    'total_fees' => ($subscription->transaction_fee ?? 0) + ($subscription->vat_amount ?? 0),
                    'payment_method' => $subscription->payment_method ?? 'UNKNOWN',
                    'status' => $subscription->status,
                    'payment_status' => $subscription->payment_status,
                    'xendit_invoice_id' => $subscription->xendit_invoice_id ?? '',
                    'start_date' => $subscription->start_date?->format('Y-m-d H:i:s') ?? '',
                    'end_date' => $subscription->end_date?->format('Y-m-d H:i:s') ?? '',
                    'created_at' => $subscription->created_at->format('Y-m-d H:i:s')
                ];
            }
        }

        // Additional Tickets data
        if ($reportType === 'all' || $reportType === 'additional_tickets') {
            $additionalTicketsQuery = TicketLimitPurchase::with(['user', 'ticketLimitPricing'])
                ->where('status', 'paid');

            // Date filtering for additional tickets
            $additionalTicketsQuery->where(function($q) use ($dateRange) {
                $q->whereBetween('paid_at', [$dateRange['start'], $dateRange['end']])
                  ->orWhere(function($subQ) use ($dateRange) {
                      $subQ->whereNull('paid_at')
                           ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                  });
            });

            $additionalTicketsData = $additionalTicketsQuery->get();
            
            $additionalTicketsSummary = [
                'total_revenue' => $additionalTicketsData->sum('amount'),
                'net_revenue' => $additionalTicketsData->sum(function($purchase) {
                    return $purchase->net_amount ?? $purchase->amount;
                }),
                'total_fees' => $additionalTicketsData->sum(function($purchase) {
                    return ($purchase->transaction_fee ?? 0) + ($purchase->vat_amount ?? 0);
                }),
                'transaction_fees' => $additionalTicketsData->sum('transaction_fee'),
                'vat_amount' => $additionalTicketsData->sum('vat_amount'),
                'total_orders' => $additionalTicketsData->count(),
                'total_tickets' => $additionalTicketsData->sum('additional_tickets'),
                'admin_revenue' => $additionalTicketsData->sum(function($purchase) use ($adminRevenuePercentage) {
                    $netAmount = $purchase->net_amount ?? $purchase->amount;
                    return $netAmount * ($adminRevenuePercentage / 100);
                })
            ];

            $data['summary']['additional_tickets'] = $additionalTicketsSummary;

            // Additional tickets details
            foreach ($additionalTicketsData as $purchase) {
                $data['details']['additional_tickets'][] = [
                    'purchase_id' => $purchase->id,
                    'customer_name' => $purchase->user?->full_name ?? 'Unknown Customer',
                    'customer_email' => $purchase->user?->email ?? '',
                    'plan_name' => $purchase->ticketLimitPricing?->name ?? 'Unknown Plan',
                    'plan_price' => $purchase->ticketLimitPricing?->price ?? 0,
                    'quantity' => $purchase->additional_tickets,
                    'gross_amount' => $purchase->amount, // Total amount paid
                    'transaction_fee' => $purchase->transaction_fee ?? 0,
                    'vat_amount' => $purchase->vat_amount ?? 0,
                    'net_amount' => $purchase->net_amount ?? 0,
                    'total_fees' => $purchase->total_fees ?? 0,
                    'payment_method' => $purchase->payment_method ?? 'UNKNOWN',
                    'status' => $purchase->status,
                    'payment_status' => $purchase->status, // Using status as payment_status
                    'xendit_invoice_id' => $purchase->xendit_invoice_id ?? '',
                    'created_at' => $purchase->created_at->format('Y-m-d H:i:s'),
                    'paid_at' => $purchase->paid_at?->format('Y-m-d H:i:s') ?? ''
                ];
            }

            // Additional tickets by plan (pricing)
            $ticketLimitPlans = TicketLimitPricing::with(['purchases' => function($query) use ($dateRange) {
                $query->where('status', 'paid')
                      ->where(function($q) use ($dateRange) {
                          $q->whereBetween('paid_at', [$dateRange['start'], $dateRange['end']])
                            ->orWhere(function($subQ) use ($dateRange) {
                                $subQ->whereNull('paid_at')
                                     ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                            });
                      });
            }])->get();

            $data['ticketLimitPlans'] = $ticketLimitPlans->map(function($plan) {
                $purchases = $plan->purchases;
                return [
                    'plan_id' => $plan->id,
                    'plan_name' => $plan->name,
                    'plan_price' => $plan->price,
                    'total_purchases' => $purchases->count(),
                    'total_tickets' => $purchases->sum('additional_tickets'),
                    'total_revenue' => $purchases->sum('amount')
                ];
            })->toArray();

            // Additional tickets by event
            $ticketLimitByEvent = TicketLimitPurchase::with(['user', 'event'])
                ->where('status', 'paid')
                ->where(function($q) use ($dateRange) {
                    $q->whereBetween('paid_at', [$dateRange['start'], $dateRange['end']])
                      ->orWhere(function($subQ) use ($dateRange) {
                          $subQ->whereNull('paid_at')
                               ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
                      });
                })
                ->get()
                ->groupBy('event_id')
                ->map(function($purchases) {
                    $firstPurchase = $purchases->first();
                    return [
                        'event_id' => $firstPurchase->event_id,
                        'title' => $firstPurchase->event?->title ?? 'Unknown Event',
                        'orders' => $purchases->count(),
                        'tickets' => $purchases->sum('additional_tickets'),
                        'revenue' => $purchases->sum('amount')
                    ];
                })->values()->toArray();

            $data['ticketLimitByEvent'] = $ticketLimitByEvent;
        }

        // Payouts data (summary + details)
        if ($reportType === 'all' || $reportType === 'payouts') {
            $payoutsQuery = \App\Models\EventPayoutRequest::with(['event', 'organizer']);

            // Filter by requested_at or processed_at within range
            $payoutsQuery->where(function($q) use ($dateRange) {
                $q->whereBetween('requested_at', [$dateRange['start'], $dateRange['end']])
                  ->orWhereBetween('processed_at', [$dateRange['start'], $dateRange['end']]);
            });

            $payouts = $payoutsQuery->get();

            $payoutSummary = [
                'paid_total' => (float) $payouts->where('status', 'paid')->sum('net_amount'),
                'processed_total' => (float) $payouts->whereIn('status', ['approved','released','paid'])->sum('net_amount'),
                'requests' => (int) $payouts->count(),
                'paid_count' => (int) $payouts->where('status','paid')->count(),
            ];

            $data['summary']['payouts'] = $payoutSummary;

            foreach ($payouts as $p) {
                $data['details']['payouts'][] = [
                    'payout_id' => $p->id,
                    'event_id' => $p->event_id,
                    'event_title' => $p->event?->title ?? 'Unknown Event',
                    'organizer' => $p->organizer?->full_name ?? $p->organizer?->name ?? 'Unknown Organizer',
                    'status' => $p->status,
                    'gross_amount' => (float) ($p->total_earnings ?? 0),
                    'fee_amount' => (float) ($p->qricket_fee ?? 0),
                    'net_amount' => (float) ($p->net_amount ?? 0),
                    'requested_at' => optional($p->requested_at)->format('Y-m-d H:i:s') ?? '',
                    'processed_at' => optional($p->processed_at)->format('Y-m-d H:i:s') ?? '',
                ];
            }
        }

        return $data;
    }

    private function generateFile($data, $format, $dateRange, $reportType = 'all')
    {
        $filename = 'admin-sales-report-' . Carbon::now()->format('Y-m-d') . '.xls';
        
        // Only support Excel format
        return $this->generateExcel($data, $filename, $reportType);
    }

    private function generateCSV($data, $filename)
    {
        $csv = "Sales Report - {$data['dateRange']}\n";
        $csv .= "Generated on: " . Carbon::now()->format('Y-m-d H:i:s') . "\n\n";

        // Summary
        $csv .= "SUMMARY\n";
        $csv .= "Period,{$data['dateRange']}\n";
        
        if (isset($data['summary']['events'])) {
            $csv .= "Event Revenue,{$data['summary']['events']['total_revenue']}\n";
            $csv .= "Event Orders,{$data['summary']['events']['total_orders']}\n";
            $csv .= "Tickets Sold,{$data['summary']['events']['total_tickets']}\n";
        }
        
        if (isset($data['summary']['subscriptions'])) {
            $csv .= "Subscription Revenue,{$data['summary']['subscriptions']['total_revenue']}\n";
            $csv .= "Total Subscriptions,{$data['summary']['subscriptions']['total_subscriptions']}\n";
            $csv .= "Active Subscriptions,{$data['summary']['subscriptions']['active_subscriptions']}\n";
        }

        $csv .= "\n";

        // Event Details
        if (isset($data['details']['events'])) {
            $csv .= "EVENT DETAILS\n";
            $csv .= "Event ID,Event Title,Status,Revenue,Orders,Tickets Sold,Avg Order Value\n";
            foreach ($data['details']['events'] as $event) {
                $csv .= "{$event['event_id']},{$event['event_title']},{$event['event_status']},{$event['revenue']},{$event['orders']},{$event['tickets_sold']},{$event['avg_order_value']}\n";
            }
            $csv .= "\n";
        }

        // Subscription Details
        if (isset($data['details']['subscriptions'])) {
            $csv .= "SUBSCRIPTION DETAILS\n";
            $csv .= "Subscription ID,User ID,Plan ID,Amount,Status,Payment Status,Created At\n";
            foreach ($data['details']['subscriptions'] as $sub) {
                $csv .= "{$sub['subscription_id']},{$sub['user_id']},{$sub['plan_id']},{$sub['amount']},{$sub['status']},{$sub['payment_status']},{$sub['created_at']}\n";
            }
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function generateExcel($data, $filename, $reportType = 'all')
    {
        try {
            // Generate Excel file using XlsxGenerator
            $xlsFile = XlsxGenerator::generateAdminSalesReport($data, $reportType);
            
            // Return Excel file download
            return response()->download($xlsFile, $filename, [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Admin Excel report generation failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Excel generation failed. Please try again.'
            ], 500);
        }
    }

    private function generatePDF($data, $filename)
    {
        // For now, return CSV as PDF format
        // In a real implementation, you'd use DomPDF or similar
        return $this->generateCSV($data, str_replace('.pdf', '.csv', $filename));
    }
}




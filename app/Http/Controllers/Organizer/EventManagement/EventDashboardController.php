<?php

namespace App\Http\Controllers\Organizer\EventManagement;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventActivityLog;
use App\Models\TicketPurchase;
use App\Models\EventTicketInformation;
use App\Services\XlsxGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventDashboardController extends Controller
{
    public function index(Event $event)
    {
        \Log::info('EventDashboardController::index - Starting', [
            'event_id' => $event->id,
            'event_title' => $event->title,
            'event_status' => $event->status,
            'event_start_date' => $event->start_date,
            'event_end_date' => $event->end_date,
            'user_id' => Auth::id(),
            'is_admin' => Auth::guard('admin')->check(),
            'request_url' => request()->url(),
            'request_method' => request()->method(),
        ]);

        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        \Log::info('EventDashboardController::index - Access Check', [
            'event_id' => $event->id,
            'is_admin' => $isAdmin,
            'is_owner' => $isOwner,
            'is_admin_event' => $isAdminEvent,
            'is_staff' => $isStaff,
            'has_access' => $hasAccess,
            'event_user_id' => $event->user_id,
            'current_user_id' => Auth::id(),
        ]);
        
        if (!$hasAccess) {
            \Log::warning('EventDashboardController::index - Access Denied', [
                'event_id' => $event->id,
                'user_id' => Auth::id(),
                'event_user_id' => $event->user_id,
            ]);
            abort(403, 'Unauthorized access to this event.');
        }

        $event->load(['tickets', 'activityLogs', 'ticketInformation']);
        
        // Load optional staff notes from R2
        $staffNotes = null;
        try {
            $disk = \Storage::disk('r2');
            $notesPath = "events/{$event->id}/staff_notes.json";
            if ($disk->exists($notesPath)) {
                $json = $disk->get($notesPath);
                $decoded = json_decode($json, true);
                if (is_array($decoded)) {
                    $staffNotes = $decoded;
                }
            }
        } catch (\Throwable $t) {
            // ignore
        }

        // Determine access scope for current user
        $user = Auth::user();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $staff = $event->staff()->where('user_id', $user?->id)->first();
        // Admins have full capabilities
        // Allow staff members to manage events even without explicit permissions
        // Permission checks - staff members need explicit permissions (no automatic access just for being staff)
        $staffRole = $staff?->role ?? null;
        $isOrganizerRole = $staffRole === 'organizer';
        $canManageEvent = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_event ?? false) || $isOrganizerRole;
        $canManageAttendance = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_attendance ?? false) || $isOrganizerRole;
        $canManageNews = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_news ?? false) || $isOrganizerRole;
        $canManageSales = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_sales ?? false) || $isOrganizerRole;
        $canManageTicket = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_ticket ?? false) || $isOrganizerRole;

        // Sales and revenue metrics (only if user can manage sales)
        $salesStats = $canManageSales ? $this->calculateEventSalesStats($event) : null;
        
        // Ticket performance metrics (only if user can manage sales - it shows revenue)
        $ticketPerformance = $canManageSales ? $this->getTicketPerformance($event) : collect();
        
        // Recent sales for this event (only if user can manage sales)
        $recentSales = $canManageSales ? $this->getRecentEventSales($event) : collect();
        
        // Sales trend data (only if user can manage sales)
        $salesTrend = $canManageSales ? $this->getEventSalesTrend($event) : null;
        
        // Event activity trend data
        $activityTrend = $this->getEventActivityTrend($event);
        
        // Basic metrics
        $totalViews = 0; // Placeholder for future analytics
        $totalSaves = $event->savedByUsers()->count();

        $recentActivity = $event->activityLogs()->latest()->limit(10)->with('user')->get();
        $totalActivityCount = $event->activityLogs()->count();

        // Check subscription for graph/analytics visibility
        $subscription = $user?->currentSubscription();
        $plan = $subscription?->getPlan();
        if (!$plan) {
            $plan = \App\Models\SubscriptionPlan::where('slug', 'free')->first();
        }
        // Check if plan has advanced_analytics feature OR is in the legacy pro tier list
        $hasAdvancedAnalytics = $plan && ($plan->advanced_analytics === true);
        $hasProSubscription = $plan && in_array($plan->slug, ['professional', 'enterprise', 'starter', 'pro', 'intermediate']);
        $canViewGraphs = $hasAdvancedAnalytics || $hasProSubscription || $isAdmin;

        // Determine layout type
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        // Load staff settings for staff members
        $staffNotes = $this->loadStaffSettingsFromR2($event->id);

        // Load payout request for owners (only when event has ended and event is not free)
        $payoutRequest = null;
        $canPayout = false;
        if ($isOwner && $event->end_date && now()->gte($event->end_date) && !$event->is_free) {
            $payoutRequest = \App\Models\EventPayoutRequest::where('event_id', $event->id)
                ->orderBy('created_at', 'desc')
                ->first();
            $canPayout = true;
        }

        return view('organizer.events.manage', compact(
            'event',
            'salesStats',
            'ticketPerformance',
            'recentSales',
            'salesTrend',
            'activityTrend',
            'totalViews',
            'totalSaves',
            'recentActivity',
            'totalActivityCount',
            'isOwner',
            'canManageEvent',
            'canManageAttendance',
            'canManageNews',
            'canManageSales',
            'canManageTicket',
            'staffNotes',
            'layoutType',
            'canViewGraphs',
            'payoutRequest',
            'canPayout'
        ));
    }

    public function salesReport(Event $event, Request $request)
    {
        // Access: owner, admin, or sales permission
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $user = Auth::user();
        $staff = $event->staff()->where('user_id', $user?->id)->first();
        
        // Check if user can manage sales (only explicit permission, not just being staff)
        $canManageSales = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_sales ?? false) || ($staff?->role === 'organizer');
        
        if (!$canManageSales) {
            abort(403, 'Unauthorized to view sales report. You need can_manage_sales permission.');
        }

        // Filters
        $status = $request->get('status'); // paid, pending, refunded, cancelled
        $search = trim((string)$request->get('search', ''));
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $sort = $request->get('sort', 'paid_at'); // paid_at, total_amount, quantity
        $dir = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = TicketPurchase::with(['user', 'ticketInformation.user'])
            ->whereHas('ticketInformation', function($q) use ($event) { $q->where('event_id', $event->id); });

        if ($status) {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$search}%"]);
            });
        }
        if ($dateFrom) {
            $query->whereDate('paid_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('paid_at', '<=', $dateTo);
        }

        // Sorting
        $sortable = ['paid_at', 'total_amount', 'quantity'];
        if (!in_array($sort, $sortable, true)) { $sort = 'paid_at'; }
        $query->orderBy($sort, $dir);

        // Clone for totals before pagination - exclude refunded purchases
        $totalAmountQuery = (clone $query)->where('status', '!=', 'refunded');
        $totalAmount = $totalAmountQuery->sum('total_amount');
        
        // Calculate net amount and fees excluding refunded
        // For free events or bypassed payments, exclude fees from calculations
        // Remove orderBy clause for aggregate queries
        $isFreeEvent = $event->is_free ? 1 : 0;
        $totals = $totalAmountQuery->reorder()->selectRaw('
            SUM(total_amount) as gross_revenue,
            SUM(
                CASE 
                    WHEN unit_price = 0 OR payment_method IN (\'bypass\', \'BYPASS\') OR ? = 1 THEN total_amount
                    ELSE COALESCE(net_amount, total_amount)
                END
            ) as net_revenue,
            SUM(
                CASE 
                    WHEN unit_price = 0 OR payment_method IN (\'bypass\', \'BYPASS\') OR ? = 1 THEN 0
                    ELSE COALESCE(transaction_fee, 0)
                END
            ) as total_transaction_fees,
            SUM(
                CASE 
                    WHEN unit_price = 0 OR payment_method IN (\'bypass\', \'BYPASS\') OR ? = 1 THEN 0
                    ELSE COALESCE(vat_amount, 0)
                END
            ) as total_vat
        ', [$isFreeEvent, $isFreeEvent, $isFreeEvent])->first();
        
        $netAmount = max(0, $totals->net_revenue ?? 0); // Ensure non-negative
        $totalFees = max(0, ($totals->total_transaction_fees ?? 0) + ($totals->total_vat ?? 0)); // Ensure non-negative
        
        // Calculate expected earnings based on net amount (net amount - Qricket fee)
        // If event is free or net is zero/non-positive, force zero expected (skip fee calculation)
        if ($event->is_free || $netAmount <= 0) {
            $expectedEarnings = 0;
            $netAmount = 0; // Ensure net amount is 0 for free events
            $totalFees = 0; // Ensure fees are 0 for free events
        } else {
            $qricketFeePercentage = config('app.qricket_fee_percentage', 5);
            $qricketFee = $netAmount * ($qricketFeePercentage / 100);
            $expectedEarnings = max(0, $netAmount - $qricketFee); // clamp to zero
        }
        
        // Ensure totalAmount is non-negative
        $totalAmount = max(0, $totalAmount ?? 0);

        // Free tickets sold for this event
        $freeTicketsSold = \App\Models\TicketPurchase::where('event_id', $event->id)
            ->where('status','paid')
            ->where('unit_price', 0)
            ->sum('quantity');
        
        $sales = $query->paginate(20)->appends($request->query());

        $statuses = ['paid' => 'Paid', 'pending' => 'Pending', 'refunded' => 'Refunded', 'cancelled' => 'Cancelled'];

        // Determine layout type
        $isAdmin = Auth::guard('admin')->check();
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.events.sales', compact('event', 'sales', 'isOwner', 'totalAmount', 'netAmount', 'totalFees', 'expectedEarnings', 'statuses', 'status', 'search', 'dateFrom', 'dateTo', 'sort', 'dir', 'isAdmin', 'layoutType', 'freeTicketsSold'));
    }

    /**
     * Export ticket sales data as XLS with colors
     */
    public function exportSales(Event $event, Request $request)
    {
        // Access: owner, admin, or sales permission
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $user = Auth::user();
        $staff = $event->staff()->where('user_id', $user?->id)->first();
        
        $canManageSales = $isAdmin || $isOwner || $isAdminEvent || ($staff?->can_manage_sales ?? false) || ($staff?->role === 'organizer');
        
        if (!$canManageSales) {
            abort(403, 'Unauthorized to export sales data.');
        }

        try {
            // Apply same filters as salesReport method
            $status = $request->get('status');
            $search = trim((string)$request->get('search', ''));
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $sort = $request->get('sort', 'paid_at');
            $dir = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $query = TicketPurchase::with(['user', 'ticketInformation.user', 'ticketInformation'])
                ->whereHas('ticketInformation', function($q) use ($event) { 
                    $q->where('event_id', $event->id); 
                });

            if ($status) {
                $query->where('status', $status);
            }
            if ($search !== '') {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%{$search}%"]);
                });
            }
            if ($dateFrom) {
                $query->whereDate('paid_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('paid_at', '<=', $dateTo);
            }

            // Sorting
            $sortable = ['paid_at', 'total_amount', 'quantity'];
            if (!in_array($sort, $sortable, true)) { $sort = 'paid_at'; }
            $query->orderBy($sort, $dir);

            // Get all sales data (no pagination for export)
            $sales = $query->get();

            // Generate XLS file
            $xlsFile = XlsxGenerator::generateSalesReport($event, $sales);
            
            // Get filename from the generated file
            $filename = basename($xlsFile);

            // Return XLS file download
            return response()->download($xlsFile, $filename, [
                'Content-Type' => 'application/vnd.ms-excel',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Sales XLS export failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Export failed. Please try again.'
            ], 500);
        }
    }

    public function loadMoreActivity(Event $event, Request $request)
    {
        // Check if user has access to this event (creator, staff, or admin)
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        $offset = $request->get('offset', 0);
        $limit = 10;

        $activities = $event->activityLogs()
            ->latest()
            ->offset($offset)
            ->limit($limit)
            ->with('user')
            ->get();

        $html = '';
        foreach ($activities as $activity) {
            $html .= view('organizer.events.partials.activity-item', compact('activity'))->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $activities->count() === $limit,
            'nextOffset' => $offset + $limit
        ]);
    }

    /**
     * Calculate comprehensive sales statistics for a specific event
     */
    private function calculateEventSalesStats(Event $event)
    {
        // Calculate total earnings from PAID ticket purchases only (exclude refunded)
        // IMPORTANT: Use total_amount (gross revenue BEFORE Xendit fees) - same as payout calculation
        // The sales tab shows total_amount and net_amount separately for transparency
        $totalEarnings = TicketPurchase::where('event_id', $event->id)
            ->where('status', 'paid') // Only paid purchases
            ->sum('total_amount') ?? 0;
        
        // For free events or bypassed payments, exclude fees from calculations
        // Get additional statistics for fee breakdown (exclude refunded)
        // Only count fees for non-free events and non-bypassed payments
        $isFreeEvent = $event->is_free ? 1 : 0;
        $revenueStats = TicketPurchase::whereHas('ticketInformation', function($query) use ($event) {
            $query->where('event_id', $event->id);
        })->where('status', 'paid') // Only paid purchases (refunded have status='refunded')
          ->selectRaw('
            SUM(total_amount) as gross_revenue,
            SUM(
                CASE 
                    WHEN unit_price = 0 OR payment_method IN (\'bypass\', \'BYPASS\') OR ? = 1 THEN total_amount
                    ELSE COALESCE(net_amount, total_amount)
                END
            ) as net_revenue,
            SUM(
                CASE 
                    WHEN unit_price = 0 OR payment_method IN (\'bypass\', \'BYPASS\') OR ? = 1 THEN 0
                    ELSE COALESCE(transaction_fee, 0)
                END
            ) as total_transaction_fees,
            SUM(
                CASE 
                    WHEN unit_price = 0 OR payment_method IN (\'bypass\', \'BYPASS\') OR ? = 1 THEN 0
                    ELSE COALESCE(vat_amount, 0)
                END
            ) as total_vat,
            COUNT(*) as total_orders
        ', [$isFreeEvent, $isFreeEvent, $isFreeEvent])->first();

        $totalRevenue = $revenueStats->gross_revenue ?? 0;
        // Net revenue is already after Xendit fees, now apply Qricket fee (5%)
        // Expected revenue should be based on net amount total, then less by Qricket fee
        $netAmountTotal = max(0, $revenueStats->net_revenue ?? 0); // Ensure non-negative
        // If event is free or net is zero/non-positive, force zero expected (skip fee calculation)
        if ($event->is_free || $netAmountTotal <= 0) {
            $expectedRevenue = 0;
            $netRevenue = 0;
            $totalFees = 0;
            $transactionFees = 0;
            $vatAmount = 0;
        } else {
            $qricketFeePercentage = config('app.qricket_fee_percentage', 5);
            $qricketFee = $netAmountTotal * ($qricketFeePercentage / 100);
            $expectedRevenue = max(0, $netAmountTotal - $qricketFee); // clamp to zero
            $netRevenue = $expectedRevenue; // Use expected revenue as net revenue
            $totalFees = ($revenueStats->total_transaction_fees ?? 0) + ($revenueStats->total_vat ?? 0);
            $transactionFees = $revenueStats->total_transaction_fees ?? 0;
            $vatAmount = $revenueStats->total_vat ?? 0;
        }

        // Total tickets sold
        $totalTicketsSold = EventTicketInformation::where('event_id', $event->id)
            ->whereHas('ticketPurchase', function($query) {
                $query->where('status', 'paid');
            })
            ->count();

        // Free tickets sold (unit_price == 0)
        $freeTicketsSold = TicketPurchase::where('event_id', $event->id)
            ->where('status','paid')
            ->where('unit_price', 0)
            ->sum('quantity');

        // Total tickets checked in
        $totalCheckedIn = EventTicketInformation::where('event_id', $event->id)
            ->whereNotNull('checked_in_at')
            ->count();

        // Conversion rate (checked in / sold)
        $conversionRate = $totalTicketsSold > 0 ? ($totalCheckedIn / $totalTicketsSold) * 100 : 0;

        // Average ticket price
        $averageTicketPrice = $totalTicketsSold > 0 ? $totalRevenue / $totalTicketsSold : 0;

        // Revenue by ticket type
        // Schema changed: ticket_purchases no longer stores ticket_information_id.
        // Fallback to grouping by unit_price when direct relation to event_tickets is unavailable.
        try {
            if (\Schema::hasTable('event_tickets') && \Schema::hasColumn('ticket_purchases', 'ticket_information_id')) {
                $revenueByType = EventTicketInformation::where('event_ticket_information.event_id', $event->id)
                    ->join('ticket_purchases', 'event_ticket_information.ticket_purchase_id', '=', 'ticket_purchases.id')
                    ->join('event_tickets', 'event_ticket_information.event_id', '=', 'event_tickets.event_id')
                    ->where('ticket_purchases.status', 'paid')
                    ->select('event_tickets.name', 'event_tickets.price', DB::raw('COUNT(*) as count'), DB::raw('SUM(event_tickets.price) as revenue'))
                    ->groupBy('event_tickets.id', 'event_tickets.name', 'event_tickets.price')
                    ->get();
            } else {
                // Fallback: group purchases by unit_price
                $revenueByType = TicketPurchase::whereHas('ticketInformation', function($q) use ($event) { $q->where('event_id', $event->id); })
                    ->where('status', 'paid')
                    ->select(DB::raw("CONCAT('Ticket ', FORMAT(unit_price, 2)) as name"), 'unit_price as price', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as revenue'))
                    ->groupBy('unit_price')
                    ->get();
            }
        } catch (\Throwable $e) {
            $revenueByType = collect();
        }

        // Total spent on additional tickets for this event by anyone (paid only)
        try {
            $additionalTicketSpend = \App\Models\TicketLimitPurchase::where('event_id', $event->id)
                ->where('status','paid')
                ->sum('amount');
        } catch (\Throwable $e) { $additionalTicketSpend = 0; }

        return [
            'totalRevenue' => max(0, $totalRevenue), // Ensure non-negative
            'netRevenue' => max(0, $netRevenue ?? 0), // Ensure non-negative
            'expectedRevenue' => max(0, $expectedRevenue ?? 0), // Expected revenue based on net amount less Qricket fee
            'totalFees' => max(0, $totalFees ?? 0), // Ensure non-negative
            'transactionFees' => max(0, $transactionFees ?? 0), // Ensure non-negative
            'vatAmount' => max(0, $vatAmount ?? 0), // Ensure non-negative
            'totalTicketsSold' => $totalTicketsSold,
            'totalCheckedIn' => $totalCheckedIn,
            'conversionRate' => round($conversionRate, 1),
            'averageTicketPrice' => round($averageTicketPrice, 2),
            'revenueByType' => $revenueByType,
            // Calculate average order value (revenue / orders)
            'totalOrders' => $revenueStats->total_orders ?? 0,
            'averageOrderValue' => ($revenueStats->total_orders ?? 0) > 0 ? round($totalRevenue / ($revenueStats->total_orders ?? 1), 2) : 0,
            'thisMonthRevenue' => max(0, $totalRevenue), // Using total revenue as this month's, ensure non-negative
            'additionalTicketSpend' => $additionalTicketSpend,
            'totalCapacity' => $totalTicketsSold + 100, // Mock capacity
            'capacityUtilization' => $totalTicketsSold > 0 ? round(($totalTicketsSold / ($totalTicketsSold + 100)) * 100, 1) : 0,
            'freeTicketsSold' => (int) $freeTicketsSold,
        ];
    }

    private function getTicketPerformance(Event $event)
    {
        return $event->eventTickets()->get()->map(function($ticket) use ($event) {
            // Schema-safe approach: aggregate by event and unit_price (proxy for ticket type)
            $purchasesCount = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->where('unit_price', $ticket->price)
                ->sum('quantity');

            // Per-type checked-in count is not directly joinable without a ticket FK; keep as 0 for now
            $checkedInCount = 0;
            
            return [
                'id' => $ticket->id,
                'name' => $ticket->name,
                'price' => $ticket->price,
                'quantity' => $ticket->quantity,
                'total_sold' => $purchasesCount,
                'checked_in' => $checkedInCount,
                'remaining' => $ticket->quantity ? ($ticket->quantity - $purchasesCount) : null,
                'utilization' => $ticket->quantity > 0 ? round(($purchasesCount / $ticket->quantity) * 100, 1) : 0,
                'total_revenue' => $ticket->price * $purchasesCount,
            ];
        });
    }

    private function getRecentEventSales(Event $event)
    {
        return TicketPurchase::whereHas('ticketInformation', function($query) use ($event) {
            $query->where('event_id', $event->id);
        })
        ->with(['user'])
        ->latest()
        ->limit(10)
        ->get();
    }

    private function getEventSalesTrend(Event $event)
    {
        $trendData = TicketPurchase::whereHas('ticketInformation', function($query) use ($event) {
            $query->where('event_id', $event->id);
        })
        ->where('status', 'paid')
        ->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as sales_count'),
            DB::raw('SUM(total_amount) as daily_revenue')
        )
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return $trendData->map(function($day) {
            return [
                'date' => $day->date,
                'sales' => $day->sales_count,
                'revenue' => $day->daily_revenue,
            ];
        });
    }

    private function getEventActivityTrend(Event $event)
    {
        // Get event views from event activity logs (viewing event pages)
        // Count unique user views per day (one view per user per day)
        // Note: With the update-or-create logic, each user will have at most one view record per day
        $viewsData = DB::table('event_activity_logs')
            ->where('event_id', $event->id)
            ->where('action', 'event_viewed')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as views_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Get event saves
        $savesData = DB::table('saved_events')
            ->where('event_id', $event->id)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as saves_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get ticket purchases
        $purchasesData = DB::table('ticket_purchases')
            ->join('event_ticket_information', 'ticket_purchases.id', '=', 'event_ticket_information.ticket_purchase_id')
            ->where('event_ticket_information.event_id', $event->id)
            ->where('ticket_purchases.status', 'paid')
            ->select(
                DB::raw('DATE(ticket_purchases.created_at) as date'),
                DB::raw('COUNT(*) as purchases_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get activity logs
        $activityData = DB::table('event_activity_logs')
            ->where('event_id', $event->id)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as activities_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Combine all data by date
        $allDates = collect()
            ->merge($viewsData->pluck('date'))
            ->merge($savesData->pluck('date'))
            ->merge($purchasesData->pluck('date'))
            ->merge($activityData->pluck('date'))
            ->unique()
            ->sort();

        return $allDates->map(function($date) use ($viewsData, $savesData, $purchasesData, $activityData) {
            $views = $viewsData->firstWhere('date', $date);
            $saves = $savesData->firstWhere('date', $date);
            $purchases = $purchasesData->firstWhere('date', $date);
            $activities = $activityData->firstWhere('date', $date);

            return [
                'date' => $date,
                'views' => $views ? $views->views_count : 0,
                'saves' => $saves ? $saves->saves_count : 0,
                'purchases' => $purchases ? $purchases->purchases_count : 0,
                'activities' => $activities ? $activities->activities_count : 0,
            ];
        });
    }

    public function getActivityTrend(Event $event)
    {
        // Check if user has access to this event (admin bypass)
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        $isStaff = $event->staff()->where('user_id', Auth::id())->exists();
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $isStaff;
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to activity trend data');
        }

        $activityTrend = $this->getEventActivityTrend($event);
        
        return response()->json([
            'success' => true,
            'data' => $activityTrend,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Load staff settings from R2 storage
     */
    private function loadStaffSettingsFromR2($eventId)
    {
        try {
            $eventDir = "events/{$eventId}";
            $staffSettingsPath = $eventDir . '/staff_settings.json';
            
            if (Storage::disk('r2')->exists($staffSettingsPath)) {
                $staffSettingsJson = Storage::disk('r2')->get($staffSettingsPath);
                return json_decode($staffSettingsJson, true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load staff settings from R2: ' . $e->getMessage());
        }
        
        return [];
    }
}



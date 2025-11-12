<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketLimitPricing;
use App\Models\Discount;
use App\Models\DiscountUsage;
use App\Models\TicketLimitPurchase;
use App\Models\SubscriptionPlan;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /**
     * Display the subscription management dashboard
     */
    public function index(Request $request)
    {
        $section = $request->get('section', 'ticket-pricing');
        
        $subscriptionPlans = SubscriptionPlan::active()->ordered()->get();
        $pricingPlans = TicketLimitPricing::ordered()->get();
        $discounts = Discount::orderBy('created_at', 'desc')->get();
        $recentPurchases = TicketLimitPurchase::with(['user', 'event'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Quick stats for sidebar
        $quickStats = [
            'total_plans' => $pricingPlans->count(),
            'active_discounts' => $discounts->where('is_active', true)->count(),
            'subscribers' => \App\Models\Subscription::where('status', 'active')->count(),
        ];

        // Route to appropriate view based on section
        switch ($section) {
            case 'ticket-pricing':
            case 'pricing':
                return $this->ticketPricing($pricingPlans, $quickStats);
            case 'discounts':
                return $this->discounts($discounts, $quickStats);
            case 'subscriptions':
                return $this->subscriptions($subscriptionPlans, $quickStats);
            default:
                // Default to ticket pricing instead of index page
                return $this->ticketPricing($pricingPlans, $quickStats);
        }
    }

    /**
     * Display ticket pricing management page
     */
    private function ticketPricing($pricingPlans, $quickStats)
    {
        // Calculate analytics data
        $totalPurchases = TicketLimitPurchase::where('status', 'paid')->count();
        $totalRevenue = TicketLimitPurchase::where('status', 'paid')->sum('amount');
        
        // Revenue over time (last 12 months)
        $revenueData = [
            'labels' => [],
            'values' => []
        ];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenueData['labels'][] = $date->format('M Y');
            $revenueData['values'][] = TicketLimitPurchase::where('status', 'paid')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
        }

        // Popular plans data
        $popularPlansData = [
            'labels' => [],
            'values' => []
        ];
        
        foreach ($pricingPlans->take(5) as $plan) {
            $popularPlansData['labels'][] = $plan->name;
            $popularPlansData['values'][] = TicketLimitPurchase::where('status', 'paid')
                ->whereHas('pricing', function($query) use ($plan) {
                    $query->where('id', $plan->id);
                })->count();
        }

        return view('admin.service-plans.ticket-pricing', compact(
            'pricingPlans', 'totalPurchases', 'totalRevenue', 'revenueData', 'popularPlansData', 'quickStats'
        ))->with([
            'title' => 'Ticket Pricing Management',
            'pageTitle' => 'Ticket Pricing Management',
            'description' => 'Manage additional ticket pricing plans and analytics'
        ]);
    }

    /**
     * Display discounts management page
     */
    private function discounts($discounts, $quickStats)
    {
        // Calculate analytics data from actual usage records
        $totalUsage = DiscountUsage::count();
        $totalSavings = DiscountUsage::sum('discount_amount') ?? 0;
        
        // Usage over time (last 12 months) - based on actual DiscountUsage records
        $usageData = [
            'labels' => [],
            'values' => []
        ];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->format('M Y');
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $usageData['labels'][] = $monthLabel;
            // Count actual usage records for this month using date range for better accuracy
            $usageCount = DiscountUsage::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $usageData['values'][] = (int) $usageCount;
        }

        // Discount types distribution - based on actual usage, not discount count
        $percentageUsage = (int) DiscountUsage::whereHas('discount', function($query) {
            $query->where('type', 'percentage');
        })->count();
        
        $fixedAmountUsage = (int) DiscountUsage::whereHas('discount', function($query) {
            $query->where('type', 'fixed_amount');
        })->count();

        $discountTypesData = [
            'labels' => ['Percentage', 'Fixed Amount'],
            'values' => [
                $percentageUsage,
                $fixedAmountUsage
            ]
        ];

        // Ensure arrays are always populated (Chart.js needs arrays even if empty)
        if (empty($usageData['labels'])) {
            $usageData['labels'] = [];
            $usageData['values'] = [];
        }
        
        if (empty($discountTypesData['labels'])) {
            $discountTypesData['labels'] = ['Percentage', 'Fixed Amount'];
            $discountTypesData['values'] = [0, 0];
        }

        return view('admin.service-plans.discounts', compact(
            'discounts', 'totalUsage', 'totalSavings', 'usageData', 'discountTypesData', 'quickStats'
        ))->with([
            'title' => 'Discount Management',
            'pageTitle' => 'Discount Management',
            'description' => 'Manage discount codes and promotional campaigns'
        ]);
    }

    /**
     * Display subscriptions management page
     */
    private function subscriptions($subscriptionPlans, $quickStats)
    {
        // Calculate analytics data
        $totalSubscribers = \App\Models\Subscription::where('status', 'active')->count();
        $monthlyRevenue = \App\Models\Subscription::where('status', 'active')
            ->join('subscription_plans', 'subscriptions.subscription_plan_id', '=', 'subscription_plans.id')
            ->sum('subscription_plans.price');
        
        // Subscription growth over time (last 12 months)
        $subscriptionGrowthData = [
            'labels' => [],
            'values' => []
        ];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $subscriptionGrowthData['labels'][] = $date->format('M Y');
            $subscriptionGrowthData['values'][] = \App\Models\Subscription::where('status', 'active')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        // Plan distribution
        $planDistributionData = [
            'labels' => [],
            'values' => []
        ];
        
        foreach ($subscriptionPlans as $plan) {
            $planDistributionData['labels'][] = $plan->name;
            $planDistributionData['values'][] = \App\Models\Subscription::where('status', 'active')
                ->where('subscription_plan_id', $plan->id)
                ->count();
        }

        return view('admin.service-plans.subscriptions', compact(
            'subscriptionPlans', 'totalSubscribers', 'monthlyRevenue', 'subscriptionGrowthData', 'planDistributionData', 'quickStats'
        ))->with([
            'title' => 'Subscription Plans Management',
            'pageTitle' => 'Subscription Plans Management',
            'description' => 'Manage subscription plans and user analytics'
        ]);
    }

    /**
     * Store a new ticket limit pricing plan
     */
    public function storePricing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ticket_quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'type' => 'required|in:free,paid',
        ]);

        // Check unique ticket_quantity per type
        $existing = TicketLimitPricing::where('ticket_quantity', $request->ticket_quantity)
            ->where('type', $request->type)
            ->first();
        
        if ($existing) {
            $validator->errors()->add('ticket_quantity', 'This ticket quantity already exists for ' . $request->type . ' events.');
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        TicketLimitPricing::create($request->all());

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('subscription_pricing_create', [
            'name' => $request->name,
            'ticket_quantity' => $request->ticket_quantity,
            'price' => $request->price,
            'currency' => $request->currency,
            'type' => $request->type,
        ], 'Created ticket limit pricing: ' . $request->name . ' (' . $request->type . ' events)');

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Pricing plan created successfully.');
    }

    /**
     * Update a ticket limit pricing plan
     */
    public function updatePricing(Request $request, TicketLimitPricing $pricing)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ticket_quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'type' => 'required|in:free,paid',
        ]);

        // Check unique ticket_quantity per type
        $existing = TicketLimitPricing::where('ticket_quantity', $request->ticket_quantity)
            ->where('type', $request->type)
            ->where('id', '!=', $pricing->id)
            ->first();
        
        if ($existing) {
            $validator->errors()->add('ticket_quantity', 'This ticket quantity already exists for ' . $request->type . ' events.');
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $pricing->update($request->all());

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Pricing plan updated successfully.');
    }

    /**
     * Delete a ticket limit pricing plan
     */
    public function destroyPricing(TicketLimitPricing $pricing)
    {
        $pricing->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pricing plan deleted successfully.']);
        }

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Pricing plan deleted successfully.');
    }

    /**
     * Store a new discount
     */
    public function storeDiscount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:discounts',
            'type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'applicable_to' => 'required|in:ticket_limit_purchase,subscription,all',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Discount::create($request->all());

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('discount_create', [
            'name' => $request->name,
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'currency' => $request->currency,
        ], 'Created discount: ' . $request->name . ' (' . $request->code . ')');

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Discount created successfully.');
    }

    /**
     * Update a discount
     */
    public function updateDiscount(Request $request, Discount $discount)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:discounts,code,' . $discount->id,
            'type' => 'required|in:percentage,fixed_amount',
            'value' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'applicable_to' => 'required|in:ticket_limit_purchase,subscription,all',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $discount->update($request->all());

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('discount_update', [
            'discount_id' => $discount->id,
            'discount_name' => $discount->name,
            'discount_code' => $discount->code,
            'discount_type' => $discount->type,
            'discount_value' => $discount->value,
            'currency' => $discount->currency,
            'applicable_to' => $discount->applicable_to,
            'is_active' => $discount->is_active,
        ], 'Updated discount: ' . $discount->name . ' (' . $discount->code . ')');

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Discount updated successfully.');
    }

    /**
     * Delete a discount
     */
    public function destroyDiscount(Discount $discount)
    {
        // Log admin activity before deletion
        Auth::guard('admin')->user()->logActivity('discount_delete', [
            'discount_id' => $discount->id,
            'discount_name' => $discount->name,
            'discount_code' => $discount->code,
            'discount_type' => $discount->type,
            'discount_value' => $discount->value,
            'currency' => $discount->currency,
            'applicable_to' => $discount->applicable_to,
            'is_active' => $discount->is_active,
        ], 'Deleted discount: ' . $discount->name . ' (' . $discount->code . ')');

        $discount->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Discount deleted successfully.']);
        }

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Discount deleted successfully.');
    }

    /**
     * Show all ticket limit purchases
     */
    public function purchases()
    {
        $purchases = TicketLimitPurchase::with(['user', 'event'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.subscriptions.purchases', compact('purchases'));
    }

    /**
     * Store a new subscription plan
     */
    public function storePlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'events_per_month' => 'nullable|integer|min:1',
            'tickets_per_event' => 'nullable|integer|min:1',
            'advanced_layouts' => 'boolean',
            'advanced_analytics' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        SubscriptionPlan::create($request->all());

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('subscription_plan_create', [
            'plan_name' => $request->name,
            'plan_slug' => $request->slug,
            'plan_price' => $request->price,
            'events_per_month' => $request->events_per_month,
            'tickets_per_event' => $request->tickets_per_event,
            'features' => [
                'advanced_layouts' => $request->advanced_layouts,
                'advanced_analytics' => $request->advanced_analytics,
            ],
        ], 'Created subscription plan: ' . $request->name);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription plan created successfully.');
    }

    /**
     * Update a subscription plan
     */
    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:subscription_plans,slug,' . $plan->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'events_per_month' => 'nullable|integer|min:1',
            'tickets_per_event' => 'nullable|integer|min:1',
            'advanced_layouts' => 'boolean',
            'advanced_analytics' => 'boolean',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $plan->update($request->all());

        // Log admin activity
        Auth::guard('admin')->user()->logActivity('subscription_plan_update', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_slug' => $plan->slug,
            'plan_price' => $plan->price,
            'events_per_month' => $plan->events_per_month,
            'tickets_per_event' => $plan->tickets_per_event,
            'is_active' => $plan->is_active,
            'features' => [
                'advanced_layouts' => $plan->advanced_layouts,
                'advanced_analytics' => $plan->advanced_analytics,
            ],
        ], 'Updated subscription plan: ' . $plan->name);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription plan updated successfully.');
    }

    /**
     * Delete a subscription plan
     */
    public function destroyPlan(SubscriptionPlan $plan)
    {
        // Log admin activity before deletion
        Auth::guard('admin')->user()->logActivity('subscription_plan_delete', [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_slug' => $plan->slug,
            'plan_price' => $plan->price,
            'events_per_month' => $plan->events_per_month,
            'tickets_per_event' => $plan->tickets_per_event,
            'is_active' => $plan->is_active,
        ], 'Deleted subscription plan: ' . $plan->name);

        $plan->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Subscription plan deleted successfully.']);
        }

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription plan deleted successfully.');
    }
}
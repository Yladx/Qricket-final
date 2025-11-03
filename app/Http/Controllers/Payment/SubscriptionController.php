<?php

namespace App\Http\Controllers\Payment;

use App\Events\AdminDashboardUpdated;
use App\Http\Controllers\Controller;
use App\Mail\PaymentConfirmationMail;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\PaymentIntentService;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::active()->ordered()->get();
        return view('subscription.index', compact('plans'));
    }

    public function plans()
    {
        $plans = SubscriptionPlan::active()->ordered()->get();
        return view('subscription.index', compact('plans'));
    }

    public function current()
    {
        $user = Auth::user();
        $currentSubscription = $user->currentSubscription();
        $plans = SubscriptionPlan::active()->ordered()->get();
        
        return view('subscription.current', compact('currentSubscription', 'plans'));
    }

    public function subscribe(Request $request)
    {
        return $this->create($request);
    }

    public function purchase(Request $request, $planSlug = null)
    {
        $plans = SubscriptionPlan::active()->ordered()->where('slug', '!=', 'enterprise')->get();
        $selectedPlan = null;
        
        // Get current subscription for logged-in user
        $currentSubscription = null;
        if (Auth::check()) {
            $currentSubscription = Auth::user()->currentSubscription();
        }
        
        // Pre-select the plan if provided
        if ($planSlug) {
            $selectedPlan = $plans->firstWhere('slug', $planSlug);
        }
        
        // If no plan selected, default to first plan (but skip current plan and free plan if user has free subscription)
        if (!$selectedPlan) {
            if ($currentSubscription && $currentSubscription->getPlan()) {
                $currentPlan = $currentSubscription->getPlan();
                $currentPlanId = $currentPlan->id;
                $hasFreeSubscription = $currentPlan->slug === 'free' || $currentPlan->price == 0;
                
                // Skip current plan when selecting default
                // Also skip free plan if user has free subscription
                $selectedPlan = $plans->filter(function($plan) use ($currentPlanId, $hasFreeSubscription) {
                    if ($plan->id === $currentPlanId) return false;
                    if ($hasFreeSubscription && ($plan->slug === 'free' || $plan->price == 0)) return false;
                    return true;
                })->first() ?? $plans->first();
            } else {
                $selectedPlan = $plans->first();
            }
        }
        
        // Get available discounts for subscriptions (check applicable_to field)
        // Filter based on usage limits and per-user limits
        $userId = auth()->id();
        $availableDiscounts = \App\Models\Discount::where('is_active', true)
            ->where(function($query) {
                $query->where('applicable_to', 'subscription')
                    ->orWhere('applicable_to', 'all');
            })
            ->get()
            ->filter(function($discount) use ($userId) {
                // Check if discount has expired
                if ($discount->expires_at && now()->gt($discount->expires_at)) {
                    return false;
                }
                
                // Check global usage limit
                if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
                    return false;
                }
                
                // Check per-user limit (only if user is authenticated)
                if ($discount->per_user_limit && $userId) {
                    try {
                        $userUsageCount = \App\Models\Subscription::where('user_id', $userId)
                            ->where('discount_code', $discount->code)
                            ->count();
                        
                        if ($userUsageCount >= $discount->per_user_limit) {
                            return false;
                        }
                    } catch (\Exception $e) {
                        // If there's an error checking user usage, exclude the discount to be safe
                        Log::warning('Error checking discount per-user limit', [
                            'discount_code' => $discount->code,
                            'user_id' => $userId,
                            'error' => $e->getMessage()
                        ]);
                        return false;
                    }
                }
                
                return true;
            })
            ->values(); // Reset keys after filtering
        
        return view('subscription.purchase', compact('plans', 'selectedPlan', 'availableDiscounts', 'currentSubscription'));
    }

    public function cancel(Request $request)
    {
        $user = Auth::user();
        $currentSubscription = $user->currentSubscription();
        
        if (!$currentSubscription) {
            return back()->withErrors(['error' => 'No active subscription found.']);
        }
        
        try {
            // Calculate the end of the current billing period
            $plan = $currentSubscription->getPlan();
            $billingPeriodEnd = null;
            
            if ($plan) {
                switch ($plan->billing_cycle) {
                    case 'monthly':
                        $billingPeriodEnd = now()->addMonth();
                        break;
                    case 'yearly':
                        $billingPeriodEnd = now()->addYear();
                        break;
                    default:
                        $billingPeriodEnd = $currentSubscription->expires_at ?? now()->addMonth();
                }
            } else {
                // Fallback to expires_at or add a month
                $billingPeriodEnd = $currentSubscription->expires_at ?? now()->addMonth();
            }
            
            $currentSubscription->update([
                'status' => 'active', // Keep active until period end
                'cancelled_at' => now(),
                'cancel_at_period_end' => true,
                'cancel_at' => $billingPeriodEnd,
                'cancellation_reason' => $request->input('reason', 'User requested cancellation'),
                'metadata' => array_merge($currentSubscription->metadata ?? [], [
                    'cancel_at_period_end' => true,
                    'cancelled_at' => now()->toISOString(),
                    'cancel_at' => $billingPeriodEnd->toISOString(),
                    'cancellation_reason' => $request->input('reason', 'User requested cancellation'),
                ])
            ]);
            
            return back()->with('success', 'Your subscription has been cancelled and will end on ' . $billingPeriodEnd->format('M j, Y') . '. You can continue using all features until then.');
        } catch (\Exception $e) {
            Log::error('Failed to cancel subscription', [
                'user_id' => $user->id,
                'subscription_id' => $currentSubscription->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Failed to cancel subscription: ' . $e->getMessage()]);
        }
    }

    public function reactivate(Request $request)
    {
        $user = Auth::user();
        $currentSubscription = $user->currentSubscription();
        
        if (!$currentSubscription || !$currentSubscription->isCancelledAtPeriodEnd()) {
            return back()->withErrors(['error' => 'No cancelled subscription found to reactivate.']);
        }
        
        try {
            $currentSubscription->update([
                'cancelled_at' => null,
                'cancel_at_period_end' => false,
                'cancel_at' => null,
                'cancellation_reason' => null,
                'metadata' => array_merge($currentSubscription->metadata ?? [], [
                    'reactivated_at' => now()->toISOString(),
                    'cancel_at_period_end' => false,
                ])
            ]);
            
            return back()->with('success', 'Your subscription has been reactivated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to reactivate subscription', [
                'user_id' => $user->id,
                'subscription_id' => $currentSubscription->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Failed to reactivate subscription: ' . $e->getMessage()]);
        }
    }

    public function toggleAutoRenew(Request $request)
    {
        $user = Auth::user();
        $currentSubscription = $user->currentSubscription();
        
        if (!$currentSubscription) {
            return back()->withErrors(['error' => 'No active subscription found.']);
        }
        
        try {
            $currentAutoRenew = (bool)($currentSubscription->metadata['auto_renew'] ?? true);
            $newAutoRenew = !$currentAutoRenew;
            
            $currentSubscription->update([
                'metadata' => array_merge($currentSubscription->metadata ?? [], [
                    'auto_renew' => $newAutoRenew,
                    'auto_renew_toggled_at' => now()->toISOString(),
                ])
            ]);
            
            $message = $newAutoRenew ? 'Auto-renewal has been enabled.' : 'Auto-renewal has been disabled.';
            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to toggle auto-renew', [
                'user_id' => $user->id,
                'subscription_id' => $currentSubscription->id,
                'error' => $e->getMessage()
            ]);
            
            return back()->withErrors(['error' => 'Failed to update auto-renewal setting: ' . $e->getMessage()]);
        }
    }

    public function invoice(Subscription $subscription)
    {
        // Allow subscription owner or admin to view the invoice
        $isOwner = $subscription->user_id === Auth::id();
        $isAdmin = Auth::guard('admin')->check();
        
        if (!$isOwner && !$isAdmin) {
            abort(403, 'Unauthorized to view this subscription invoice.');
        }
        
        // Generate invoice data
        $invoiceData = [
            'subscription' => $subscription,
            'plan' => $subscription->getPlan(),
            'user' => $subscription->user,
            'invoice_date' => $subscription->start_date ?? $subscription->starts_at,
            'due_date' => $subscription->end_date ?? $subscription->expires_at,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency ?? 'PHP',
        ];
        
        return view('subscription.invoice', $invoiceData);
    }

    public function status()
    {
        $user = Auth::user();
        $currentSubscription = $user->currentSubscription();
        
        return response()->json([
            'has_subscription' => $currentSubscription !== null,
            'subscription' => $currentSubscription ? [
                'id' => $currentSubscription->id,
                'status' => $currentSubscription->status,
                'plan' => $currentSubscription->getPlan()?->name,
                'amount' => $currentSubscription->amount,
                'currency' => $currentSubscription->currency,
                'start_date' => $currentSubscription->start_date ?? $currentSubscription->starts_at,
                'end_date' => $currentSubscription->end_date ?? $currentSubscription->expires_at,
                'auto_renew' => (bool)($currentSubscription->metadata['auto_renew'] ?? true),
            ] : null
        ]);
    }

    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
                'discount_code' => 'nullable|string|max:20',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check your input.',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }

        try {
            $plan = SubscriptionPlan::findOrFail($request->plan_id);
            
            // Prevent downgrades and free plan selection for free users - check if user has an existing subscription
            $user = $request->user() ?? Auth::user();
            if ($user) {
                $currentSubscription = $user->currentSubscription();
                if ($currentSubscription && $currentSubscription->getPlan()) {
                    $currentPlan = $currentSubscription->getPlan();
                    
                    // Prevent free plan selection if user already has free plan
                    if (($plan->slug === 'free' || $plan->price == 0) && ($currentPlan->slug === 'free' || $currentPlan->price == 0)) {
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'You already have the Free plan. Please select a paid plan to upgrade.'
                            ], 400);
                        }
                        return back()->withErrors(['error' => 'You already have the Free plan. Please select a paid plan to upgrade.']);
                    }
                    
                    // Check if the new plan has a lower sort_order (downgrade)
                    if ($plan->sort_order < $currentPlan->sort_order) {
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Downgrades are not allowed. You can only upgrade to a higher plan or maintain your current plan.'
                            ], 400);
                        }
                        return back()->withErrors(['error' => 'Downgrades are not allowed. You can only upgrade to a higher plan or maintain your current plan.']);
                    }
                }
            }
            
            $discountCode = $request->discount_code ? strtoupper(trim($request->discount_code)) : null;
            $discount = null;
            $finalPrice = $plan->price;

            // Validate and apply discount if provided
            if ($discountCode) {
                $discount = \App\Models\Discount::where('code', $discountCode)
                    ->where('is_active', true)
                    ->first();

                if ($discount) {
                    // Check if discount has expired
                    if ($discount->expires_at && now()->gt($discount->expires_at)) {
                        Log::warning('Discount code expired', [
                            'code' => $discountCode,
                            'expires_at' => $discount->expires_at,
                            'user_id' => auth()->id()
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => 'This discount code has expired'
                        ], 400);
                    }

                    // Check usage limit
                    if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
                        Log::warning('Discount code usage limit reached', [
                            'code' => $discountCode,
                            'used_count' => $discount->used_count,
                            'usage_limit' => $discount->usage_limit,
                            'user_id' => auth()->id()
                        ]);
                        return response()->json([
                            'success' => false,
                            'message' => 'This discount code has reached its usage limit'
                        ], 400);
                    }

                    // Check per-user limit
                    if ($discount->per_user_limit) {
                        $userUsageCount = \App\Models\Subscription::where('user_id', auth()->id())
                            ->where('discount_code', $discountCode)
                            ->count();

                        if ($userUsageCount >= $discount->per_user_limit) {
                            Log::warning('User discount code limit reached', [
                                'code' => $discountCode,
                                'user_id' => auth()->id(),
                                'usage_count' => $userUsageCount,
                                'per_user_limit' => $discount->per_user_limit
                            ]);
                            return response()->json([
                                'success' => false,
                                'message' => 'You have already used this discount code the maximum number of times'
                            ], 400);
                        }
                    }

                    // Calculate discounted price
                    if ($discount->type === 'percentage') {
                        $finalPrice = $plan->price * (1 - $discount->value / 100);
                    } elseif ($discount->type === 'fixed') {
                        $finalPrice = max(0, $plan->price - $discount->value);
                    }
                } else {
                    Log::warning('Invalid discount code', [
                        'code' => $discountCode,
                        'user_id' => auth()->id()
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid discount code'
                    ], 400);
                }
            }

            // Ensure final price is not zero or negative
            if ($finalPrice <= 0) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid subscription amount. The final price after discount cannot be zero or negative.'
                    ], 400);
                }
                return back()->withErrors(['error' => 'Invalid subscription amount. The final price after discount cannot be zero or negative.']);
            }

            $user = $request->user() ?? Auth::user();
            
            // Validate user is authenticated
            if (!$user) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You must be logged in to purchase a subscription.'
                    ], 401);
                }
                return redirect()->route('login')->withErrors(['error' => 'You must be logged in to purchase a subscription.']);
            }
            
            Log::info('Creating invoice for subscription', [
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'user_name' => $user?->full_name,
                'plan_id' => $plan->id,
                'plan_slug' => $plan->slug,
                'plan_price' => $plan->price,
                'discount_code' => $discountCode,
                'discount_id' => $discount?->id,
                'final_price' => $finalPrice
            ]);
            
            // Free plan cannot be purchased through checkout UI
            if ($plan->price == 0 || $plan->slug === 'free') {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The Free plan cannot be purchased. Please select a paid plan.'
                    ], 400);
                }
                return back()->withErrors(['error' => 'The Free plan cannot be purchased. Please select a paid plan.']);
            }
            
            // Bypass payments in development if configured
            if (config('services.xendit.bypass_payment')) {
                // Cancel any existing subscription
                $existingSubscription = $user->currentSubscription();
                if ($existingSubscription) {
                    $existingSubscription->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);
                }

                // Create subscription and mark as paid without calling Xendit
                // Calculate fees even for bypass payments (for consistency)
                $paymentMethod = 'BYPASS';
                $fees = \App\Services\XenditFeeCalculator::calculateFees($finalPrice, $paymentMethod);
                
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'subscription_plan_id' => $plan->id,
                    'plan_id' => $plan->slug, // Legacy field
                    'status' => 'active',
                    'starts_at' => now(),
                    'expires_at' => $plan->slug === 'enterprise' ? null : now()->addMonth(),
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'paid',
                    'amount' => $finalPrice,
                    'currency' => $plan->currency,
                    'start_date' => now(),
                    'end_date' => $plan->slug === 'enterprise' ? null : now()->addMonth(),
                    'discount_code' => $discountCode,
                    'transaction_fee' => $fees['transaction_fee'],
                    'vat_amount' => $fees['vat_amount'],
                    'net_amount' => $fees['net_amount'],
                    'total_fees' => $fees['total_fees'],
                    'metadata' => [
                        'upgraded_at' => now(),
                        'previous_plan' => $existingSubscription?->getPlan()?->slug,
                        'bypassed_payment' => true,
                        'original_price' => $plan->price,
                        'discount_applied' => $discount ? [
                            'code' => $discount->code,
                            'type' => $discount->type,
                            'value' => $discount->value,
                            'savings' => $plan->price - $finalPrice
                        ] : null,
                    ],
                ]);

                // Record discount usage if discount was applied
                if ($discount && $discountCode) {
                    try {
                        $discount->incrementUsage();
                        $discount->recordUsage(
                            $user->id,
                            'subscription',
                            $subscription->id,
                            Subscription::class,
                            $plan->price - $finalPrice,
                            $plan->price
                        );
                } catch (\Exception $e) {
                        Log::error('Failed to record discount usage: ' . $e->getMessage());
                    }
                }

                // Log user activity
                try {
                    ActivityLog::create([
                    'user_id' => $user->id,
                        'action' => 'subscription_purchased',
                        'description' => "User purchased {$plan->name} subscription plan",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    'metadata' => [
                            'subscription_id' => $subscription->id,
                            'plan_name' => $plan->name,
                            'plan_slug' => $plan->slug,
                            'amount' => $finalPrice,
                            'currency' => $plan->currency,
                            'discount_applied' => $discount ? true : false,
                            'discount_code' => $discountCode,
                            'payment_method' => 'bypass',
                            'timestamp' => now()->toISOString()
                        ]
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to log subscription purchase activity: ' . $e->getMessage());
                }

                // Trigger admin sales report update
                try {
                    $reportsController = new \App\Http\Controllers\Admin\ReportsController();
                    $reportsController->triggerSalesUpdate(null);
                } catch (\Exception $e) {
                    Log::error('Failed to trigger admin sales update after bypass subscription: ' . $e->getMessage());
                }

                // Handle AJAX requests by returning JSON success
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => "Successfully subscribed to the {$plan->name} plan (payment bypass).",
                        'redirect_url' => route('subscription.success')
                    ]);
                }

                return redirect()->route('subscription.success')
                    ->with('success', "Successfully subscribed to the {$plan->name} plan (payment bypass).");
            }

            // Create Xendit invoice for paid plans
            $customerInfo = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address
            ];
            
            // Validate customer info
            if (empty($customerInfo['first_name']) || empty($customerInfo['last_name']) || 
                empty($customerInfo['email']) || empty($customerInfo['phone']) || empty($customerInfo['address'])) {
                throw new \Exception('Invalid input data: All customer information fields are required.');
            }
            
            $invoice = $this->createXenditInvoice($plan, $user, $discountCode, $discount, $finalPrice, $customerInfo);

            // Validate invoice response
            if (!$invoice || !is_array($invoice) || empty($invoice['invoice_url'])) {
                throw new \Exception('Invalid invoice response from payment gateway. Please try again.');
            }

            // Cache payment intent (no DB writes for paid plans)
            try {
                $intent = [
                    'type' => 'subscription',
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'plan_slug' => $plan->slug,
                    'discount_code' => $discountCode,
                    'final_price' => $finalPrice,
                    'original_price' => $plan->price,
                    'customer' => $customerInfo,
                    'created_at' => now()->toIso8601String(),
                ];
                $invoiceId = is_array($invoice) ? ($invoice['id'] ?? ($invoice['data']['id'] ?? $invoice['external_id'] ?? null)) : null;
                if ($invoiceId) {
                    (new PaymentIntentService())->store($invoiceId, $intent);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to store subscription payment intent', ['error' => $e->getMessage()]);
            }

            // Handle AJAX requests by returning redirect URL
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $invoice['invoice_url'],
                    'message' => 'Redirecting to payment page...'
                ]);
            }

            // Redirect directly to Xendit payment page
            return redirect($invoice['invoice_url']);
            
        } catch (\Exception $e) {
            $user = $request->user() ?? Auth::user();
            
            // Extract user-friendly error message
            $errorMessage = $e->getMessage();
            $userFriendlyMessage = 'Error processing subscription. Please try again.';
            
            // Provide more specific error messages for common issues
            if (str_contains($errorMessage, 'Invalid input data')) {
                $userFriendlyMessage = 'Invalid subscription amount. Please check your discount code and try again.';
            } elseif (str_contains($errorMessage, 'Invalid invoice response')) {
                $userFriendlyMessage = 'Payment gateway error. Please try again or contact support if the issue persists.';
            } elseif (str_contains($errorMessage, 'Failed to create payment invoice')) {
                $userFriendlyMessage = 'Unable to create payment invoice. Please verify your payment details and try again.';
            }
            
            Log::error('Failed to create invoice for subscription', [
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'plan_id' => $request->plan_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Handle AJAX requests with JSON error response
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $userFriendlyMessage,
                    'error' => $errorMessage // Include technical error for debugging
                ], 500);
            }
            
            return back()->withErrors(['error' => $userFriendlyMessage]);
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        $token = $request->header('x-callback-token');
        $signature = $request->header('x-xendit-signature');

        // Save webhook data to local storage for debugging
        $this->saveWebhookToStorage($payload, $token);

        // Validate webhook token
        if ($token !== config('services.xendit.callback_token')) {
            Log::warning('Invalid webhook token received', [
                'received_token' => $token,
                'expected_token' => config('services.xendit.callback_token'),
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Verify webhook signature if available
        if ($signature && !$this->verifyWebhookSignature(request(), $signature)) {
            Log::warning('Invalid webhook signature received', [
                'received_signature' => $signature,
                'payload' => $payload
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        Log::info('Xendit webhook received', $payload);

        // Determine event type from payload
        $eventType = $this->determineEventType($payload);
        Log::info('Event type determined', ['event_type' => $eventType]);

        try {
            switch ($eventType) {
                case 'invoice.paid':
                case 'payment.succeeded':
                case 'payment.completed':
                    return $this->handlePaymentSuccess($payload);
                case 'invoice.expired':
                    return $this->handleInvoiceExpired($payload);
                case 'invoice.cancelled':
                    return $this->handleInvoiceCancelled($payload);
                case 'payment.failed':
                case 'payment.declined':
                    return $this->handlePaymentFailed($payload);
                case 'invoice.voided':
                    return $this->handleInvoiceVoided($payload);
                case 'payment.pending':
                    return $this->handlePaymentPending($payload);
                default:
                    Log::info('Unhandled webhook event type', [
                        'event_type' => $eventType,
                        'payload' => $payload
                    ]);
                    return response()->json(['status' => 'ignored']);
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
     * Verify webhook signature from Xendit
     */
    private function verifyWebhookSignature(Request $request, $signature)
    {
        try {
            $payload = $request->getContent();
            $secretKey = config('services.xendit.webhook_secret');
            
            if (!$secretKey) {
                Log::warning('No webhook secret configured, skipping signature verification');
                return true; // Skip verification if no secret configured
            }

            $expectedSignature = hash_hmac('sha256', $payload, $secretKey);
            
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Error verifying webhook signature', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Determine the event type from the webhook payload
     */
    private function determineEventType($payload)
    {
        // Check for explicit event type (new Xendit format)
        if (isset($payload['event'])) {
            return $payload['event'];
        }

        // Check for event type in data object
        if (isset($payload['data']['event'])) {
            return $payload['data']['event'];
        }

        // Fallback: Infer from status and other fields (older format)
        $status = $payload['status'] ?? $payload['data']['status'] ?? null;
        $type = $payload['type'] ?? $payload['data']['type'] ?? null;

        if ($type === 'INVOICE') {
            switch (strtoupper($status)) {
                case 'PAID':
                case 'SUCCEEDED':
                    return 'invoice.paid';
                case 'EXPIRED':
                    return 'invoice.expired';
                case 'CANCELLED':
                case 'CANCELED':
                    return 'invoice.cancelled';
                case 'VOIDED':
                    return 'invoice.voided';
                case 'PENDING':
                    return 'payment.pending';
            }
        }

        if ($type === 'PAYMENT') {
            switch (strtoupper($status)) {
                case 'COMPLETED':
                case 'SUCCEEDED':
                case 'PAID':
                    return 'payment.succeeded';
                case 'FAILED':
                case 'DECLINED':
                    return 'payment.failed';
                case 'PENDING':
                    return 'payment.pending';
            }
        }

        // Additional checks for different payload structures
        if (isset($payload['payment_status'])) {
            switch (strtoupper($payload['payment_status'])) {
                case 'PAID':
                case 'SUCCEEDED':
                case 'COMPLETED':
                    return 'payment.succeeded';
                case 'FAILED':
                case 'DECLINED':
                    return 'payment.failed';
                case 'PENDING':
                    return 'payment.pending';
            }
        }

        return 'unknown';
    }

    /**
     * Handle successful payment events
     */
    private function handlePaymentSuccess($payload)
    {
        // Extract invoice ID from different possible locations
        $invoiceId = $payload['id'] ?? $payload['data']['id'] ?? $payload['invoice_id'] ?? null;
        
        if (!$invoiceId) {
            Log::error('No invoice ID found in payment success payload', ['payload' => $payload]);
            return response()->json(['error' => 'No invoice ID found'], 400);
        }

        Log::info('Processing payment success for invoice', ['invoice_id' => $invoiceId]);

        // Verify payment status with Xendit API
        $verifiedStatus = $this->verifyPaymentStatusWithXendit($invoiceId);
        
        if ($verifiedStatus !== 'PAID' && $verifiedStatus !== 'SUCCEEDED' && $verifiedStatus !== 'COMPLETED') {
            Log::warning('Payment status verification failed', [
                'invoice_id' => $invoiceId,
                'webhook_status' => $payload['status'] ?? 'unknown',
                'verified_status' => $verifiedStatus
            ]);
            return response()->json(['error' => 'Payment verification failed'], 400);
        }

        // Find or create subscription using cached intent first
        $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
        
        if (!$subscription) {
            // Check cache intent
            $intent = (new PaymentIntentService())->get($invoiceId) ?? [];
            if (!empty($intent) && ($intent['type'] ?? null) === 'subscription') {
                $user = \App\Models\User::find($intent['user_id'] ?? 0);
                $plan = \App\Models\SubscriptionPlan::find($intent['plan_id'] ?? 0);
                if ($user && $plan) {
                    // Cancel existing subscription if any
                    if ($user->currentSubscription()) {
                        $user->currentSubscription()->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                    }
                    
                    // Extract payment method from webhook payload
                    $paymentMethod = $payload['payment_method'] ?? $payload['data']['payment_method'] ?? $payload['payment']['method'] ?? 'CREDIT_CARD';
                    $amount = $intent['final_price'] ?? $plan->price;
                    
                    // Calculate fees
                    $fees = \App\Services\XenditFeeCalculator::calculateFees($amount, $paymentMethod);
                    
                    $subscription = Subscription::create([
                        'user_id' => $user->id,
                        'subscription_plan_id' => $plan->id,
                        'plan_id' => $plan->slug,
                        'status' => 'active',
                        'starts_at' => now(),
                        'expires_at' => $plan->slug === 'enterprise' ? null : now()->addMonth(),
                        'payment_method' => $paymentMethod,
                        'payment_status' => 'paid',
                        'amount' => $amount,
                        'currency' => $plan->currency,
                        'start_date' => now(),
                        'end_date' => $plan->slug === 'enterprise' ? null : now()->addMonth(),
                        'discount_code' => $intent['discount_code'] ?? null,
                        'xendit_invoice_id' => $invoiceId,
                        'xendit_payment_id' => $payload['payment_id'] ?? $payload['data']['payment_id'] ?? $payload['payment']['id'] ?? null,
                        'transaction_fee' => $fees['transaction_fee'],
                        'vat_amount' => $fees['vat_amount'],
                        'net_amount' => $fees['net_amount'],
                        'total_fees' => $fees['total_fees'],
                        'metadata' => [
                            'original_price' => $intent['original_price'] ?? $plan->price,
                            'created_from' => 'cache_intent',
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
                            Log::error('Failed to record discount usage in webhook: ' . $e->getMessage());
                        }
                    }
                    
                    // Clear intent after creation
                    (new PaymentIntentService())->forget($invoiceId);
                }
            }
        }

        if (!$subscription) {
            Log::error('Failed to create or find subscription for payment', [
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
                // Extract payment method from webhook payload
                $paymentMethod = $payload['payment_method'] ?? $payload['data']['payment_method'] ?? $payload['payment']['method'] ?? 'CREDIT_CARD';
                
                // Calculate fees using the subscription amount
                $fees = \App\Services\XenditFeeCalculator::calculateFees($subscription->amount, $paymentMethod);
                
                $subscription->update([
                    'status' => 'active',
                    'payment_status' => 'paid',
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['data']['payment_id'] ?? $payload['payment']['id'] ?? null,
                    'payment_method' => $paymentMethod,
                    'transaction_fee' => $fees['transaction_fee'],
                    'vat_amount' => $fees['vat_amount'],
                    'net_amount' => $fees['net_amount'],
                    'total_fees' => $fees['total_fees'],
                ]);

                Log::info('Subscription payment status updated successfully', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id,
                    'payment_status' => 'paid',
                'xendit_payment_id' => $payload['payment_id'] ?? $payload['data']['payment_id'] ?? $payload['payment']['id'] ?? null
            ]);

            // Send payment confirmation email
            if ($subscription->user) {
                $this->sendPaymentConfirmationEmail($subscription);
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
     * Handle invoice expired events
     */
    private function handleInvoiceExpired($payload)
    {
        $invoiceId = $payload['id'] ?? $payload['data']['id'] ?? null;
        
        if ($invoiceId) {
            $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'expired',
                    'payment_status' => 'expired',
                ]);

                Log::info('Subscription marked as expired', [
                    'subscription_id' => $subscription->id,
                    'xendit_invoice_id' => $invoiceId
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle invoice cancelled events
     */
    private function handleInvoiceCancelled($payload)
    {
        $invoiceId = $payload['id'] ?? $payload['data']['id'] ?? null;
        
        if ($invoiceId) {
            $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'cancelled',
                    'payment_status' => 'cancelled',
                ]);

                Log::info('Subscription marked as cancelled', [
                    'subscription_id' => $subscription->id,
                    'xendit_invoice_id' => $invoiceId
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payment failed events
     */
    private function handlePaymentFailed($payload)
    {
        $invoiceId = $payload['id'] ?? $payload['data']['id'] ?? null;
        
        if ($invoiceId) {
            $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'failed',
                    'payment_status' => 'failed',
                ]);

                Log::info('Subscription marked as failed', [
                    'subscription_id' => $subscription->id,
                    'xendit_invoice_id' => $invoiceId
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle invoice voided events
     */
    private function handleInvoiceVoided($payload)
    {
        $invoiceId = $payload['id'] ?? $payload['data']['id'] ?? null;
        
        if ($invoiceId) {
            $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'voided',
                    'payment_status' => 'voided',
                ]);

                Log::info('Subscription marked as voided', [
                    'subscription_id' => $subscription->id,
                    'xendit_invoice_id' => $invoiceId
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Handle payment pending events
     */
    private function handlePaymentPending($payload)
    {
        $invoiceId = $payload['id'] ?? $payload['data']['id'] ?? null;
        
        if ($invoiceId) {
            $subscription = Subscription::where('xendit_invoice_id', $invoiceId)->first();
            
            if ($subscription) {
                $subscription->update([
                    'status' => 'pending',
                    'payment_status' => 'pending',
                ]);

                Log::info('Subscription marked as pending', [
                    'subscription_id' => $subscription->id,
                    'xendit_invoice_id' => $invoiceId
                ]);
            }
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
            $customerData = $payload['customer'] ?? $payload['data']['customer'] ?? [];
            $userEmail = $customerData['email'] ?? $payload['email'] ?? $payload['payer_email'] ?? null;
            
            // Extract customer name from various possible locations
            $firstName = $customerData['given_names'] ?? $customerData['first_name'] ?? $customerData['firstname'] ?? '';
            $lastName = $customerData['surname'] ?? $customerData['last_name'] ?? $customerData['lastname'] ?? '';
            
            // If no separate first/last name, try to split full name
            if (empty($firstName) && empty($lastName)) {
                $fullName = $customerData['name'] ?? $customerData['full_name'] ?? '';
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
                        'password' => bcrypt(str_random(12)), // Temporary password
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
            }

            // If still no user found, create a placeholder user
            if (!$user) {
                $generatedEmail = 'user_' . time() . '_' . rand(1000, 9999) . '@webhook.local';
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $generatedEmail,
                    'password' => bcrypt(str_random(12)),
                ]);
                
                Log::info('Created placeholder user for webhook', [
                    'user_id' => $user->id,
                    'email' => $generatedEmail,
                    'name' => $fullName
                ]);
            }

            // Validate and update user data if user exists
            if ($user) {
                $user = $this->validateAndUpdateUserData($user, $customerData);
            }

            // Determine plan from amount and items
            $amount = $payload['amount'] ?? $payload['data']['amount'] ?? $payload['payment']['amount'] ?? null;
            $planSlug = $this->inferPlanFromAmount($amount);
            
            // If we have items array, try to get plan from there
            if (isset($payload['items']) && is_array($payload['items']) && !empty($payload['items'])) {
                $item = $payload['items'][0]; // Get first item
                $itemName = strtolower($item['name'] ?? '');
                
                if (strpos($itemName, 'basic') !== false) {
                    $planSlug = 'basic';
                } elseif (strpos($itemName, 'pro') !== false) {
                    $planSlug = 'pro';
                } elseif (strpos($itemName, 'enterprise') !== false) {
                    $planSlug = 'enterprise';
                } elseif (strpos($itemName, 'free') !== false) {
                    $planSlug = 'free';
                }
                
                Log::info('Plan determined from items array', [
                    'item_name' => $item['name'] ?? 'unknown',
                    'inferred_plan' => $planSlug
                ]);
            }
            
            // Find the subscription plan by slug
            $subscriptionPlan = SubscriptionPlan::where('slug', $planSlug)->first();
            if (!$subscriptionPlan) {
                Log::error('Subscription plan not found', ['plan_slug' => $planSlug]);
                return null;
            }
            
            // Extract invoice ID from various locations
            $invoiceId = $payload['id'] ?? $payload['data']['id'] ?? $payload['invoice_id'] ?? null;
            
            // Extract payment ID from various locations
            $paymentId = $payload['payment_id'] ?? $payload['data']['payment_id'] ?? $payload['payment']['id'] ?? null;

            // Extract currency
            $currency = $payload['currency'] ?? $payload['data']['currency'] ?? $payload['payment']['currency'] ?? 'PHP';

            // Extract payment method
            $paymentMethod = $payload['payment_method'] ?? $payload['data']['payment_method'] ?? $payload['payment']['method'] ?? 'CREDIT_CARD';
            
            // Calculate fees
            $fees = \App\Services\XenditFeeCalculator::calculateFees($amount, $paymentMethod);

            $subscription = Subscription::create([
                'user_id' => $user->id, // Always ensure we have a valid user_id
                'subscription_plan_id' => $subscriptionPlan->id,
                'plan_id' => $planSlug, // Keep legacy field
                'status' => 'active',
                'xendit_invoice_id' => $invoiceId,
                'xendit_payment_id' => $paymentId,
                'amount' => $amount,
                'currency' => $currency,
                'start_date' => now(),
                'end_date' => $subscriptionPlan->slug === 'enterprise' ? null : now()->addMonth(),
                'starts_at' => now(),
                'expires_at' => $subscriptionPlan->slug === 'enterprise' ? null : now()->addMonth(),
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentId,
                'transaction_fee' => $fees['transaction_fee'],
                'vat_amount' => $fees['vat_amount'],
                'net_amount' => $fees['net_amount'],
                'total_fees' => $fees['total_fees'],
            ]);

            Log::info('Subscription created from webhook', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'plan_id' => $subscription->plan_id,
                'invoice_id' => $invoiceId,
                'payment_id' => $paymentId,
                'amount' => $amount,
                'currency' => $currency
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
     * Validate and update user data from webhook
     */
    private function validateAndUpdateUserData($user, $customerData)
    {
        try {
            $updates = [];
            
            // Extract name data
            $firstName = $customerData['given_names'] ?? $customerData['first_name'] ?? $customerData['firstname'] ?? '';
            $lastName = $customerData['surname'] ?? $customerData['last_name'] ?? $customerData['lastname'] ?? '';
            
            // If no separate first/last name, try to split full name
            if (empty($firstName) && empty($lastName)) {
                $fullName = $customerData['name'] ?? $customerData['full_name'] ?? '';
                if (!empty($fullName)) {
                    $nameParts = explode(' ', trim($fullName), 2);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';
                }
            }

            // Update first name if we have new data and current is empty or different
            if (!empty($firstName) && (empty($user->first_name) || $user->first_name !== $firstName)) {
                $updates['first_name'] = $firstName;
            }

            // Update last name if we have new data and current is empty or different
            if (!empty($lastName) && (empty($user->last_name) || $user->last_name !== $lastName)) {
                $updates['last_name'] = $lastName;
            }

            // Update email if provided and different
            $email = $customerData['email'] ?? null;
            if ($email && $user->email !== $email) {
                // Check if email is already taken by another user
                $existingUser = User::where('email', $email)->where('id', '!=', $user->id)->first();
                if (!$existingUser) {
                    $updates['email'] = $email;
                } else {
                    Log::warning('Email already taken by another user', [
                        'current_user_id' => $user->id,
                        'existing_user_id' => $existingUser->id,
                        'email' => $email
                    ]);
                }
            }

            // Apply updates if any
            if (!empty($updates)) {
                $user->update($updates);
                
                Log::info('User data updated from webhook', [
                    'user_id' => $user->id,
                    'updates' => $updates
                ]);
            }

            return $user;

        } catch (\Exception $e) {
            Log::error('Failed to validate and update user data', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'customer_data' => $customerData
            ]);
            return $user;
        }
    }

    /**
     * Infer plan from amount
     */
    private function inferPlanFromAmount($amount)
    {
        $planSlug = match((float)$amount) {
            0.0, 0 => 'free',
            10.0, 10 => 'basic',
            25.0, 25 => 'pro',
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
                    $plan = $subscription->getPlan();
                    if (!$plan) {
                        Log::error('Plan not found for subscription', ['subscription_id' => $subscription->id]);
                        return;
                    }
                    
                    Mail::to($subscription->user->email)
                        ->send(new PaymentConfirmationMail($subscription, $plan->toArray()));
                    
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
     * Save webhook data to local storage for debugging
     */
    private function saveWebhookToStorage($payload, $token)
    {
        try {
            // Create webhooks directory if it doesn't exist
            $webhooksDir = storage_path('app/webhooks');
            if (!file_exists($webhooksDir)) {
                mkdir($webhooksDir, 0755, true);
            }

            // Create a filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $status = $payload['status'] ?? 'unknown';
            $invoiceId = $payload['id'] ?? 'unknown';
            $filename = "webhook_{$timestamp}_{$status}_{$invoiceId}.json";
            $filepath = $webhooksDir . '/' . $filename;

            // Prepare data to save
            $dataToSave = [
                'webhook_data' => $payload,
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


    private function createXenditInvoice($plan, $user, $discountCode = null, $discount = null, $finalPrice = null, $customerInfo = null)
    {
        try {
            $amount = $finalPrice ?? $plan->price;
            
            // Validate amount - Xendit requires amount > 0
            if ($amount <= 0) {
                throw new \Exception('Invalid input data: Subscription amount must be greater than zero. Cannot create invoice for zero or negative amount.');
            }
            
            $items = [
                [
                    'name' => $plan->name,
                    'quantity' => 1,
                    'price' => $amount,
                    'category' => 'Subscription',
                ],
            ];

            // Add discount line item if applicable
            if ($discount && $discountCode) {
                $discountAmount = $plan->price - $amount;
                if ($discountAmount > 0) {
                    $items[] = [
                        'name' => "Discount ({$discountCode})",
                        'quantity' => 1,
                        'price' => -$discountAmount, // Negative amount for discount
                        'category' => 'Discount',
                    ];
                }
            }

            $response = Http::withBasicAuth(config('services.xendit.secret_key'), '')
                ->post('https://api.xendit.co/v2/invoices', [
                    'external_id' => 'subscription-' . uniqid(),
                    'amount' => $amount,
                    'description' => "Subscription to {$plan->name}" . ($discountCode ? " (Discount: {$discountCode})" : ''),
                    'invoice_duration' => 1800, // 30 minutes
                    'customer' => [
                        'given_names' => $customerInfo ? $customerInfo['first_name'] : $user->first_name,
                        'family_name' => $customerInfo ? $customerInfo['last_name'] : $user->last_name,
                        'email' => $customerInfo ? $customerInfo['email'] : $user->email,
                        'mobile_number' => $customerInfo ? $customerInfo['phone'] : $user->phone,
                        'addresses' => $customerInfo ? [
                            [
                                'country' => 'Philippines',
                                'street_line1' => $customerInfo['address'],
                                'city' => 'Manila',
                                'postal_code' => '1000'
                            ]
                        ] : null
                    ],
                    'success_redirect_url' => route('subscription.success'),
                    'failure_redirect_url' => route('subscription.failure'),
                    'currency' => $plan->currency,
                    'payment_methods' => ['CREDIT_CARD', 'GCASH', 'PAYMAYA', 'SHOPEEPAY'],
                    'items' => $items,
                ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? 'Unknown error';
                
                // Parse Xendit validation errors if present
                if (isset($errorBody['errors'])) {
                    $validationErrors = [];
                    foreach ($errorBody['errors'] as $error) {
                        if (is_array($error)) {
                            $validationErrors[] = $error['message'] ?? json_encode($error);
                        } else {
                            $validationErrors[] = $error;
                        }
                    }
                    if (!empty($validationErrors)) {
                        $errorMessage = 'Validation error: ' . implode(', ', $validationErrors);
                    }
                }
                
                Log::error('Xendit invoice creation failed', [
                    'status' => $response->status(),
                    'body' => $errorBody,
                    'plan' => $plan,
                    'user' => $user->email,
                    'amount' => $amount,
                    'final_price' => $finalPrice,
                    'api_key' => substr(config('services.xendit.secret_key'), 0, 5) . '...' // Log only first 5 chars for security
                ]);
                
                // Provide user-friendly error message based on status code
                if ($response->status() === 400) {
                    throw new \Exception('Invalid input data: ' . $errorMessage);
                } elseif ($response->status() === 401) {
                    throw new \Exception('Payment gateway authentication failed. Please contact support.');
                } elseif ($response->status() === 500) {
                    throw new \Exception('Payment gateway error. Please try again later.');
                } else {
                    throw new \Exception('Failed to create payment invoice: ' . $errorMessage);
                }
            }

            $invoiceData = $response->json();
            
            // Save invoice JSON to local storage
            $this->saveInvoiceToStorage($invoiceData, $user, $plan);

            Log::info('Xendit invoice created (no DB pending row)', [
                'user_id' => $user->id,
                'invoice_id' => $invoiceData['id'] ?? null,
                'plan_id' => $plan->id
            ]);

            return $invoiceData;
        } catch (\Exception $e) {
            Log::error('Exception in createXenditInvoice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Save invoice JSON data to local storage for debugging
     */
    private function saveInvoiceToStorage($invoiceData, $user, $plan)
    {
        try {
            // Create invoices directory if it doesn't exist
            $invoicesDir = storage_path('app/invoices');
            if (!file_exists($invoicesDir)) {
                mkdir($invoicesDir, 0755, true);
            }

            // Create a filename with timestamp and user info
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = "invoice_{$timestamp}_{$user->id}_{$plan['id']}.json";
            $filepath = $invoicesDir . '/' . $filename;

            // Prepare data to save (exclude sensitive information)
            $dataToSave = [
                'invoice_data' => $invoiceData,
                'user_info' => [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                ],
                'plan_info' => $plan,
                'created_at' => now()->toISOString(),
                'file_info' => [
                    'filename' => $filename,
                    'saved_at' => now()->toISOString(),
                ]
            ];

            // Save to JSON file
            file_put_contents($filepath, json_encode($dataToSave, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            Log::info('Invoice JSON saved to local storage', [
                'filepath' => $filepath,
                'invoice_id' => $invoiceData['id'] ?? null,
                'user_id' => $user->id,
                'plan_id' => $plan['id']
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save invoice JSON to storage', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceData['id'] ?? null,
                'user_id' => $user->id
            ]);
        }
    }

    public function success()
    {
        return view('subscription.success');
    }

    public function failure()
    {
        return view('subscription.failure');
    }

    /**
     * Manually check and update payment status for a subscription
     * This can be used to fix subscriptions stuck in pending status
     */
    public function checkPaymentStatus(Request $request, $subscriptionId)
    {
        $subscription = Subscription::with('user')->findOrFail($subscriptionId);
        
        if ($subscription->payment_status === 'paid') {
            return response()->json([
                'message' => 'Subscription is already paid',
                'subscription' => $subscription
            ]);
        }

        // Check with Xendit API for payment status
        try {
            $response = Http::withBasicAuth(config('services.xendit.secret_key'), '')
                ->get("https://api.xendit.co/v2/invoices/{$subscription->xendit_invoice_id}");

            if ($response->successful()) {
                $invoiceData = $response->json();
                
                if ($invoiceData['status'] === 'PAID') {
                    // Update subscription status
                    $subscription->update([
                        'status' => 'active',
                        'payment_status' => 'paid',
                        'xendit_payment_id' => $invoiceData['payment_id'] ?? null,
                    ]);

                    // Send payment confirmation email
                    $plan = $subscription->getPlan();
                    if ($plan) {
                        Mail::to($subscription->user->email)
                            ->send(new PaymentConfirmationMail($subscription, $plan->toArray()));
                    }

                    return response()->json([
                        'message' => 'Payment status updated to paid',
                        'subscription' => $subscription->fresh(),
                        'invoice_status' => $invoiceData['status']
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Payment is still pending',
                        'subscription' => $subscription,
                        'invoice_status' => $invoiceData['status']
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Failed to check payment status',
                    'error' => $response->json()
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error checking payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
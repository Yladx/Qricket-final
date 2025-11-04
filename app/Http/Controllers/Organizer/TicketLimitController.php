<?php

namespace App\Http\Controllers\Organizer;

use App\Events\AdminDashboardUpdated;
use App\Events\SalesReportUpdated;
use App\Events\DashboardStatsUpdated;
use App\Http\Controllers\Controller;
use App\Models\TicketLimitPricing;
use App\Models\TicketLimitPurchase;
use App\Models\Discount;
use App\Models\Event;
use App\Models\User;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdditionalTicketPurchaseConfirmation;

class TicketLimitController extends Controller
{
    /**
     * Show the ticket limit purchase page for an event
     */
    public function show(Event $event)
    {
        // Check if user has access to this event
        $isAdmin = Auth::guard('admin')->check();
        $isOwner = $event->user_id === Auth::id();
        $isAdminEvent = $event->user_id === null && $isAdmin;
        
        $hasAccess = $isAdmin || $isOwner || $isAdminEvent || $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        // Filter pricing plans based on event type (free or paid)
        $eventType = $event->is_free ? 'free' : 'paid';
        $pricingPlans = TicketLimitPricing::active()
            ->where('type', $eventType)
            ->ordered()
            ->get();
        $userId = auth()->id();
        $discounts = Discount::where('is_active', true)
            ->where(function($query) {
                $query->where('applicable_to', 'ticket_limit_purchase')
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
                        $userUsageCount = TicketLimitPurchase::where('user_id', $userId)
                            ->where('discount_code', $discount->code)
                            ->count();
                        
                        if ($userUsageCount >= $discount->per_user_limit) {
                            return false;
                        }
                    } catch (\Exception $e) {
                        // If there's an error checking user usage, exclude the discount to be safe
                        \Log::warning('Error checking discount per-user limit', [
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

        // Get all additional ticket purchases for this event
        $additionalPurchases = TicketLimitPurchase::where('event_id', $event->id)
            ->where('status', 'paid')
            ->with(['user', 'ticketLimitPricing'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalAdditionalTickets = $additionalPurchases->sum('additional_tickets');
        $totalRevenue = $additionalPurchases->sum('net_amount');

        // Determine layout type based on access
        $layoutType = $isAdmin ? 'admin' : 'organizer';

        return view('organizer.ticket-limits.show', compact('event', 'pricingPlans', 'discounts', 'additionalPurchases', 'totalAdditionalTickets', 'totalRevenue', 'layoutType', 'isAdmin'));
    }

    /**
     * Process ticket limit purchase
     */
    public function purchase(Request $request, Event $event)
    {
        // Check if user has access to this event
        $hasAccess = Auth::guard('admin')->check() || $event->user_id === Auth::id() || 
                    $event->staff()->where('user_id', Auth::id())->exists();
        
        if (!$hasAccess) {
            abort(403, 'Unauthorized access to this event.');
        }

        $validator = Validator::make($request->all(), [
            'pricing_id' => 'required|exists:ticket_limit_pricing,id',
            'discount_code' => 'nullable|string|exists:discounts,code',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:1000',
            'company' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $pricing = TicketLimitPricing::findOrFail($request->pricing_id);
        
        // Validate that the pricing type matches the event type
        $eventType = $event->is_free ? 'free' : 'paid';
        if ($pricing->type !== $eventType) {
            return redirect()->back()
                ->withErrors(['pricing_id' => 'This pricing plan is for ' . $pricing->type . ' events, but your event is ' . $eventType . '.'])
                ->withInput();
        }
        
        $discount = null;
        $finalAmount = $pricing->price;

        // Apply discount if provided
        if ($request->discount_code) {
            $discount = Discount::where('code', $request->discount_code)->first();
            if ($discount && $discount->canBeUsedBy(Auth::id())) {
                $finalAmount = $discount->applyTo($pricing->price);
            } else {
                return redirect()->back()
                    ->withErrors(['discount_code' => 'Invalid or expired discount code, or you have already used this discount.'])
                    ->withInput();
            }
        }

        // Generate unique purchase ID
        $purchaseId = 'additional-ticket-' . time() . '-' . Auth::id();

        // Create purchase record (fees will be calculated when payment is confirmed via webhook)
        $purchase = TicketLimitPurchase::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'ticket_limit_pricing_id' => $pricing->id,
            'purchase_id' => $purchaseId,
            'additional_tickets' => $pricing->ticket_quantity,
            'amount' => $finalAmount,
            'currency' => $pricing->currency,
            'status' => 'pending',
            'payment_method' => 'xendit',
            'discount_code' => $request->discount_code,
            'customer_details' => [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'company' => $request->company,
                'tax_id' => $request->tax_id,
                'pricing_plan' => $pricing->name,
                'original_price' => $pricing->price,
                'discount_applied' => $discount ? $discount->code : null,
                'discount_amount' => $discount ? $discount->getDiscountAmount($pricing->price) : 0,
                'created_at' => now()->toISOString(),
            ],
        ]);

        // Increment discount usage if applied
        if ($discount) {
            $discount->incrementUsage();
            $discount->recordUsage(
                Auth::id(),
                'ticket_limit_purchase',
                $purchase->id,
                TicketLimitPurchase::class,
                $discount->getDiscountAmount($pricing->price),
                $pricing->price
            );
        }

        // Create Xendit invoice for payment
        try {
            $invoiceData = $this->createXenditInvoice($purchase, $pricing, $discount, $request);
            
            if ($invoiceData && isset($invoiceData['invoice_url'])) {
                // Defer DB updates: cache intent, redirect to invoice
                try {
                    $intent = [
                        'type' => 'additional_ticket',
                    'purchase_id' => $purchase->purchase_id,
                    'user_id' => $purchase->user_id,
                        'event_id' => $purchase->event_id,
                    'amount' => $purchase->amount,
                        'created_at' => now()->toIso8601String(),
                    ];
                    (new \App\Services\PaymentIntentService())->store($invoiceData['id'], $intent);
                } catch (\Exception $e) { Log::warning('Cache intent store failed (additional)', ['error' => $e->getMessage()]); }

                // Log activity for admin (without persisting payment fields yet)
                $this->logAdditionalTicketPurchase($purchase, $event);

                return redirect($invoiceData['invoice_url']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create Xendit invoice for additional ticket purchase', [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Fallback: mark as paid manually if Xendit fails
        $purchase->markAsPaid('manual', 'admin_approved');

        // Send confirmation email
        $this->sendConfirmationEmail($purchase);

        // Trigger admin sales report update (this already broadcasts AdminDashboardUpdated with proper metrics)
        try {
            $reportsController = new \App\Http\Controllers\Admin\ReportsController();
            $reportsController->triggerSalesUpdate(null);
        } catch (\Exception $e) {
            Log::error('Failed to trigger admin sales update after ticket limit purchase: ' . $e->getMessage());
        }

        // Broadcast sales report updates for organizer dashboard
        try {
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
                broadcast(new \App\Events\SalesReportUpdated($event->id, $organizerId, $salesData, $eventStats));
                
                // Also broadcast DashboardStatsUpdated for organizer dashboard
                try {
                    $user = \App\Models\User::find($organizerId);
                    if ($user) {
                        $dashboardStats = $this->calculateOrganizerDashboardStats($user);
                        broadcast(new \App\Events\DashboardStatsUpdated($organizerId, $dashboardStats));
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to broadcast organizer dashboard stats: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast additional ticket purchase updates: ' . $e->getMessage());
        }

        return redirect()->route('organizer.events.manage', $event)
            ->with('success', "Successfully purchased {$pricing->ticket_quantity} additional tickets for {$event->title}!");
    }

    /**
     * Validate discount code via AJAX
     */
    public function validateDiscount(Request $request)
    {
        $discount = Discount::where('code', $request->code)->first();
        
        if (!$discount || !$discount->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or expired discount code'
            ]);
        }

        return response()->json([
            'valid' => true,
            'discount' => [
                'name' => $discount->name,
                'type' => $discount->type,
                'value' => $discount->value,
                'formatted_value' => $discount->formatted_value,
            ]
        ]);
    }

    /**
     * Show success page after additional ticket purchase
     */
    public function success($purchaseId)
    {
        $purchase = TicketLimitPurchase::where('purchase_id', $purchaseId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('organizer.ticket-limits.success', compact('purchase'));
    }

    /**
     * Show failure page after additional ticket purchase
     */
    public function failure($purchaseId)
    {
        $purchase = TicketLimitPurchase::where('purchase_id', $purchaseId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('organizer.ticket-limits.failure', compact('purchase'));
    }

    /**
     * Show user's additional ticket purchases
     */
    public function myPurchases()
    {
        $purchases = TicketLimitPurchase::where('user_id', Auth::id())
            ->with(['event', 'ticketLimitPricing'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('organizer.ticket-limits.my-purchases', compact('purchases'));
    }

    /**
     * Show invoice for additional ticket purchase
     */
    public function viewInvoice($purchaseId)
    {
        // Allow admins to view any invoice, but regular users can only view their own
        $isAdmin = Auth::guard('admin')->check();
        
        $query = TicketLimitPurchase::where('purchase_id', $purchaseId)
            ->with(['event', 'ticketLimitPricing', 'user']);
            
        if (!$isAdmin) {
            $query->where('user_id', Auth::id());
        }
        
        $purchase = $query->firstOrFail();

        return view('organizer.ticket-limits.invoice', compact('purchase'));
    }

    /**
     * Create Xendit invoice for additional ticket purchase
     */
    private function createXenditInvoice($purchase, $pricing, $discount, $request)
    {
        try {
            $user = $purchase->user;
            $event = $purchase->event;
            
            $items = [
                [
                    'name' => "Additional Tickets - {$pricing->name}",
                    'category' => 'Additional Tickets',
                    'price' => $purchase->amount,
                    'quantity' => 1,
                ]
            ];

            $invoiceData = [
                'external_id' => $purchase->purchase_id,
                'amount' => $purchase->amount,
                'currency' => $purchase->currency,
                'description' => "Additional tickets purchase for {$event->title}",
                'items' => $items,
                'customer' => [
                    'given_names' => $request->first_name,
                    'surname' => $request->last_name,
                    'email' => $request->email,
                    'mobile_number' => $request->phone,
                    'address' => $request->address,
                    'company' => $request->company,
                    'tax_id' => $request->tax_id,
                ],
                'customer_notification_preference' => [
                    'invoice_created' => ['email'],
                    'invoice_reminder' => ['email'],
                    'invoice_paid' => ['email'],
                ],
                'success_redirect_url' => route('additional-tickets.purchase.success', $purchase->purchase_id),
                'failure_redirect_url' => route('additional-tickets.purchase.failure', $purchase->purchase_id),
                'invoice_duration' => 1800, // 30 minutes
                'payment_methods' => ['CREDIT_CARD', 'GCASH', 'PAYMAYA', 'SHOPEEPAY'],
                'should_send_email' => true,
            ];

            // Log the invoice data being sent to Xendit
            \Log::info('Creating Xendit invoice for additional tickets', [
                    'purchase_id' => $purchase->purchase_id,
                'amount' => $invoiceData['amount'],
                'payment_methods' => $invoiceData['payment_methods'],
                'customer_email' => $invoiceData['customer']['email'],
                'invoice_duration' => $invoiceData['invoice_duration'],
            ]);

            $response = \Illuminate\Support\Facades\Http::withBasicAuth(config('services.xendit.secret_key'), '')
                ->timeout(30)
                ->post('https://api.xendit.co/v2/invoices', $invoiceData);

            if ($response->successful()) {
                $responseData = $response->json();
                \Log::info('Xendit invoice created successfully', [
                    'purchase_id' => $purchase->purchase_id,
                    'invoice_id' => $responseData['id'] ?? 'unknown',
                    'invoice_url' => $responseData['invoice_url'] ?? 'unknown',
                    'available_payment_methods' => $responseData['available_payment_methods'] ?? 'not provided',
                ]);
                return $responseData;
            } else {
                Log::error('Xendit invoice creation failed', [
                    'response' => $response->body(),
                    'status' => $response->status(),
                    'purchase_id' => $purchase->purchase_id
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception creating Xendit invoice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'purchase_id' => $purchase->purchase_id
            ]);
            return null;
        }
    }

    /**
     * Log additional ticket purchase activity for admin
     */
    private function logAdditionalTicketPurchase($purchase, $event)
    {
        try {
            // Log for admin activity
            AdminActivityLog::create([
                'admin_id' => null, // System activity
                'action' => 'additional_ticket_purchase',
                'description' => "Additional ticket purchase created for event: {$event->title}",
                'metadata' => [
                    'purchase_id' => $purchase->purchase_id,
                    'user_id' => $purchase->user_id,
                    'event_id' => $event->id,
                    'event_title' => $event->title,
                    'additional_tickets' => $purchase->additional_tickets,
                    'amount' => $purchase->amount,
                    'currency' => $purchase->currency,
                    'status' => $purchase->status,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log additional ticket purchase activity', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->purchase_id
            ]);
        }
    }

    /**
     * Send confirmation email for additional ticket purchase
     */
    private function sendConfirmationEmail($purchase)
    {
        try {
            if ($purchase->user && $purchase->user->email) {
                Mail::to($purchase->user->email)
                    ->send(new AdditionalTicketPurchaseConfirmation($purchase));
                
                Log::info('Additional ticket purchase confirmation email sent', [
                    'purchase_id' => $purchase->purchase_id,
                    'user_email' => $purchase->user->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send additional ticket purchase confirmation email', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchase->purchase_id,
                'user_email' => $purchase->user->email ?? 'unknown'
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
            $events = Event::where(function($query) use ($user) {
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
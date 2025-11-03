<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id', // Legacy field
        'subscription_plan_id', // New field
        'status',
        'starts_at',
        'expires_at',
        'cancelled_at',
        'cancel_at_period_end',
        'cancel_at',
        'cancellation_reason',
        'payment_method',
        'payment_reference',
        'discount_code',
        'metadata',
        // Legacy fields
        'xendit_invoice_id',
        'xendit_payment_id',
        'amount',
        'currency',
        'start_date',
        'end_date',
        'payment_status',
        // Fee fields
        'transaction_fee',
        'vat_amount',
        'net_amount',
        'total_fees',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancel_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'metadata' => 'array',
        // Legacy fields
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'amount' => 'decimal:2',
        // Fee fields
        'transaction_fee' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'total_fees' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        // Handle both new and legacy plan references
        if ($this->subscription_plan_id) {
            return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
        }
        
        // Fallback to legacy plan_id (if it's a slug)
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id', 'slug');
    }

    /**
     * Get the plan for this subscription (handles both new and legacy systems).
     */
    public function getPlan(): ?SubscriptionPlan
    {
        if ($this->subscription_plan_id) {
            return $this->plan;
        }
        
        // Handle legacy plan_id as slug
        if ($this->plan_id) {
            return SubscriptionPlan::where('slug', $this->plan_id)->first();
        }
        
        return null;
    }

    public function isActive(): bool
    {
        // Check if subscription is cancelled but still within the active period
        if ($this->cancel_at_period_end && $this->cancel_at && $this->cancel_at > now()) {
            return $this->status === 'active' && 
                   $this->starts_at <= now() && 
                   ($this->expires_at === null || $this->expires_at > now());
        }
        
        // Regular active check
        return $this->status === 'active' && 
               $this->starts_at <= now() && 
               ($this->expires_at === null || $this->expires_at > now());
    }

    public function isExpired(): bool
    {
        // If cancelled at period end, check against cancel_at date
        if ($this->cancel_at_period_end && $this->cancel_at) {
            return $this->cancel_at <= now();
        }
        
        // Regular expiration check
        return $this->expires_at !== null && $this->expires_at <= now();
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function isCancelledAtPeriodEnd(): bool
    {
        return $this->cancel_at_period_end && $this->cancel_at !== null;
    }

    public function getEffectiveEndDate(): ?Carbon
    {
        if ($this->cancel_at_period_end && $this->cancel_at) {
            return $this->cancel_at;
        }
        
        return $this->expires_at;
    }

    public function getCancellationStatus(): string
    {
        if ($this->cancel_at_period_end && $this->cancel_at) {
            if ($this->cancel_at > now()) {
                return 'cancelled_at_period_end';
            } else {
                return 'cancelled';
            }
        }
        
        if ($this->cancelled_at) {
            return 'cancelled';
        }
        
        return 'active';
    }

    public function getRemainingDays(): ?int
    {
        $endDate = $this->getEffectiveEndDate();
        if ($endDate === null) {
            return null; // Unlimited
        }
        
        return max(0, now()->diffInDays($endDate, false));
    }

    public function canCreateEvent(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // Check if user has reached monthly published event limit
        $plan = $this->getPlan();
        if ($plan && !$plan->isUnlimited('events_per_month')) {
            $currentMonthStart = now()->startOfMonth();
            $publishedEventsThisMonth = $this->user->events()
                ->where('status', 'published')
                ->where('created_at', '>=', $currentMonthStart)
                ->count();
            
            if ($publishedEventsThisMonth >= $plan->events_per_month) {
                return false;
            }
        }

        return true;
    }

    public function canCreateTicket($ticketCount = 1): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // Check if event has reached ticket limit
        $plan = $this->getPlan();
        if ($plan && !$plan->isUnlimited('tickets_per_event')) {
            // This would need to be called in context of a specific event
            // We'll handle this in the middleware/service
            return true;
        }

        return true;
    }

    public function getEventsRemainingThisMonth(): ?int
    {
        $plan = $this->getPlan();
        if (!$plan || $plan->isUnlimited('events_per_month')) {
            return null;
        }

        $currentMonthStart = now()->startOfMonth();
        $publishedEventsThisMonth = $this->user->events()
            ->where('status', 'published')
            ->where('created_at', '>=', $currentMonthStart)
            ->count();

        return max(0, $plan->events_per_month - $publishedEventsThisMonth);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('starts_at', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }
}
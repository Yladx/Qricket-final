<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TicketLimitPurchase model (orders for additional ticket bundles)
 *
 * Columns:
 * - user_id: buyer
 * - event_id: event receiving additional tickets
 * - ticket_limit_pricing_id: selected bundle
 * - additional_tickets: quantity unlocked by this order
 * - amount, currency: bundle price and currency
 * - status: pending|paid|failed|expired|refunded
 * - payment_method, payment_reference: gateway info
 * - purchase_id: order reference
 * - xendit_invoice_id, xendit_payment_id: provider ids
 * - payment_details, customer_details: payload snapshots
 * - expires_at, paid_at: invoice expiry and payment time
 * - discount_code, metadata: extras
 * - transaction_fee, vat_amount, net_amount, total_fees: fee breakdown
 */
class TicketLimitPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_limit_pricing_id',
        'additional_tickets',
        'amount',
        'currency',
        'status',
        'payment_method',
        'payment_reference',
        'purchase_id',
        'xendit_invoice_id',
        'xendit_payment_id',
        'payment_details',
        'customer_details',
        'expires_at',
        'discount_code',
        'paid_at',
        'metadata',
        'transaction_fee',
        'vat_amount',
        'net_amount',
        'total_fees',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
        'payment_details' => 'array',
        'customer_details' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user who made the purchase
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event this purchase is for
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the ticket limit pricing this purchase is for
     */
    public function ticketLimitPricing(): BelongsTo
    {
        return $this->belongsTo(TicketLimitPricing::class);
    }

    /**
     * Check if the purchase is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Mark the purchase as paid
     */
    public function markAsPaid(string $paymentMethod = null, string $paymentReference = null): void
    {
        $paymentMethod = $paymentMethod ?? $this->payment_method ?? 'CREDIT_CARD';
        
        // Calculate fees
        $fees = \App\Services\XenditFeeCalculator::calculateFees($this->amount, $paymentMethod);
        
        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
            'paid_at' => now(),
            'transaction_fee' => $fees['transaction_fee'],
            'vat_amount' => $fees['vat_amount'],
            'net_amount' => $fees['net_amount'],
            'total_fees' => $fees['total_fees'],
        ]);
    }

    /**
     * Get the ticket limit pricing this purchase is for (alias for ticketLimitPricing)
     */
    public function pricing(): BelongsTo
    {
        return $this->ticketLimitPricing();
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }
}
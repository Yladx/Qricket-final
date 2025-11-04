<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * TicketPurchase model (order-level record)
 *
 * Columns:
 * - user_id: buyer user id
 * - event_id: related event
 * - ticket_type_id: legacy/nullable; we primarily infer via unit_price
 * - purchase_id: human-readable/order reference id
 * - quantity: number of tickets purchased in this order
 * - unit_price: price per ticket at time of purchase
 * - total_amount: gross amount (quantity * unit_price)
 * - currency: ISO currency code (e.g., PHP)
 * - status: pending|paid|failed|expired|refunded
 * - payment_method: summary label (e.g., EWallet, Card)
 * - xendit_payment_id: provider payment id
 * - xendit_invoice_id: provider invoice id
 * - payment_details: provider/raw payload snapshot
 * - customer_details: name/email/phone/address snapshot used for invoice
 * - expires_at: time when a pending invoice expires
 * - paid_at: time when payment completed (status=paid)
 * - transaction_fee: provider fee amount
 * - vat_amount: VAT portion (if applicable)
 * - net_amount: net revenue after fees/taxes
 * - total_fees: combined fees (gateway + platform) snapshot
 */
class TicketPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_type_id',
        'purchase_id',
        'quantity',
        'unit_price',
        'total_amount',
        'currency',
        'status',
        'payment_method',
        'xendit_payment_id',
        'xendit_invoice_id',
        'payment_details',
        'customer_details',
        'expires_at',
        'paid_at',
        'transaction_fee',
        'vat_amount',
        'net_amount',
        'total_fees',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'transaction_fee' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'total_fees' => 'decimal:2',
        'payment_details' => 'array',
        'customer_details' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that owns the ticket purchase.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event that owns the ticket purchase.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the ticket type for this purchase.
     * Note: We derive ticket type from unit_price matching EventTicket price
     */
    public function getTicketTypeAttribute()
    {
        return \App\Models\EventTicket::where('event_id', $this->event_id)
            ->where('price', $this->unit_price)
            ->first();
    }

    /**
     * Get the individual ticket information records for this purchase.
     */
    public function ticketInformation(): HasMany
    {
        return $this->hasMany(EventTicketInformation::class);
    }

    /**
     * Scope a query to only include paid purchases.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending purchases.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include failed purchases.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include expired purchases.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Check if the purchase is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the purchase is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the purchase is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Generate a unique purchase ID.
     */
    public static function generatePurchaseId(): string
    {
        return 'PURCHASE_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketLimitPricing extends Model
{
    use HasFactory;

    protected $table = 'ticket_limit_pricing';

    protected $fillable = [
        'name',
        'ticket_quantity',
        'price',
        'currency',
        'description',
        'is_active',
        'sort_order',
        'type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active pricing
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filtering by event type
     */
    public function scopeForEventType($query, $eventType)
    {
        $type = $eventType === true || $eventType === 1 || $eventType === '1' ? 'free' : 'paid';
        return $query->where('type', $type);
    }

    /**
     * Scope for ordering by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('ticket_quantity');
    }

    /**
     * Get formatted price with currency
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Get price per ticket
     */
    public function getPricePerTicketAttribute(): float
    {
        return $this->ticket_quantity > 0 ? $this->price / $this->ticket_quantity : 0;
    }

    /**
     * Get formatted price per ticket
     */
    public function getFormattedPricePerTicketAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price_per_ticket, 2);
    }

    /**
     * Get all purchases for this pricing plan
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(TicketLimitPurchase::class, 'ticket_limit_pricing_id');
    }

    /**
     * Get paid purchases for this pricing plan
     */
    public function paidPurchases(): HasMany
    {
        return $this->purchases()->where('status', 'paid');
    }

    /**
     * Get total revenue from this pricing plan
     */
    public function getTotalRevenueAttribute(): float
    {
        return $this->paidPurchases()->sum('amount');
    }

    /**
     * Get total tickets sold for this pricing plan
     */
    public function getTotalTicketsSoldAttribute(): int
    {
        return $this->paidPurchases()->sum('additional_tickets');
    }

    /**
     * Get total orders for this pricing plan
     */
    public function getTotalOrdersAttribute(): int
    {
        return $this->paidPurchases()->count();
    }
}
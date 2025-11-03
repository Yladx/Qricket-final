<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTicketType extends Model
{
    use HasFactory;

    protected $table = 'event_tickets';

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity',
        'sold_count',
        'sale_start',
        'sale_end',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'sold_count' => 'integer',
        'sale_start' => 'datetime',
        'sale_end' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the event that owns the ticket type.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope a query to only include active ticket types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include available ticket types.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('quantity')
                          ->orWhereRaw('quantity > sold_count');
                    });
    }

    /**
     * Get the tickets available for this type.
     */
    public function getAvailableTicketsAttribute()
    {
        if (is_null($this->quantity)) {
            return null; // Unlimited
        }
        
        return $this->quantity - $this->sold_count;
    }

    /**
     * Check if this ticket type is available.
     */
    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }

        if (!is_null($this->quantity) && $this->sold_count >= $this->quantity) {
            return false;
        }

        $now = now();
        if ($this->sale_start && $now < $this->sale_start) {
            return false;
        }

        if ($this->sale_end && $now > $this->sale_end) {
            return false;
        }

        return true;
    }

    /**
     * Check if this ticket type is sold out.
     */
    public function isSoldOut()
    {
        return !is_null($this->quantity) && $this->sold_count >= $this->quantity;
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }
}





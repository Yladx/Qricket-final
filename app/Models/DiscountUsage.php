<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'discount_id',
        'usage_type',
        'reference_id',
        'reference_type',
        'discount_amount',
        'original_amount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
    ];

    /**
     * Get the user that used the discount.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the discount that was used.
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get the reference model (purchase, subscription, etc.).
     */
    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    /**
     * Scope for usage by specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for usage of specific discount.
     */
    public function scopeByDiscount($query, $discountId)
    {
        return $query->where('discount_id', $discountId);
    }

    /**
     * Scope for usage by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('usage_type', $type);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'currency',
        'applicable_to',
        'usage_limit',
        'per_user_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'description',
        'conditions',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'conditions' => 'array',
    ];

    /**
     * Scope for active discounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')
                          ->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                    });
    }

    /**
     * Scope for available discounts (not exceeded usage limit)
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereRaw('used_count < usage_limit');
        });
    }

    /**
     * Check if discount is currently valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Apply discount to an amount
     */
    public function applyTo(float $amount): float
    {
        if (!$this->isValid()) {
            return $amount;
        }

        if ($this->type === 'percentage') {
            return $amount * (1 - $this->value / 100);
        } else {
            return max(0, $amount - $this->value);
        }
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount(float $originalAmount): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->type === 'percentage') {
            return $originalAmount * ($this->value / 100);
        } else {
            return min($this->value, $originalAmount);
        }
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Get formatted value
     */
    public function getFormattedValueAttribute(): string
    {
        if ($this->type === 'percentage') {
            return $this->value . '%';
        } else {
            return $this->currency . ' ' . number_format($this->value, 2);
        }
    }

    /**
     * Check if discount is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if discount is not yet active
     */
    public function isNotYetActive(): bool
    {
        return $this->starts_at && $this->starts_at->isFuture();
    }

    /**
     * Get all usages of this discount.
     */
    public function usages(): HasMany
    {
        return $this->hasMany(DiscountUsage::class);
    }

    /**
     * Check if a specific user can use this discount.
     */
    public function canBeUsedBy(int $userId): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check per-user limit
        if ($this->per_user_limit) {
            $userUsageCount = $this->usages()
                ->where('user_id', $userId)
                ->count();
            
            if ($userUsageCount >= $this->per_user_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get usage count for a specific user.
     */
    public function getUserUsageCount(int $userId): int
    {
        return $this->usages()
            ->where('user_id', $userId)
            ->count();
    }

    /**
     * Record usage of this discount by a user.
     */
    public function recordUsage(int $userId, string $usageType = 'general', $referenceId = null, string $referenceType = null, float $discountAmount = null, float $originalAmount = null): DiscountUsage
    {
        return $this->usages()->create([
            'user_id' => $userId,
            'usage_type' => $usageType,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'discount_amount' => $discountAmount,
            'original_amount' => $originalAmount,
        ]);
    }

}
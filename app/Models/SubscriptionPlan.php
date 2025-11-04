<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SubscriptionPlan model (pricing tier definition)
 *
 * Columns:
 * - name: plan name
 * - slug: unique slug key
 * - description: marketing description
 * - price: monthly price of the plan
 * - currency: currency code for price
 * - events_per_month: allowed new events per month (null = unlimited)
 * - tickets_per_event: base ticket cap per event (null = unlimited)
 * - advanced_layouts: access flag for premium layouts
 * - advanced_analytics: access flag for analytics
 * - features: array of extra feature flags
 * - is_active: whether plan is sellable
 * - sort_order: ordering weight in UI
 * - free_events_count: included free events quota
 * - free_event_ticket_limit: per-event ticket cap for free events
 */
class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'events_per_month',
        'tickets_per_event',
        'advanced_layouts',
        'advanced_analytics',
        'features',
        'is_active',
        'sort_order',
        'free_events_count',
        'free_event_ticket_limit',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'events_per_month' => 'integer',
        'tickets_per_event' => 'integer',
        'advanced_layouts' => 'boolean',
        'advanced_analytics' => 'boolean',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'free_events_count' => 'integer',
        'free_event_ticket_limit' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isUnlimited($field): bool
    {
        return is_null($this->$field);
    }

    public function hasFeature($feature): bool
    {
        return $this->features && in_array($feature, $this->features);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}

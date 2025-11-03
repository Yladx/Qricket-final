<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'max_event_duration_days',
        'advanced_layouts',
        'advanced_analytics',
        'custom_domain',
        'priority_support',
        'api_access',
        'white_label',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'events_per_month' => 'integer',
        'tickets_per_event' => 'integer',
        'max_event_duration_days' => 'integer',
        'advanced_layouts' => 'boolean',
        'advanced_analytics' => 'boolean',
        'custom_domain' => 'boolean',
        'priority_support' => 'boolean',
        'api_access' => 'boolean',
        'white_label' => 'boolean',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
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

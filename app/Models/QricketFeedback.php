<?php
/**
 * QricketFeedback model (platform feedback)
 * Columns: user_id, rating, message, page, metadata
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QricketFeedback extends Model
{
    use HasFactory;

    protected $table = 'qricket_feedback';

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'navigation_ease',
        'information_found',
        'design_rating',
        'performance_rating',
        'process_satisfaction',
        'improvements_suggestions',
        'additional_comments',
        'feedback_type',
        'event_id',
        'ticket_purchase_id',
    ];

    protected $casts = [
        'navigation_ease' => 'integer',
        'information_found' => 'integer',
        'design_rating' => 'integer',
        'performance_rating' => 'integer',
        'process_satisfaction' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the feedback
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event associated with the feedback
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the ticket purchase associated with the feedback
     */
    public function ticketPurchase(): BelongsTo
    {
        return $this->belongsTo(TicketPurchase::class);
    }

    /**
     * Get the average rating across all star ratings
     */
    public function getAverageRatingAttribute(): float
    {
        $ratings = array_filter([
            $this->navigation_ease,
            $this->information_found,
            $this->design_rating,
            $this->performance_rating,
            $this->process_satisfaction,
        ]);

        return count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 2) : 0;
    }

    /**
     * Get the total number of ratings provided
     */
    public function getTotalRatingsAttribute(): int
    {
        return count(array_filter([
            $this->navigation_ease,
            $this->information_found,
            $this->design_rating,
            $this->performance_rating,
            $this->process_satisfaction,
        ]));
    }

    /**
     * Scope for website feedback only
     */
    public function scopeWebsite($query)
    {
        return $query->where('feedback_type', 'website');
    }

    /**
     * Scope for event feedback only
     */
    public function scopeEvent($query)
    {
        return $query->where('feedback_type', 'event');
    }

    /**
     * Scope for recent feedback
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
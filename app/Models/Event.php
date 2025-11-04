<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Event model
 *
 * Properties (columns):
 * - id: numeric event identifier (10-digit) used across URLs and QR data
 * - user_id: creator user id (null when created by admin)
 * - subscription_id: subscription snapshot when event was created
 * - title: public event title
 * - is_free: whether all tickets are forced free (price 0)
 * - description: long-form event description shown on event page
 * - category: event category/tag for browsing
 * - start_date: datetime the event starts
 * - end_date: datetime the event ends (also used to force ticket sale_end)
 * - location: human readable venue/location
 * - location_url: optional map/location URL
 * - tags: comma separated keywords
 * - poster: R2/local storage path to poster image
 * - thumbnail: R2/local storage path to thumbnail image
 * - layout_path: R2 path to custom layout JSON
 * - status: moderation/publish state (draft|pending_approval|approved|published|rejected|scheduled)
 * - scheduled_publish_at: optional datetime to auto-publish later
 * - feedback_emails_sent_at: timestamp when feedback emails were sent
 * - end_notified_at: timestamp when end-of-event notifications were sent
 * - feedback_notified_at: timestamp when feedback notifications were sent
 */
class Event extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'id',
        'user_id',
        'subscription_id',
        'title',
        'is_free',
        'description',
        'category',
        'start_date',
        'end_date',
        'location',
        'location_url',
        'tags',
        'poster',
        'thumbnail',
        'layout_path',
        'status',
        // approval-related columns moved to event_verification_requests table
        'scheduled_publish_at',
        // 'approval_notes' moved to verification requests
        'feedback_emails_sent_at',
        'end_notified_at',
        'feedback_notified_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'is_free' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        // approval-related casts removed
        'scheduled_publish_at' => 'datetime',
        'feedback_emails_sent_at' => 'datetime',
        'end_notified_at' => 'datetime',
        'feedback_notified_at' => 'datetime',
    ];

    /**
     * Boot the model and register event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            // Generate a 10-digit random ID if not already set
            if (empty($event->id)) {
                $event->id = static::generateUniqueEventId();
            }
        });
    }

    /**
     * Generate a unique 10-digit random event ID.
     * 
     * @return int
     */
    protected static function generateUniqueEventId(): int
    {
        $maxAttempts = 100;
        $attempt = 0;

        do {
            // Generate a random 10-digit number (1000000000 to 9999999999)
            $id = random_int(1000000000, 9999999999);
            $attempt++;

            // Check if this ID already exists
            if (!static::where('id', $id)->exists()) {
                return $id;
            }

            // Prevent infinite loop
            if ($attempt >= $maxAttempts) {
                \Log::error('Failed to generate unique event ID after ' . $maxAttempts . ' attempts');
                throw new \Exception('Unable to generate unique event ID. Please try again.');
            }
        } while (true);
    }

    /**
     * Get the user who created the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subscription that was active when the event was created.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // Removed organizers relationship - using event_staff only

    // Removed organizerUsers relationship - using event_staff only

    // Removed mainOrganizer and coOrganizers relationships - using event_staff only

    /**
     * Get the staff members for the event.
     */
    public function staff(): HasMany
    {
        return $this->hasMany(EventStaff::class);
    }

    /**
     * Get the users that are staff members of the event.
     */
    public function staffUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_staff')
                    ->withPivot(['role', 'can_manage_event', 'can_manage_news', 'can_manage_attendance', 'can_entry_scan'])
                    ->withTimestamps();
    }

    /**
     * Get the ticket types for the event.
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(EventTicket::class);
    }

    /**
     * Backwards-compatible alias: some views/controllers call `$event->tickets`.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(EventTicketType::class);
    }

    /**
     * Get the activity logs for the event.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(EventActivityLog::class);
    }

    /**
     * Get the news for the event.
     */
    public function news(): HasMany
    {
        return $this->hasMany(EventNews::class);
    }

    /**
     * Get the users that have saved this event.
     */
    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_events')
                    ->withPivot('saved_at')
                    ->withTimestamps();
    }

    /**
     * Event ticket types (ticket configuration)
     */
    public function eventTickets(): HasMany
    {
        return $this->hasMany(EventTicket::class);
    }

    /**
     * Ticket information rows (individual tickets with QR codes)
     */
    public function ticketInformation(): HasMany
    {
        return $this->hasMany(EventTicketInformation::class);
    }

    /**
     * Ticket purchases for this event
     */
    public function ticketPurchases(): HasMany
    {
        return $this->hasMany(TicketPurchase::class);
    }

    /**
     * Payout requests for this event
     */
    public function payoutRequests(): HasMany
    {
        return $this->hasMany(EventPayoutRequest::class);
    }

    /**
     * Get the admin that approved the event.
     */
    // approvedBy relation removed (approval metadata now lives in event_verification_requests)

    /**
     * Scope a query to only include events with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include approved events.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include draft events.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include events in a specific category.
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include events starting after a specific date.
     */
    public function scopeStartingAfter($query, $date)
    {
        return $query->where('start_date', '>', $date);
    }

    /**
     * Scope a query to only include events starting before a specific date.
     */
    public function scopeStartingBefore($query, $date)
    {
        return $query->where('start_date', '<', $date);
    }

    /**
     * Scope a query to only include events organized by a specific user (using event_staff).
     */
    public function scopeOrganizedBy($query, $userId)
    {
        return $query->whereHas('staff', function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('role', 'organizer');
        });
    }

    /**
     * Check if the event can be resubmitted for approval.
     * Can only resubmit if rejected and 30 minutes have passed.
     */
    // Resubmission timing is handled by verification request policies now

    /**
     * Get the time remaining before the event can be resubmitted.
     */
    // getResubmitTimeRemaining removed (moved to verification workflow)

    /**
     * Check if the event is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the event is a draft.
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the event is pending approval.
     */
    public function isPendingApproval()
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Check if the event is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the event has started.
     */
    public function hasStarted()
    {
        return $this->start_date <= now();
    }

    /**
     * Check if the event has ended.
     */
    public function hasEnded()
    {
        return $this->end_date <= now();
    }

    /**
     * Check if the event is currently happening.
     */
    public function isHappening()
    {
        return $this->hasStarted() && !$this->hasEnded();
    }

    /**
     * Check if a user is an organizer of this event (using event_staff).
     */
    public function isOrganizedBy($userId)
    {
        return $this->staff()->where('user_id', $userId)->where('role', 'organizer')->exists();
    }

    /**
     * Check if a user is the main organizer of this event (using event_staff).
     */
    public function isMainOrganizedBy($userId)
    {
        return $this->staff()->where('user_id', $userId)->where('role', 'organizer')->exists();
    }

    /**
     * Check if a user is a staff member of this event.
     */
    public function isStaffedBy($userId)
    {
        return $this->staff()->where('user_id', $userId)->exists();
    }

    /**
     * Get the total number of tickets sold for this event.
     */
    public function getTotalTicketsSoldAttribute()
    {
        return $this->ticketTypes()->sum('sold_count');
    }

    /**
     * Get the total revenue from ticket sales.
     */
    public function getTotalRevenueAttribute()
    {
        return $this->ticketTypes()->get()->sum(function ($ticketType) {
            return $ticketType->sold_count * $ticketType->price;
        });
    }

    /**
     * Get the ticket price range (min and max prices).
     */
    public function getTicketPriceRangeAttribute()
    {
        $tickets = $this->eventTickets()->get();
        
        if ($tickets->isEmpty()) {
            return ['min' => 0, 'max' => 0, 'display' => 'Free'];
        }
        
        $prices = $tickets->pluck('price')->filter()->toArray();
        
        if (empty($prices)) {
            return ['min' => 0, 'max' => 0, 'display' => 'Free'];
        }
        
        $minPrice = min($prices);
        $maxPrice = max($prices);
        
        // If all tickets are free (0), display "Free"
        if ($minPrice == 0 && $maxPrice == 0) {
            return ['min' => 0, 'max' => 0, 'display' => 'Free'];
        }
        
        // If min and max are the same, display single price
        if ($minPrice == $maxPrice) {
            return [
                'min' => $minPrice,
                'max' => $maxPrice,
                'display' => '₱' . number_format($minPrice, 2)
            ];
        }
        
        // Display price range
        return [
            'min' => $minPrice,
            'max' => $maxPrice,
            'display' => '₱' . number_format($minPrice, 2) . ' - ₱' . number_format($maxPrice, 2)
        ];
    }

    /**
     * Get the formatted start date.
     */
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('M j, Y g:i A');
    }

    /**
     * Get the formatted end date.
     */
    public function getFormattedEndDateAttribute()
    {
        return $this->end_date->format('M j, Y g:i A');
    }

    /**
     * Get the duration of the event in hours.
     */
    public function getDurationInHoursAttribute()
    {
        return $this->start_date->diffInHours($this->end_date);
    }

    /**
     * Get the poster URL.
     */
    public function getPosterUrlAttribute()
    {
        if (!$this->poster) return null;
        
        // Check if it's an R2 path (contains 'events/')
        if (str_contains($this->poster, 'events/')) {
            try {
                $disk = \Storage::disk('r2');
                
                // Check if file exists
                if (!$disk->exists($this->poster)) {
                    \Log::warning("Poster file not found: {$this->poster}");
                    return null;
                }
                
                // Try to get signed URL first (for private files)
                try {
                    return $disk->temporaryUrl($this->poster, now()->addMinutes(60));
                } catch (\Exception $e) {
                    \Log::info("Signed URL failed for {$this->poster}, trying public URL");
                    
                    // Fallback to public URL
                    $publicUrl = config('filesystems.disks.r2.url') . '/' . $this->poster;
                    return $publicUrl;
                }
            } catch (\Exception $e) {
                \Log::error("Error generating poster URL for {$this->poster}: " . $e->getMessage());
                return null;
            }
        }
        
        // Fallback to local storage
        return asset('storage/' . $this->poster);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) return null;
        
        // Check if it's an R2 path (contains 'events/')
        if (str_contains($this->thumbnail, 'events/')) {
            // Use cache key based on thumbnail path and expiration
            $cacheKey = 'thumbnail_url_' . md5($this->thumbnail) . '_' . now()->format('Y-m-d-H');
            
            return \Cache::remember($cacheKey, 55, function() {
                try {
                    $disk = \Storage::disk('r2');
                    
                    // Check if file exists
                    if (!$disk->exists($this->thumbnail)) {
                        \Log::warning("Thumbnail file not found: {$this->thumbnail}");
                        return null;
                    }
                    
                    // Try to get signed URL first (for private files)
                    try {
                        return $disk->temporaryUrl($this->thumbnail, now()->addMinutes(60));
                    } catch (\Exception $e) {
                        \Log::info("Signed URL failed for {$this->thumbnail}, trying public URL");
                        
                        // Fallback to public URL
                        $publicUrl = config('filesystems.disks.r2.url') . '/' . $this->thumbnail;
                        return $publicUrl;
                    }
                } catch (\Exception $e) {
                    \Log::error("Error generating thumbnail URL for {$this->thumbnail}: " . $e->getMessage());
                    return null;
                }
            });
        }
        
        // Fallback to local storage
        return asset('storage/' . $this->thumbnail);
    }

    /**
     * Get the layout JSON from R2 storage.
     */
    public function getLayoutDataAttribute()
    {
        if (!$this->layout_path) return null;
        
        try {
            $content = \Storage::disk('r2')->get($this->layout_path);
            if ($content) {
                $data = json_decode($content, true);
                // Return the entire data array, not just layout_data key
                return $data;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load layout data from R2: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Get tags as an array
     */
    public function getTagsArrayAttribute(): array
    {
        if (empty($this->tags)) {
            return [];
        }
        
        return array_map('trim', explode(',', $this->tags));
    }

    /**
     * Set tags from array
     */
    public function setTagsFromArray(array $tags): void
    {
        $this->tags = implode(', ', array_filter($tags));
    }

    /**
     * Get all feedback submissions for this event
     */
    public function feedback()
    {
        return $this->hasMany(EventFeedback::class);
    }

    /**
     * Scope to find events that haven't had feedback emails sent yet
     */
    public function scopeFeedbackEmailsSent($query)
    {
        return $query->whereNotNull('feedback_emails_sent_at');
    }

    /**
     * Scope to find events that haven't had feedback emails sent yet
     */
    public function scopeFeedbackEmailsNotSent($query)
    {
        return $query->whereNull('feedback_emails_sent_at');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * EventActivityLog model (activities tied to a specific event)
 *
 * Columns:
 * - event_id: target event
 * - user_id: actor (nullable when admin)
 * - action: event/ticket/news/staff actions key
 * - description: readable text
 * - metadata: JSON context
 * - ip_address, user_agent: request info
 */
class EventActivityLog extends Model
{
    use HasFactory;

    protected $table = 'event_activity_logs';

    protected $fillable = [
        'event_id',
        'user_id',
        'action',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the event that owns the activity log.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include recent activity.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query by action type.
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get the formatted created date.
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get the time ago string.
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Create an activity log entry.
     */
    public static function log($eventId, $userId, $action, $description = null, $metadata = null)
    {
        // Handle admin users - if userId is null and admin is logged in, set userId to null
        $finalUserId = $userId;
        if ($userId === null && Auth::guard('admin')->check()) {
            $finalUserId = null; // Admin-created events have null user_id
        }

        // For event_viewed action, update existing view if it exists within the same day
        if ($action === 'event_viewed' && $finalUserId !== null) {
            return static::logEventView($eventId, $finalUserId, $description, $metadata);
        }

        $row = static::create([
            'event_id' => $eventId,
            'user_id' => $finalUserId,
            'action' => $action,
            'description' => $description ?? ucfirst(str_replace('_', ' ', $action)),
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Broadcast a lightweight metrics ping for admin dashboard real-time updates
        try { event(new \App\Events\AdminMetricsPing('activity:'.$action)); } catch (\Throwable $e) { /* ignore */ }

        return $row;
    }

    /**
     * Log event view with update-or-create logic to prevent duplicates.
     * Updates existing view record for the same user and event within the same day,
     * or creates a new one if no record exists for today.
     * 
     * The returned model will have a 'wasRecentlyCreated' property set to true if it's a new view,
     * or false if it was an update to an existing view.
     * 
     * @return EventActivityLog
     */
    public static function logEventView($eventId, $userId, $description = null, $metadata = null)
    {
        $today = now()->startOfDay();
        $tomorrow = now()->copy()->addDay()->startOfDay();

        // Check if a view record exists for this user, event, and today
        $existingView = static::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->where('action', 'event_viewed')
            ->whereBetween('created_at', [$today, $tomorrow])
            ->first();

        if ($existingView) {
            // Update existing view record with new timestamp and metadata
            $existingView->update([
                'updated_at' => now(),
                'metadata' => $metadata ?? $existingView->metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Broadcast a lightweight metrics ping for admin dashboard real-time updates
            try { event(new \App\Events\AdminMetricsPing('activity:event_viewed')); } catch (\Throwable $e) { /* ignore */ }

            // Return the view with a flag indicating it was updated (not new)
            $existingView->wasRecentlyCreated = false;
            return $existingView;
        }

        // Create new view record if none exists for today
        $row = static::create([
            'event_id' => $eventId,
            'user_id' => $userId,
            'action' => 'event_viewed',
            'description' => $description ?? 'Event viewed',
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Mark as newly created
        $row->wasRecentlyCreated = true;

        // Broadcast a lightweight metrics ping for admin dashboard real-time updates
        try { event(new \App\Events\AdminMetricsPing('activity:event_viewed')); } catch (\Throwable $e) { /* ignore */ }

        return $row;
    }

    /**
     * Scope a query to only include activity for specific events.
     */
    public function scopeForEvents($query, $eventIds)
    {
        return $query->whereIn('event_id', $eventIds);
    }

    /**
     * Scope a query to only include activity by specific users.
     */
    public function scopeByUsers($query, $userIds)
    {
        return $query->whereIn('user_id', $userIds);
    }

    /**
     * Get the action badge color.
     */
    public function getActionBadgeColorAttribute()
    {
        return match($this->action) {
            // Event actions
            'event_created' => 'green',
            'event_updated' => 'blue',
            'event_deleted' => 'red',
            'event_published' => 'green',
            'event_unpublished' => 'yellow',
            'event_submitted_for_approval' => 'orange',
            'event_scheduled' => 'purple',
            'event_approved' => 'green',
            'event_rejected' => 'red',
            
            // Ticket actions
            'ticket_created' => 'blue',
            'ticket_updated' => 'blue',
            'ticket_edited' => 'blue',
            'ticket_deleted' => 'red',
            'ticket_purchased' => 'green',
            'ticket_cancelled' => 'red',
            
            // Registration actions
            'registration_created' => 'green',
            'registration_cancelled' => 'red',
            'registration_confirmed' => 'green',
            
            // Attendance actions
            'check_in' => 'teal',
            'check_out' => 'indigo',
            'attendance_verified' => 'green',
            'attendance_rejected' => 'red',
            
            // Staff actions
            'staff_added' => 'purple',
            'staff_removed' => 'red',
            'staff_updated' => 'blue',
            'staff_role_changed' => 'purple',
            
            // News actions
            'news_created' => 'blue',
            'news_published' => 'green',
            'news_updated' => 'blue',
            'news_deleted' => 'red',
            
            // Certificate actions
            'certificate_generated' => 'green',
            'certificate_sent' => 'blue',
            'certificate_viewed' => 'gray',
            // Event views
            'event_viewed' => 'gray',
            
            // User actions
            'user_login' => 'purple',
            'user_logout' => 'orange',
            'user_joined' => 'green',
            'user_left' => 'red',
            
            // System actions
            'settings_updated' => 'blue',
            'permissions_changed' => 'purple',
            'data_exported' => 'green',
            'data_imported' => 'blue',
            
            default => 'gray'
        };
    }

    /**
     * Get the action icon.
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            // Event actions
            'event_created' => 'plus',
            'event_updated' => 'edit',
            'event_deleted' => 'trash',
            'event_published' => 'eye',
            'event_unpublished' => 'eye-slash',
            'event_submitted_for_approval' => 'clock',
            'event_scheduled' => 'calendar',
            'event_approved' => 'check',
            'event_rejected' => 'x',
            
            // Ticket actions
            'ticket_created' => 'ticket',
            'ticket_updated' => 'edit',
            'ticket_edited' => 'edit',
            'ticket_deleted' => 'trash',
            'ticket_purchased' => 'shopping-cart',
            'ticket_cancelled' => 'x',
            
            // Registration actions
            'registration_created' => 'user-plus',
            'registration_cancelled' => 'user-minus',
            'registration_confirmed' => 'check',
            
            // Attendance actions
            'check_in' => 'log-in',
            'check_out' => 'log-out',
            'attendance_verified' => 'check',
            'attendance_rejected' => 'x',
            
            // Staff actions
            'staff_added' => 'user-plus',
            'staff_removed' => 'user-minus',
            'staff_updated' => 'edit',
            'staff_role_changed' => 'users',
            
            // News actions
            'news_created' => 'newspaper',
            'news_published' => 'eye',
            'news_updated' => 'edit',
            'news_deleted' => 'trash',
            
            // Certificate actions
            'certificate_generated' => 'file-text',
            'certificate_sent' => 'mail',
            'certificate_viewed' => 'eye',
            // Event views
            'event_viewed' => 'eye',
            
            // User actions
            'user_login' => 'log-in',
            'user_logout' => 'log-out',
            'user_joined' => 'user-plus',
            'user_left' => 'user-minus',
            
            // System actions
            'settings_updated' => 'settings',
            'permissions_changed' => 'shield',
            'data_exported' => 'download',
            'data_imported' => 'upload',
            
            default => 'info'
        };
    }
}

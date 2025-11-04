<?php
/**
 * EventStaff model (event staff assignments)
 * Columns: event_id, user_id, role, can_manage_event, can_manage_staff, can_manage_ticket, can_manage_news, can_manage_attendance, can_manage_sales
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventStaff extends Model
{
    use HasFactory;

    protected $table = 'event_staff';

    protected $fillable = [
        'event_id',
        'user_id',
        'role',
        'can_manage_event',
        'can_manage_staff',
        'can_manage_news',
        'can_manage_attendance',
        'can_manage_sales',
        'can_manage_ticket',
    ];

    protected $casts = [
        'can_manage_event' => 'boolean',
        'can_manage_staff' => 'boolean',
        'can_manage_news' => 'boolean',
        'can_manage_attendance' => 'boolean',
        'can_manage_sales' => 'boolean',
        'can_manage_ticket' => 'boolean',
    ];

    /**
     * Get the event that owns the staff member.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that is the staff member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include administrators.
     */
    public function scopeAdministrators($query)
    {
        return $query->where('role', 'administrator');
    }

    /**
     * Scope a query to only include managers.
     */
    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    /**
     * Scope a query to only include support staff.
     */
    public function scopeSupport($query)
    {
        return $query->where('role', 'support');
    }

    /**
     * Check if the staff member can manage the event.
     */
    public function canManageEvent()
    {
        return $this->can_manage_event;
    }

    /**
     * Check if the staff member can manage news.
     */
    public function canManageNews()
    {
        return $this->can_manage_news;
    }

    /**
     * Check if the staff member can manage attendance.
     */
    public function canManageAttendance()
    {
        return $this->can_manage_attendance;
    }

    // Scanning is part of attendance permissions

    /**
     * Check if the staff member is an administrator.
     */
    public function isAdministrator()
    {
        return $this->role === 'administrator';
    }

    /**
     * Check if the staff member is a manager.
     */
    public function isManager()
    {
        return $this->role === 'manager';
    }

    /**
     * Check if the staff member is support.
     */
    public function isSupport()
    {
        return $this->role === 'support';
    }
}


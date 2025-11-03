<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventOrganizer extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'role',
    ];

    /**
     * Get the event that owns the organizer.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that is the organizer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include organizers for a specific event.
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope a query to only include organizers with a specific role.
     */
    public function scopeWithRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if the user is an organizer of the event.
     */
    public static function isOrganizer($userId, $eventId)
    {
        return static::where('user_id', $userId)
                    ->where('event_id', $eventId)
                    ->exists();
    }

    /**
     * Check if the user is the main organizer of the event.
     */
    public static function isMainOrganizer($userId, $eventId)
    {
        return static::where('user_id', $userId)
                    ->where('event_id', $eventId)
                    ->where('role', 'organizer')
                    ->exists();
    }

    /**
     * Add an organizer to an event.
     */
    public static function addOrganizer($eventId, $userId, $role = 'co_organizer')
    {
        return static::create([
            'event_id' => $eventId,
            'user_id' => $userId,
            'role' => $role,
        ]);
    }

    /**
     * Remove an organizer from an event.
     */
    public static function removeOrganizer($eventId, $userId)
    {
        return static::where('event_id', $eventId)
                    ->where('user_id', $userId)
                    ->delete();
    }
}
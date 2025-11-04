<?php
/**
 * TicketShare model (legacy generic share records)
 * Columns: ticket_information_id, from_user_id, to_user_id, status, accepted_at
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketShare extends Model
{
    use HasFactory;

    protected $table = 'event_ticket_shares';

    protected $fillable = [
        'event_id',
        'ticket_type_id',
        'sharer_id',
        'recipient_id',
        'shared_ticket_id',
        'quantity',
        'status',
        'expires_at',
        'shared_ticket_ids',
        'message',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'shared_ticket_ids' => 'array',
    ];

    /**
     * Get the ticket type being shared.
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(EventTicket::class, 'ticket_type_id');
    }

    /**
     * Get the event that owns the ticket share.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who is sharing the tickets.
     */
    public function sharer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sharer_id');
    }

    /**
     * Get the user who is receiving the shared tickets.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the specific ticket being shared.
     */
    public function sharedTicket(): BelongsTo
    {
        return $this->belongsTo(EventTicketInformation::class, 'shared_ticket_id');
    }

    /**
     * Check if the share is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the share can be accepted.
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Scope for active shares.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for shares received by a user.
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    /**
     * Scope for shares made by a user.
     */
    public function scopeSharedBy($query, $userId)
    {
        return $query->where('sharer_id', $userId);
    }
}

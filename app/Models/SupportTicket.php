<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * SupportTicket model (user support request)
 *
 * Columns:
 * - ticket_number: public ticket id (e.g., TKT-XXXX)
 * - user_id: creator (requester) user id
 * - name: requester name (fallback when no user)
 * - email: requester email (fallback when no user)
 * - subject: short subject shown in lists
 * - message: initial message/body created with the ticket
 * - status: open|in_progress|resolved|closed
 * - priority: low|medium|high|urgent
 * - resolved_at: timestamp when closed/resolved
 */
class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'name',
        'email',
        'subject',
        'message',
        'status',
        'priority',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    /**
     * Boot method to generate ticket number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the user that owns the ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all replies for this ticket.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(SupportReply::class, 'ticket_id')->orderBy('created_at');
    }

    /**
     * Get the latest reply for this ticket.
     */
    public function latestReply(): HasMany
    {
        return $this->hasMany(SupportReply::class, 'ticket_id')->latest();
    }

    /**
     * Check if ticket is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if ticket is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if ticket is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if ticket is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'open' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'resolved' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityBadgeColor(): string
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
            'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'high' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        };
    }

    /**
     * Scope for open tickets.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for resolved tickets.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope for closed tickets.
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for high priority tickets.
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }
}
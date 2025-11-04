<?php
/**
 * EventRegistration model (RSVP/registration records)
 * Columns: event_id, user_id, status, registered_at, checked_in_at
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use HasFactory;

    protected $table = 'event_registrations';

    protected $fillable = [
        'event_id',
        'user_id',
        'ticket_type_id',
        'quantity',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'transaction_id',
        'checked_in',
        'checked_in_at',
        'notes',
    ];

    protected $casts = [
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the event that owns the registration.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user that owns the registration.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ticket type for the registration.
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(EventTicketType::class, 'ticket_type_id');
    }

    /**
     * Scope a query to only include checked-in registrations.
     */
    public function scopeCheckedIn($query)
    {
        return $query->where('checked_in', true);
    }

    /**
     * Scope a query to only include pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('checked_in', false);
    }

    /**
     * Scope a query to only include confirmed registrations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include cancelled registrations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Check if the registration is checked in.
     */
    public function isCheckedIn()
    {
        return $this->checked_in;
    }

    /**
     * Check if the registration is pending.
     */
    public function isPending()
    {
        return !$this->checked_in;
    }

    /**
     * Check if the registration is confirmed.
     */
    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if the registration is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Mark the registration as checked in.
     */
    public function checkIn()
    {
        $this->update([
            'checked_in' => true,
            'checked_in_at' => now(),
        ]);
    }

    /**
     * Cancel the registration.
     */
    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Get the registration status badge color.
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'confirmed' => 'green',
            'pending' => 'yellow',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get the payment status badge color.
     */
    public function getPaymentStatusBadgeColorAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            'refunded' => 'blue',
            default => 'gray'
        };
    }
}

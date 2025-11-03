<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'item_id',
        'purchase_id',
        'amount',
        'reason',
        'referral_id',
        'status',
        'admin_notes',
        'xendit_refund_id',
        'processed_at',
        'failure_reason',
        'refunded_at',
        'refunded_ticket_ids',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'refunded_ticket_ids' => 'array',
    ];

    /**
     * Get the user that owns the refund request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ticket purchase if type is ticket.
     */
    public function ticketPurchase(): BelongsTo
    {
        return $this->belongsTo(TicketPurchase::class, 'item_id');
    }

    /**
     * Get the subscription if type is subscription.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'item_id');
    }

    /**
     * Get the additional ticket purchase if type is additional_ticket.
     */
    public function additionalTicketPurchase(): BelongsTo
    {
        return $this->belongsTo(TicketLimitPurchase::class, 'item_id');
    }

    /**
     * Get the refunded ticket information records.
     */
    public function refundedTickets()
    {
        if (!$this->refunded_ticket_ids) {
            return collect();
        }
        
        return EventTicketInformation::whereIn('id', $this->refunded_ticket_ids)->get();
    }

    /**
     * Scope for pending refunds.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved refunds.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for processed refunds.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
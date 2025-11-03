<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPayoutRequest extends Model
{
    use HasFactory;

    protected $table = 'event_payout_requests';

    protected $fillable = [
        'event_id',
        'organizer_id',
        'total_earnings',
        'qricket_fee',
        'net_amount',
        'status',
        'notes',
        'requested_at',
        'processed_at',
    ];

    protected $casts = [
        'total_earnings' => 'decimal:2',
        'qricket_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }
}

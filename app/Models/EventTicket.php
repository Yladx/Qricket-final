<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventTicket extends Model
{
    use HasFactory;

    protected $table = 'event_tickets';

    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quantity',
        'sold_count',
        'sale_start',
        'sale_end',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_start' => 'datetime',
        'sale_end' => 'datetime',
    ];

    /**
     * Get the event that owns the ticket.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the ticket information records for this ticket type.
     */
    public function ticketInformation(): HasMany
    {
        return $this->hasMany(EventTicketInformation::class, 'event_ticket_id');
    }
}

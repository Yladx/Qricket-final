<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketInformation extends Model
{
    use HasFactory;

    protected $table = 'event_ticket_information';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'event_id',
        'ticket_purchase_id',
        'qr_code_data',
        'checked_in_at',
        'certificate_given',
    ];

    protected $casts = [
        'qr_code_data' => 'encrypted',
        'checked_in_at' => 'datetime',
        'certificate_given' => 'boolean',
    ];

    /**
     * Boot method to automatically generate ticket_id
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($ticket) {
            if (empty($ticket->ticket_id)) {
                $ticket->ticket_id = static::generateTicketId($ticket->event_id);
            }
        });
    }

    /**
     * Generate a unique ticket ID in format: EVENTID_UNIQUEID
     */
    public static function generateTicketId($eventId)
    {
        do {
            $uniqueId = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
            $ticketId = $eventId . '_' . $uniqueId;
        } while (static::where('ticket_id', $ticketId)->exists());
        
        return $ticketId;
    }

    /**
     * Get the user that owns this ticket.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the event that this ticket belongs to.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Pending/processed share records for this ticket.
     */
    public function shares()
    {
        return $this->hasMany(EventTicketShare::class, 'shared_ticket_id');
    }

    /**
     * Get the ticket purchase that this ticket belongs to.
     */
    public function ticketPurchase(): BelongsTo
    {
        return $this->belongsTo(TicketPurchase::class);
    }

    /**
     * Generate a unique encrypted QR code data for this ticket.
     */
    public function generateQRCodeData(): string
    {
        // Create unique ticket identifier
        $ticketNumber = 'TICKET_' . $this->ticketPurchase->purchase_id . '_' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        
        // Generate encrypted data
        $qrData = [
            'ticket_id' => $ticketNumber,
            'ticket_information_id' => $this->id,
            'purchase_id' => $this->ticketPurchase->purchase_id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'generated_at' => now()->timestamp,
            'signature' => $this->generateSignature($ticketNumber)
        ];
        
        return base64_encode(json_encode($qrData));
    }

    /**
     * Generate a cryptographic signature for the ticket.
     */
    private function generateSignature(string $ticketNumber): string
    {
        $data = $ticketNumber . $this->id . config('app.key');
        return hash('sha256', $data);
    }

    /**
     * Validate QR code data and return ticket information.
     */
    public static function validateQRCode(string $qrData): ?self
    {
        try {
            $decodedData = json_decode(base64_decode($qrData), true);
            
            if (!$decodedData || !isset($decodedData['ticket_information_id'])) {
                return null;
            }
            
            $ticket = self::find($decodedData['ticket_information_id']);
            
            if (!$ticket) {
                return null;
            }
            
            // Verify signature
            $expectedSignature = $ticket->generateSignature($decodedData['ticket_id']);
            if ($decodedData['signature'] !== $expectedSignature) {
                return null;
            }
            
            // Verify ticket belongs to paid purchase
            if ($ticket->ticketPurchase->status !== 'paid') {
                return null;
            }
            
            return $ticket;
            
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the QR code URL for this ticket.
     */
    public function getQRCodeUrlAttribute(): string
    {
        return route('tickets.qr-code', $this->id);
    }
}


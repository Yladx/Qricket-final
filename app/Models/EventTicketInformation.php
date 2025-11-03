<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTicketInformation extends Model
{
    use HasFactory;

    protected $table = 'event_ticket_information';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'shared_at',
        'shared_by_user_id',
        'event_id',
        'ticket_type_id',
        'ticket_purchase_id',
        'qr_code_data',
        'checked_in_at',
        'certificate_given',
    ];

    protected $casts = [
        'qr_code_data' => 'encrypted',
        'checked_in_at' => 'datetime',
        'shared_at' => 'datetime',
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
     * Get the ticket type for this ticket.
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(EventTicket::class, 'ticket_type_id');
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
     * Get the user who shared this ticket.
     */
    public function sharedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    /**
     * Check if this ticket is shared.
     */
    public function isShared(): bool
    {
        return !is_null($this->shared_at) || !is_null($this->shared_by_user_id);
    }

    /**
     * Get the ticket purchase that this ticket belongs to.
     */
    public function ticketPurchase(): BelongsTo
    {
        return $this->belongsTo(TicketPurchase::class);
    }

    /**
     * Generate encrypted QR code data for this ticket.
     */
    public function generateQRCodeData(): string
    {
        \Log::info('Generating encrypted QR code data', [
            'ticket_id' => $this->id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'ticket_display_id' => $this->ticket_id,
            'purchase_id' => $this->ticketPurchase->purchase_id ?? 'null',
            'purchase_status' => $this->ticketPurchase->status ?? 'null'
        ]);
        
        // Check if ticket purchase exists and is paid
        if (!$this->ticketPurchase) {
            \Log::error('Cannot generate QR code - no ticket purchase found', [
                'ticket_information_id' => $this->id,
                'ticket_id' => $this->ticket_id
            ]);
            throw new \Exception('No ticket purchase found for this ticket');
        }
        
        if ($this->ticketPurchase->status !== 'paid') {
            \Log::error('Cannot generate QR code - ticket purchase not paid', [
                'ticket_information_id' => $this->id,
                'ticket_id' => $this->ticket_id,
                'purchase_status' => $this->ticketPurchase->status
            ]);
            throw new \Exception('Ticket purchase is not paid');
        }
        
        // Create encrypted QR data with ticket information
        $qrData = [
            'ticket_id' => $this->ticket_id,
            'ticket_information_id' => $this->id,
            'purchase_id' => $this->ticketPurchase->purchase_id,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'generated_at' => now()->timestamp,
            'signature' => $this->generateSignature($this->ticket_id)
        ];
        
        // Encrypt the data using Laravel's encryption
        $encryptedData = encrypt(json_encode($qrData));
        
        \Log::info('Encrypted QR code data generated', [
            'ticket_id' => $this->ticket_id,
            'encrypted_data_length' => strlen($encryptedData),
            'original_data' => $qrData
        ]);
        
        return $encryptedData;
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
     * Validate encrypted QR code data and return ticket information.
     */
    public static function validateQRCode(string $qrData, int $eventId): ?self
    {
        try {
            // Decrypt the QR data using Laravel's decryption
            $decryptedJson = decrypt($qrData);
            $decryptedData = json_decode($decryptedJson, true);
            
            if (!$decryptedData || !isset($decryptedData['ticket_information_id'])) {
                \Log::warning('QR code validation failed - invalid decrypted data', [
                    'event_id' => $eventId,
                    'decrypted_data' => $decryptedData
                ]);
                return null;
            }
            
            // Verify the signature
            $ticket = self::find($decryptedData['ticket_information_id']);
            if (!$ticket) {
                \Log::warning('QR code validation failed - ticket not found', [
                    'ticket_information_id' => $decryptedData['ticket_information_id'],
                    'event_id' => $eventId
                ]);
                return null;
            }
            
            $expectedSignature = $ticket->generateSignature($decryptedData['ticket_id']);
            if ($decryptedData['signature'] !== $expectedSignature) {
                \Log::warning('QR code validation failed - invalid signature', [
                    'ticket_id' => $decryptedData['ticket_id'],
                    'expected_signature' => $expectedSignature,
                    'provided_signature' => $decryptedData['signature']
                ]);
                return null;
            }
            
            // Verify event ID matches
            if ($decryptedData['event_id'] != $eventId) {
                \Log::warning('QR code validation failed - event ID mismatch', [
                    'expected_event_id' => $eventId,
                    'provided_event_id' => $decryptedData['event_id']
                ]);
                return null;
            }
            
            \Log::info('QR code validation successful', [
                'ticket_id' => $ticket->ticket_id,
                'event_id' => $eventId,
                'user_id' => $ticket->user_id
            ]);
            
            return $ticket;
            
        } catch (\Exception $e) {
            \Log::error('QR code validation failed with exception', [
                'error' => $e->getMessage(),
                'event_id' => $eventId
            ]);
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

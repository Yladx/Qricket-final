<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id','organizer_id','gross_amount','platform_fee','net_amount','currency',
        'status','xendit_disbursement_id','destination_bank_code','destination_account_number',
        'destination_account_name','notes','failure_reason','paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'gross_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function event(){ return $this->belongsTo(Event::class); }
    public function organizer(){ return $this->belongsTo(User::class, 'organizer_id'); }
}



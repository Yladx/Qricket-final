<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventNews extends Model
{
    use HasFactory;

    protected $table = 'event_news';

    protected $fillable = [
        'event_id',
        'user_id',
        'title',
        'body',
        'attachments',
        'visibility',
        'published_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'published_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
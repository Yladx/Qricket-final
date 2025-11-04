<?php
/**
 * SavedEvent model (user bookmarks)
 * Columns: user_id, event_id, saved_at
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
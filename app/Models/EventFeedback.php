<?php
/**
 * EventFeedback model (post-event feedback submissions)
 *
 * Common columns (inferred):
 * - event_id: target event
 * - user_id: respondent
 * - rating: numeric rating value
 * - comments: free-text feedback
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventFeedback extends Model
{
    use HasFactory;

    protected $table = 'event_feedback';

    protected $fillable = [
        'event_id',
        'user_id',
        'responses',
        'rating',
        'comments',
        'submitted_at',
    ];

    protected $casts = [
        'responses' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}



<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'admin_id',
        'message',
        'is_admin_reply',
    ];

    /**
     * Get the ticket that owns the reply.
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /**
     * Get the user that made the reply.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin that made the reply.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the author name for display.
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->is_admin_reply && $this->admin) {
            return $this->admin->username . ' (Admin)';
        } elseif ($this->user) {
            return $this->user->full_name;
        } else {
            return 'Unknown User';
        }
    }

    /**
     * Get the author avatar for display.
     */
    public function getAuthorAvatarAttribute(): string
    {
        if ($this->is_admin_reply && $this->admin) {
            return 'admin';
        } elseif ($this->user) {
            return 'user';
        } else {
            return 'guest';
        }
    }

    /**
     * Scope for admin replies.
     */
    public function scopeAdminReplies($query)
    {
        return $query->where('is_admin_reply', true);
    }

    /**
     * Scope for user replies.
     */
    public function scopeUserReplies($query)
    {
        return $query->where('is_admin_reply', false);
    }
}
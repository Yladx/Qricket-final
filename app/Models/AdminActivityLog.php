<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * Get the admin that owns the activity log.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Scope for successful login attempts.
     */
    public function scopeSuccessfulLogins($query)
    {
        return $query->where('action', 'login');
    }

    /**
     * Scope for failed login attempts.
     */
    public function scopeFailedLogins($query)
    {
        return $query->where('action', 'login_failed');
    }

    /**
     * Scope for logout attempts.
     */
    public function scopeLogouts($query)
    {
        return $query->where('action', 'logout');
    }
}
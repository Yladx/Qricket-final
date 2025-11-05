<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;

/**
 * Admin model (backoffice users)
 *
 * Columns:
 * - username: unique admin handle
 * - password: hashed password
 * - role: superadmin|manager|support|... used for authorization
 * - pin: encrypted short PIN used for sensitive actions
 * - last_login_at: last successful login time
 * - failed_login_attempts: counter for lockout policy
 * - locked_until: datetime lock expires
 * - current_session_id: session marker for single-session policies
 */
class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'role',
        'pin',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
        'current_session_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'pin',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'failed_login_attempts' => 'integer',
        ];
    }

    /**
     * Encrypt PIN before saving.
     */
    public function setPinAttribute($value)
    {
        $this->attributes['pin'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt PIN when retrieving.
     */
    public function getPinAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            // If decryption fails, return the original value (for backward compatibility)
            return $value;
        }
    }

    /**
     * Get the activity logs for the admin.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class);
    }

    /**
     * Log an activity for the admin.
     */
    public function logActivity(string $action, ?array $metadata = null, ?string $description = null): AdminActivityLog
    {
        return $this->activityLogs()->create([
            'action' => $action,
            'description' => $description ?? ucfirst(str_replace('_', ' ', $action)),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Check if admin is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if admin has specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AdminAccess model (fine-grained admin permissions)
 *
 * Columns:
 * - admin_id: owner admin
 * - can_manage_admins|users|verifications|support: capability switches
 * - can_view_logs: audit access
 * - custom_permissions: additional JSON flags per feature
 */
class AdminAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'can_manage_admins',
        'can_manage_users',
        'can_manage_verifications',
        'can_view_logs',
        'can_manage_support',
        'custom_permissions',
    ];

    protected $casts = [
        'can_manage_admins' => 'boolean',
        'can_manage_users' => 'boolean',
        'can_manage_verifications' => 'boolean',
        'can_view_logs' => 'boolean',
        'can_manage_support' => 'boolean',
        'custom_permissions' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}


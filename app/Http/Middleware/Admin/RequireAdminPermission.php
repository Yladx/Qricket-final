<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * Usage: ->middleware('admin.permission:manage_users')
     */
    public function handle(Request $request, Closure $next, string $permissionKey): Response
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            abort(403);
        }

        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        $access = optional($admin->access);

        $allowed = match ($permissionKey) {
            'manage_users' => (bool) $access->can_manage_users,
            'manage_support' => (bool) $access->can_manage_support,
            'manage_admins' => (bool) $access->can_manage_admins,
            'manage_verifications' => (bool) $access->can_manage_verifications,
            'view_logs' => (bool) $access->can_view_logs,
            default => false,
        };

        if (!$allowed) {
            abort(403);
        }

        return $next($request);
    }
}



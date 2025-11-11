<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // CRITICAL: First check if a regular user (not admin) is logged in
        // If a regular user is logged in and trying to access admin routes, deny access
        if (Auth::guard('web')->check() && !Auth::guard('admin')->check()) {
            $user = Auth::guard('web')->user();
            // Check if this is NOT a shadow admin user (shadow users have @admin.local email)
            $isShadowAdmin = $user && str_contains($user->email ?? '', '@admin.local');
            
            if (!$isShadowAdmin) {
                // Regular user is logged in - deny admin access
                return redirect()->route('admin.login')
                    ->with('error', 'Please log out from your user account before accessing admin area.');
            }
        }
        
        // Check if admin is authenticated and has admin role in session
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login as admin to access this area.');
        }

        // Verify admin role is stored in session
        $sessionRole = session('admin_role');
        if (!$sessionRole || !in_array($sessionRole, ['admin', 'superadmin'])) {
            Auth::guard('admin')->logout();
            // Also logout shadow user if exists
            if (Auth::guard('web')->check()) {
                $webUser = Auth::guard('web')->user();
                if ($webUser && str_contains($webUser->email ?? '', '@admin.local')) {
                    Auth::guard('web')->logout();
                }
            }
            return redirect()->route('admin.login')->with('error', 'Invalid admin session. Please login again.');
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventUserAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prevent regular users from accessing admin routes
        // Check if a regular user is logged in (but NOT an admin)
        if (Auth::guard('web')->check() && !Auth::guard('admin')->check()) {
            // Check if the logged-in user is NOT a shadow admin user
            $user = Auth::guard('web')->user();
            $isShadowAdmin = $user && str_contains($user->email, '@admin.local');
            
            if (!$isShadowAdmin) {
                // Regular user is logged in - deny access to admin routes
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('admin.login')
                    ->with('error', 'Please log out from your user account before accessing admin area.');
            }
        }

        return $next($request);
    }
}

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
        // Bypass: allow accessing admin routes even if a regular user session exists.
        // This enables admins to sign in without logging the user out first.
        // If you want to force logout the user automatically instead, uncomment below:
        // if (Auth::guard('web')->check()) {
        //     Auth::guard('web')->logout();
        //     $request->session()->invalidate();
        //     $request->session()->regenerateToken();
        // }

        return $next($request);
    }
}

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
        // Check if admin is authenticated and has admin role in session
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login as admin to access this area.');
        }

        // Verify admin role is stored in session
        $sessionRole = session('admin_role');
        if (!$sessionRole || !in_array($sessionRole, ['admin', 'superadmin'])) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Invalid admin session. Please login again.');
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware\Organizer;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifiedOrganizerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Allow admin access
        if (auth()->guard('admin')->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if (!$user->isVerifiedOrganizer()) {
            // If user is not verified as organizer, redirect to dashboard with error message
            return redirect()->route('dashboard')->with('error', 'You must be a verified organizer to access this area.');
        }

        return $next($request);
    }
}

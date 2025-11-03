<?php

namespace App\Http\Middleware\System;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetNavigationVariables
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set default values for all views
        $isOrganizer = false;
        $isOrganizerSide = false;
        
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is organizer verified
            $isOrganizer = $user->is_organizer_verified ?? false;
            
            // Check if user is currently on organizer side
            $currentMode = session('user_mode', 'user');
            $isOrganizerSide = $currentMode === 'organizer' || $request->routeIs('organizer.*');
        }
        
        // Share variables with all views
        view()->share('isOrganizer', $isOrganizer);
        view()->share('isOrganizerSide', $isOrganizerSide);
        
        return $next($request);
    }
}
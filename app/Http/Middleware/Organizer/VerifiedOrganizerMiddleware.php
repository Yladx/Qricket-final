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

        // Allow access for event owner or staff for routes that carry an {event} parameter
        $routeEvent = $request->route('event');
        if ($routeEvent) {
            // Resolve to model if it's an ID
            $event = is_object($routeEvent) ? $routeEvent : \App\Models\Event::find($routeEvent);
            if ($event) {
                $isOwner = (int) $event->user_id === (int) $user->id;
                $isStaff = $event->staff()->where('user_id', $user->id)->exists();
                if ($isOwner || $isStaff) {
                    return $next($request);
                }
            }
        }

        if (!$user->isVerifiedOrganizer()) {
            // If user is not verified and not owner/staff on the target event, block
            return redirect()->route('dashboard')->with('error', 'You must be a verified organizer to access this area.');
        }

        return $next($request);
    }
}

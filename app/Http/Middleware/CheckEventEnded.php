<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEventEnded
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TEMPORARILY DISABLED TO FIX DATABASE CONNECTION LIMIT
        // TODO: Re-enable once redirect issues are resolved
        return $next($request);
        
        /* DISABLED CODE - Original implementation
        $event = $request->route('event');
        
        // If event has ended and it's an edit/update request, redirect to info view
        if ($event && $event->end_date && now()->gte($event->end_date)) {
            $routeName = $request->route()->getName();
            
            // Skip if this is already an info route
            if (str_contains($routeName, 'info.')) {
            return $next($request);
        }

            // Check if this is a PUT/POST method (update operation)
            if (in_array($request->method(), ['PUT', 'PATCH', 'POST'])) {
                return redirect()->back()
                    ->with('error', 'This event has ended and can no longer be modified.');
            }
            
            // Check if this is an edit route
            if (str_contains($routeName, 'edit.')) {
                // Map edit routes to their corresponding info routes
                $redirectRoute = str_replace('edit.basic', 'info.basic', $routeName);
                $redirectRoute = str_replace('edit.layout', 'info.layout', $redirectRoute);
                $redirectRoute = str_replace('edit.certificate', 'info.certificate', $redirectRoute);
                $redirectRoute = str_replace('edit.tickets', 'info.tickets', $redirectRoute);
                $redirectRoute = str_replace('edit.staff', 'info.staff', $redirectRoute);
                
                return redirect()->route($redirectRoute, $event)
                    ->with('info', 'This event has ended and can only be viewed.');
            }
        }

        return $next($request);
        */
    }
}


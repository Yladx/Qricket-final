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

        // Helper function to check if user is owner or staff of an event
        $checkEventAccess = function($event) use ($user) {
            if (!$event) {
                return false;
            }
            
            // Resolve to model if it's an ID
            if (!is_object($event)) {
                try {
                    $event = \App\Models\Event::find($event);
                } catch (\Exception $e) {
                    \Log::warning('Error finding event in VerifiedOrganizerMiddleware', [
                        'error' => $e->getMessage(),
                        'event_id' => $event,
                        'user_id' => $user->id
                    ]);
                    return false;
                }
            }
            
            if (!$event) {
                return false;
            }
            
            $isOwner = (int) $event->user_id === (int) $user->id;
            
            // Check if user is staff member
            try {
                $isStaff = $event->staff()->where('user_id', $user->id)->exists();
            } catch (\Exception $e) {
                \Log::warning('Error checking staff access in VerifiedOrganizerMiddleware', [
                    'error' => $e->getMessage(),
                    'event_id' => $event->id,
                    'user_id' => $user->id
                ]);
                $isStaff = false;
            }
            
            return $isOwner || $isStaff;
        };

        // Allow access for event owner or staff for routes that carry an {event} or {id} parameter
        // Try multiple ways to get the event parameter
        $routeEvent = null;
        
        // First, try route parameter (works with route model binding)
        $routeEvent = $request->route('event') ?? $request->route('id');
        
        // If route model binding returned a model, use it directly
        if ($routeEvent && is_object($routeEvent)) {
            if ($checkEventAccess($routeEvent)) {
                \Log::info('VerifiedOrganizerMiddleware: Allowing access via route model binding', [
                    'user_id' => $user->id,
                    'event_id' => $routeEvent->id,
                    'route' => $request->route()->getName()
                ]);
                return $next($request);
            }
        } elseif ($routeEvent) {
            // Route parameter exists but might be an ID
            if ($checkEventAccess($routeEvent)) {
                $event = is_object($routeEvent) ? $routeEvent : \App\Models\Event::find($routeEvent);
                if ($event) {
                    \Log::info('VerifiedOrganizerMiddleware: Allowing access via route parameter', [
                        'user_id' => $user->id,
                        'event_id' => $event->id,
                        'route' => $request->route()->getName()
                    ]);
                    return $next($request);
                }
            }
        }

        // Allow access to edit/save routes for event owners and staff even if not verified
        // Check if this is an edit route (basic, layout, certificate, tickets) or autosave/store route
        $routeName = $request->route()->getName() ?? '';
        $isEditRoute = str_contains($routeName, 'edit.basic') ||
            str_contains($routeName, 'edit.layout') ||
            str_contains($routeName, 'edit.certificate') ||
            str_contains($routeName, 'tickets') ||
            str_contains($routeName, 'info.basic') ||
            str_contains($routeName, 'info.layout') ||
            str_contains($routeName, 'info.certificate') ||
            str_contains($routeName, 'step1.store') ||
            str_contains($routeName, 'step1') ||
            str_contains($routeName, 'basicinfo') ||
            str_contains($routeName, 'layout.autosave') ||
            str_contains($routeName, 'ticket.autosave') ||
            str_contains($routeName, 'certificate.autosave') ||
            str_contains($routeName, 'layout.store') ||
            str_contains($routeName, 'ticket.store') ||
            str_contains($routeName, 'certificate.store') ||
            str_contains($routeName, 'certificate.upload') ||
            str_contains($routeName, 'certificate.remove') ||
            str_contains($routeName, 'certificate.status') ||
            str_contains($routeName, 'upload-layout-image') ||
            str_contains($routeName, 'get-fresh-image-url') ||
            str_contains($routeName, 'update-session-layout') ||
            str_contains($routeName, 'layouts.render') ||
            str_contains($routeName, 'layout.preview') ||
            str_contains($routeName, 'tag-category-mappings') ||
            str_contains($routeName, 'existing-tags') ||
            str_contains($routeName, 'events.cancel');

        if ($isEditRoute) {
            // Try to get event from route parameter first
            $eventParam = $request->route('event') ?? $request->route('id');

            // If not in route, try to get from request data (for autosave routes that use query string or body)
            if (!$eventParam) {
                // Check query string first (for GET requests and form submissions with ?id=)
                $eventParam = $request->query('id') ??
                    $request->query('event_id') ??
                    // Then check POST/PUT body
                    $request->input('id') ??
                    $request->input('event_id');
            }

            // For layout.preview.load route, extract event ID from cloudPath parameter
            // cloudPath format: events/{eventId}/layout.json
            if (!$eventParam && str_contains($routeName, 'layout.preview.load')) {
                $cloudPath = $request->route('cloudPath');
                if ($cloudPath && preg_match('#events/(\d+)/#', $cloudPath, $matches)) {
                    $eventParam = (int) $matches[1];
                }
            }

            if ($eventParam) {
                // Use the helper function to check access
                if ($checkEventAccess($eventParam)) {
                    $event = is_object($eventParam) ? $eventParam : \App\Models\Event::find($eventParam);
                    if ($event) {
                        // Event owner and staff can always access their events for editing/saving, even if not verified
                        \Log::info('VerifiedOrganizerMiddleware: Allowing access for owner/staff on edit route', [
                            'user_id' => $user->id,
                            'event_id' => $event->id,
                            'route' => $routeName,
                            'event_param_type' => is_object($eventParam) ? 'model' : 'id'
                        ]);
                        return $next($request);
                    }
                }
            } else {
                // Log when event param is not found for debugging
                \Log::debug('VerifiedOrganizerMiddleware: No event parameter found for edit route', [
                    'route' => $routeName,
                    'route_params' => $request->route()->parameters(),
                    'query_params' => $request->query(),
                    'input_params' => $request->input(),
                    'user_id' => $user->id
                ]);
            }
        }

        if (!$user->isVerifiedOrganizer()) {
            // For AJAX requests, return JSON response with SweetAlert notification
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'requires_verification' => true,
                    'message' => 'You must be a verified organizer to access this feature.',
                    'sweetalert' => [
                        'title' => 'Verification Required',
                        'text' => 'Please verify as organizer to access this functionality.',
                        'icon' => 'warning',
                        'confirmButtonText' => 'Verify Now',
                        'showCancelButton' => true,
                        'cancelButtonText' => 'Cancel',
                        'confirmButtonColor' => '#10b981',
                    ],
                    'redirect_url' => route('organizer.verification')
                ], 403);
            }

            // For regular requests, redirect with SweetAlert session data
            return redirect()->route('dashboard')->with('sweetalert', [
                'title' => 'Verification Required',
                'text' => 'Please verify as organizer to access this functionality.',
                'icon' => 'warning',
                'confirmButtonText' => 'Verify Now',
                'showCancelButton' => true,
                'cancelButtonText' => 'Cancel',
                'confirmButtonColor' => '#10b981',
                'redirect_url' => route('organizer.verification')
            ]);
        }

        return $next($request);
    }
}

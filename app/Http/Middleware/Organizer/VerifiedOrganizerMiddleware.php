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

        // Allow access for event owner or staff for routes that carry an {event} or {id} parameter
        $routeEvent = $request->route('event') ?? $request->route('id');

        if ($routeEvent) {
            try {
                // Resolve to model if it's an ID
                $event = is_object($routeEvent) ? $routeEvent : \App\Models\Event::find($routeEvent);
                if ($event) {
                    $isOwner = (int) $event->user_id === (int) $user->id;
                    $isStaff = $event->staff()->where('user_id', $user->id)->exists();
                    if ($isOwner || $isStaff) {
                        // Event owners and staff can always access their events, even if not verified
                        \Log::info('VerifiedOrganizerMiddleware: Allowing access via route parameter', [
                            'user_id' => $user->id,
                            'event_id' => $event->id,
                            'is_owner' => $isOwner,
                            'is_staff' => $isStaff,
                            'route' => $request->route()->getName()
                        ]);
                        return $next($request);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Error checking event access via route parameter', [
                    'error' => $e->getMessage(),
                    'route_event' => $routeEvent,
                    'user_id' => $user->id
                ]);
            }
        }

        // Allow access to edit/save routes for event owners even if not verified
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
            str_contains($routeName, 'update-session-layout') ||
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

            if ($eventParam) {
                try {
                    $event = is_object($eventParam) ? $eventParam : \App\Models\Event::find($eventParam);
                    if ($event) {
                        $isOwner = (int) $event->user_id === (int) $user->id;
                        $isStaff = $event->staff()->where('user_id', $user->id)->exists();
                        if ($isOwner || $isStaff) {
                            // Event owner and staff can always access their events for editing/saving, even if not verified
                            \Log::info('VerifiedOrganizerMiddleware: Allowing access for owner/staff', [
                                'user_id' => $user->id,
                                'event_id' => $event->id,
                                'is_owner' => $isOwner,
                                'is_staff' => $isStaff,
                                'route' => $routeName
                            ]);
                            return $next($request);
                        }
                    }
                } catch (\Exception $e) {
                    // Log error but continue to verification check
                    \Log::warning('Error checking event ownership in VerifiedOrganizerMiddleware', [
                        'error' => $e->getMessage(),
                        'route' => $routeName,
                        'user_id' => $user->id,
                        'event_param' => $eventParam
                    ]);
                }
            } else {
                // Log when event param is not found for debugging
                \Log::debug('VerifiedOrganizerMiddleware: No event parameter found', [
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

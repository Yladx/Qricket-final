<?php

namespace App\Http\Middleware\Organizer;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrganizerGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for guest routes and authentication routes
        $guestRoutes = [
            'welcome', 'login', 'logout', 'register', 'password.request', 
            'password.email', 'password.reset', 'password.update', 'verification.notice', 
            'verification.verify', 'subscription.index', 'events.show', 'events.browse',
            'events.index', 'tickets.purchase', 'tickets.purchase.success', 'tickets.purchase.failure'
        ];
        
        // Allow public routes
        $publicPaths = [
            '/', 'subscription', 'help*', 'support/*', 'events/*', 'tickets/purchase*', 'broadcasting/*'
        ];
        
        if ($request->routeIs($guestRoutes) || $this->isPublicPath($request, $publicPaths)) {
            return $next($request);
        }
        
        // If admin is logged in, allow access to organizer routes/pages
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has organizer privileges
        $user = Auth::user();
        
        // Allow access to organizer routes for all authenticated users
        // Verification is optional and only affects certain features like event creation
        
        // Prevent access to organizer routes if user is not in organizer mode
        if ($request->routeIs('organizer.*')) {
            $currentMode = session('user_mode', 'user');
            if ($currentMode !== 'organizer') {
                \Log::info('Blocked organizer route access - user not in organizer mode', [
                    'user_id' => $user->id,
                    'route' => $request->route()?->getName(),
                    'current_mode' => $currentMode,
                    'url' => $request->url()
                ]);
                
                // Redirect to welcome page with SweetAlert message
                return redirect()->route('welcome')->with('sweetalert', [
                    'title' => 'Access Denied',
                    'text' => 'You must switch to organizer mode to access organizer features.',
                    'icon' => 'warning',
                    'confirmButtonText' => 'Switch to Organizer Mode',
                    'showCancelButton' => true,
                    'cancelButtonText' => 'Stay in User Mode'
                ]);
            }
        }
        
        // Allow access to welcome page from both organizer and user modes
        
        // Prevent access to user routes if user is in organizer mode
        // Exclude logout, profile, mode-switch, API, broadcasting, help, user support, subscription, organizer verification, events routes, and welcome page as they should be accessible from both modes
        $isBroadcastingRoute = str_starts_with($request->path(), 'broadcasting/') || str_starts_with($request->path(), 'admin/broadcasting/');
        $isApiRoute = str_starts_with($request->path(), 'api/');
        $isHelpRoute = str_starts_with($request->path(), 'help');
        $isUserSupportRoute = str_starts_with($request->path(), 'support/'); // User support tickets
        $isSubscriptionRoute = str_starts_with($request->path(), 'subscription');

        // Admin global immunity: allow help, support, subscription, and tickets pages regardless of verification/mode
        if (Auth::guard('admin')->check()) {
            if ($isHelpRoute || $isUserSupportRoute || $isSubscriptionRoute || str_starts_with($request->path(), 'tickets')) {
                return $next($request);
            }
        }
        // Define which routes are accessible from any mode
        $isPublicEventRoute = str_starts_with($request->path(), 'events/') && 
                             !$request->routeIs('events.saved'); // General event pages accessible from any mode (except saved events)
        $isUserSpecificRoute = $request->routeIs('events.saved') || 
                              $request->routeIs('tickets.my-purchases') || 
                              $request->routeIs('tickets.individual') || 
                              $request->routeIs('tickets.details') || 
                              $request->routeIs('tickets.invoice') || 
                              $request->routeIs('tickets.purchase.success') || 
                              $request->routeIs('tickets.purchase.failure'); // User-specific routes blocked in organizer mode
        $isWelcomeRoute = $request->routeIs('welcome') || $request->is('/'); // Welcome page accessible from any mode
        
        // Block user-specific routes if in organizer mode
        if ($isUserSpecificRoute) {
            $currentMode = session('user_mode');
            if ($currentMode === 'organizer') {
                \Log::info('Blocked user-specific route access - user in organizer mode', [
                    'user_id' => $user->id,
                    'route' => $request->route()?->getName(),
                    'current_mode' => $currentMode,
                    'url' => $request->url()
                ]);
                
                // Redirect to organizer dashboard with SweetAlert message
                return redirect()->route('organizer.dashboard')->with('sweetalert', [
                    'title' => 'Access Denied',
                    'text' => 'You must switch to user mode to access user features.',
                    'icon' => 'warning',
                    'confirmButtonText' => 'Switch to User Mode',
                    'showCancelButton' => true,
                    'cancelButtonText' => 'Stay in Organizer Mode'
                ]);
            }
        }
        
        // Block other user routes if in organizer mode
        if (!$request->routeIs('organizer.*') && 
            !$request->routeIs('logout') && 
            !$request->routeIs('profile.*') &&
            !$request->routeIs('user.mode-switch') &&
            !$request->routeIs('organizer.verification') &&
            !$isApiRoute &&
            !$isBroadcastingRoute &&
            !$isHelpRoute &&
            !$isUserSupportRoute &&
            !$isSubscriptionRoute &&
            !$isPublicEventRoute &&
            !$isWelcomeRoute &&
            !$isUserSpecificRoute) {
            $currentMode = session('user_mode');
            if ($currentMode === 'organizer') {
                \Log::info('Blocked user route access - user in organizer mode', [
                    'user_id' => $user->id,
                    'route' => $request->route()?->getName(),
                    'current_mode' => $currentMode,
                    'url' => $request->url()
                ]);
                
                // Redirect to organizer dashboard with SweetAlert message
                return redirect()->route('organizer.dashboard')->with('sweetalert', [
                    'title' => 'Access Denied',
                    'text' => 'You must switch to user mode to access user features.',
                    'icon' => 'warning',
                    'confirmButtonText' => 'Switch to User Mode',
                    'showCancelButton' => true,
                    'cancelButtonText' => 'Stay in Organizer Mode'
                ]);
            }
        }
        
        // Variables are now set by SetNavigationVariables middleware

        // Set user_mode in session based on current route
        $currentRoute = $request->route()?->getName();
        $currentMode = session('user_mode');
        
        if ($request->routeIs('organizer.*')) {
            // Set to organizer when accessing organizer routes (after passing the mode check)
            session(['user_mode' => 'organizer']);
            \Log::info('Set user_mode to organizer', [
                'user_id' => $user->id,
                'route' => $currentRoute,
                'previous_mode' => $currentMode
            ]);
        } else {
            // Set to user when accessing user routes, but only if not manually switched to organizer
            if ($currentMode !== 'organizer') {
                session(['user_mode' => 'user']);
                \Log::info('Set user_mode to user', [
                    'user_id' => $user->id,
                    'route' => $currentRoute,
                    'previous_mode' => $currentMode
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Check if the request path matches any of the public paths
     */
    private function isPublicPath(Request $request, array $publicPaths): bool
    {
        $path = $request->path();
        
        foreach ($publicPaths as $publicPath) {
            if (str_ends_with($publicPath, '*')) {
                $prefix = rtrim($publicPath, '*');
                if (str_starts_with($path, $prefix)) {
                    return true;
                }
            } elseif ($path === $publicPath) {
                return true;
            }
        }
        
        return false;
    }
}
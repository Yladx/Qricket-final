<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return $next($request);
        }

        // Check if user can publish events (this middleware is now only used for publishing, not creation)
        if (!$user->canCreateEvent()) {
            $remainingEvents = $user->getRemainingEventsThisMonth();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Event publishing limit reached',
                    'message' => $remainingEvents === 0 
                        ? 'You have reached your monthly event publishing limit. Please upgrade your subscription to publish more events.'
                        : "You can publish {$remainingEvents} more events this month.",
                    'subscription_required' => true,
                    'remaining_events' => $remainingEvents,
                ], 403);
            }

            return redirect()->route('organizer.subscription.plans')
                ->with('error', 'You have reached your monthly event publishing limit. Please upgrade your subscription to publish more events.');
        }

        return $next($request);
    }
}

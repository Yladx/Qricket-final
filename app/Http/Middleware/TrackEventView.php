<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\EventActivityLog;
use Pusher\Pusher;
use Symfony\Component\HttpFoundation\Response;

class TrackEventView {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only track for GET requests to event pages
        if ($request->isMethod('GET') && $request->route('event')) {
            $event = $request->route('event');
            $user = Auth::user();
            
            // Skip if user is not authenticated or is admin/organizer viewing their own event
            if (!$user || Auth::guard('admin')->check()) {
                return $response;
            }
            
            // Skip if user is the event owner or staff
            if ($event->user_id == $user->id || $event->staff->contains('user_id', $user->id)) {
                return $response;
            }
            
            try {
                // Log the event view (will update existing view if it exists for today)
                $viewRecord = EventActivityLog::logEventView(
                    $event->id,
                    $user->id,
                    "User viewed event: {$event->title}",
                    [
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'referer' => $request->header('referer'),
                        'timestamp' => now()->toISOString()
                    ]
                );
                
                // Trigger Pusher event for real-time updates (only on new views, not updates)
                // Check if this view was newly created (not updated)
                $isNewView = isset($viewRecord->wasRecentlyCreated) && $viewRecord->wasRecentlyCreated;
                if (!$isNewView) {
                    // Check if created_at is very recent (within last 5 seconds) to determine if it's new
                    $isNewView = $viewRecord->created_at->greaterThan(now()->subSeconds(5));
                }
                
                if ($isNewView) {
                    // Only trigger Pusher if this is a fresh view (newly created)
                    // This prevents spamming Pusher on every page refresh
                    $this->triggerPusherEvent('event-viewed', [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'user_name' => $user->first_name . ' ' . $user->last_name,
                        'timestamp' => now()->toISOString()
                    ]);
                }
                
            } catch (\Exception $e) {
                Log::error('Error tracking event view: ' . $e->getMessage());
            }
        }
        
        return $response;
    }
    
    /**
     * Trigger Pusher event
     */
    private function triggerPusherEvent($event, $data)
    {
        try {
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                config('broadcasting.connections.pusher.options')
            );
            
            $pusher->trigger('event-updates', $event, $data);
        } catch (\Exception $e) {
            Log::error('Error triggering Pusher event: ' . $e->getMessage());
        }
    }
}

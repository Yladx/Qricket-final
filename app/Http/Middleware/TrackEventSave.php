<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\EventActivityLog;
use App\Models\ActivityLog;
use Pusher\Pusher;
use Symfony\Component\HttpFoundation\Response;

class TrackEventSave {
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
        
        // Track event save/unsave actions
        if ($request->isMethod('POST') && $request->route('event') && 
            ($request->route()->getName() === 'events.save' || $request->route()->getName() === 'events.unsave')) {
            
            $event = $request->route('event');
            $user = Auth::user();
            
            if (!$user || Auth::guard('admin')->check()) {
                return $response;
            }
            
            // Skip if user is the event owner or staff
            if ($event->user_id == $user->id || $event->staff->contains('user_id', $user->id)) {
                return $response;
            }
            
            try {
                $action = $request->route()->getName() === 'events.save' ? 'save_event' : 'unsave_event';
                $description = $action === 'save_event' ? "User saved event: {$event->title}" : "User unsaved event: {$event->title}";
                
                // Log the event save/unsave in EventActivityLog
                EventActivityLog::create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'action' => $action,
                    'description' => $description,
                    'metadata' => json_encode([
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'timestamp' => now()->toISOString()
                    ])
                ]);
                
                // Also log to general user activity log
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => $action,
                    'description' => $description,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'timestamp' => now()->toISOString()
                    ]
                ]);
                
                // Trigger Pusher event for real-time updates
                $this->triggerPusherEvent('event-saved', [
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'user_name' => $user->first_name . ' ' . $user->last_name,
                    'action' => $action,
                    'timestamp' => now()->toISOString()
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error tracking event save: ' . $e->getMessage());
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

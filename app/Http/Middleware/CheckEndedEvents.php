<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\EventEndNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckEndedEvents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for ended events once per minute (cache key based on current minute)
        $cacheKey = 'ended_events_check_' . now()->format('Y-m-d-H-i');
        
        // Check if we've already processed this minute - if yes, skip
        if (Cache::has($cacheKey)) {
            return $next($request);
        }
        
        // Process ended events (will run in background if queue is configured)
        try {
            Log::info('CheckEndedEvents: Triggered - checking for ended events', [
                'cache_key' => $cacheKey,
                'url' => $request->fullUrl()
            ]);
            
            // Cache immediately to prevent duplicate processing
            Cache::put($cacheKey, true, now()->addMinutes(2));
            
            // Run synchronously but use dispatch to queue it if possible
            if (config('queue.default') !== 'sync') {
                // Queue it for background processing
                dispatch(function() {
                    try {
                        Log::info('CheckEndedEvents: Queue job started');
                        EventEndNotificationService::processEndedEvents();
                    } catch (\Throwable $e) {
                        Log::error('CheckEndedEvents: Queue job failed', [
                            'error' => $e->getMessage()
                        ]);
                    }
                })->afterResponse();
            } else {
                // Run directly if no queue configured
                EventEndNotificationService::processEndedEvents();
            }
            
        } catch (\Throwable $e) {
            Log::error('CheckEndedEvents: Failed to process ended events', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl()
            ]);
        }
        
        return $next($request);
    }
}


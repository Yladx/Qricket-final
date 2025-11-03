<?php

namespace App\Listeners;

use App\Services\EventEndNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CheckEventEndStatus
{
    /**
     * Handle the event.
     */
    public function handle(): void
    {
        // Only run this check once per minute to avoid overload
        $cacheKey = 'event_end_check_' . now()->format('Y-m-d-H-i');
        
        if (Cache::has($cacheKey)) {
            return; // Already processed this minute
        }
        
        // Cache for 2 minutes to ensure we don't run multiple times
        Cache::put($cacheKey, true, now()->addMinutes(2));
        
        try {
            Log::info('CheckEventEndStatus: Triggered automatically', [
                'timestamp' => now()
            ]);
            
            // Run the event end notification service
            $result = EventEndNotificationService::processEndedEvents();
            
            Log::info('CheckEventEndStatus: Completed', [
                'result' => $result
            ]);
            
        } catch (\Throwable $e) {
            Log::error('CheckEventEndStatus: Failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}


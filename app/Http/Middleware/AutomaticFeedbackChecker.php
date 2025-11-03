<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutomaticFeedbackChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // TEMPORARILY DISABLED TO REDUCE DATABASE CONNECTIONS
        return $next($request);
        
        /* DISABLED CODE
        // Skip this middleware for certain routes to avoid performance issues
        $skipRoutes = [
            'api.',
            'admin.',
            'organizer.',
        ];
        
        $routeName = $request->route()?->getName() ?? '';
        $shouldSkip = false;
        
        foreach ($skipRoutes as $skip) {
            if (str_starts_with($routeName, $skip)) {
                $shouldSkip = true;
                break;
            }
        }
        
        // Only run on public user-facing pages to avoid performance impact on admin/organizer areas
        if (!$shouldSkip && auth()->check() && auth()->user()) {
            try {
                // Run in background to avoid blocking the request
                dispatch(function() {
                    \App\Services\AutomaticFeedbackService::sendFeedbackForEndedEvents();
                })->afterResponse();
            } catch (\Exception $e) {
                // Silently fail to not disrupt user experience
                \Log::warning('AutomaticFeedbackChecker failed: ' . $e->getMessage());
            }
        }
        
        return $next($request);
        */
    }
}


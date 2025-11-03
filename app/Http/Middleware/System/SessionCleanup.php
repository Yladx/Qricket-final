<?php

namespace App\Http\Middleware\System;

use App\Services\UserSessionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SessionCleanup
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Clean up expired sessions before processing the request
        $this->cleanupExpiredSessions();
        
        // Check if current session is expired
        $this->checkCurrentSession($request);
        
        return $next($request);
    }

    /**
     * Clean up expired sessions (optimized)
     */
    private function cleanupExpiredSessions()
    {
        // Only check for expired sessions occasionally to reduce load
        $lastCleanup = Cache::get('last_session_cleanup', 0);
        $now = now()->timestamp;
        
        // Only cleanup every 2 minutes to reduce load
        if ($now - $lastCleanup < 120) {
            return;
        }
        
        $sessionDriver = config('session.driver');
        
        if ($sessionDriver === 'database') {
            $expiredCount = DB::table('sessions')
                ->where('last_activity', '<', now()->subMinutes(30)->timestamp)
                ->count();

            if ($expiredCount > 0) {
                // Get the count before cleanup
                $oldCounts = UserSessionService::getCachedCounts();
                
                // Delete expired sessions
                try {
                    DB::table('sessions')
                        ->where('last_activity', '<', now()->subMinutes(30)->timestamp)
                        ->delete();
                } catch (\Exception $e) {
                    \Log::error('SessionCleanup: Failed to delete expired sessions: ' . $e->getMessage());
                    return;
                }
                
                // Update counts and broadcast changes
                $newCounts = UserSessionService::updateCounts();
                
                // Only broadcast if counts actually changed
                if ($oldCounts['users'] !== $newCounts['users'] || 
                    $oldCounts['guests'] !== $newCounts['guests'] || 
                    $oldCounts['admins'] !== $newCounts['admins']) {
                    
                    // Broadcast session cleanup
                    event(new \App\Events\UserActivity('session_cleanup', $newCounts['users'], $newCounts['guests']));
                }
                
                // Update cleanup timestamp
                Cache::put('last_session_cleanup', $now, now()->addMinutes(5));
            }
        } else {
            // For file sessions, clean up expired session files
            $sessionFilesPath = config('session.files', storage_path('framework/sessions'));
            $expiredTimestamp = now()->subMinutes(30)->timestamp;
            $cleanedCount = 0;
            
            if (is_dir($sessionFilesPath)) {
                $files = glob($sessionFilesPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $fileModified = filemtime($file);
                        if ($fileModified < $expiredTimestamp) {
                            @unlink($file);
                            $cleanedCount++;
                        }
                    }
                }
            }
            
            if ($cleanedCount > 0) {
                // Update counts for file sessions
                $oldCounts = UserSessionService::getCachedCounts();
                $newCounts = UserSessionService::updateCounts();
                
                // Only broadcast if counts actually changed
                if ($oldCounts['users'] !== $newCounts['users'] || 
                    $oldCounts['guests'] !== $newCounts['guests'] || 
                    $oldCounts['admins'] !== $newCounts['admins']) {
                    
                    // Broadcast session cleanup
                    event(new \App\Events\UserActivity('session_cleanup', $newCounts['users'], $newCounts['guests']));
                }
                
                // Update cleanup timestamp
                Cache::put('last_session_cleanup', $now, now()->addMinutes(5));
            }
        }
    }

    /**
     * Check if current session is expired (optimized)
     */
    private function checkCurrentSession(Request $request)
    {
        $sessionId = $request->session()->getId();
        
        if ($sessionId) {
            // Use cache to avoid query on every request
            $cacheKey = "session_check_{$sessionId}";
            $lastCheck = Cache::get($cacheKey, 0);
            $now = now()->timestamp;
            
            // Only check session every 5 minutes to reduce load
            if ($now - $lastCheck < 300) {
                return;
            }
            
            $sessionDriver = config('session.driver');
            $sessionExpired = false;
            
            if ($sessionDriver === 'database') {
                try {
                    $session = DB::table('sessions')
                        ->where('id', $sessionId)
                        ->first();
                    
                    if ($session && $session->last_activity < now()->subMinutes(30)->timestamp) {
                        // Session expired, update role to guest
                        DB::table('sessions')
                            ->where('id', $sessionId)
                            ->update(['role' => 'guest']);
                        
                        $sessionExpired = true;
                    }
                } catch (\Exception $e) {
                    \Log::error('SessionCleanup: Failed to check database session: ' . $e->getMessage());
                }
            } else {
                // For file sessions, check if session file exists and is recent
                $sessionFilesPath = config('session.files', storage_path('framework/sessions'));
                $sessionPath = $sessionFilesPath . '/' . $sessionId;
                
                if (file_exists($sessionPath)) {
                    $fileModified = filemtime($sessionPath);
                    if ($fileModified < now()->subMinutes(30)->timestamp) {
                        // Session file expired, clear session role
                        $request->session()->forget('user_role');
                        $sessionExpired = true;
                    }
                }
            }
            
            if ($sessionExpired) {
                // Update counts
                UserSessionService::updateCounts();
            }
            
            // Cache the check timestamp
            Cache::put($cacheKey, $now, now()->addMinutes(5));
        }
    }
}



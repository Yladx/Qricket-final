<?php

namespace App\Services;

use App\Events\UserActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserSessionService
{
    /**
     * Get current logged-in user count
     */
    public static function getLoggedInUserCount()
    {
        // Count active sessions with user role (from database tracking table)
        return DB::table('sessions')
            ->where('role', 'user')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->count();
    }

    /**
     * Get current guest count
     */
    public static function getGuestCount()
    {
        // Count active sessions with guest role (from database tracking table)
        return DB::table('sessions')
            ->where('role', 'guest')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->count();
    }

    /**
     * Get current admin count
     */
    public static function getAdminCount()
    {
        // Count active sessions with admin role (from database tracking table)
        return DB::table('sessions')
            ->where('role', 'admin')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->count();
    }

    /**
     * Update user counts and broadcast to admin dashboard
     */
    public static function updateCounts()
    {
        return self::recalculateAndBroadcast();
    }

    /**
     * Get cached counts
     */
    public static function getCachedCounts()
    {
        return Cache::get('user_counts', [
            'users' => 0,
            'guests' => 0,
            'admins' => 0,
            'total' => 0
        ]);
    }

    /**
     * Handle user login
     */
    public static function handleUserLogin($userId)
    {
        // Update session role in database (for tracking, regardless of session driver)
        $sessionId = session()->getId();
        if ($sessionId) {
            // Check if session record exists
            $exists = DB::table('sessions')->where('id', $sessionId)->exists();
            
            if ($exists) {
                // Update existing record
                DB::table('sessions')
                    ->where('id', $sessionId)
                    ->update([
                        'role' => 'user',
                        'user_id' => $userId,
                        'last_activity' => now()->timestamp,
                    ]);
            } else {
                // Insert new record with required payload field
                DB::table('sessions')->insert([
                    'id' => $sessionId,
                    'role' => 'user',
                    'user_id' => $userId,
                    'last_activity' => now()->timestamp,
                    'payload' => '', // Empty payload for tracking-only records
                ]);
            }
        }

        // Update counts and broadcast
        $counts = self::updateCounts();

        // Ensure immediate update is broadcast even if cache comparison suppresses it
        $onlineAdminIds = DB::table('sessions')
            ->where('role', 'admin')
            ->whereNotNull('user_id')
            ->where('last_activity', '>', now()->subMinutes(5)->timestamp)
            ->pluck('user_id')
            ->toArray();
            
        event(new \App\Events\UserActivity('count_update', $counts['users'], $counts['guests'], $onlineAdminIds));
    }

    /**
     * Handle user logout
     */
    public static function handleUserLogout()
    {
        // Update session role in database
        $sessionId = session()->getId();
        if ($sessionId) {
            // Update session record for tracking
            DB::table('sessions')
                ->where('id', $sessionId)
                ->update([
                    'role' => 'guest',
                    'user_id' => null,
                    'last_activity' => now()->timestamp,
                ]);
        }

        // Update counts and broadcast
        self::updateCounts();
    }

    /**
     * Handle admin login
     */
    public static function handleAdminLogin($adminId)
    {
        // Update session role in database (for tracking)
        $sessionId = session()->getId();
        if ($sessionId) {
            // Get admin's user_id from shadow user if available, or use admin_id
            $shadowEmail = 'admin+' . $adminId . '@admin.local';
            $shadowUser = DB::table('users')->where('email', $shadowEmail)->first();
            $userId = $shadowUser ? $shadowUser->id : null;
            
            // Check if session record exists
            $exists = DB::table('sessions')->where('id', $sessionId)->exists();
            
            if ($exists) {
                // Update existing record
                DB::table('sessions')
                    ->where('id', $sessionId)
                    ->update([
                        'role' => 'admin',
                        'user_id' => $userId,
                        'last_activity' => now()->timestamp,
                    ]);
            } else {
                // Insert new record with required payload field
                DB::table('sessions')->insert([
                    'id' => $sessionId,
                    'role' => 'admin',
                    'user_id' => $userId,
                    'last_activity' => now()->timestamp,
                    'payload' => '', // Empty payload for tracking-only records
                ]);
            }
        }

        // Update counts and broadcast
        self::updateCounts();
    }

    /**
     * Handle admin logout
     */
    public static function handleAdminLogout()
    {
        // Update session role in database
        $sessionId = session()->getId();
        if ($sessionId) {
            // Update session record for tracking
            DB::table('sessions')
                ->where('id', $sessionId)
                ->update([
                    'role' => 'guest',
                    'user_id' => null,
                    'last_activity' => now()->timestamp,
                ]);
        }

        // Update counts and broadcast
        self::updateCounts();
    }

    /**
     * Force immediate update of counts (for testing or manual triggers)
     */
    public static function forceUpdate()
    {
        // Clear cache to force fresh calculation
        Cache::forget('user_counts');
        
        // Update and broadcast
        return self::updateCounts();
    }

    /**
     * Monitor sessions and clean up expired ones
     */
    public static function monitorSessions()
    {
        // Clean up expired sessions
        $expiredCount = DB::table('sessions')
            ->where('last_activity', '<', now()->subMinutes(30)->timestamp)
            ->count();

        if ($expiredCount > 0) {
            // Delete expired sessions
            DB::table('sessions')
                ->where('last_activity', '<', now()->subMinutes(30)->timestamp)
                ->delete();
            
            // Update counts
            self::updateCounts();
            
            return $expiredCount;
        }

        return 0;
    }

    /**
     * Clean up orphaned admin sessions (admin sessions without valid admin authentication)
     */
    public static function cleanupOrphanedAdminSessions()
    {
        // Get all admin sessions from database
        $adminSessions = DB::table('sessions')
            ->where('role', 'admin')
            ->whereNotNull('user_id')
            ->get();

        $cleanedCount = 0;

        foreach ($adminSessions as $session) {
            // Check if the admin user still exists and has a valid session
            $admin = DB::table('admins')->where('id', $session->user_id)->first();
            
            if (!$admin || !$admin->current_session_id || $admin->current_session_id !== $session->id) {
                // This is an orphaned admin session - clean it up
                DB::table('sessions')
                    ->where('id', $session->id)
                    ->update(['role' => 'guest', 'user_id' => null]);
                
                $cleanedCount++;
            }
        }

        if ($cleanedCount > 0) {
            // Update counts after cleanup
            self::updateCounts();
        }

        return $cleanedCount;
    }

    /**
     * Get detailed session information
     */
    public static function getSessionDetails()
    {
        // Always query database sessions table for tracking (works for both drivers)
        $sessions = DB::table('sessions')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->select('role', 'last_activity', 'user_id')
            ->get();

        $details = [
            'users' => $sessions->where('role', 'user')->count(),
            'guests' => $sessions->where('role', 'guest')->count(),
            'admins' => $sessions->where('role', 'admin')->count(),
            'total' => $sessions->count(),
            'sessions' => $sessions->groupBy('role')->toArray()
        ];

        return $details;
    }

    /**
     * Check if counts have changed without updating cache (optimized)
     */
    public static function hasCountsChanged()
    {
        // Get cached counts first (fast)
        $cachedCounts = Cache::get('user_counts', [
            'users' => 0,
            'guests' => 0,
            'admins' => 0,
        ]);

        // Get current counts with a single optimized query
        $currentCounts = self::getAllCountsOptimized();

        return $currentCounts['users'] !== $cachedCounts['users'] ||
               $currentCounts['guests'] !== $cachedCounts['guests'] ||
               $currentCounts['admins'] !== $cachedCounts['admins'];
    }

    /**
     * Get all counts with a single optimized query
     */
    public static function getAllCountsOptimized()
    {
        $sessionDriver = config('session.driver');
        
        if ($sessionDriver === 'database') {
            $counts = DB::table('sessions')
                ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                ->selectRaw('
                    SUM(CASE WHEN (user_id IS NOT NULL AND (role IS NULL OR role <> "admin")) OR role = "user" THEN 1 ELSE 0 END) as users,
                    SUM(CASE WHEN (user_id IS NULL AND (role IS NULL OR role <> "admin")) OR role = "guest" THEN 1 ELSE 0 END) as guests,
                    SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as admins
                ')
                ->first();

            return [
                'users' => (int) ($counts->users ?? 0),
                'guests' => (int) ($counts->guests ?? 0),
                'admins' => (int) ($counts->admins ?? 0),
            ];
        } else {
            // For file sessions, count from database sessions table (which is maintained separately for tracking)
            // Even when using file driver, we maintain the sessions table for online tracking
            $counts = DB::table('sessions')
                ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                ->selectRaw('
                    SUM(CASE WHEN (user_id IS NOT NULL AND (role IS NULL OR role <> "admin")) OR role = "user" THEN 1 ELSE 0 END) as users,
                    SUM(CASE WHEN (user_id IS NULL AND (role IS NULL OR role <> "admin")) OR role = "guest" THEN 1 ELSE 0 END) as guests,
                    SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as admins
                ')
                ->first();

            return [
                'users' => (int) ($counts->users ?? 0),
                'guests' => (int) ($counts->guests ?? 0),
                'admins' => (int) ($counts->admins ?? 0),
            ];
        }
    }

    /**
     * Get counts only if they have changed
     */
    public static function getCountsIfChanged()
    {
        // Always recalc to guarantee fresh values; broadcast only if changed
        return [
            'counts' => self::recalculateAndBroadcast(),
            'changed' => true,
        ];
    }

    /**
     * Recalculate counts in a single query, cache them, and broadcast when changed.
     */
    public static function recalculateAndBroadcast(): array
    {
        // Use optimized counts that consider user_id/role and exclude admins from user/guest buckets
        $optimized = self::getAllCountsOptimized();

        $newCounts = [
            'users' => (int) ($optimized['users'] ?? 0),
            'guests' => (int) ($optimized['guests'] ?? 0),
            'admins' => (int) ($optimized['admins'] ?? 0),
        ];
        $newCounts['total'] = $newCounts['users'] + $newCounts['guests'] + $newCounts['admins'];

        $old = Cache::get('user_counts', ['users'=>-1,'guests'=>-1,'admins'=>-1,'total'=>-1]);
        Cache::put('user_counts', $newCounts, now()->addMinutes(5));

        if ($old['users'] !== $newCounts['users'] || $old['guests'] !== $newCounts['guests'] || $old['admins'] !== $newCounts['admins']) {
            // Get online admin IDs
            $onlineAdminIds = DB::table('sessions')
                ->where('role', 'admin')
                ->whereNotNull('user_id')
                ->where('last_activity', '>', now()->subMinutes(5)->timestamp)
                ->pluck('user_id')
                ->toArray();
                
            event(new UserActivity('count_update', $newCounts['users'], $newCounts['guests'], $onlineAdminIds));
        }

        return $newCounts;
    }
}



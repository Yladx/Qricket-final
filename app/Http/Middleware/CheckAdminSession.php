<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Admin;
use App\Events\AdminLoggedOut;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware for admin login and logout routes to prevent interference
        if ($request->routeIs('admin.login') || $request->routeIs('admin.pin') || $request->routeIs('admin.logout')) {
            \Log::info('CheckAdminSession: Skipping middleware for route: ' . $request->route()->getName());
            return $next($request);
        }
        
        \Log::info('CheckAdminSession: Running middleware for route: ' . ($request->route() ? $request->route()->getName() : 'unknown'));
        
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            // Check if current session matches the stored session ID
            // Only check for conflicts if there's a stored session ID that differs from current
            // This prevents false positives during logout or session regeneration
            if ($admin->current_session_id && $admin->current_session_id !== session()->getId()) {
                // Only verify session in database if using database session driver
                $sessionDriver = config('session.driver');
                $existingSession = null;
                
                if ($sessionDriver === 'database') {
                    // Verify the session actually exists and is active
                    // Only treat as conflict if the other session is actively being used (recent activity)
                    try {
                        $existingSession = \Illuminate\Support\Facades\DB::table('sessions')
                            ->where('id', $admin->current_session_id)
                            ->where('role', 'admin')
                            ->where('last_activity', '>', now()->subMinutes(5)) // Only if very recent (active session)
                            ->first();
                    } catch (\Exception $e) {
                        \Log::error('CheckAdminSession: Failed to check database session: ' . $e->getMessage());
                    }
                } else {
                    // For file sessions, check if the session file exists and is very recent
                    $sessionFilesPath = config('session.files', storage_path('framework/sessions'));
                    $sessionPath = $sessionFilesPath . '/' . $admin->current_session_id;
                    if (file_exists($sessionPath)) {
                        // Check if session file is very recent (modified within last 5 minutes)
                        // This indicates an active session from another device
                        $fileModified = filemtime($sessionPath);
                        if ($fileModified > now()->subMinutes(5)->timestamp) {
                            $existingSession = (object)['id' => $admin->current_session_id];
                        }
                    }
                }
                    
                // Only trigger conflict if there's actually an active conflicting session
                // This should only happen when someone logs in from another device
                // IMPORTANT: Don't fire Pusher event here - it's already fired during login in AdminAuthController
                // This middleware just handles the cleanup for the old session when it makes its next request
                if ($existingSession) {
                    \Log::info('CheckAdminSession: Active session conflict detected - this is the OLD session being logged out', [
                        'old_session_id' => session()->getId(),
                        'new_session_id' => $admin->current_session_id,
                    ]);
                    
                    // This is the OLD session - just log out, don't fire another Pusher event
                    // The Pusher event was already fired during the new device's login process
                    // Firing it again would cause duplicate notifications on both devices
                    
                    // Session conflict detected - logout the admin
                    Auth::guard('admin')->logout();
                    Auth::guard('web')->logout();
                    
                    // Clear session tracking and update session role
                    $admin->update(['current_session_id' => null]);
                    \App\Services\UserSessionService::handleAdminLogout();
                    
                    // Log session conflict
                    $admin->logActivity('session_conflict', [
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'stored_session_id' => $admin->current_session_id,
                        'current_session_id' => session()->getId(),
                    ]);
                    
                    // Redirect - the Pusher notification should have already shown the message
                    // Only show message here if Pusher didn't work
                    return redirect()->route('admin.login')
                        ->with('session_conflict', 'Your session has expired due to login from another location. Please log in again.');
                } else {
                    // No active conflicting session - just clean up stale session ID
                    // This happens when logging out normally (old session ID becomes stale)
                    \Log::info('CheckAdminSession: Stale session ID detected, cleaning up');
                    $admin->update(['current_session_id' => null]);
                }
            }
            
            // Update session ID if it's not set (for existing sessions)
            if (!$admin->current_session_id) {
                $admin->update(['current_session_id' => session()->getId()]);
            }
        } else {
            // If no admin is authenticated but session still shows admin role, clean it up
            $sessionRole = session('user_role');
            if ($sessionRole === 'admin') {
                \Log::info('CheckAdminSession: Cleaning up stale admin session data');
                // Clear session conflict message if it exists to prevent stale messages
                session()->forget('session_conflict');
                
                session()->forget('user_role');
                session()->forget('admin_role');
                session()->forget('admin_id');
                session()->forget('admin_as_user');
                
                // Also clean up database session (only if using database session driver)
                $sessionDriver = config('session.driver');
                if ($sessionDriver === 'database') {
                    $sessionId = session()->getId();
                    if ($sessionId) {
                        try {
                            \Illuminate\Support\Facades\DB::table('sessions')
                                ->where('id', $sessionId)
                                ->update(['role' => 'guest', 'user_id' => null]);
                        } catch (\Exception $e) {
                            \Log::error('CheckAdminSession: Failed to update database session: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
        
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware\System;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetSessionRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = session()->getId();
        
        // Determine role based on authentication status
        $role = 'guest';
        $userIdForSession = null;
        
        // Check admin authentication first
        if (Auth::guard('admin')->check()) {
            $role = 'admin';
            $userIdForSession = Auth::guard('admin')->id();
        } 
        // Then check user authentication
        elseif (Auth::guard('web')->check()) {
            $role = 'user';
            $userIdForSession = Auth::guard('web')->id();
        }
        
        // Update session role and user_id in database for every request
        // This maintains the sessions table for online tracking, regardless of session driver
        // Even when using 'file' driver, we still maintain the database table for tracking purposes
        if ($sessionId) {
            try {
                $updateData = [
                    'role' => $role,
                    'last_activity' => now()->timestamp,
                ];
                
                // For admin, get the shadow user's ID for tracking
                if ($role === 'admin' && $userIdForSession) {
                    // Admin ID is stored, but we need the shadow user ID for consistency
                    $shadowEmail = 'admin+' . $userIdForSession . '@admin.local';
                    $shadowUser = DB::table('users')->where('email', $shadowEmail)->first();
                    $updateData['user_id'] = $shadowUser ? $shadowUser->id : null;
                } elseif ($userIdForSession && $role === 'user') {
                    // Regular user - use their user_id directly
                    $updateData['user_id'] = $userIdForSession;
                } else {
                    // Clear user_id for guests
                    $updateData['user_id'] = null;
                }

                // Check if session record exists
                $exists = DB::table('sessions')->where('id', $sessionId)->exists();
                
                if ($exists) {
                    // Update existing record (don't overwrite payload)
                    DB::table('sessions')
                        ->where('id', $sessionId)
                        ->update($updateData);
                } else {
                    // Insert new record with required payload field
                    // When using file sessions, payload can be empty since we're just tracking
                    $insertData = array_merge($updateData, [
                        'id' => $sessionId,
                        'payload' => '', // Empty payload for tracking-only records
                    ]);
                    DB::table('sessions')->insert($insertData);
                }
            } catch (\Exception $e) {
                // Log error but don't break the request
                \Log::error('Failed to update session role: ' . $e->getMessage());
            }
        }
        
        // Store in current session for immediate access (works for all session drivers)
        session(['user_role' => $role]);
        
        return $next($request);
    }
}
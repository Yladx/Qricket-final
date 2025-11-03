<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SessionRoleHelper
{
    /**
     * Get the current session role
     */
    public static function getCurrentRole(): string
    {
        $sessionId = Session::getId();
        
        if (!$sessionId) {
            return 'guest';
        }
        
        $session = DB::table('sessions')
            ->where('id', $sessionId)
            ->first();
            
        return $session ? $session->role : 'guest';
    }
    
    /**
     * Check if current session is admin
     */
    public static function isAdmin(): bool
    {
        return self::getCurrentRole() === 'admin';
    }
    
    /**
     * Check if current session is user
     */
    public static function isUser(): bool
    {
        return self::getCurrentRole() === 'user';
    }
    
    /**
     * Check if current session is guest
     */
    public static function isGuest(): bool
    {
        return self::getCurrentRole() === 'guest';
    }
    
    /**
     * Get all sessions with their roles
     */
    public static function getAllSessionsWithRoles()
    {
        return DB::table('sessions')
            ->select('id', 'user_id', 'role', 'ip_address', 'user_agent', 'last_activity')
            ->orderBy('last_activity', 'desc')
            ->get();
    }
}

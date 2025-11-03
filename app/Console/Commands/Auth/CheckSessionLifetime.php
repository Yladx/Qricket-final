<?php

namespace App\Console\Commands\Auth;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CheckSessionLifetime extends Command
{
    protected $signature = 'session:lifetime';
    protected $description = 'Check and display current session lifetime configuration';

    public function handle()
    {
        $this->info('=== Session Lifetime Configuration ===');
        
        // Get session configuration
        $lifetime = config('session.lifetime', 120);
        $expireOnClose = config('session.expire_on_close', false);
        $driver = config('session.driver', 'database');
        
        $this->info("\n📋 Current Session Configuration:");
        $this->info("   Lifetime: {$lifetime} minutes");
        $this->info("   Expire on Close: " . ($expireOnClose ? 'YES' : 'NO'));
        $this->info("   Driver: {$driver}");
        
        // Check active sessions
        $activeSessions = DB::table('sessions')->count();
        $expiredSessions = DB::table('sessions')
            ->where('last_activity', '<', now()->subMinutes($lifetime)->timestamp)
            ->count();
        
        $this->info("\n📊 Session Statistics:");
        $this->info("   Total Sessions: {$activeSessions}");
        $this->info("   Expired Sessions: {$expiredSessions}");
        $this->info("   Active Sessions: " . ($activeSessions - $expiredSessions));
        
        // Show recent sessions
        $this->info("\n🕐 Recent Sessions:");
        $recentSessions = DB::table('sessions')
            ->select('id', 'user_id', 'role', 'last_activity')
            ->orderBy('last_activity', 'desc')
            ->limit(5)
            ->get();
        
        $headers = ['Session ID', 'User ID', 'Role', 'Last Activity', 'Status'];
        $rows = [];
        
        foreach ($recentSessions as $session) {
            $lastActivity = \Carbon\Carbon::createFromTimestamp($session->last_activity);
            $minutesAgo = now()->diffInMinutes($lastActivity);
            $isExpired = $minutesAgo > $lifetime;
            
            $rows[] = [
                substr($session->id, 0, 15) . '...',
                $session->user_id ?? 'N/A',
                $session->role ?? 'guest',
                $lastActivity->format('Y-m-d H:i:s'),
                $isExpired ? 'EXPIRED' : "Active ({$minutesAgo}m ago)"
            ];
        }
        
        $this->table($headers, $rows);
        
        $this->info("\n💡 To Change Session Lifetime:");
        $this->info("   1. Add to .env file:");
        $this->info("      SESSION_LIFETIME=30");
        $this->info("      SESSION_EXPIRE_ON_CLOSE=false");
        $this->info("   2. Run: php artisan config:clear");
        $this->info("   3. Restart your server");
        
        return 0;
    }
}
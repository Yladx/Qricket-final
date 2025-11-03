<?php

namespace App\Console\Commands\Auth;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;

class DebugSession extends Command
{
    protected $signature = 'debug:session';
    protected $description = 'Debug session conflict issues';

    public function handle()
    {
        $this->info('=== Session Debug Information ===');
        
        // Check admin record
        $admin = Admin::where('username', 'admin')->first();
        if ($admin) {
            $this->info("Admin ID: {$admin->id}");
            $this->info("Admin current_session_id: " . ($admin->current_session_id ?: 'NULL'));
            $this->info("Admin locked_until: " . ($admin->locked_until ?: 'NULL'));
            $this->info("Admin failed_login_attempts: {$admin->failed_login_attempts}");
        } else {
            $this->error('Admin not found');
        }
        
        // Check sessions table
        $this->info("\n=== Sessions Table ===");
        $sessions = DB::table('sessions')->get();
        $this->info("Total sessions: " . $sessions->count());
        
        foreach ($sessions as $session) {
            $this->info("Session ID: {$session->id}");
            $this->info("  Role: {$session->role}");
            $this->info("  User ID: " . ($session->user_id ?: 'NULL'));
            $this->info("  Last Activity: {$session->last_activity}");
            $this->info("  ---");
        }
        
        // Check if there are any admin sessions
        $adminSessions = DB::table('sessions')->where('role', 'admin')->get();
        $this->info("\nAdmin sessions: " . $adminSessions->count());
        
        if ($adminSessions->count() > 0) {
            foreach ($adminSessions as $session) {
                $this->warn("Found admin session: {$session->id}");
            }
        }
        
        $this->info("\n=== Recommendations ===");
        if ($adminSessions->count() > 0) {
            $this->warn('There are admin sessions in the database. This might cause session conflicts.');
            $this->info('Run: php artisan tinker --execute="DB::table(\'sessions\')->where(\'role\', \'admin\')->delete();"');
        }
        
        if ($admin && $admin->current_session_id) {
            $this->warn('Admin has a current_session_id set. This might cause session conflicts.');
            $this->info('Run: php artisan tinker --execute="App\Models\Admin::where(\'username\', \'admin\')->update([\'current_session_id\' => null]);"');
        }
        
        $this->info('Debug complete.');
    }
}
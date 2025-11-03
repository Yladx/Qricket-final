<?php

namespace App\Console\Commands\Auth;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class TestAdminLogin extends Command
{
    protected $signature = 'test:admin-login {username} {password}';
    protected $description = 'Test admin login logic without actually logging in';

    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');
        
        $this->info("Testing admin login for username: {$username}");
        
        $admin = Admin::where('username', $username)->first();
        
        if (!$admin) {
            $this->error("Admin with username '{$username}' not found.");
            return 1;
        }
        
        $this->info("Admin found: ID {$admin->id}");
        $this->info("Current session ID: " . ($admin->current_session_id ?: 'NULL'));
        $this->info("Locked until: " . ($admin->locked_until ?: 'NULL'));
        $this->info("Failed attempts: {$admin->failed_login_attempts}");
        
        // Test password
        if (Hash::check($password, $admin->password)) {
            $this->info("✓ Password is correct");
            
            // Test session conflict logic
            if ($admin->current_session_id) {
                $this->info("Admin has current_session_id: {$admin->current_session_id}");
                
                $existingSession = DB::table('sessions')
                    ->where('id', $admin->current_session_id)
                    ->where('role', 'admin')
                    ->where('last_activity', '>', now()->subMinutes(30))
                    ->first();
                    
                if ($existingSession) {
                    $this->warn("⚠️  Would show session conflict message");
                    $this->info("Existing session found: {$existingSession->id}");
                } else {
                    $this->info("✓ No active session conflict - would clean up stale session ID");
                }
            } else {
                $this->info("✓ No current_session_id - no session conflict possible");
            }
            
        } else {
            $this->error("✗ Password is incorrect");
        }
        
        $this->info("Test complete.");
        return 0;
    }
}
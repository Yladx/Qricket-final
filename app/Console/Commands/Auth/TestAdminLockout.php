<?php

namespace App\Console\Commands\Auth;

use Illuminate\Console\Command;
use App\Models\Admin;
use Carbon\Carbon;

class TestAdminLockout extends Command
{
    protected $signature = 'test:admin-lockout {username}';
    protected $description = 'Test admin lockout functionality by simulating a lockout';

    public function handle()
    {
        $username = $this->argument('username');
        
        $admin = Admin::where('username', $username)->first();
        
        if (!$admin) {
            $this->error("Admin with username '{$username}' not found.");
            return 1;
        }
        
        // Simulate a lockout for 2 minutes
        $admin->failed_login_attempts = 3;
        $admin->locked_until = Carbon::now()->addMinutes(2);
        $admin->save();
        
        $this->info("Admin '{$username}' has been locked for 2 minutes.");
        $this->info("Lockout expires at: " . $admin->locked_until->format('Y-m-d H:i:s'));
        $this->info("Visit the admin login page to test the lockout timer.");
        
        return 0;
    }
}


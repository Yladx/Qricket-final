<?php

namespace App\Console\Commands\Admin;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Events\AdminLoggedOut;

class TestLogoutNotification extends Command
{
    protected $signature = 'test:logout-notification {admin_id} {reason=session_conflict}';
    protected $description = 'Test admin logout notification via Pusher';

    public function handle()
    {
        $adminId = $this->argument('admin_id');
        $reason = $this->argument('reason');
        
        $admin = Admin::find($adminId);
        
        if (!$admin) {
            $this->error("Admin with ID '{$adminId}' not found.");
            return 1;
        }
        
        $this->info("Sending logout notification to admin: {$admin->username} (ID: {$adminId})");
        $this->info("Reason: {$reason}");
        
        // Fire the logout event
        event(new AdminLoggedOut($adminId, $reason, [
            'test_mode' => true,
            'triggered_by' => 'console_command',
            'timestamp' => now()->toDateTimeString()
        ]));
        
        $this->info("✅ Logout notification sent successfully!");
        $this->info("Check the admin dashboard in another browser/tab to see the SweetAlert notification.");
        
        return 0;
    }
}
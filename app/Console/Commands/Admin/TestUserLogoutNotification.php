<?php

namespace App\Console\Commands\Admin;

use Illuminate\Console\Command;
use App\Models\User;
use App\Events\UserLoggedOut;

class TestUserLogoutNotification extends Command
{
    protected $signature = 'test:user-logout-notification {user_id} {reason=session_conflict}';
    protected $description = 'Test user logout notification via Pusher';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $reason = $this->argument('reason');
        
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID '{$userId}' not found.");
            return 1;
        }
        
        $this->info("Sending logout notification to user: {$user->name} (ID: {$userId})");
        $this->info("Reason: {$reason}");
        
        // Fire the logout event
        event(new UserLoggedOut($userId, $reason, [
            'test_mode' => true,
            'triggered_by' => 'console_command',
            'timestamp' => now()->toDateTimeString()
        ]));
        
        $this->info("✅ User logout notification sent successfully!");
        $this->info("Check the user dashboard in another browser/tab to see the SweetAlert notification.");
        
        return 0;
    }
}
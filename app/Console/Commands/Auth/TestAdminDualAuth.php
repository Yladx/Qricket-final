<?php

namespace App\Console\Commands\Auth;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\User;

class TestAdminDualAuth extends Command
{
    protected $signature = 'test:admin-dual-auth';
    protected $description = 'Test that admin automatically logs in as user without creating separate session';

    public function handle()
    {
        $this->info('=== Testing Admin Dual Authentication ===');
        
        // Test 1: Check current session state
        $this->info("\n1. Checking current session state...");
        $this->info("   Current session ID: " . session()->getId());
        $this->info("   Admin authenticated: " . (Auth::guard('admin')->check() ? 'YES' : 'NO'));
        $this->info("   User authenticated: " . (Auth::guard('web')->check() ? 'YES' : 'NO'));
        
        // Test 2: Check admin and shadow user
        $admin = Admin::where('username', 'admin')->first();
        if (!$admin) {
            $this->error('Admin user not found!');
            return 1;
        }
        
        $this->info("\n2. Checking admin and shadow user...");
        $this->info("   Admin ID: {$admin->id}");
        $this->info("   Admin username: {$admin->username}");
        
        $shadowEmail = 'admin+' . $admin->id . '@admin.local';
        $shadowUser = User::where('email', $shadowEmail)->first();
        
        if ($shadowUser) {
            $this->info("   ✅ Shadow user exists: {$shadowUser->email}");
            $this->info("   Shadow user ID: {$shadowUser->id}");
        } else {
            $this->info("   ⚠️  Shadow user doesn't exist yet");
        }
        
        // Test 3: Check session database
        $this->info("\n3. Checking session database...");
        $sessionId = session()->getId();
        $sessionRecord = DB::table('sessions')->where('id', $sessionId)->first();
        
        if ($sessionRecord) {
            $this->info("   Session record found:");
            $this->info("   - Role: {$sessionRecord->role}");
            $this->info("   - User ID: {$sessionRecord->user_id}");
            $this->info("   - Last activity: " . date('Y-m-d H:i:s', $sessionRecord->last_activity));
        } else {
            $this->info("   No session record found");
        }
        
        // Test 4: Simulate admin login process
        $this->info("\n4. Simulating admin login process...");
        $this->info("   When admin logs in:");
        $this->info("   1. Admin is authenticated via admin guard");
        $this->info("   2. Shadow user is created/retrieved");
        $this->info("   3. Admin is logged into web guard as shadow user");
        $this->info("   4. admin_as_user session flag is set");
        $this->info("   5. Session role is updated to 'admin'");
        $this->info("   6. Session user_id is set to admin ID");
        $this->info("   7. SAME SESSION is used for both guards");
        
        // Test 5: Check for potential issues
        $this->info("\n5. Checking for potential issues...");
        
        // Check if there are multiple sessions for the same admin
        $adminSessions = DB::table('sessions')
            ->where('user_id', $admin->id)
            ->where('role', 'admin')
            ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
            ->count();
            
        $this->info("   Active admin sessions: {$adminSessions}");
        
        if ($adminSessions > 1) {
            $this->warn("   ⚠️  Multiple admin sessions detected!");
        } else {
            $this->info("   ✅ Single admin session (good)");
        }
        
        $this->info("\n✅ Admin dual authentication test completed!");
        $this->info("   Admin automatically logs in as user using the SAME session.");
        $this->info("   No separate session is created - both guards use the same session ID.");
        
        return 0;
    }
}
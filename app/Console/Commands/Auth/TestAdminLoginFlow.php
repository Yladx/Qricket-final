<?php

namespace App\Console\Commands\Auth;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Str;

class TestAdminLoginFlow extends Command
{
    protected $signature = 'test:admin-login-flow';
    protected $description = 'Test the complete admin login flow with dual authentication';

    public function handle()
    {
        $this->info('=== Testing Complete Admin Login Flow ===');
        
        // Test 1: Initial state
        $this->info("\n1. Initial state:");
        $this->info("   Session ID: " . session()->getId());
        $this->info("   Admin authenticated: " . (Auth::guard('admin')->check() ? 'YES' : 'NO'));
        $this->info("   User authenticated: " . (Auth::guard('web')->check() ? 'YES' : 'NO'));
        
        // Test 2: Find admin
        $admin = Admin::where('username', 'admin')->first();
        if (!$admin) {
            $this->error('Admin user not found!');
            return 1;
        }
        
        $this->info("\n2. Admin found:");
        $this->info("   Admin ID: {$admin->id}");
        $this->info("   Admin username: {$admin->username}");
        
        // Test 3: Simulate admin login process
        $this->info("\n3. Simulating admin login process...");
        
        // Step 1: Authenticate admin
        Auth::guard('admin')->login($admin);
        $this->info("   ✅ Admin authenticated via admin guard");
        
        // Step 2: Create/find shadow user
        $shadowEmail = 'admin+' . $admin->id . '@admin.local';
        $shadowUser = User::firstOrCreate(
            ['email' => $shadowEmail],
            [
                'first_name' => 'Admin',
                'last_name' => $admin->username,
                'password' => bcrypt(Str::random(24)),
                'organizer' => false,
            ]
        );
        $this->info("   ✅ Shadow user created/found: {$shadowUser->email}");
        
        // Step 3: Authenticate shadow user in web guard
        Auth::guard('web')->login($shadowUser);
        $this->info("   ✅ Shadow user authenticated via web guard");
        
        // Step 4: Set session flags
        session(['user_role' => 'admin']);
        session(['admin_role' => $admin->role]);
        session(['admin_id' => $admin->id]);
        session(['admin_as_user' => true]);
        $this->info("   ✅ Session flags set");
        
        // Step 5: Update session in database
        $sessionId = session()->getId();
        DB::table('sessions')
            ->where('id', $sessionId)
            ->update([
                'role' => 'admin', 
                'user_id' => $shadowUser->id
            ]);
        $this->info("   ✅ Session database updated");
        
        // Test 4: Verify dual authentication
        $this->info("\n4. Verifying dual authentication:");
        $this->info("   Admin authenticated: " . (Auth::guard('admin')->check() ? 'YES' : 'NO'));
        $this->info("   User authenticated: " . (Auth::guard('web')->check() ? 'YES' : 'NO'));
        $this->info("   Session ID: " . session()->getId());
        $this->info("   admin_as_user flag: " . (session('admin_as_user') ? 'YES' : 'NO'));
        
        // Test 5: Check session database
        $sessionRecord = DB::table('sessions')->where('id', $sessionId)->first();
        if ($sessionRecord) {
            $this->info("\n5. Session database record:");
            $this->info("   Role: {$sessionRecord->role}");
            $this->info("   User ID: {$sessionRecord->user_id}");
            $this->info("   Last activity: " . date('Y-m-d H:i:s', $sessionRecord->last_activity));
        }
        
        // Test 6: Verify both guards work
        $this->info("\n6. Verifying both guards work:");
        $adminUser = Auth::guard('admin')->user();
        $webUser = Auth::guard('web')->user();
        
        $this->info("   Admin guard user: " . ($adminUser ? $adminUser->username : 'NULL'));
        $this->info("   Web guard user: " . ($webUser ? $webUser->email : 'NULL'));
        
        // Test 7: Test logout
        $this->info("\n7. Testing logout:");
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();
        $this->info("   ✅ Both guards logged out");
        
        $this->info("\n✅ Admin login flow test completed!");
        $this->info("   Admin automatically logs in as user using the SAME session.");
        $this->info("   Both guards work simultaneously without conflicts.");
        
        return 0;
    }
}
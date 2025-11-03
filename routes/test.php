<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// Debug route for verification document URLs
Route::get('/debug-verification-urls', function () {
    try {
        $verification = \App\Models\OrganizerVerification::first();
        
        if (!$verification) {
            return response()->json([
                'status' => 'error',
                'message' => 'No verification records found'
            ]);
        }
        
        $disk = Storage::disk('r2');
        
        return response()->json([
            'status' => 'success',
            'verification_id' => $verification->id,
            'file_path' => $verification->document_file_path,
            'file_exists' => $disk->exists($verification->document_file_path),
            'document_url' => $verification->document_url,
            'download_url' => $verification->download_url,
            'is_pdf' => $verification->isPdf(),
            'is_image' => $verification->isImage(),
            'file_extension' => pathinfo($verification->document_file_path, PATHINFO_EXTENSION),
            'bucket' => config('filesystems.disks.r2.bucket'),
            'endpoint' => config('filesystems.disks.r2.endpoint'),
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('debug.verification.urls');

// Test route for R2 storage
Route::get('/test-r2-storage', function () {
    try {
        $disk = Storage::disk('r2');
        
        // Test if we can access the bucket
        $testFile = 'test-connection-' . time() . '.txt';
        $testContent = 'R2 Storage Test - ' . now();
        
        // Try to put a test file
        $disk->put($testFile, $testContent);
        
        // Try to get the file back
        $retrievedContent = $disk->get($testFile);
        
        // Try to delete the test file
        $disk->delete($testFile);
        
        return response()->json([
            'status' => 'success',
            'message' => 'R2 Storage connection successful!',
            'bucket' => config('filesystems.disks.r2.bucket'),
            'endpoint' => config('filesystems.disks.r2.endpoint'),
            'test_content' => $retrievedContent,
            'timestamp' => now()
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'R2 Storage connection failed: ' . $e->getMessage(),
            'bucket' => config('filesystems.disks.r2.bucket'),
            'endpoint' => config('filesystems.disks.r2.endpoint'),
            'timestamp' => now()
        ], 500);
    }
})->name('test.r2.storage');

// Test route for session roles
Route::get('/test-session-roles', function () {
    $sessions = \App\Helpers\SessionRoleHelper::getAllSessionsWithRoles();
    $currentRole = \App\Helpers\SessionRoleHelper::getCurrentRole();
    
    return response()->json([
        'current_role' => $currentRole,
        'is_admin' => \App\Helpers\SessionRoleHelper::isAdmin(),
        'is_user' => \App\Helpers\SessionRoleHelper::isUser(),
        'is_guest' => \App\Helpers\SessionRoleHelper::isGuest(),
        'all_sessions' => $sessions
    ]);
})->name('test.session.roles');

// Debug route for authentication status
Route::get('/debug-auth', function () {
    return response()->json([
        'web_guard_check' => Auth::guard('web')->check(),
        'admin_guard_check' => Auth::guard('admin')->check(),
        'web_user' => Auth::guard('web')->user() ? Auth::guard('web')->user()->email : null,
        'admin_user' => Auth::guard('admin')->user() ? Auth::guard('admin')->user()->username : null,
        'session_id' => session()->getId(),
        'session_role' => session('user_role'),
        'all_session_data' => session()->all()
    ]);
})->name('debug.auth');

// Force session role update route
Route::get('/force-session-role-update', function () {
    $sessionId = session()->getId();
    $role = 'guest';
    
    if (Auth::guard('admin')->check()) {
        $role = 'admin';
    } elseif (Auth::guard('web')->check()) {
        $role = 'user';
    }
    
    $updated = DB::table('sessions')
        ->where('id', $sessionId)
        ->update(['role' => $role]);
    
    return response()->json([
        'session_id' => $sessionId,
        'role_set_to' => $role,
        'rows_updated' => $updated,
        'current_sessions' => DB::table('sessions')->select('id', 'user_id', 'role')->get()
    ]);
})->name('force.session.role.update');

// Test admin encryption
Route::get('/test-admin-encryption', function () {
    $admin = \App\Models\Admin::where('username', 'superadmin')->first();
    
    return response()->json([
        'username' => $admin->username,
        'pin_decrypted' => $admin->pin,
        'pin_raw_length' => strlen($admin->getRawOriginal('pin')),
        'password_hashed' => substr($admin->password, 0, 20) . '...',
        'encryption_status' => 'PIN is encrypted, username is plain text for login purposes'
    ]);
})->name('test.admin.encryption');


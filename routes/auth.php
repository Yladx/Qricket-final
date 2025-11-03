<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\VerificationSuccessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

Route::middleware('guest')->group(function () {
    // Account status page (for deleted/banned users)
    Route::get('account-status', function(Request $request) {
        $type = $request->get('type', 'deleted');
        $user = null;
        
        // Try to get user from session if not logged in
        if (session('banned_user')) {
            $user = session('banned_user');
        }
        
        return view('auth.account-status', compact('type', 'user'));
    })->name('account.status');
    
    // Cancel account deletion
    Route::post('account-status/cancel-deletion', [\App\Http\Controllers\User\ProfileController::class, 'cancelDeletion'])
        ->name('account.cancel-deletion');

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('registration-success', function () {
        return view('auth.registration-success');
    })->name('registration.success');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    // Guest-accessible verification page
    Route::get('email-verification', function () {
        return view('auth.verify-email');
    })->name('verification.guest');

    // Guest-accessible resend verification with rate limiting
    Route::post('email-verification/resend', function (Request $request) {
        $email = $request->input('email');
        
        if (!$email) {
            return back()->with('error', 'Email address is required.');
        }
        
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return back()->with('error', 'No account found with this email address.');
        }
        
        if ($user->hasVerifiedEmail()) {
            return back()->with('success', 'This email address is already verified.');
        }
        
        $user->sendEmailVerificationNotification();
        
        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:3,1')->name('verification.send.guest');
});

// Email verification routes (accessible without authentication)
Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// Public route to show verification success message (no auto-login)
Route::get('verify-email/success', function () {
    return view('auth.verification-success');
})->name('verification.success');

// Test route to verify R2 storage (remove in production)
Route::get('test-r2-storage', function () {
    try {
        $testContent = 'Test file content - ' . now();
        $fileName = 'test-' . Str::uuid() . '.txt';
        
        $filePath = Storage::disk('r2')->put($fileName, $testContent);
        
        if (Storage::disk('r2')->exists($filePath)) {
            $fileSize = Storage::disk('r2')->size($filePath);
            $fileContent = Storage::disk('r2')->get($filePath);
            
            // Clean up test file
            Storage::disk('r2')->delete($filePath);
            
            return response()->json([
                'status' => 'success',
                'message' => 'R2 storage is working correctly',
                'test_file_path' => $filePath,
                'file_size' => $fileSize,
                'file_content' => $fileContent
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'File upload verification failed'
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'R2 storage test failed: ' . $e->getMessage()
        ]);
    }
})->name('test.r2.storage');


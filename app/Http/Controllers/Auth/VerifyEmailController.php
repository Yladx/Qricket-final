<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email address as verified.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $userId = $request->route('id');
        $hash = $request->route('hash');
        
        // Find user by ID
        $user = User::findOrFail($userId);
        
        // Verify the hash matches the user's email
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            \Log::error('Email verification failed - hash mismatch', [
                'user_id' => $user->id,
                'email' => $user->email,
                'expected_hash' => sha1($user->getEmailForVerification()),
                'provided_hash' => $hash
            ]);
            abort(403, 'Invalid verification link');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('verification.success');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            // Log email verification
            $user->logActivity('email_verified', 'Email address verified successfully', [
                'verification_method' => 'email_link',
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip()
            ]);
        } else {
            \Log::error('Email verification failed', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
        }

        return redirect()->route('verification.success');
    }
}

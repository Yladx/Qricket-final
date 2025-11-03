<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[a-z]/',      // at least one lowercase
                'regex:/[A-Z]/',      // at least one uppercase
                'regex:/[0-9]/',      // at least one number
                'regex:/[^A-Za-z0-9]/' // at least one symbol
            ],
        ], [
            'password.regex' => 'Password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least :min characters.',
        ]);

        $user = $request->user();
        
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Log password change
        $user->logActivity('password_change', 'Password updated successfully', [
            'change_method' => 'profile_settings',
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip()
        ]);

        // Send password change email notification
        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\PasswordChangeMail($user));
        } catch (\Exception $e) {
            \Log::error('Failed to send password change email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return back()->with('status', 'password-updated');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\UserSessionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Set user mode to 'user' by default when logging in
        session(['user_mode' => 'user']);

        // Get user and update session with correct user_id and role
        $user = Auth::user();
        $sessionId = session()->getId();
        
        if ($sessionId && $user) {
            // Store role in session (works for all session drivers)
            session(['user_role' => 'user']);
            
            // Update session in database only if using database session driver
            $sessionDriver = config('session.driver');
            if ($sessionDriver === 'database') {
            try {
                DB::table('sessions')
                    ->where('id', $sessionId)
                    ->update([
                        'role' => 'user',
                        'user_id' => $user->id,
                        'last_activity' => now()->timestamp,
                    ]);
            } catch (\Exception $e) {
                \Log::error('Failed to update session role and user_id: ' . $e->getMessage());
                }
            }
            
            // Log successful login attempt
            \Log::info('Login attempt for user: ' . $user->email . ', Verified: ' . ($user->hasVerifiedEmail() ? 'Yes' : 'No'));
        }

        // Check if user's email is verified
        
        if (!$user->hasVerifiedEmail()) {
            \Log::info('User email not verified, sending verification email and redirecting');
            
            // Store email for pre-filling on verification page
            $userEmail = $user->email;
            
            // Send verification email automatically
            $user->sendEmailVerificationNotification();
            
            // Log out the user immediately
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Store email in session for verification page
            session(['verification_email' => $userEmail]);
            
            // Always redirect to guest-accessible verification page
            return redirect()->route('verification.guest')->with('error', 'Please verify your email address before logging in. We have sent a new verification email to your registered email address.');
        }

        // Log successful login
        $user->logActivity('login', 'User logged in successfully', [
            'login_method' => 'email',
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip()
        ]);

        // Update user counts
        UserSessionService::handleUserLogin($user->id);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log logout before destroying session
        $user = Auth::user();
        if ($user) {
            $user->logActivity('logout', 'User logged out', [
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip()
            ]);
        }

        // Update user counts BEFORE logout to ensure immediate update
        UserSessionService::handleUserLogout();

        // Clear all session data before logout
        $request->session()->forget('user_mode');
        $request->session()->forget('user_role');
        $request->session()->forget('admin_role');
        $request->session()->forget('admin_id');
        $request->session()->forget('admin_as_user');

        // Logout from web guard
        Auth::guard('web')->logout();

        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear any remaining session data
        $request->session()->flush();

        return redirect('/')
            ->with('success', 'You have been logged out successfully.')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }
}
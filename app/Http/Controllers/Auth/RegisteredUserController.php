<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserSessionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::min(8)
                    ->mixedCase()  // at least one uppercase and one lowercase
                    ->numbers()    // at least one number
                    ->symbols(),   // at least one symbol
                'regex:/^\S+$/'   // no spaces
            ],
            'terms' => ['accepted'],
        ], [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.mixed' => 'Password must include uppercase and lowercase letters.',
            'password.numbers' => 'Password must include at least one number.',
            'password.symbols' => 'Password must include at least one symbol.',
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least :min characters long.',
            'password.regex' => 'Password cannot contain spaces.',
            'terms.accepted' => 'You must accept the Terms and Conditions to continue.',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        // Don't auto-login the user - they need to verify email first
        // Auth::login($user);

        // Update user counts
        UserSessionService::handleUserLogin($user->id);

        // Log successful registration
        $user->logActivity('registration', 'New user account created', [
            'registration_method' => 'email',
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'email_verified' => false
        ]);

        // Check if this is an AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Please check your email to verify your account.',
                'redirect_url' => route('login')
            ]);
        }

        return redirect()->route('verification.notice')->with('success', 'Registration successful! Please check your email to verify your account.');
    }
}

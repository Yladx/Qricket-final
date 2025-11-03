<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        // Check if user is logged in as admin - redirect to admin dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }
        
        // If admin is authenticated as user (shadow user), bypass email verification
        if ($request->user() && $request->user()->email && str_starts_with($request->user()->email, 'admin+')) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }
        
        // Don't redirect if already verified - just show the view (if needed)
        // The middleware will handle preventing access to protected routes
        if (! $request->user()) {
            return redirect()->route('login');
        }
        
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }
        
        return view('auth.verify-email');
    }
}

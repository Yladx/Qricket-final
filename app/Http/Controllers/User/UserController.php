<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function switchMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|in:user,organizer'
        ]);

        $mode = $request->input('mode');
        $previousMode = session('user_mode', 'user');
        
        // Store the mode in session
        session(['user_mode' => $mode]);
        
        // Log the mode switch for debugging
        \Log::info('User mode switched', [
            'user_id' => Auth::id(),
            'previous_mode' => $previousMode,
            'new_mode' => $mode,
            'route' => $request->route()?->getName()
        ]);
        
        // Determine redirect URL based on mode
        $redirectUrl = $mode === 'organizer' ? route('organizer.dashboard') : route('dashboard');
        
        // Check if this is an AJAX request
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'mode' => $mode,
                'redirect_url' => $redirectUrl
            ]);
        }
        
        // For form submissions, redirect directly
        return redirect($redirectUrl);
    }
}
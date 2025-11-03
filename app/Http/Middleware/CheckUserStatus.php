<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user) {
            // Check if user is deleted
            if ($user->status === \App\Models\User::STATUS_DELETED || $user->deleted_at !== null) {
                Auth::logout();
                return redirect()->route('account.status', ['type' => 'deleted']);
            }

            // Check if user is banned
            if ($user->status === \App\Models\User::STATUS_BANNED || $user->banned_at !== null) {
                // If there is a banned_until and it's past, auto-unban
                if ($user->banned_until && now()->greaterThan($user->banned_until)) {
                    $user->unban();
                } else {
                $request->session()->put('banned_user', $user->toArray());
                Auth::logout();
                return redirect()->route('account.status', ['type' => 'banned']);
                }
            }
        }

        return $next($request);
    }
}

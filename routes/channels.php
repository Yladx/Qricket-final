<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('user.{id}', function ($user, int $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.support.{ticketNumber}', function ($user, $ticketNumber) {
    // Check if user is admin (either via guard or session)
    $ok = Auth::guard('admin')->check() || session('user_role') === 'admin';
    \Log::info('Broadcast auth admin.support check', [
        'ticket_number' => $ticketNumber,
        'ok' => $ok, 
        'admin_guard' => Auth::guard('admin')->check(), 
        'session_role' => session('user_role')
    ]);
    return $ok;
});

Broadcast::channel('admin.notifications', function () {
    $ok = Auth::guard('admin')->check() || session('user_role') === 'admin';
    \Log::info('Broadcast auth admin.notifications check', ['ok' => $ok, 'admin_guard' => Auth::guard('admin')->check(), 'session_role' => session('user_role')]);
    return $ok;
});

// Dashboard real-time channels
Broadcast::channel('dashboard.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('event.{eventId}', function ($user, $eventId) {
    // Allow access if user created the event or is staff member
    $event = \App\Models\Event::find($eventId);
    if (!$event) return false;
    
    return $event->user_id === $user->id || 
           $event->staff()->where('user_id', $user->id)->exists();
});

Broadcast::channel('admin-dashboard.{adminId}', function ($admin, $adminId) {
    // Check if admin is authenticated and matches the admin ID
    $ok = Auth::guard('admin')->check() && Auth::guard('admin')->id() == $adminId;
    \Log::info('Broadcast auth admin-dashboard check', [
        'admin_id' => $adminId,
        'authenticated_admin_id' => Auth::guard('admin')->id(),
        'ok' => $ok
    ]);
    return $ok;
});

Broadcast::channel('organizer-dashboard.{organizerId}', function ($user, $organizerId) {
    // Check if user is authenticated and matches the organizer ID
    return (int) $user->id === (int) $organizerId;
});


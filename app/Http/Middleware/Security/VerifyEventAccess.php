<?php

namespace App\Http\Middleware\Security;

use App\Models\Event;
use App\Models\EventStaff;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEventAccess
{
    /**
     * Handle an incoming request.
     * Ability values (string): manage_event, manage_news, manage_attendance, entry_scan
     */
    public function handle(Request $request, Closure $next, string $ability)
    {
        // Admins have global access to manage any event sections
        if (\Illuminate\Support\Facades\Auth::guard('admin')->check()) {
            return $next($request);
        }
        
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        $routeEvent = $request->route('event');
        if ($routeEvent instanceof Event) {
            $event = $routeEvent;
        } else {
            $event = Event::query()->findOrFail((int) $routeEvent);
        }

        // Event owner always allowed
        if ((int) $event->user_id === (int) $user->id) {
            return $next($request);
        }

        // Find staff record
        $staff = EventStaff::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$staff) {
            abort(403, 'You are not assigned to this event.');
        }

        $granted = match ($ability) {
            'manage_event' => ($staff->can_manage_event ?? false) || ($staff->can_manage_staff ?? false) || ($staff->role === 'organizer'),
            'manage_news' => ($staff->can_manage_news ?? false) || ($staff->role === 'organizer'),
            'manage_attendance' => ($staff->can_manage_attendance ?? false) || ($staff->can_manage_event ?? false) || ($staff->role === 'organizer'),
            'manage_sales' => ($staff->can_manage_sales ?? false) || ($staff->can_manage_event ?? false) || ($staff->role === 'organizer'),
            'manage_ticket' => ($staff->can_manage_ticket ?? false) || ($staff->can_manage_event ?? false) || ($staff->role === 'organizer'),
            default => false,
        };

        if (!$granted) {
            abort(403, 'You do not have permission for this action.');
        }

        return $next($request);
    }
}



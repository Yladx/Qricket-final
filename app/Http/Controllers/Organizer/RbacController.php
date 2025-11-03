<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventStaff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RbacController extends Controller
{
    public function index(Event $event)
    {
        $this->authorizeEventOwner($event);
        $staff = EventStaff::with('user')->where('event_id', $event->id)->get();
        return view('organizer.events.staff.index', compact('event', 'staff'));
    }

    public function assign(Request $request, Event $event)
    {
        $this->authorizeEventOwner($event);
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string'],
            'can_manage_event' => ['sometimes', 'boolean'],
            'can_manage_news' => ['sometimes', 'boolean'],
            'can_manage_attendance' => ['sometimes', 'boolean'],
            'can_entry_scan' => ['sometimes', 'boolean'],
        ]);

        EventStaff::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $validated['user_id']],
            [
                'role' => $validated['role'],
                'can_manage_event' => (bool)($validated['can_manage_event'] ?? false),
                'can_manage_news' => (bool)($validated['can_manage_news'] ?? false),
                'can_manage_attendance' => (bool)($validated['can_manage_attendance'] ?? false),
                'can_entry_scan' => (bool)($validated['can_entry_scan'] ?? false),
            ]
        );

        return back()->with('success', 'Staff access saved.');
    }

    public function revoke(Event $event, User $user)
    {
        $this->authorizeEventOwner($event);
        EventStaff::where('event_id', $event->id)->where('user_id', $user->id)->delete();
        return back()->with('success', 'Staff access removed.');
    }

    private function authorizeEventOwner(Event $event): void
    {
        $user = Auth::user();
        if (!$user || (int) $event->user_id !== (int) $user->id) {
            abort(403);
        }
    }
}

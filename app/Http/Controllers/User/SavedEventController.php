<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

class SavedEventController extends Controller
{
    public function toggle(Request $request, Event $event)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User must be logged in to save events'
            ], 401);
        }

        // Check if user is currently in organizer mode using session
        $isOrganizerMode = session('user_mode') === 'organizer';
        
        if ($isOrganizerMode) {
            return response()->json([
                'success' => false,
                'message' => 'You must switch to user mode to access saved events',
                'action' => 'switch_mode',
                'title' => 'Switch to User Mode',
                'text' => 'Saved events are only available in user mode. Would you like to switch to user mode?',
                'confirmButtonText' => 'Switch to User Mode',
                'cancelButtonText' => 'Cancel',
                'showCancelButton' => true
            ], 403);
        }

        // Check if event is already saved using the relationship
        $isCurrentlySaved = $user->savedEvents()->where('event_id', $event->id)->exists();

        if ($isCurrentlySaved) {
            // Event is saved, so unsave it
            $user->savedEvents()->detach($event->id);
            $isSaved = false;
            $message = 'Event removed from saved events';
        } else {
            // Event is not saved, so save it
            $user->savedEvents()->attach($event->id);
            $isSaved = true;
            $message = 'Event added to saved events';
        }

        return response()->json([
            'success' => true,
            'is_saved' => $isSaved,
            'message' => $message
        ]);
    }

    public function index(Request $request)
    {
        // Check if user is currently in organizer mode using session
        $isOrganizerMode = session('user_mode') === 'organizer';
        
        if ($isOrganizerMode) {
            // If in organizer mode, show the alert but still render the page
            return view('events.saved', [
                'savedEvents' => collect(),
                'switch_mode' => [
                    'title' => 'Switch to User Mode',
                    'text' => 'Saved events are only available in user mode. Would you like to switch to user mode?',
                    'confirmButtonText' => 'Switch to User Mode',
                    'cancelButtonText' => 'Cancel',
                    'showCancelButton' => true
                ]
            ]);
        }

        $user = Auth::user();
        $savedEvents = $user->savedEvents()
            ->select('events.*', 'saved_events.created_at as saved_at')
            ->paginate(12);
        
        return view('events.saved', compact('savedEvents'));
    }

    public function store(Request $request, Event $event)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User must be logged in to save events'
            ], 401);
        }

        // Check if user is currently in organizer mode using session
        $isOrganizerMode = session('user_mode') === 'organizer';
        
        if ($isOrganizerMode) {
            return response()->json([
                'success' => false,
                'message' => 'You must switch to user mode to access saved events',
                'action' => 'switch_mode',
                'title' => 'Switch to User Mode',
                'text' => 'Saved events are only available in user mode. Would you like to switch to user mode?',
                'confirmButtonText' => 'Switch to User Mode',
                'cancelButtonText' => 'Cancel',
                'showCancelButton' => true
            ], 403);
        }

        // Save the event
        $user->savedEvents()->syncWithoutDetaching([$event->id]);

        return response()->json([
            'success' => true,
            'message' => 'Event saved successfully'
        ]);
    }

    public function destroy(Request $request, Event $event)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User must be logged in to unsave events'
            ], 401);
        }

        // Check if user is currently in organizer mode using session
        $isOrganizerMode = session('user_mode') === 'organizer';
        
        if ($isOrganizerMode) {
            return response()->json([
                'success' => false,
                'message' => 'You must switch to user mode to access saved events',
                'action' => 'switch_mode',
                'title' => 'Switch to User Mode',
                'text' => 'Saved events are only available in user mode. Would you like to switch to user mode?',
                'confirmButtonText' => 'Switch to User Mode',
                'cancelButtonText' => 'Cancel',
                'showCancelButton' => true
            ], 403);
        }

        // Unsave the event
        $user->savedEvents()->detach($event->id);

        return response()->json([
            'success' => true,
            'message' => 'Event removed from saved events'
        ]);
    }
}
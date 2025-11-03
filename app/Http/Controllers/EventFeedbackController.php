<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventFeedback;
use App\Models\EventTicketPurchase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EventFeedbackController extends Controller
{
    /**
     * Show the feedback form for an event
     */
    public function show(Event $event)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to provide feedback.');
        }

        // Check if user attended this event
        $hasAttended = EventTicketPurchase::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();

        if (!$hasAttended) {
            return redirect()->route('events.show', $event)
                ->with('error', 'You can only provide feedback for events you attended.');
        }

        // Check if user already submitted feedback
        $existingFeedback = EventFeedback::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingFeedback) {
            return redirect()->route('events.show', $event)
                ->with('info', 'You have already submitted feedback for this event.');
        }

        // Check if event has ended
        if (!$event->end_date || !$event->end_date->isPast()) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Feedback can only be submitted after the event has ended.');
        }

        return view('events.feedback', compact('event'));
    }

    /**
     * Store the feedback submission
     */
    public function store(Request $request, Event $event)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in to provide feedback.');
        }

        // Validate the request
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:1000',
            'responses' => 'nullable|array',
        ]);

        // Check if user attended this event
        $hasAttended = EventTicketPurchase::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->exists();

        if (!$hasAttended) {
            return redirect()->route('events.show', $event)
                ->with('error', 'You can only provide feedback for events you attended.');
        }

        // Check if user already submitted feedback
        $existingFeedback = EventFeedback::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($existingFeedback) {
            return redirect()->route('events.show', $event)
                ->with('info', 'You have already submitted feedback for this event.');
        }

        // Check if event has ended
        if (!$event->end_date || !$event->end_date->isPast()) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Feedback can only be submitted after the event has ended.');
        }

        try {
            // Create feedback record
            EventFeedback::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'comments' => $request->comments,
                'responses' => $request->responses ?? [],
                'submitted_at' => now(),
            ]);

            Log::info('Event feedback submitted', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'rating' => $request->rating,
                'has_comments' => !empty($request->comments),
            ]);

            return redirect()->route('events.show', $event)
                ->with('success', 'Thank you for your feedback! Your input helps us improve future events.');

        } catch (\Exception $e) {
            Log::error('Failed to submit event feedback', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit feedback. Please try again.')
                ->withInput();
        }
    }
}

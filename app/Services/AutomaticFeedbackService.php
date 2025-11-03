<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventFeedback;
use App\Notifications\EventEndedFeedback;
use App\Models\EventTicketInformation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutomaticFeedbackService
{
    /**
     * Check for ended events and send feedback emails automatically
     * This is called by middleware or scheduled task
     */
    public static function sendFeedbackForEndedEvents(): int
    {
        $emailsSent = 0;
        
        // Find events that ended 1-3 hours ago
        $cutoffTime = Carbon::now()->subHours(1);
        $maxTime = Carbon::now()->subHours(3);
        
        $endedEvents = Event::where('status', 'published')
            ->where('end_date', '<=', $cutoffTime)
            ->where('end_date', '>=', $maxTime)
            ->whereNull('feedback_emails_sent_at')
            ->get();
        
        if ($endedEvents->isEmpty()) {
            return 0;
        }
        
        foreach ($endedEvents as $event) {
            // Get attendees who purchased tickets and checked in
            $attendees = EventTicketInformation::where('event_id', $event->id)
                ->whereNotNull('checked_in_at')
                ->whereHas('ticketPurchase', function($q) {
                    $q->where('status', 'paid');
                })
                ->with('user')
                ->get()
                ->map(function($ticket) {
                    return $ticket->user;
                })
                ->filter()
                ->unique('id');
            
            if ($attendees->isEmpty()) {
                Log::info("EventFeedback: No attendees found for event {$event->id}");
                // Still mark as sent to avoid retrying
                $event->update(['feedback_emails_sent_at' => now()]);
                continue;
            }
            
            $emailsSentForEvent = 0;
            
            foreach ($attendees as $attendee) {
                // Check if user already submitted feedback
                $existingFeedback = EventFeedback::where('event_id', $event->id)
                    ->where('user_id', $attendee->id)
                    ->exists();
                
                if ($existingFeedback) {
                    continue;
                }
                
                try {
                    // Send email notification
                    $attendee->notify(new EventEndedFeedback($event));
                    
                    // Create in-app notification
                    \App\Models\Notification::create([
                        'user_id' => $attendee->id,
                        'admin_id' => null,
                        'type' => 'event',
                        'action' => 'feedback_request',
                        'reference_id' => $event->id,
                        'title' => 'Feedback Request',
                        'message' => "Please share your feedback about '{$event->title}'. Your input helps us improve future events.",
                        'is_read' => false,
                    ]);
                    
                    $emailsSentForEvent++;
                    $emailsSent++;
                    
                    Log::info("EventFeedback: Email and notification sent to {$attendee->email} for event {$event->id}");
                } catch (\Exception $e) {
                    Log::error("EventFeedback: Failed to send email/notification to {$attendee->email}", [
                        'event_id' => $event->id,
                        'user_id' => $attendee->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Mark that feedback emails were sent for this event
            $event->update(['feedback_emails_sent_at' => now()]);
            
            Log::info("EventFeedback: Sent {$emailsSentForEvent} emails for event {$event->id} - {$event->title}");
        }
        
        return $emailsSent;
    }
}


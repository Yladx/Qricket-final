<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\Notification;
use App\Models\EventTicketInformation;
use App\Mail\EventEndedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EventEndNotificationService
{
    /**
     * Send notifications to users for ended events
     * 
     * @param Event $event
     * @return array Counts of notifications sent by type
     */
    public static function notifyUsersForEndedEvent(Event $event): array
    {
        $notifications = [
            'participants' => 0,
            'saved_users' => 0,
            'total' => 0
        ];

        // Check if already notified
        if ($event->end_notified_at) {
            echo "  Skipping: Already notified on {$event->end_notified_at}\n";
            return $notifications;
        }
        
        // Check if end_date is valid and in the past
        if (!$event->end_date) {
            echo "  Skipping: No end_date set\n";
            return $notifications;
        }
        
        if ($event->end_date->isFuture()) {
            echo "  Skipping: End date is in future ({$event->end_date})\n";
            return $notifications;
        }

        try {
            echo "  Event ended on: {$event->end_date}\n";
            echo "  Current time: " . now() . "\n";
            
            // 1. Notify users who purchased tickets (attended)
            $notifications['participants'] = self::notifyEventAttendees($event);
            
            // 2. Notify users who saved the event
            $notifications['saved_users'] = self::notifySavedEventUsers($event);
            
            $notifications['total'] = $notifications['participants'] + $notifications['saved_users'];
            
            // 3. Mark event as notified (use save to ensure update)
            $event->end_notified_at = now();
            $event->save();
            echo "  ✓ Marked as notified (database updated)\n";
            
            Log::info('EventEndNotificationService: Event marked as notified in database', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'end_notified_at' => $event->end_notified_at,
                'participants_notified' => $notifications['participants'],
                'saved_users_notified' => $notifications['saved_users'],
                'total_notifications' => $notifications['total']
            ]);
            
        } catch (\Throwable $e) {
            echo "  ✗ ERROR: {$e->getMessage()}\n";
            echo "  Stack trace: " . substr($e->getTraceAsString(), 0, 200) . "...\n";
            Log::error('EventEndNotification: Failed to send notifications', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $notifications;
    }

    /**
     * Notify users who attended the event (purchased tickets)
     */
    private static function notifyEventAttendees(Event $event): int
    {
        $notifiedCount = 0;
        
        try {
            // Get all users who purchased tickets (regardless of checked-in status)
            $attendeeIds = EventTicketInformation::where('event_id', $event->id)
                ->whereHas('ticketPurchase', function($q) { 
                    $q->where('status', 'paid'); 
                })
                ->distinct()
                ->pluck('user_id')
                ->filter()
                ->unique();
            
            echo "  Checking for attendees in event_ticket_information table...\n";
            echo "  Found " . $attendeeIds->count() . " attendee ID(s)\n";
            
            Log::info('EventEndNotificationService: Checking attendees', [
                'event_id' => $event->id,
                'attendee_count' => $attendeeIds->count(),
                'attendee_ids' => $attendeeIds->toArray()
            ]);
            
            if ($attendeeIds->isEmpty()) {
                echo "  No attendees found\n";
                return 0;
            }
            
            $attendees = User::whereIn('id', $attendeeIds)->get();
            echo "  Found {$attendees->count()} attendee(s) to notify\n";
            
            Log::info('EventEndNotificationService: Attendees to notify', [
                'count' => $attendees->count(),
                'user_ids' => $attendees->pluck('id')->toArray(),
                'emails' => $attendees->pluck('email')->toArray()
            ]);
            
            foreach ($attendees as $attendee) {
                try {
                    // Create in-app notification
                    $notification = Notification::create([
                        'user_id' => $attendee->id,
                        'admin_id' => null,
                        'type' => 'event',
                        'action' => 'event_ended',
                        'reference_id' => $event->id,
                        'title' => 'Event Ended',
                        'message' => "Event '{$event->title}' has ended. Please share your feedback!",
                        'is_read' => false,
                    ]);
                    
                    Log::info('EventEndNotificationService: In-app notification created for participant', [
                        'notification_id' => $notification->id,
                        'user_id' => $attendee->id,
                        'event_id' => $event->id
                    ]);
                    
                    // Send email
                    try {
                        Mail::to($attendee->email)->send(new EventEndedMail($event, $attendee));
                        echo "    ✓ Email sent to: {$attendee->email}\n";
                        
                        Log::info('EventEndNotificationService: Email sent to participant', [
                            'event_id' => $event->id,
                            'user_id' => $attendee->id,
                            'user_email' => $attendee->email
                        ]);
                    } catch (\Throwable $mailError) {
                        echo "    ✗ Email failed for {$attendee->email}: {$mailError->getMessage()}\n";
                        Log::error('EventEndNotificationService: Email send failed for participant', [
                            'event_id' => $event->id,
                            'user_id' => $attendee->id,
                            'user_email' => $attendee->email,
                            'error' => $mailError->getMessage()
                        ]);
                    }
                    
                    echo "    ✓ Notified participant: {$attendee->email}\n";
                    $notifiedCount++;
                    
                } catch (\Throwable $e) {
                    echo "    ✗ Failed to notify {$attendee->email}: {$e->getMessage()}\n";
                    Log::warning('EventEndNotification: Failed to notify attendee', [
                        'event_id' => $event->id,
                        'user_id' => $attendee->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
        } catch (\Throwable $e) {
            echo "  ERROR getting attendees: {$e->getMessage()}\n";
            Log::error('EventEndNotification: Failed to get attendees', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $notifiedCount;
    }

    /**
     * Notify users who saved the event
     */
    private static function notifySavedEventUsers(Event $event): int
    {
        $notifiedCount = 0;
        
        try {
            // Get users who saved this event
            $savedByUsers = $event->savedByUsers;
            $totalSaved = $savedByUsers->count();
            echo "  Found {$totalSaved} user(s) who saved this event\n";
            
            foreach ($savedByUsers as $user) {
                // Check if user also participated (has tickets)
                $hasTicket = EventTicketInformation::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->whereHas('ticketPurchase', function($q) {
                        $q->where('status', 'paid');
                    })
                    ->exists();
                
                // Only notify saved users who didn't participate (participants are already notified above)
                if ($hasTicket) {
                    continue; // Already notified as participant
                }
                
                try {
                    // Create in-app notification for saved users who didn't participate
                    Notification::create([
                        'user_id' => $user->id,
                        'admin_id' => null,
                        'type' => 'event',
                        'action' => 'event_ended_saved',
                        'reference_id' => $event->id,
                        'title' => 'Event Ended',
                        'message' => "Event '{$event->title}' has ended. Please share your feedback!",
                        'is_read' => false,
                    ]);
                    
                    // No email sent to saved users who didn't participate (only participants get emails)
                    echo "    ✓ Notified saved user (no email): {$user->email}\n";
                    $notifiedCount++;
                    
                } catch (\Throwable $e) {
                    echo "    ✗ Failed to notify {$user->email}: {$e->getMessage()}\n";
                    Log::warning('EventEndNotification: Failed to notify saved user', [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
        } catch (\Throwable $e) {
            echo "  ERROR getting saved users: {$e->getMessage()}\n";
            Log::error('EventEndNotification: Failed to get saved users', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return $notifiedCount;
    }

    /**
     * Process all ended events that haven't been notified
     */
    public static function processEndedEvents(): array
    {
        $summary = [
            'events_processed' => 0,
            'total_notifications' => 0,
            'participants_notified' => 0,
            'saved_users_notified' => 0,
            'errors' => []
        ];
        
        $startTime = now();
        
        echo "\n========================================\n";
        echo "EVENT END NOTIFICATION SERVICE\n";
        echo "Started at: {$startTime}\n";
        echo "========================================\n\n";
        
        Log::info('EventEndNotificationService: Started', ['timestamp' => $startTime]);
        
        try {
            // Find events that ended but haven't been notified
            // Check ALL ended events, regardless of when they ended
            $endedEvents = Event::whereIn('status', ['published', 'approved'])
                ->whereNotNull('end_date')
                ->where('end_date', '<=', now())
                ->whereNull('end_notified_at')
                ->limit(50)
                ->get();
            
            echo "Found {$endedEvents->count()} ended event(s) to process\n\n";
            
            Log::info('EventEndNotificationService: Found ended events', [
                'count' => $endedEvents->count(),
                'event_ids' => $endedEvents->pluck('id')->toArray()
            ]);
            
            foreach ($endedEvents as $event) {
                try {
                    echo "Processing Event #{$event->id}: {$event->title}\n";
                    echo "  End Date: {$event->end_date}\n";
                    
                    Log::info('EventEndNotificationService: Processing event', [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'end_date' => $event->end_date
                    ]);
                    
                    $result = self::notifyUsersForEndedEvent($event);
                    
                    echo "  Participants notified: {$result['participants']}\n";
                    echo "  Saved users notified: {$result['saved_users']}\n";
                    echo "  Total notifications: {$result['total']}\n\n";
                    
                    Log::info('EventEndNotificationService: Event processed successfully', [
                        'event_id' => $event->id,
                        'participants_notified' => $result['participants'],
                        'saved_users_notified' => $result['saved_users'],
                        'total_notifications' => $result['total']
                    ]);
                    
                    $summary['events_processed']++;
                    $summary['total_notifications'] += $result['total'];
                    $summary['participants_notified'] += $result['participants'];
                    $summary['saved_users_notified'] += $result['saved_users'];
                    
                } catch (\Throwable $e) {
                    $errorMsg = "Failed to process event #{$event->id}: {$e->getMessage()}";
                    echo "  ERROR: {$errorMsg}\n\n";
                    
                    $summary['errors'][] = [
                        'event_id' => $event->id,
                        'error' => $e->getMessage()
                    ];
                    Log::error('EventEndNotification: Failed to process event', [
                        'event_id' => $event->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);
            
            echo "\n========================================\n";
            echo "SUMMARY\n";
            echo "Events processed: {$summary['events_processed']}\n";
            echo "Participants notified: {$summary['participants_notified']}\n";
            echo "Saved users notified: {$summary['saved_users_notified']}\n";
            echo "Total notifications sent: {$summary['total_notifications']}\n";
            echo "Errors: " . count($summary['errors']) . "\n";
            echo "Duration: {$duration} seconds\n";
            echo "Completed at: {$endTime}\n";
            echo "========================================\n\n";
            
            Log::info('EventEndNotificationService: Completed successfully', [
                'events_processed' => $summary['events_processed'],
                'total_notifications' => $summary['total_notifications'],
                'participants_notified' => $summary['participants_notified'],
                'saved_users_notified' => $summary['saved_users_notified'],
                'duration_seconds' => $duration,
                'started_at' => $startTime,
                'completed_at' => $endTime
            ]);
            
        } catch (\Throwable $e) {
            echo "\nCRITICAL ERROR: {$e->getMessage()}\n";
            Log::error('EventEndNotification: Failed to process ended events', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $summary;
    }
}


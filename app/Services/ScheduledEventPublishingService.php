<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventVerificationRequest;
use App\Models\EventActivityLog;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class ScheduledEventPublishingService
{
    /**
     * Process all scheduled events that have reached their publish date
     * 
     * @return array Summary of processed events
     */
    public static function processScheduledEvents(): array
    {
        $summary = [
            'events_processed' => 0,
            'events_published' => 0,
            'errors' => []
        ];
        
        $startTime = now();
        
        echo "\n========================================\n";
        echo "SCHEDULED EVENT PUBLISHING SERVICE\n";
        echo "Started at: {$startTime}\n";
        echo "========================================\n\n";
        
        Log::info('ScheduledEventPublishingService: Started', ['timestamp' => $startTime]);
        
        try {
            // Find events that are scheduled to be published (scheduled_publish_at <= now())
            // and are in 'scheduled' status
            $scheduledEvents = Event::where('status', 'scheduled')
                ->whereNotNull('scheduled_publish_at')
                ->where('scheduled_publish_at', '<=', now())
                ->limit(50)
                ->get();
            
            echo "Found {$scheduledEvents->count()} scheduled event(s) to publish\n\n";
            
            Log::info('ScheduledEventPublishingService: Found scheduled events', [
                'count' => $scheduledEvents->count(),
                'event_ids' => $scheduledEvents->pluck('id')->toArray()
            ]);
            
            foreach ($scheduledEvents as $event) {
                try {
                    echo "Processing Event #{$event->id}: {$event->title}\n";
                    echo "  Scheduled publish at: {$event->scheduled_publish_at}\n";
                    echo "  Current time: " . now() . "\n";
                    
                    Log::info('ScheduledEventPublishingService: Processing event', [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'scheduled_publish_at' => $event->scheduled_publish_at
                    ]);
                    
                    // Auto-publish the event (change status directly to 'published')
                    $event->update([
                        'status' => 'published',
                        'approved_at' => now(),
                    ]);
                    
                    echo "  ✓ Status changed to 'published'\n";
                    
                    // Create an auto-approved verification request record for tracking
                    try {
                        EventVerificationRequest::create([
                            'event_id' => $event->id,
                            'organizer_id' => $event->user_id,
                            'status' => 'approved',
                            'submitted_at' => now(),
                            'reviewed_at' => now(),
                            'notes' => 'Auto-approved via scheduled publishing',
                            'reviewed_by' => null, // System auto-approval
                        ]);
                        echo "  ✓ Verification request created (auto-approved)\n";
                    } catch (\Exception $e) {
                        echo "  ✗ Failed to create verification request: {$e->getMessage()}\n";
                        Log::warning('ScheduledEventPublishingService: Failed to create event verification request', [
                            'event_id' => $event->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    // Log event auto-publishing
                    EventActivityLog::log(
                        $event->id,
                        $event->user_id,
                        'event_published',
                        'Event auto-published via scheduled publishing',
                        [
                            'event_title' => $event->title,
                            'published_at' => now()->toISOString(),
                            'scheduled_publish_at' => $event->scheduled_publish_at?->toISOString(),
                            'auto_published' => true,
                        ]
                    );
                    echo "  ✓ Activity logged\n";
                    
                    // Notify organizer that their event was published
                    try {
                        $organizer = \App\Models\User::find($event->user_id);
                        if ($organizer) {
                            // Create in-app notification for organizer
                            Notification::create([
                                'user_id' => $event->user_id,
                                'admin_id' => null,
                                'type' => 'event',
                                'action' => 'published',
                                'reference_id' => $event->id,
                                'title' => 'Event Published',
                                'message' => "Your scheduled event '{$event->title}' has been automatically published.",
                                'is_read' => false,
                                'data' => [
                                    'event_id' => $event->id,
                                    'event_title' => $event->title,
                                    'published_at' => now()->toISOString(),
                                    'auto_published' => true,
                                ],
                            ]);
                            
                            // Send real-time notification
                            NotificationService::sendUserNotification(
                                (int) $event->user_id,
                                "Your scheduled event '{$event->title}' has been automatically published.",
                                'success',
                                'Event Published'
                            );
                            
                            echo "  ✓ Organizer notified: {$organizer->email}\n";
                        }
                    } catch (\Exception $e) {
                        echo "  ✗ Failed to notify organizer: {$e->getMessage()}\n";
                        Log::warning('ScheduledEventPublishingService: Failed to create organizer notification', [
                            'event_id' => $event->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                    
                    echo "  ✓ Event published successfully\n\n";
                    
                    $summary['events_published']++;
                    $summary['events_processed']++;
                    
                    Log::info('ScheduledEventPublishingService: Event published successfully', [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'published_at' => now()->toISOString()
                    ]);
                    
                } catch (\Throwable $e) {
                    $errorMsg = "Failed to publish event #{$event->id}: {$e->getMessage()}";
                    echo "  ✗ ERROR: {$errorMsg}\n\n";
                    
                    $summary['errors'][] = [
                        'event_id' => $event->id,
                        'error' => $e->getMessage()
                    ];
                    
                    Log::error('ScheduledEventPublishingService: Failed to publish event', [
                        'event_id' => $event->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    $summary['events_processed']++;
                }
            }
            
            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);
            
            echo "\n========================================\n";
            echo "SUMMARY\n";
            echo "Events processed: {$summary['events_processed']}\n";
            echo "Events published: {$summary['events_published']}\n";
            echo "Errors: " . count($summary['errors']) . "\n";
            echo "Duration: {$duration} seconds\n";
            echo "Completed at: {$endTime}\n";
            echo "========================================\n\n";
            
            Log::info('ScheduledEventPublishingService: Completed successfully', [
                'events_processed' => $summary['events_processed'],
                'events_published' => $summary['events_published'],
                'duration_seconds' => $duration,
                'started_at' => $startTime,
                'completed_at' => $endTime
            ]);
            
        } catch (\Throwable $e) {
            echo "\nCRITICAL ERROR: {$e->getMessage()}\n";
            Log::error('ScheduledEventPublishingService: Failed to process scheduled events', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return $summary;
    }
}


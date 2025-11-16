<?php

namespace App\Console\Commands;

use App\Models\EventNews;
use App\Models\Event;
use App\Models\SavedEvent;
use App\Models\Notification;
use App\Models\TicketPurchase;
use App\Notifications\EventNewsNotification;
use App\Events\EventNewsPublished;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PublishScheduledNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled news articles that have reached their publish date and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for scheduled news to publish...');

        // Find news articles that are scheduled to be published (published_at <= now())
        // Check if they were published within the last 5 minutes (to send notifications)
        $scheduledNews = EventNews::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('published_at', '>=', now()->subMinutes(5))
            ->with('event')
            ->get();

        if ($scheduledNews->isEmpty()) {
            $this->info('No scheduled news to publish.');
            return 0;
        }

        $publishedCount = 0;
        $notificationCount = 0;

        foreach ($scheduledNews as $news) {
            try {
                $event = $news->event;
                if (!$event) {
                    $this->warn("News ID {$news->id} has no associated event, skipping.");
                    continue;
                }

                // Send notifications to event subscribers (same logic as EventNewsController)
                $this->notifyEventSubscribers($news, $event);
                
                $notificationCount++;
                $this->info("Published and notified: News '{$news->title}' (ID: {$news->id})");
                $publishedCount++;
                
            } catch (\Exception $e) {
                $this->error("Error processing news ID {$news->id}: " . $e->getMessage());
                Log::error('PublishScheduledNews error', [
                    'news_id' => $news->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Processed {$publishedCount} scheduled news articles. Sent {$notificationCount} notification batches.");
        return 0;
    }

    /**
     * Send notifications to users who have saved this event or bought tickets
     * Respects the news visibility setting: 'public', 'ticket_buyers', or 'saved_users'
     * (Same logic as EventNewsController::notifyEventSubscribers)
     */
    private function notifyEventSubscribers(EventNews $news, Event $event)
    {
        $userIds = [];
        
        // Respect the visibility setting
        $visibility = $news->visibility ?? 'public';
        
        // Get users based on visibility setting
        if ($visibility === 'public' || $visibility === 'ticket_buyers') {
            // Get all users who bought tickets for this event
            $ticketBuyerIds = TicketPurchase::where('event_id', $event->id)
                ->where('status', 'paid')
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            $userIds = array_merge($userIds, $ticketBuyerIds);
        }
        
        if ($visibility === 'public' || $visibility === 'saved_users') {
            // Get all users who have saved this event
            $savedEvents = SavedEvent::where('event_id', $event->id)->with('user')->get();
            $savedUserIds = $savedEvents->pluck('user_id')->toArray();
            $userIds = array_merge($userIds, $savedUserIds);
        }
        
        // Remove duplicates
        $userIds = array_unique($userIds);
        
        if (empty($userIds)) {
            Log::info('No users to notify for scheduled news', [
                'event_id' => $event->id,
                'news_id' => $news->id,
                'visibility' => $visibility,
            ]);
            return;
        }
        
        $notificationsCreated = 0;
        $emailsSent = 0;
        $emailsFailed = 0;
        
        // Send notification to each user
        foreach ($userIds as $userId) {
            try {
                $user = \App\Models\User::find($userId);
                if (!$user) continue;
                
                // Skip if user has no email
                if (empty($user->email)) {
                    Log::warning('User has no email, skipping email notification', [
                        'user_id' => $userId,
                    ]);
                } else {
                    // Send email notification
                    try {
                        $user->notify(new EventNewsNotification($event, $news));
                        $emailsSent++;
                    } catch (\Exception $emailException) {
                        $emailsFailed++;
                        Log::error('Failed to send email notification', [
                            'user_id' => $userId,
                            'user_email' => $user->email,
                            'error' => $emailException->getMessage(),
                        ]);
                    }
                }
                
                // Create in-app notification
                Notification::create([
                    'user_id' => $userId,
                    'type' => 'event',
                    'action' => 'news',
                    'reference_id' => $event->id,
                    'title' => $news->title,
                    'message' => $news->title,
                    'is_read' => false,
                    'data' => [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'news_id' => $news->id,
                        'news_title' => $news->title,
                    ],
                ]);
                
                // Broadcast real-time notification
                event(new EventNewsPublished($userId, $news, $event));
                
                $notificationsCreated++;
            } catch (\Exception $e) {
                Log::error('Failed to send news notification to user', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::info('Event news notifications sent (scheduled)', [
            'event_id' => $event->id,
            'news_id' => $news->id,
            'notifications_sent' => $notificationsCreated,
            'emails_sent' => $emailsSent,
            'emails_failed' => $emailsFailed,
            'total_users_notified' => count($userIds),
            'visibility' => $visibility,
            'news_title' => $news->title
        ]);
    }
}


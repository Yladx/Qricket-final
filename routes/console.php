<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\URL;
use App\Models\Event;
use App\Models\TicketInformation;
use App\Models\User;
use App\Notifications\EventFeedbackRequest;
use App\Mail\EventEndedMail;
use App\Services\EventEndNotificationService;
use App\Services\ScheduledEventPublishingService;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Test a specific RefundRequest via console
Artisan::command('refund:test {id}', function ($id) {
    \Artisan::call('xendit:test-refund-record', ['refundRequestId' => $id]);
    $this->info('Triggered test refund for RefundRequest ID: ' . $id);
})->purpose('Process a specific RefundRequest through Xendit for testing');

// Laravel 11: define the scheduler in routes/console.php
Schedule::call(function(){
    // Ensure absolute URLs in console context (no HTTP request)
    try {
        $root = config('app.url') ?: env('APP_URL');
        if ($root) { URL::forceRootUrl($root); }
        if (is_string($root) && str_starts_with($root, 'https://')) { URL::forceScheme('https'); }
    } catch (\Throwable $e) { /* ignore */ }
    
    // Handle event end notifications using the service
    EventEndNotificationService::processEndedEvents();
    
    // Handle scheduled event publishing using the service
    ScheduledEventPublishingService::processScheduledEvents();
    
    // Handle feedback request notifications - DISABLED (replaced with Qricket website feedback system)
    // This command is no longer needed as we now use the Qricket website feedback system
    // that triggers automatically after user actions like ticket purchases
    return;
    
    $events = Event::whereNotNull('end_date')
        ->where('end_date', '<=', now())
        ->where(function($q){ $q->whereNull('feedback_notified_at')->orWhere('feedback_notified_at','<', now()->subDays(30)); })
        ->limit(100)
        ->get();
    foreach ($events as $event) {
        try {
            $url = route('events.feedback.show', $event);
            $attendeeIds = TicketInformation::where('event_id', $event->id)
                ->whereHas('ticketPurchase', function($q){ $q->where('status','paid'); })
                ->pluck('user_id')->unique()->filter();
            $users = User::whereIn('id', $attendeeIds)->get();
            
            foreach ($users as $u) { 
                // Send email notification (existing)
                $u->notify(new EventFeedbackRequest($event, $url));
                
                // Create in-app notification
                \App\Models\Notification::create([
                    'user_id' => $u->id,
                    'admin_id' => null,
                    'type' => 'event',
                    'action' => 'feedback_request',
                    'reference_id' => $event->id,
                    'is_read' => false,
                ]);
            }
            $event->forceFill(['feedback_notified_at' => now()])->save();
        } catch (\Throwable $e) { /* continue */ }
    }
})->everyFifteenMinutes();

// Schedule event end notifications to run every 15 minutes
Schedule::command('events:notify-ended')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->description('Process ended event notifications');

// Schedule the feedback email command to run every 6 hours
// This will automatically send feedback emails to attendees when events end
Schedule::command('events:send-feedback-emails --hours=1')->everySixHours();

// Schedule news publishing to run every 5 minutes
// This will publish scheduled news articles and send notifications
Schedule::command('news:publish-scheduled')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->description('Publish scheduled news articles');

// Schedule event publishing to run every 15 minutes
// This will auto-publish scheduled events when their publish date arrives (no approval required)
Schedule::command('events:publish-scheduled')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->description('Auto-publish scheduled events');

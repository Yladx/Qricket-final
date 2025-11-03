<?php

namespace App\Console\Commands\Maintenance;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\EventTicketPurchase;
use App\Models\EventFeedback;
use App\Notifications\EventEndedFeedback;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendEventEndedFeedbackEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-feedback-emails {--hours=24 : Hours after event end to send feedback emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send feedback request emails to attendees of recently ended events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hoursAfterEnd = $this->option('hours');
        $cutoffTime = Carbon::now()->subHours($hoursAfterEnd);
        
        $this->info("Looking for events that ended {$hoursAfterEnd} hours ago (before {$cutoffTime})...");

        // Find events that ended within the specified timeframe
        $endedEvents = Event::where('status', 'published')
            ->where('end_date', '<=', $cutoffTime)
            ->where('end_date', '>=', Carbon::now()->subDays(7)) // Only events from last 7 days
            ->feedbackEmailsNotSent() // Events that haven't had feedback emails sent yet
            ->get();

        if ($endedEvents->isEmpty()) {
            $this->info('No events found that need feedback emails.');
            return 0;
        }

        $this->info("Found {$endedEvents->count()} events that need feedback emails.");

        $totalEmailsSent = 0;

        foreach ($endedEvents as $event) {
            $this->info("Processing event: {$event->title} (ID: {$event->id})");
            
            // Get all attendees who purchased tickets
            $attendees = EventTicketPurchase::where('event_id', $event->id)
                ->where('status', 'completed')
                ->with('user')
                ->get()
                ->pluck('user')
                ->filter()
                ->unique('id');

            if ($attendees->isEmpty()) {
                $this->warn("No attendees found for event: {$event->title}");
                continue;
            }

            $emailsSentForEvent = 0;

            foreach ($attendees as $attendee) {
                // Check if user already submitted feedback
                $existingFeedback = EventFeedback::where('event_id', $event->id)
                    ->where('user_id', $attendee->id)
                    ->exists();

                if ($existingFeedback) {
                    $this->line("  Skipping {$attendee->email} - already submitted feedback");
                    continue;
                }

                try {
                    $attendee->notify(new EventEndedFeedback($event));
                    $emailsSentForEvent++;
                    $totalEmailsSent++;
                    
                    $this->line("  Sent feedback email to: {$attendee->email}");
                } catch (\Exception $e) {
                    $this->error("  Failed to send email to {$attendee->email}: {$e->getMessage()}");
                    Log::error("Failed to send feedback email", [
                        'event_id' => $event->id,
                        'user_id' => $attendee->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Mark that feedback emails were sent for this event
            $event->update(['feedback_emails_sent_at' => now()]);
            
            $this->info("  Sent {$emailsSentForEvent} emails for event: {$event->title}");
        }

        $this->info("Completed! Total emails sent: {$totalEmailsSent}");
        
        Log::info("Event feedback emails sent", [
            'events_processed' => $endedEvents->count(),
            'total_emails_sent' => $totalEmailsSent,
            'hours_after_end' => $hoursAfterEnd
        ]);

        return 0;
    }
}

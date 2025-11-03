<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Services\EventEndNotificationService;

class TestEventEndNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:event-end-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the event end notification service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Event End Notification Service...');
        $this->line('');
        
        // Check for ended events
        $endedEvents = Event::whereIn('status', ['published', 'approved'])
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now())
            ->whereNull('end_notified_at')
            ->limit(50)
            ->get();
        
        $this->info("Found {$endedEvents->count()} ended event(s) that haven't been notified");
        $this->line('');
        
        if ($endedEvents->count() === 0) {
            $this->warn('No events found that need notifications.');
            $this->line('');
            $this->line('Checking if there are any ended events at all...');
            
            $allEndedEvents = Event::whereIn('status', ['published', 'approved'])
                ->whereNotNull('end_date')
                ->where('end_date', '<=', now())
                ->get(['id', 'title', 'end_date', 'end_notified_at']);
            
            $this->table(
                ['ID', 'Title', 'End Date', 'Notified At'],
                $allEndedEvents->map(function($event) {
                    return [
                        $event->id,
                        \Str::limit($event->title, 40),
                        $event->end_date?->format('Y-m-d H:i'),
                        $event->end_notified_at?->format('Y-m-d H:i') ?? 'NULL'
                    ];
                })->toArray()
            );
        } else {
            foreach ($endedEvents->take(3) as $event) {
                $this->line("Event #{$event->id}: {$event->title}");
                $this->line("  End Date: {$event->end_date}");
                $this->line("  Status: {$event->status}");
                $this->line("  Has end_notified_at: " . ($event->end_notified_at ? 'YES' : 'NO'));
                $this->line('');
            }
            
            $this->info('Attempting to process notifications...');
            $this->line('');
            
            // Run the actual service
            $summary = EventEndNotificationService::processEndedEvents();
            
            $this->line('');
            $this->info('Summary:');
            $this->line("Events processed: {$summary['events_processed']}");
            $this->line("Total notifications: {$summary['total_notifications']}");
            $this->line("Participants notified: {$summary['participants_notified']}");
            $this->line("Saved users notified: {$summary['saved_users_notified']}");
            $this->line("Errors: " . count($summary['errors']));
            
            if (!empty($summary['errors'])) {
                $this->error('Errors encountered:');
                foreach ($summary['errors'] as $error) {
                    $this->error("  - {$error}");
                }
            }
            
            // Check if end_notified_at was updated
            $this->line('');
            $this->info('Verifying database updates...');
            
            foreach ($endedEvents as $event) {
                $event->refresh();
                $this->line("Event #{$event->id}: end_notified_at = " . ($event->end_notified_at ? $event->end_notified_at->format('Y-m-d H:i') : 'NULL'));
            }
        }
        
        return Command::SUCCESS;
    }
}


<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EventEndNotificationService;

class ProcessEventEndNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:notify-ended';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to users for ended events (participants and saved users)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing ended event notifications...');
        
        $result = EventEndNotificationService::processEndedEvents();
        
        $this->info("Processed {$result['events_processed']} events");
        $this->info("Sent {$result['total_notifications']} notifications total");
        $this->info("- {$result['participants_notified']} participant notifications");
        $this->info("- {$result['saved_users_notified']} saved user notifications");
        
        if (!empty($result['errors'])) {
            $this->error("Errors occurred: " . count($result['errors']));
            foreach ($result['errors'] as $error) {
                $this->error("Event ID {$error['event_id']}: {$error['error']}");
            }
        }
        
        return Command::SUCCESS;
    }
}


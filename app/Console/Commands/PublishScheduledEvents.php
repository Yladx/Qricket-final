<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScheduledEventPublishingService;

class PublishScheduledEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-publish scheduled events that have reached their publish date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled event publishing...');
        
        $result = ScheduledEventPublishingService::processScheduledEvents();
        
        $this->info("Processed {$result['events_processed']} events");
        $this->info("Published {$result['events_published']} events");
        
        if (!empty($result['errors'])) {
            $this->error("Errors occurred: " . count($result['errors']));
            foreach ($result['errors'] as $error) {
                $this->error("Event ID {$error['event_id']}: {$error['error']}");
            }
        }
        
        return Command::SUCCESS;
    }
}


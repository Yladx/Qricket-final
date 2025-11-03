<?php

namespace App\Console\Commands\Auth;

use Illuminate\Console\Command;
use App\Services\UserSessionService;

class CleanupOrphanedAdminSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:sessions:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned admin sessions that are no longer valid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of orphaned admin sessions...');
        
        $cleanedCount = UserSessionService::cleanupOrphanedAdminSessions();
        
        if ($cleanedCount > 0) {
            $this->info("Cleaned up {$cleanedCount} orphaned admin sessions.");
        } else {
            $this->info('No orphaned admin sessions found.');
        }
        
        // Also run general session monitoring
        $expiredCount = UserSessionService::monitorSessions();
        
        if ($expiredCount > 0) {
            $this->info("Also cleaned up {$expiredCount} expired sessions.");
        }
        
        $this->info('Session cleanup completed successfully.');
        
        return Command::SUCCESS;
    }
}
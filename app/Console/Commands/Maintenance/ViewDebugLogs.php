<?php

namespace App\Console\Commands\Maintenance;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ViewDebugLogs extends Command
{
    protected $signature = 'debug:logs {--tail=50 : Number of lines to show}';
    protected $description = 'View debug logs filtered by current admin';

    public function handle()
    {
        $logPath = storage_path('logs/laravel.log');
        
        if (!File::exists($logPath)) {
            $this->error('Log file not found: ' . $logPath);
            return 1;
        }
        
        $tail = (int) $this->option('tail');
        $allLines = File::lines($logPath);
        $debugLines = [];
        
        foreach ($allLines as $line) {
            if (str_contains($line, '[DEBUG]')) {
                $debugLines[] = $line;
            }
        }
        
        // Get last N lines
        $debugLines = array_slice($debugLines, -$tail);
        
        if (empty($debugLines)) {
            $this->info('No debug logs found.');
            return 0;
        }
        
        $this->info("Showing last {$tail} debug log entries:");
        $this->line('');
        
        foreach ($debugLines as $line) {
            $this->line($line);
        }
        
        return 0;
    }
}
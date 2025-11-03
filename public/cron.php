<?php

/**
 * Laravel Scheduler Cron Runner
 * 
 * This file should be called every minute by your hosting provider's cron job.
 * 
 * URL: https://yourdomain.com/cron.php
 */

// Change to the public directory
chdir(__DIR__);

// Boot Laravel
require __DIR__ . '/../vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // Run the scheduler
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $status = $kernel->call('schedule:run');
    
    // Output for debugging
    echo "Cron executed successfully at " . date('Y-m-d H:i:s') . "\n";
    echo "Status: {$status}\n";
    
    exit($status);
    
} catch (\Exception $e) {
    // Log the error
    file_put_contents(__DIR__ . '/../storage/logs/cron.log', 
        date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", 
        FILE_APPEND
    );
    
    echo "Error: " . $e->getMessage();
    exit(1);
}


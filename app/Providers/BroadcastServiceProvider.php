<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register user (web) broadcasting auth routes
        Broadcast::routes([
            'middleware' => ['web', 'auth'],
        ]);

        // Register admin broadcasting auth routes under /admin prefix with admin guard
        Broadcast::routes([
            'middleware' => ['web', 'auth:admin'],
            'prefix' => 'admin',
        ]);

        // Load channel authorization callbacks
        require base_path('routes/channels.php');
    }
}



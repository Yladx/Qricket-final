<?php

/**
 * Emergency Cache Clearing Tool
 *
 * WARNING: This file clears all application caches.
 * Access this file via: https://yourdomain.com/fix-cache.php
 *
 * IMPORTANT: DELETE THIS FILE AFTER USE FOR SECURITY!
 */

// Disable display of errors
error_reporting(0);
ini_set('display_errors', 0);

// Security check - replace 'YOUR_SECRET_KEY' with a random string
// Change this to a random password before using
define('CACHE_KEY', 'qricket2024fix');

// Check for secret key
if (!isset($_GET['key']) || $_GET['key'] !== CACHE_KEY) {
    http_response_code(403);
    die('Access Denied');
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Cache Clear Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 600px;
        }

        .success {
            color: green;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 4px;
        }

        .error {
            color: red;
            padding: 10px;
            background: #ffebee;
            border-radius: 4px;
        }

        .info {
            color: #1976d2;
            padding: 10px;
            background: #e3f2fd;
            border-radius: 4px;
        }

        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Laravel Cache Clear Tool</h1>

        <?php
        $results = [];

        try {
            // Get the Laravel app instance
            require __DIR__ . '/../vendor/autoload.php';
            $app = require_once __DIR__ . '/../bootstrap/app.php';
            $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

            echo '<div class="info">Laravel application loaded successfully.</div>';

            // Run artisan commands
            // NOTE: cache:clear and optimize:clear try to delete from database
            // We'll skip those to avoid database connection issues
            $commands = [
                'config:clear',
                'route:clear',
                'view:clear',
            ];

            // Manually clear cache files without database
            try {
                // Clear application cache files
                $cacheFiles = [
                    __DIR__ . '/../storage/framework/cache/data/',
                    __DIR__ . '/../storage/framework/views/',
                    __DIR__ . '/../bootstrap/cache/routes.php',
                    __DIR__ . '/../bootstrap/cache/config.php',
                ];

                foreach ($cacheFiles as $path) {
                    if (is_dir($path)) {
                        $files = glob($path . '*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                @unlink($file);
                            }
                        }
                    } elseif (is_file($path)) {
                        @unlink($path);
                    }
                }

                echo '<div class="success">✓ Manual cache files cleared</div>';
            } catch (\Exception $e) {
                echo '<div class="error">✗ Manual cache clear failed: ' . $e->getMessage() . '</div>';
            }

            foreach ($commands as $command) {
                try {
                    $kernel->call($command);
                    $output = ob_get_clean();
                    $results[] = ['command' => $command, 'status' => 'success', 'output' => $output];
                    echo '<div class="success">✓ ' . $command . ' - Success</div>';
                } catch (\Exception $e) {
                    $results[] = ['command' => $command, 'status' => 'error', 'error' => $e->getMessage()];
                    echo '<div class="error">✗ ' . $command . ' - Failed: ' . $e->getMessage() . '</div>';
                }
                ob_start();
            }

            // Try to optimize
            try {
                $kernel->call('config:cache');
                $results[] = ['command' => 'config:cache', 'status' => 'success'];
                echo '<div class="success">✓ config:cache - Success</div>';
            } catch (\Exception $e) {
                $results[] = ['command' => 'config:cache', 'status' => 'error', 'error' => $e->getMessage()];
                echo '<div class="error">✗ config:cache - Failed: ' . $e->getMessage() . '</div>';
            }

            // Try to cache routes
            try {
                $kernel->call('route:cache');
                $results[] = ['command' => 'route:cache', 'status' => 'success'];
                echo '<div class="success">✓ route:cache - Success</div>';
            } catch (\Exception $e) {
                $results[] = ['command' => 'route:cache', 'status' => 'error', 'error' => $e->getMessage()];
                echo '<div class="error">✗ route:cache - Failed: ' . $e->getMessage() . '</div>';
            }

            ob_end_clean();
        } catch (\Exception $e) {
            echo '<div class="error">Failed to initialize Laravel: ' . $e->getMessage() . '</div>';
        }
        ?>

        <h2>Completed!</h2>
        <p>You can now <strong>DELETE this file</strong> for security.</p>
        <p><em>Access this file at: <code>https://yourdomain.com/fix-cache.php?key=YOUR_SECRET_KEY</code></em></p>
    </div>
</body>

</html>

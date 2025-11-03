<?php
/**
 * Path Finder for Laravel
 * 
 * Visit this file in your browser to find your project path:
 * https://yourdomain.com/find-path.php
 */

echo "=== Project Path Finder ===\n\n";

echo "Current directory: " . __DIR__ . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Project root: " . dirname(__DIR__) . "\n\n";

echo "=== Cron Command ===\n\n";
echo "Use this command in your cron job:\n\n";
echo "cd " . dirname(__DIR__) . " && php artisan schedule:run\n\n";

echo "=== Full Cron Command ===\n\n";
echo dirname(__DIR__) . "/artisan schedule:run\n\n";

echo "=== Test Path ===\n\n";

$artisanPath = dirname(__DIR__) . '/artisan';

if (file_exists($artisanPath)) {
    echo "✓ Artisan file exists!\n";
    echo "Path: {$artisanPath}\n\n";
    echo "SUCCESS! Use this path in your cron job.\n";
} else {
    echo "✗ Artisan file not found at: {$artisanPath}\n";
    echo "Please check your Laravel installation.\n";
}

echo "\n=== Remove This File ===\n";
echo "After you copy the path above, DELETE this file (find-path.php) for security!\n";


<?php
/**
 * Test webhook token configuration
 * Run this to verify Xendit webhook token is properly configured
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

echo "=== Xendit Webhook Token Configuration Test ===\n\n";

echo "Environment Variables:\n";
echo "XENDIT_WEBHOOK_TOKEN: " . env('XENDIT_WEBHOOK_TOKEN') . "\n";
echo "Length: " . strlen(env('XENDIT_WEBHOOK_TOKEN') ?? '') . "\n\n";

echo "Config Values:\n";
echo "config('services.xendit.webhook_token'): " . config('services.xendit.webhook_token') . "\n";
echo "config('services.xendit.callback_token'): " . config('services.xendit.callback_token') . "\n\n";

echo "Token Match Test:\n";
$envToken = env('XENDIT_WEBHOOK_TOKEN');
$configToken = config('services.xendit.callback_token');
echo "env('XENDIT_WEBHOOK_TOKEN') === config('services.xendit.callback_token'): " . ($envToken === $configToken ? 'TRUE' : 'FALSE') . "\n\n";

echo "Expected Xendit Token: UeVyL7Cf31cvGLHfmvvn1x1VcWlCPYxCIdrdefgJlSpHdFRc\n";
echo "Actual Token: " . $envToken . "\n";
echo "Match: " . ($envToken === 'UeVyL7Cf31cvGLHfmvvn1x1VcWlCPYxCIdrdefgJlSpHdFRc' ? 'TRUE' : 'FALSE') . "\n\n";

echo "=== Test Complete ===\n";

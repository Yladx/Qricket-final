<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing ticket ID generation:\n";

$ticket = \App\Models\TicketInformation::create([
    'user_id' => 1,
    'event_id' => 4,
    'ticket_purchase_id' => 1
]);

echo "Generated ticket_id: " . $ticket->ticket_id . "\n";
echo "Format check: " . (preg_match('/^\d+_[A-F0-9]{8}$/', $ticket->ticket_id) ? 'PASS' : 'FAIL') . "\n";

// Clean up
$ticket->delete();
echo "Test completed.\n";

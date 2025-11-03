<?php

// Simple test script to show QR code encryption
require_once 'vendor/autoload.php';

use App\Services\QRCodeEncryptionService;

// Test data
$testData = [
    'ticket_id' => '2_01A562DD',
    'ticket_information_id' => 123,
    'purchase_id' => 'PURCHASE_456',
    'user_id' => 789,
    'generated_at' => time(),
    'signature' => 'test_signature'
];

$eventId = 2;

echo "=== QR Code Encryption Test ===\n\n";

echo "Original data:\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

echo "Encrypting data for event {$eventId}...\n";
$encrypted = QRCodeEncryptionService::encrypt($testData, $eventId);

echo "Encrypted QR code data:\n";
echo $encrypted . "\n\n";

echo "Encrypted data length: " . strlen($encrypted) . " characters\n\n";

echo "Decrypting data...\n";
$decrypted = QRCodeEncryptionService::decrypt($encrypted, $eventId);

if ($decrypted) {
    echo "✅ Decryption successful!\n";
    echo "Decrypted data:\n";
    echo json_encode($decrypted, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "❌ Decryption failed!\n\n";
}

echo "Testing wrong event ID...\n";
$wrongEventDecrypt = QRCodeEncryptionService::decrypt($encrypted, $eventId + 1);

if (!$wrongEventDecrypt) {
    echo "✅ Security test passed - wrong event ID rejected\n";
} else {
    echo "❌ Security test failed - wrong event ID accepted\n";
}

echo "\n=== What external scanners see ===\n";
echo "Google Lens or other QR scanners would see:\n";
echo $encrypted . "\n";
echo "\nThis is completely encrypted and meaningless to external scanners!\n";

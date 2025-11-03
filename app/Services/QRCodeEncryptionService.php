<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class QRCodeEncryptionService
{
    /**
     * Encrypt QR code data for a specific event
     */
    public static function encrypt(array $data, int $eventId): string
    {
        try {
            // Add event ID to ensure QR code is tied to specific event
            $data['event_id'] = $eventId;
            $data['encrypted_at'] = now()->timestamp;
            
            // Create a unique encryption key for this event
            $encryptionKey = self::getEventEncryptionKey($eventId);
            
            // Serialize and encrypt the data
            $serializedData = json_encode($data);
            $encryptedData = self::encryptWithKey($serializedData, $encryptionKey);
            
            // Add integrity check
            $integrityHash = hash('sha256', $encryptedData . $encryptionKey);
            
            // Combine encrypted data with integrity hash
            $finalData = base64_encode($encryptedData . '|' . $integrityHash);
            
            Log::info('QR code encrypted', [
                'event_id' => $eventId,
                'ticket_id' => $data['ticket_id'] ?? 'unknown',
                'data_length' => strlen($finalData)
            ]);
            
            return $finalData;
            
        } catch (\Exception $e) {
            Log::error('QR code encryption failed', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Decrypt QR code data and validate it's for the correct event
     */
    public static function decrypt(string $encryptedData, int $expectedEventId): ?array
    {
        try {
            // Decode base64
            $decodedData = base64_decode($encryptedData);
            if (!$decodedData) {
                return null;
            }
            
            // Split encrypted data and integrity hash
            $parts = explode('|', $decodedData, 2);
            if (count($parts) !== 2) {
                return null;
            }
            
            [$encryptedContent, $integrityHash] = $parts;
            
            // Get encryption key for the expected event
            $encryptionKey = self::getEventEncryptionKey($expectedEventId);
            
            // Verify integrity
            $expectedHash = hash('sha256', $encryptedContent . $encryptionKey);
            if (!hash_equals($expectedHash, $integrityHash)) {
                Log::warning('QR code integrity check failed', [
                    'expected_event_id' => $expectedEventId
                ]);
                return null;
            }
            
            // Decrypt the content
            $decryptedContent = self::decryptWithKey($encryptedContent, $encryptionKey);
            if (!$decryptedContent) {
                return null;
            }
            
            // Deserialize the data
            $data = json_decode($decryptedContent, true);
            if (!$data) {
                return null;
            }
            
            // Verify the event ID matches
            if (!isset($data['event_id']) || $data['event_id'] !== $expectedEventId) {
                Log::warning('QR code event ID mismatch', [
                    'expected_event_id' => $expectedEventId,
                    'actual_event_id' => $data['event_id'] ?? 'missing'
                ]);
                return null;
            }
            
            // Check if QR code is not too old (optional security measure)
            if (isset($data['encrypted_at'])) {
                $ageInDays = (now()->timestamp - $data['encrypted_at']) / (24 * 60 * 60);
                if ($ageInDays > 365) { // QR codes expire after 1 year
                    Log::warning('QR code expired', [
                        'event_id' => $expectedEventId,
                        'age_days' => $ageInDays
                    ]);
                    return null;
                }
            }
            
            Log::info('QR code decrypted successfully', [
                'event_id' => $expectedEventId,
                'ticket_id' => $data['ticket_id'] ?? 'unknown'
            ]);
            
            return $data;
            
        } catch (\Exception $e) {
            Log::error('QR code decryption failed', [
                'expected_event_id' => $expectedEventId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Generate a unique encryption key for an event
     */
    private static function getEventEncryptionKey(int $eventId): string
    {
        // Use app key + event ID + a secret salt to create event-specific key
        $baseKey = config('app.key');
        $eventSalt = 'QR_EVENT_SALT_' . $eventId;
        
        // Create a deterministic but unique key for this event
        return hash('sha256', $baseKey . $eventSalt . $eventId);
    }
    
    /**
     * Encrypt data with a specific key using AES-256-CBC
     */
    private static function encryptWithKey(string $data, string $key): string
    {
        $iv = random_bytes(16); // Generate random IV
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        if ($encrypted === false) {
            throw new \Exception('Encryption failed');
        }
        
        // Combine IV and encrypted data
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data with a specific key using AES-256-CBC
     */
    private static function decryptWithKey(string $encryptedData, string $key): ?string
    {
        $data = base64_decode($encryptedData);
        if (!$data) {
            return null;
        }
        
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        
        return $decrypted !== false ? $decrypted : null;
    }
    
    /**
     * Validate that a QR code is properly encrypted and for the correct event
     */
    public static function validateQRCode(string $qrData, int $eventId): bool
    {
        $decrypted = self::decrypt($qrData, $eventId);
        return $decrypted !== null;
    }
}

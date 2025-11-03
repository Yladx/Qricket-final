<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class XenditDisbursementService
{
    public function disburse(string $externalId, float $amount, string $bankCode, string $accountNumber, string $accountName, string $description = ''): string
    {
        $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('Xendit API key not configured');
        }

        // Convert bank code to Xendit channel code format (only supported codes)
        $channelCodeMap = [
            'BDO_ONLINE_BANKING' => 'PH_BDO',
            'BPI_ONLINE_BANKING' => 'PH_BPI', 
            'PNB_ONLINE_BANKING' => 'PH_PNB',
            'RCBC_ONLINE_BANKING' => 'PH_RCBC',
            'UNIONBANK_ONLINE_BANKING' => 'PH_UBP',
            'CHINABANK_ONLINE_BANKING' => 'PH_CBC',
            'LANDBANK_ONLINE_BANKING' => 'PH_LBP',
            'BOC_ONLINE_BANKING' => 'PH_BOC',
            // Note: METROBANK, SECURITY_BANK, MAYBANK, ROBINSONS_BANK, INSTAPAY, PESONET are not supported
            'METROBANK_ONLINE_BANKING' => 'PH_BPI', // Fallback to BPI
            'SECURITY_BANK_ONLINE_BANKING' => 'PH_BPI', // Fallback to BPI
            'MAYBANK_ONLINE_BANKING' => 'PH_BPI', // Fallback to BPI
            'ROBINSONS_BANK_ONLINE_BANKING' => 'PH_BPI', // Fallback to BPI
            'INSTAPAY_ONLINE_BANKING' => 'PH_BPI', // Fallback to BPI
            'PESONET_ONLINE_BANKING' => 'PH_BPI', // Fallback to BPI
            'BANK_TRANSFER' => 'PH_BPI', // Default to BPI for generic bank transfer
        ];
        
        $channelCode = $channelCodeMap[$bankCode] ?? 'PH_BPI';

        $payload = [
            'reference_id' => $externalId,
            'currency' => 'PHP',
            'channel_code' => $channelCode,
            'amount' => (int) round($amount),
            'description' => $description,
            'channel_properties' => [
                'account_number' => $accountNumber,
                'account_holder_name' => $accountName,
            ],
        ];

        // Generate idempotency key
        $idempotencyKey = 'payout_' . $externalId . '_' . time();

        // Log the payload for debugging
        Log::info('Xendit disbursement payload', $payload);

        // Make the actual API call
        $response = Http::withBasicAuth($apiKey, '')
            ->asJson()
            ->withHeaders([
                'idempotency-key' => $idempotencyKey,
            ])
            ->post('https://api.xendit.co/v2/payouts', $payload);

        if (!$response->successful()) {
            // Handle different error cases
            if ($response->status() === 403) {
                Log::warning('Xendit account not allowed for payouts API - account needs verification', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload
                ]);
                throw new \RuntimeException('Xendit account not verified for disbursements. Please contact Xendit support to enable payouts API.');
            }
            
            if ($response->status() === 404) {
                Log::warning('Xendit payouts API not available - endpoint not found', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload
                ]);
                throw new \RuntimeException('Xendit payouts API not available. Your account may not have disbursement access enabled.');
            }

            Log::error('Xendit disbursement failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);
            throw new \RuntimeException('Xendit disbursement failed: '.$response->status().' '.$response->body());
        }

        $data = $response->json();
        Log::info('Xendit disbursement successful', $data);
        return (string)($data['id'] ?? $data['disbursement_id'] ?? '');
    }
}



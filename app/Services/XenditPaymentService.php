<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditPaymentService
{
    public function createPaymentPage(array $data): array
    {
        $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('Xendit API key not configured');
        }

        // Calculate fees for the payment method
        $paymentMethod = $data['payment_method'] ?? 'CREDIT_CARD';
        $fees = XenditFeeCalculator::calculateFees($data['amount'], $paymentMethod);

        $payload = [
            'external_id' => $data['external_id'],
            'amount' => (int) round($data['amount']),
            'description' => $data['description'],
            'invoice_duration' => 1800, // 30 minutes
            'currency' => 'PHP',
            'success_redirect_url' => $data['success_url'],
            'failure_redirect_url' => $data['failure_url'],
            'payment_methods' => [
                'CREDIT_CARD',
                'GCASH',
                'PAYMAYA',
                'SHOPEEPAY'
            ],
            'customer' => [
                'given_names' => $data['customer_name'] ?? 'Payout Recipient',
                'email' => $data['customer_email'] ?? 'payout@qricket.com',
            ],
            'customer_notification_preference' => [
                'invoice_created' => ['email'],
                'invoice_reminder' => ['email'],
                'invoice_paid' => ['email'],
                'invoice_expired' => ['email']
            ],
            'fees' => [
                [
                    'type' => 'TRANSACTION',
                    'value' => $fees['transaction_fee']
                ]
            ],
            'taxes' => [
                [
                    'name' => 'VAT',
                    'rate' => 0.12,
                    'amount' => $fees['vat_amount']
                ]
            ]
        ];

        Log::info('Creating Xendit payment page', [
            'external_id' => $data['external_id'],
            'amount' => $data['amount'],
            'description' => $data['description'],
            'payment_method' => $paymentMethod,
            'fees' => $fees
        ]);

        $response = Http::withBasicAuth($apiKey, '')
            ->asJson()
            ->post('https://api.xendit.co/v2/invoices', $payload);

        if (!$response->successful()) {
            Log::error('Xendit payment page creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);
            throw new \RuntimeException('Xendit payment page creation failed: ' . $response->status() . ' ' . $response->body());
        }

        $responseData = $response->json();
        
        // Add fee information to response
        $responseData['fee_breakdown'] = $fees;
        
        Log::info('Xendit payment page created successfully', [
            'invoice_id' => $responseData['id'],
            'external_id' => $responseData['external_id'],
            'invoice_url' => $responseData['invoice_url'],
            'fees' => $fees
        ]);

        return $responseData;
    }

    public function getInvoiceStatus(string $invoiceId): array
    {
        $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
        if (!$apiKey) {
            throw new \RuntimeException('Xendit API key not configured');
        }

        $response = Http::withBasicAuth($apiKey, '')
            ->timeout(30)
            ->get("https://api.xendit.co/v2/invoices/{$invoiceId}");

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to get invoice status: ' . $response->status() . ' ' . $response->body());
        }

        return $response->json();
    }
}

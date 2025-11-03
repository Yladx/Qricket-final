<?php

namespace App\Http\Controllers;

use App\Services\XenditDisbursementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestDisbursementController extends Controller
{
    public function index()
    {
        return view('test.disbursement');
    }

    public function testDisbursement(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $service = new XenditDisbursementService();
        
        try {
            $externalId = 'test_' . time() . '_' . rand(1000, 9999);
            $amount = $request->amount;
            $bankCode = $request->bank_code;
            $accountNumber = $request->account_number;
            $accountName = $request->account_name;
            $description = $request->description ?? 'Test disbursement';

            Log::info('Starting disbursement test', [
                'external_id' => $externalId,
                'amount' => $amount,
                'bank_code' => $bankCode,
                'account_number' => $accountNumber,
                'account_name' => $accountName,
                'description' => $description,
            ]);

            $result = $service->disburse(
                $externalId,
                $amount,
                $bankCode,
                $accountNumber,
                $accountName,
                $description
            );

            return response()->json([
                'success' => true,
                'message' => 'Disbursement test completed successfully',
                'data' => [
                    'external_id' => $externalId,
                    'disbursement_id' => $result,
                    'amount' => $amount,
                    'bank_code' => $bankCode,
                    'account_number' => $accountNumber,
                    'account_name' => $accountName,
                    'description' => $description,
                    'timestamp' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Disbursement test failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Disbursement test failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    public function testApiEndpoint(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'method' => 'required|string|in:GET,POST,PUT,DELETE',
            'payload' => 'nullable|array',
        ]);

        try {
            $apiKey = config('services.xendit.secret_key') ?? config('services.xendit.api_key');
            
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Xendit API key not configured',
                ], 400);
            }

            $response = \Illuminate\Support\Facades\Http::withBasicAuth($apiKey, '')
                ->asJson()
                ->withHeaders([
                    'idempotency-key' => 'test_' . time(),
                ])
                ->{strtolower($request->method)}($request->endpoint, $request->payload ?? []);

            return response()->json([
                'success' => true,
                'message' => 'API endpoint test completed',
                'data' => [
                    'endpoint' => $request->endpoint,
                    'method' => $request->method,
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'body' => $response->body(),
                    'json' => $response->json(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API endpoint test failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSupportedBanks()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'banks' => [
                    ['code' => 'BANK_TRANSFER', 'name' => 'Bank Transfer (Generic)'],
                    ['code' => 'BDO_ONLINE_BANKING', 'name' => 'BDO Bank Transfer'],
                    ['code' => 'BPI_ONLINE_BANKING', 'name' => 'BPI Online Banking'],
                    ['code' => 'METROBANK_ONLINE_BANKING', 'name' => 'Metrobank Bank Transfer'],
                    ['code' => 'PNB_ONLINE_BANKING', 'name' => 'PNB Bank Transfer'],
                    ['code' => 'RCBC_ONLINE_BANKING', 'name' => 'RCBC Online Banking'],
                    ['code' => 'SECURITY_BANK_ONLINE_BANKING', 'name' => 'Security Bank Bank Transfer'],
                    ['code' => 'UNIONBANK_ONLINE_BANKING', 'name' => 'UnionBank Online Banking'],
                    ['code' => 'CHINABANK_ONLINE_BANKING', 'name' => 'Chinabank Online Banking'],
                    ['code' => 'PSBANK_ONLINE_BANKING', 'name' => 'PSBank Bank Transfer'],
                    ['code' => 'LANDBANK_ONLINE_BANKING', 'name' => 'LandBank Bank Transfer'],
                    ['code' => 'MAYBANK_ONLINE_BANKING', 'name' => 'Maybank Bank Transfer'],
                    ['code' => 'BOC_ONLINE_BANKING', 'name' => 'Bank of China Bank Transfer'],
                    ['code' => 'ROBINSONS_BANK_ONLINE_BANKING', 'name' => 'Robinsons Bank Bank Transfer'],
                    ['code' => 'INSTAPAY_ONLINE_BANKING', 'name' => 'InstaPay Bank Transfer'],
                    ['code' => 'PESONET_ONLINE_BANKING', 'name' => 'PESONet Bank Transfer'],
                ]
            ]
        ]);
    }
}

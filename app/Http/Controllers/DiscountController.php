<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discount;
use Illuminate\Support\Facades\Log;

class DiscountController extends Controller
{
    /**
     * Validate a discount code for API use
     */
    public function validateDiscount(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|max:20',
                'type' => 'required|string|in:subscription,ticket'
            ]);

            $code = strtoupper(trim($request->code));
            $type = $request->type;
            
            Log::info('Discount code validation attempt (API)', [
                'code' => $code,
                'type' => $type,
                'user_id' => auth()->id()
            ]);

            // Find the discount code
            $discount = Discount::where('code', $code)
                ->where('is_active', true)
                ->where(function($query) use ($type) {
                    $query->where('applicable_to', $type)
                          ->orWhere('applicable_to', 'all');
                })
                ->first();

            if (!$discount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid discount code'
                ]);
            }

            // Check if discount has expired
            if ($discount->expires_at && now()->gt($discount->expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount code has expired'
                ]);
            }

            // Check usage limits
            if ($discount->usage_limit && $discount->used_count >= $discount->usage_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount code usage limit reached'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Discount code applied successfully',
                'discount' => [
                    'id' => $discount->id,
                    'code' => $discount->code,
                    'type' => $discount->type,
                    'value' => $discount->value,
                    'description' => $discount->description
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Discount validation error (API)', [
                'error' => $e->getMessage(),
                'code' => $request->code ?? 'N/A',
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error validating discount code'
            ], 500);
        }
    }

}

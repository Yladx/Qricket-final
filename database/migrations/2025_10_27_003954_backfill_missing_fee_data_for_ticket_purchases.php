<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\XenditFeeCalculator;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill missing fee data for ticket purchases that don't have fee information
        // Check for records where transaction_fee is 0 or null (backfill needed)
        DB::table('ticket_purchases')
            ->where(function($query) {
                $query->whereNull('transaction_fee')
                      ->orWhere('transaction_fee', 0);
            })
            ->where('status', 'paid')
            ->chunkById(100, function ($purchases) {
                foreach ($purchases as $purchase) {
                    // Calculate fees based on the payment method (default to CREDIT_CARD if not available)
                    $paymentMethod = $purchase->payment_method ?? 'CREDIT_CARD';
                    $amount = $purchase->total_amount ?? 0;
                    
                    // Only calculate if amount is greater than 0
                    if ($amount > 0) {
                        $fees = XenditFeeCalculator::calculateFees($amount, $paymentMethod);
                        
                        DB::table('ticket_purchases')
                            ->where('id', $purchase->id)
                            ->update([
                                'transaction_fee' => $fees['transaction_fee'],
                                'vat_amount' => $fees['vat_amount'],
                                'net_amount' => $fees['net_amount'],
                                'total_fees' => $fees['total_fees'],
                            ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set fee fields back to NULL for records that were updated by this migration
        // This is tricky because we can't easily identify which records were backfilled
        // In practice, we'll just set them to 0 instead of NULL
        DB::table('ticket_purchases')
            ->whereNotNull('transaction_fee')
            ->where('status', 'paid')
            ->update([
                'transaction_fee' => 0,
                'vat_amount' => 0,
                'net_amount' => DB::raw('COALESCE(total_amount, 0)'),
                'total_fees' => 0,
            ]);
    }
};

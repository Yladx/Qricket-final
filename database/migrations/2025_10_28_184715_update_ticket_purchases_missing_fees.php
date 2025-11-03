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
        // Update ticket purchases that are missing fee data
        $ticketPurchases = DB::table('ticket_purchases')
            ->whereNull('transaction_fee')
            ->orWhereNull('vat_amount')
            ->orWhereNull('net_amount')
            ->orWhereNull('total_fees')
            ->get();

        foreach ($ticketPurchases as $purchase) {
            $amount = $purchase->total_amount ?? 0;
            $paymentMethod = $purchase->payment_method ?? 'CREDIT_CARD';
            
            // For bypass/free events, use BYPASS payment method
            if (str_contains($purchase->xendit_payment_id ?? '', 'BYPASS') || 
                str_contains($purchase->xendit_payment_id ?? '', 'FREE')) {
                $paymentMethod = 'BYPASS';
            }
            
            $fees = XenditFeeCalculator::calculateFees($amount, $paymentMethod);
            
            DB::table('ticket_purchases')
                ->where('id', $purchase->id)
                ->update([
                    'transaction_fee' => $fees['transaction_fee'],
                    'vat_amount' => $fees['vat_amount'],
                    'net_amount' => $fees['net_amount'],
                    'total_fees' => $fees['total_fees'],
                    'payment_method' => $paymentMethod,
                    'updated_at' => now(),
                ]);
        }

        // Update additional ticket purchases that are missing fee data
        $additionalTicketPurchases = DB::table('ticket_limit_purchases')
            ->whereNull('transaction_fee')
            ->orWhereNull('vat_amount')
            ->orWhereNull('net_amount')
            ->orWhereNull('total_fees')
            ->get();

        foreach ($additionalTicketPurchases as $purchase) {
            $amount = $purchase->amount ?? 0;
            $paymentMethod = $purchase->payment_method ?? 'CREDIT_CARD';
            
            $fees = XenditFeeCalculator::calculateFees($amount, $paymentMethod);
            
            DB::table('ticket_limit_purchases')
                ->where('id', $purchase->id)
                ->update([
                    'transaction_fee' => $fees['transaction_fee'],
                    'vat_amount' => $fees['vat_amount'],
                    'net_amount' => $fees['net_amount'],
                    'total_fees' => $fees['total_fees'],
                    'payment_method' => $paymentMethod,
                    'updated_at' => now(),
                ]);
        }

        // Update subscriptions that are missing fee data
        $subscriptions = DB::table('subscriptions')
            ->whereNull('transaction_fee')
            ->orWhereNull('vat_amount')
            ->orWhereNull('net_amount')
            ->orWhereNull('total_fees')
            ->get();

        foreach ($subscriptions as $subscription) {
            $amount = $subscription->amount ?? 0;
            $paymentMethod = $subscription->payment_method ?? 'CREDIT_CARD';
            
            $fees = XenditFeeCalculator::calculateFees($amount, $paymentMethod);
            
            DB::table('subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'transaction_fee' => $fees['transaction_fee'],
                    'vat_amount' => $fees['vat_amount'],
                    'net_amount' => $fees['net_amount'],
                    'total_fees' => $fees['total_fees'],
                    'payment_method' => $paymentMethod,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only updates data, no schema changes to reverse
    }
};
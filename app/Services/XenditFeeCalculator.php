<?php

namespace App\Services;

class XenditFeeCalculator
{
    /**
     * Calculate fees for a given amount and payment method
     */
    public static function calculateFees($amount, $paymentMethod)
    {
        // Check for payment method aliases (e.g., EWALLET -> GCASH)
        $aliases = config("xendit_fees.payment_method_aliases", []);
        if (isset($aliases[$paymentMethod])) {
            $paymentMethod = $aliases[$paymentMethod];
        }
        
        $config = config("xendit_fees.payment_methods.{$paymentMethod}");
        
        if (!$config) {
            // Use default if payment method not found
            $config = config("xendit_fees.payment_methods.CREDIT_CARD");
        }
        
        // Calculate transaction fee
        $transactionFee = ($amount * $config['transaction_fee_rate']) + $config['fixed_fee'];
        
        // Calculate VAT on transaction fee
        $vatAmount = $transactionFee * $config['vat_rate'];
        
        // Calculate net amount
        $netAmount = $amount - $transactionFee - $vatAmount;
        
        return [
            'transaction_fee' => round($transactionFee, 2),
            'vat_amount' => round($vatAmount, 2),
            'net_amount' => round($netAmount, 2),
            'total_fees' => round($transactionFee + $vatAmount, 2),
            'payment_method' => $paymentMethod
        ];
    }
    
    /**
     * Get available payment methods
     */
    public static function getAvailablePaymentMethods()
    {
        return array_keys(config('xendit_fees.payment_methods', []));
    }
    
    /**
     * Get fee breakdown for display
     */
    public static function getFeeBreakdown($amount, $paymentMethod)
    {
        $fees = self::calculateFees($amount, $paymentMethod);
        
        return [
            'gross_amount' => $amount,
            'transaction_fee' => $fees['transaction_fee'],
            'vat_amount' => $fees['vat_amount'],
            'net_amount' => $fees['net_amount'],
            'total_fees' => $fees['total_fees'],
            'payment_method' => $paymentMethod,
            'breakdown' => [
                'Transaction Fee' => $fees['transaction_fee'],
                'VAT (12%)' => $fees['vat_amount'],
                'Total Fees' => $fees['total_fees'],
                'Net Amount' => $fees['net_amount']
            ]
        ];
    }
    
    /**
     * Calculate fees for multiple items
     */
    public static function calculateBulkFees($items, $paymentMethod)
    {
        $totalGross = 0;
        $totalTransactionFee = 0;
        $totalVat = 0;
        $totalNet = 0;
        
        foreach ($items as $item) {
            $fees = self::calculateFees($item['amount'], $paymentMethod);
            $totalGross += $item['amount'];
            $totalTransactionFee += $fees['transaction_fee'];
            $totalVat += $fees['vat_amount'];
            $totalNet += $fees['net_amount'];
        }
        
        return [
            'total_gross' => round($totalGross, 2),
            'total_transaction_fee' => round($totalTransactionFee, 2),
            'total_vat' => round($totalVat, 2),
            'total_net' => round($totalNet, 2),
            'total_fees' => round($totalTransactionFee + $totalVat, 2),
            'payment_method' => $paymentMethod
        ];
    }
}

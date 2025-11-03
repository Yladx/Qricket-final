<?php

return [
    'payment_methods' => [
        'GCASH' => [
            'transaction_fee_rate' => 0.023, // 2.30%
            'fixed_fee' => 0,
            'vat_rate' => 0.12, // 12% VAT on transaction fee
        ],
        'GRABPAY' => [
            'transaction_fee_rate' => 0.02, // 2.00%
            'fixed_fee' => 0,
            'vat_rate' => 0.12, // 12% VAT on transaction fee
        ],
        'SHOPEEPAY' => [
            'transaction_fee_rate' => 0.02, // 2.00%
            'fixed_fee' => 0,
            'vat_rate' => 0.12, // 12% VAT on transaction fee
        ],
        'PAYMAYA' => [
            'transaction_fee_rate' => 0.018, // 1.80%
            'fixed_fee' => 0,
            'vat_rate' => 0.12, // 12% VAT on transaction fee
        ],
        'CREDIT_CARD' => [
            'transaction_fee_rate' => 0.032, // 3.20%
            'fixed_fee' => 10.00,
            'vat_rate' => 0.12, // 12% VAT on transaction fee
        ],
        'LOCAL_CARD' => [
            'transaction_fee_rate' => 0.032, // 3.20%
            'fixed_fee' => 10.00,
            'vat_rate' => 0.12, // 12% VAT on transaction fee
        ],
        'INTERNATIONAL' => [
            'transaction_fee_rate' => 0.042, // 4.20%
            'fixed_fee' => 10.00,
            'vat_rate' => 0.12, // 12% VAT on transaction fee
        ],
        'INTERNATIONAL_CARD' => [
            'transaction_fee_rate' => 0.042, // 4.20%
            'fixed_fee' => 10.00,
            'vat_rate' => 0.12, // 12% VAT on transaction fee
        ],
    ],
    
    // Generic EWALLET - use average of e-wallet rates as fallback
    'payment_method_aliases' => [
        'EWALLET' => 'GCASH', // Default to GCash rate (most common)
        'DEBIT_CARD' => 'CREDIT_CARD',
        'BANK_TRANSFER' => 'CREDIT_CARD',
        'LOCAL' => 'LOCAL_CARD',
        'INTERNATIONAL_CARD' => 'INTERNATIONAL',
    ],
    
    'default_payment_method' => 'CREDIT_CARD',
    'currency' => 'PHP',
    'vat_rate' => 0.12, // 12% VAT on transaction fees
];

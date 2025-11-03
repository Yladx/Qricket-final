<?php

namespace Database\Seeders;

use App\Models\Discount;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            [
                'name' => 'New User Welcome',
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10.00,
                'currency' => 'PHP',
                'applicable_to' => 'all',
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
                'description' => 'Welcome discount for new users',
                'conditions' => json_encode(['new_users_only' => true]),
            ],
            [
                'name' => 'Early Bird Special',
                'code' => 'EARLY20',
                'type' => 'percentage',
                'value' => 20.00,
                'currency' => 'PHP',
                'applicable_to' => 'subscription',
                'usage_limit' => 50,
                'per_user_limit' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
                'description' => 'Early bird discount for subscription plans',
                'conditions' => json_encode(['early_bird' => true]),
            ],
            [
                'name' => 'Additional Tickets Discount',
                'code' => 'TICKETS15',
                'type' => 'percentage',
                'value' => 15.00,
                'currency' => 'PHP',
                'applicable_to' => 'ticket_limit_purchase',
                'usage_limit' => 200,
                'per_user_limit' => 2,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
                'description' => 'Discount for additional ticket purchases',
                'conditions' => json_encode(['ticket_purchase' => true]),
            ],
            [
                'name' => 'Fixed Amount Discount',
                'code' => 'SAVE50',
                'type' => 'fixed_amount',
                'value' => 50.00,
                'currency' => 'PHP',
                'applicable_to' => 'all',
                'usage_limit' => 75,
                'per_user_limit' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
                'description' => 'Fixed amount discount of PHP 50',
                'conditions' => json_encode(['minimum_amount' => 100]),
            ],
            [
                'name' => 'VIP Member Discount',
                'code' => 'VIP25',
                'type' => 'percentage',
                'value' => 25.00,
                'currency' => 'PHP',
                'applicable_to' => 'all',
                'usage_limit' => 25,
                'per_user_limit' => 3,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
                'description' => 'VIP member exclusive discount',
                'conditions' => json_encode(['vip_members' => true]),
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::firstOrCreate(
                ['code' => $discount['code']],
                $discount
            );
        }
    }
}

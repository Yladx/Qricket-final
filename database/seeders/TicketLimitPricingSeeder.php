<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TicketLimitPricing;
use App\Models\Discount;

class TicketLimitPricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample ticket limit pricing plans for paid events
        $paidPricingPlans = [
            [
                'name' => '100 Additional Tickets',
                'ticket_quantity' => 100,
                'price' => 500.00,
                'currency' => 'PHP',
                'description' => 'Perfect for small to medium events that need extra capacity',
                'is_active' => true,
                'sort_order' => 1,
                'type' => 'paid',
            ],
            [
                'name' => '200 Additional Tickets',
                'ticket_quantity' => 200,
                'price' => 900.00,
                'currency' => 'PHP',
                'description' => 'Great value for larger events requiring more tickets',
                'is_active' => true,
                'sort_order' => 2,
                'type' => 'paid',
            ],
            [
                'name' => '500 Additional Tickets',
                'ticket_quantity' => 500,
                'price' => 2000.00,
                'currency' => 'PHP',
                'description' => 'Best value for major events and conferences',
                'is_active' => true,
                'sort_order' => 3,
                'type' => 'paid',
            ],
            [
                'name' => '1000 Additional Tickets',
                'ticket_quantity' => 1000,
                'price' => 3500.00,
                'currency' => 'PHP',
                'description' => 'Ultimate package for large-scale events and festivals',
                'is_active' => true,
                'sort_order' => 4,
                'type' => 'paid',
            ],
        ];

        // Sample ticket limit pricing plans for free events
        $freePricingPlans = [
            [
                'name' => '50 Additional Tickets',
                'ticket_quantity' => 50,
                'price' => 250.00,
                'currency' => 'PHP',
                'description' => 'Perfect for small free events that need extra capacity',
                'is_active' => true,
                'sort_order' => 1,
                'type' => 'free',
            ],
            [
                'name' => '100 Additional Tickets',
                'ticket_quantity' => 100,
                'price' => 450.00,
                'currency' => 'PHP',
                'description' => 'Great value for free events requiring more tickets',
                'is_active' => true,
                'sort_order' => 2,
                'type' => 'free',
            ],
            [
                'name' => '200 Additional Tickets',
                'ticket_quantity' => 200,
                'price' => 800.00,
                'currency' => 'PHP',
                'description' => 'Best value for larger free events',
                'is_active' => true,
                'sort_order' => 3,
                'type' => 'free',
            ],
        ];

        // Create paid event pricing plans
        foreach ($paidPricingPlans as $plan) {
            TicketLimitPricing::firstOrCreate(
                ['ticket_quantity' => $plan['ticket_quantity'], 'type' => 'paid'],
                $plan
            );
        }

        // Create free event pricing plans
        foreach ($freePricingPlans as $plan) {
            TicketLimitPricing::firstOrCreate(
                ['ticket_quantity' => $plan['ticket_quantity'], 'type' => 'free'],
                $plan
            );
        }
    }
}
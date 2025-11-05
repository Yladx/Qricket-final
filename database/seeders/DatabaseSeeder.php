<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run the essential seeders
        $this->call([
            AdminSeeder::class,
            SubscriptionPlanSeeder::class,
            TicketLimitPricingSeeder::class,
            DiscountSeeder::class,
            UserStatusSeeder::class,
            DemoEventDataSeeder::class,
        ]);
    }
}


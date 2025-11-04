<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Event;
use App\Models\EventTicket;
use App\Models\SubscriptionPlan;
use App\Models\Discount;
use App\Models\TicketLimitPricing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class EventAndUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get 2 users
        $users = [
            User::firstOrCreate(
                ['email' => 'organizer1@test.com'],
                [
                    'first_name' => 'John',
                    'last_name' => 'Organizer',
                    'password' => Hash::make('password'),
                    'organizer' => true,
                    'verified_organizer' => now(),
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                ]
            ),
            User::firstOrCreate(
                ['email' => 'organizer2@test.com'],
                [
                    'first_name' => 'Jane',
                    'last_name' => 'EventMaster',
                    'password' => Hash::make('password'),
                    'organizer' => true,
                    'verified_organizer' => now(),
                    'status' => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                ]
            ),
        ];

        // Delete existing events for these users to start fresh
        Event::whereIn('user_id', [$users[0]->id, $users[1]->id])->delete();
        EventTicket::whereIn('event_id', Event::whereIn('user_id', [$users[0]->id, $users[1]->id])->pluck('id'))->delete();

        // Event 1: Ended Event 1
        $endedEvent1 = Event::create([
            'user_id' => $users[0]->id,
            'title' => 'Summer Music Festival 2025',
            'description' => 'A spectacular outdoor music festival featuring top artists from around the world. Enjoy live performances, food vendors, and an unforgettable experience under the stars. Multiple stages with different music genres.',
            'category' => 'Music',
            'start_date' => Carbon::now()->subDays(7)->setTime(14, 0),
            'end_date' => Carbon::now()->subDays(6)->setTime(22, 0),
            'location' => 'Central Park, New York',
            'location_url' => 'https://maps.google.com/?q=Central+Park',
            'tags' => 'Music, Festival, Live Performance, Outdoor',
            'status' => 'published',
        ]);

        // Event 1 Ticket Types
        EventTicket::create([
            'event_id' => $endedEvent1->id,
            'name' => 'General Admission',
            'description' => 'Access to all stages',
            'price' => 150.00,
            'quantity' => 500,
            'sold_count' => 320,
            'sale_start' => Carbon::now()->subMonths(2),
            'sale_end' => Carbon::now()->subDays(7),
            'is_active' => false,
        ]);
        EventTicket::create([
            'event_id' => $endedEvent1->id,
            'name' => 'VIP Pass',
            'description' => 'VIP area access and complimentary drinks',
            'price' => 350.00,
            'quantity' => 50,
            'sold_count' => 45,
            'sale_start' => Carbon::now()->subMonths(2),
            'sale_end' => Carbon::now()->subDays(7),
            'is_active' => false,
        ]);

        // Event 2: Ended Event 2
        $endedEvent2 = Event::create([
            'user_id' => $users[1]->id,
            'title' => 'Tech Conference 2025',
            'description' => 'Join leading tech innovators and entrepreneurs for a day of inspiring talks, networking opportunities, and hands-on workshops. Explore the latest trends in AI, blockchain, and software development.',
            'category' => 'Technology',
            'start_date' => Carbon::now()->subDays(14)->setTime(9, 0),
            'end_date' => Carbon::now()->subDays(13)->setTime(17, 0),
            'location' => 'Convention Center, San Francisco',
            'location_url' => 'https://maps.google.com/?q=SF+Convention+Center',
            'tags' => 'Technology, Conference, Innovation, Networking',
            'status' => 'published',
        ]);

        // Event 2 Ticket Types
        EventTicket::create([
            'event_id' => $endedEvent2->id,
            'name' => 'Standard Ticket',
            'description' => 'Access to all sessions',
            'price' => 200.00,
            'quantity' => 300,
            'sold_count' => 285,
            'sale_start' => Carbon::now()->subMonths(3),
            'sale_end' => Carbon::now()->subDays(14),
            'is_active' => false,
        ]);

        // Event 3: Upcoming Event 1
        $upcomingEvent1 = Event::create([
            'user_id' => $users[0]->id,
            'title' => 'Winter Fashion Show',
            'description' => 'Discover the latest winter fashion trends from renowned designers. An exclusive evening of style, elegance, and innovation. Preview the 2025/2026 winter collection before it hits stores.',
            'category' => 'Fashion',
            'start_date' => Carbon::now()->addMonths(2)->setTime(18, 0),
            'end_date' => Carbon::now()->addMonths(2)->setTime(22, 0),
            'location' => 'Metropolitan Hall, Los Angeles',
            'location_url' => 'https://maps.google.com/?q=Metropolitan+Hall+LA',
            'tags' => 'Fashion, Runway, Designer, Exclusive',
            'status' => 'published',
        ]);

        // Event 3 Ticket Types
        EventTicket::create([
            'event_id' => $upcomingEvent1->id,
            'name' => 'General Access',
            'description' => 'Standard seating',
            'price' => 80.00,
            'quantity' => 200,
            'sold_count' => 65,
            'sale_start' => Carbon::now()->subDays(30),
            'sale_end' => Carbon::now()->addMonths(2),
            'is_active' => true,
        ]);

        // Event 4: Upcoming Event 2
        $upcomingEvent2 = Event::create([
            'user_id' => $users[1]->id,
            'title' => 'Food & Wine Festival',
            'description' => 'Savor delicious cuisine from top chefs around the world. Enjoy wine tastings, cooking demonstrations, and meet renowned culinary experts. A gastronomic journey you won\'t forget.',
            'category' => 'Food & Drink',
            'start_date' => Carbon::now()->addMonths(3)->setTime(12, 0),
            'end_date' => Carbon::now()->addMonths(3)->setTime(20, 0),
            'location' => 'Riverside Park, Chicago',
            'location_url' => 'https://maps.google.com/?q=Riverside+Park+Chicago',
            'tags' => 'Food, Wine, Culinary, Festival',
            'status' => 'published',
        ]);

        // Event 4 Ticket Types
        EventTicket::create([
            'event_id' => $upcomingEvent2->id,
            'name' => 'Day Pass',
            'description' => 'Full day access',
            'price' => 120.00,
            'quantity' => 400,
            'sold_count' => 128,
            'sale_start' => Carbon::now()->subDays(15),
            'sale_end' => Carbon::now()->addMonths(3),
            'is_active' => true,
        ]);
        EventTicket::create([
            'event_id' => $upcomingEvent2->id,
            'name' => 'VIP Chef\'s Table',
            'description' => 'Exclusive chef\'s table experience',
            'price' => 500.00,
            'quantity' => 20,
            'sold_count' => 8,
            'sale_start' => Carbon::now()->subDays(15),
            'sale_end' => Carbon::now()->addMonths(3),
            'is_active' => true,
        ]);

        // Event 5: Rejected Event
        $rejectedEvent = Event::create([
            'user_id' => $users[0]->id,
            'title' => 'Inappropriate Content Event',
            'description' => 'This event was rejected due to inappropriate content and violation of platform guidelines.',
            'category' => 'Other',
            'start_date' => Carbon::now()->addMonths(1)->setTime(15, 0),
            'end_date' => Carbon::now()->addMonths(1)->setTime(19, 0),
            'location' => 'TBD',
            'tags' => 'Cancelled',
            'status' => 'rejected',
            'rejection_reason' => 'Event does not meet our community guidelines and contains inappropriate content.',
            'rejected_at' => Carbon::now()->subDays(10),
        ]);

        // Seed Subscription Plans
        $this->seedSubscriptionPlans();

        // Seed Discounts
        $this->seedDiscounts();

        // Seed Ticket Limit Pricing
        $this->seedTicketLimitPricing();

        $this->command->info('Successfully seeded:');
        $this->command->info('- 2 Users (Organizers)');
        $this->command->info('- 5 Events with different statuses');
        $this->command->info('- Multiple ticket types with realistic data');
        $this->command->info('- Subscription plans');
        $this->command->info('- Discounts');
        $this->command->info('- Ticket limit pricing');
    }

    private function seedSubscriptionPlans(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SubscriptionPlan::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started with your first events',
                'price' => 0,
                'currency' => 'PHP',
                'events_per_month' => 1,
                'tickets_per_event' => 100,
                'advanced_layouts' => false,
                'advanced_analytics' => false,
                'features' => ['Basic analytics', 'Email support'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Great for small businesses and regular event organizers',
                'price' => 299,
                'currency' => 'PHP',
                'events_per_month' => 5,
                'tickets_per_event' => 500,
                'advanced_layouts' => true,
                'advanced_analytics' => true,
                'features' => ['Advanced analytics', 'Email support', 'Advanced layouts'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Ideal for professional event organizers and agencies',
                'price' => 799,
                'currency' => 'PHP',
                'events_per_month' => 15,
                'tickets_per_event' => 2000,
                'advanced_layouts' => true,
                'advanced_analytics' => true,
                'features' => ['Priority support', 'API access', 'Custom domain', 'White label options'],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Custom solutions for large organizations',
                'price' => 0,
                'currency' => 'PHP',
                'events_per_month' => null, // Unlimited
                'tickets_per_event' => null, // Unlimited
                'advanced_layouts' => true,
                'advanced_analytics' => true,
                'features' => ['Dedicated account manager', 'Custom integrations', 'Unlimited everything'],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }

    private function seedDiscounts(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Discount::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $discounts = [
            [
                'name' => 'Early Bird 20% Off',
                'code' => 'EARLY20',
                'type' => 'percentage',
                'value' => 20,
                'currency' => 'PHP',
                'applicable_to' => 'subscription',
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
                'description' => 'Get 20% off on your first month subscription',
            ],
            [
                'name' => 'Student Discount',
                'code' => 'STUDENT50',
                'type' => 'percentage',
                'value' => 50,
                'currency' => 'PHP',
                'applicable_to' => 'subscription',
                'usage_limit' => 50,
                'per_user_limit' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
                'description' => 'Special 50% discount for students',
            ],
            [
                'name' => 'New User ₱100 Off',
                'code' => 'NEWUSER100',
                'type' => 'fixed_amount',
                'value' => 100,
                'currency' => 'PHP',
                'applicable_to' => 'all',
                'usage_limit' => 500,
                'per_user_limit' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
                'description' => '₱100 off for new users on any purchase',
            ],
            [
                'name' => 'Holiday Special',
                'code' => 'HOLIDAY30',
                'type' => 'percentage',
                'value' => 30,
                'currency' => 'PHP',
                'applicable_to' => 'subscription',
                'usage_limit' => 200,
                'per_user_limit' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
                'description' => '30% off for holiday season',
            ],
            [
                'name' => 'Bundle Discount',
                'code' => 'BUNDLE15',
                'type' => 'percentage',
                'value' => 15,
                'currency' => 'PHP',
                'applicable_to' => 'ticket_limit_purchase',
                'usage_limit' => null,
                'per_user_limit' => 5,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(12),
                'is_active' => true,
                'description' => '15% off when buying additional tickets',
            ],
        ];

        foreach ($discounts as $discount) {
            Discount::create($discount);
        }
    }

    private function seedTicketLimitPricing(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        TicketLimitPricing::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Paid event pricing plans
        $paidPricingPlans = [
            [
                'name' => 'Small Pack',
                'ticket_quantity' => 50,
                'price' => 299.00,
                'currency' => 'PHP',
                'description' => 'Perfect for small events',
                'is_active' => true,
                'sort_order' => 1,
                'type' => 'paid',
            ],
            [
                'name' => 'Standard Pack',
                'ticket_quantity' => 100,
                'price' => 499.00,
                'currency' => 'PHP',
                'description' => 'Great value for regular events',
                'is_active' => true,
                'sort_order' => 2,
                'type' => 'paid',
            ],
            [
                'name' => 'Large Pack',
                'ticket_quantity' => 250,
                'price' => 999.00,
                'currency' => 'PHP',
                'description' => 'Best for larger gatherings',
                'is_active' => true,
                'sort_order' => 3,
                'type' => 'paid',
            ],
            [
                'name' => 'Premium Pack',
                'ticket_quantity' => 500,
                'price' => 1799.00,
                'currency' => 'PHP',
                'description' => 'Premium option for major events',
                'is_active' => true,
                'sort_order' => 4,
                'type' => 'paid',
            ],
            [
                'name' => 'Enterprise Pack',
                'ticket_quantity' => 1000,
                'price' => 2999.00,
                'currency' => 'PHP',
                'description' => 'Maximum tickets for enterprise events',
                'is_active' => true,
                'sort_order' => 5,
                'type' => 'paid',
            ],
        ];

        // Free event pricing plans
        $freePricingPlans = [
            [
                'name' => 'Small Pack (Free)',
                'ticket_quantity' => 50,
                'price' => 249.00,
                'currency' => 'PHP',
                'description' => 'Perfect for small free events',
                'is_active' => true,
                'sort_order' => 1,
                'type' => 'free',
            ],
            [
                'name' => 'Standard Pack (Free)',
                'ticket_quantity' => 100,
                'price' => 449.00,
                'currency' => 'PHP',
                'description' => 'Great value for free events',
                'is_active' => true,
                'sort_order' => 2,
                'type' => 'free',
            ],
            [
                'name' => 'Large Pack (Free)',
                'ticket_quantity' => 200,
                'price' => 799.00,
                'currency' => 'PHP',
                'description' => 'Best for larger free gatherings',
                'is_active' => true,
                'sort_order' => 3,
                'type' => 'free',
            ],
        ];

        foreach ($paidPricingPlans as $plan) {
            TicketLimitPricing::create($plan);
        }

        foreach ($freePricingPlans as $plan) {
            TicketLimitPricing::create($plan);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for getting started - publish 1 event per month',
                'price' => 0.00,
                        'currency' => 'PHP',
                'events_per_month' => 1,
                'tickets_per_event' => 100,
                'max_event_duration_days' => 7,
                'advanced_layouts' => false,
                'advanced_analytics' => false,
                'api_access' => false,
                'white_label' => false,
                'features' => [
                    'Basic layout templates',
                    'Simple analytics',
                    'Email support',
                    'Mobile responsive',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'description' => 'Great for small events - publish up to 5 events per month',
                        'price' => 399.00,
                        'currency' => 'PHP',
                'events_per_month' => 5,
                'tickets_per_event' => 500,
                'max_event_duration_days' => 30,
                'advanced_layouts' => false,
                'advanced_analytics' => false,
                'api_access' => false,
                'white_label' => false,
                'features' => [
                    'Standard layout templates',
                    'Basic analytics',
                    'Email support',
                    'Mobile responsive',
                    'Event reminders',
                    'Basic customization',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Intermediate',
                'slug' => 'intermediate',
                'description' => 'Advanced features for growing organizers - publish up to 10 events per month',
                'price' => 750.00,
                        'currency' => 'PHP',
                'events_per_month' => 10,
                'tickets_per_event' => 1500,
                'max_event_duration_days' => 60,
                'advanced_layouts' => true,
                'advanced_analytics' => true,
                'api_access' => true,
                'white_label' => true,
                'features' => [
                    'All advanced features',
                    'Custom branding',
                    'API access',
                    'White label solution',
                    'Dedicated support',
                    'Custom integrations',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Advanced features - publish up to 20 events per month',
                        'price' => 1000.00,
                        'currency' => 'PHP',
                'events_per_month' => 20,
                'tickets_per_event' => 2000,
                'max_event_duration_days' => 90,
                'advanced_layouts' => true,
                'advanced_analytics' => true,
                'api_access' => false,
                'white_label' => false,
                'features' => [
                    'Advanced layout templates',
                    'Advanced analytics',
                    'Mobile responsive',
                    'Event reminders',
                    'Advanced customization',
                    'Social media integration',
                    'QR code generation',
                    'Export capabilities',
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}

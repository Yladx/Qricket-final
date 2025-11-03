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
                        'price' => 299.00,
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
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Advanced features - publish up to 20 events per month',
                        'price' => 799.00,
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
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited features - publish unlimited events per month',
                'price' => 0.00, // Custom pricing
                        'currency' => 'PHP',
                'events_per_month' => null, // Unlimited
                'tickets_per_event' => null, // Unlimited
                'max_event_duration_days' => null, // Unlimited
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
                    'Unlimited events',
                    'Unlimited tickets',
                    'SSO integration',
                    'Advanced reporting',
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

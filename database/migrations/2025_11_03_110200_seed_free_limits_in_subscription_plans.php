<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Apply defaults for common plans by slug
        $plans = [
            'free' => ['free_events_count' => 1, 'free_event_ticket_limit' => 100],
            'basic' => ['free_events_count' => 3, 'free_event_ticket_limit' => 200],
            'pro' => ['free_events_count' => 8, 'free_event_ticket_limit' => 300],
        ];

        foreach ($plans as $slug => $vals) {
            DB::table('subscription_plans')
                ->where('slug', $slug)
                ->update(array_merge($vals, [
                    'updated_at' => now(),
                ]));
        }
    }

    public function down(): void
    {
        // Revert to zeros (safe default)
        DB::table('subscription_plans')->update([
            'free_events_count' => 0,
            'free_event_ticket_limit' => 0,
            'updated_at' => now(),
        ]);
    }
};



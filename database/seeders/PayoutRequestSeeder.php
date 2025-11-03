<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PayoutRequest;
use App\Models\Event;
use App\Models\User;

class PayoutRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some events that have ended
        $events = Event::where('end_date', '<', now())
            ->where('status', 'published')
            ->with('user')
            ->take(3)
            ->get();

        if ($events->isEmpty()) {
            $this->command->info('No ended events found. Creating sample events first...');
            
            // Create sample events
            $organizer = User::first();
            if (!$organizer) {
                $organizer = User::create([
                    'name' => 'Sample Organizer',
                    'email' => 'organizer@example.com',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);
            }

            $events = collect();
            for ($i = 1; $i <= 3; $i++) {
                $event = Event::create([
                    'user_id' => $organizer->id,
                    'title' => "Sample Event $i",
                    'description' => "This is a sample event for testing payout requests.",
                    'start_date' => now()->subDays(10),
                    'end_date' => now()->subDays(1),
                    'location' => 'Sample Location',
                    'category' => 'Technology',
                    'status' => 'published',
                ]);
                $events->push($event);
            }
        }

        foreach ($events as $event) {
            // Create payout requests with different statuses
            $statuses = ['pending', 'approved', 'rejected', 'paid'];
            
            foreach ($statuses as $status) {
                PayoutRequest::create([
                    'event_id' => $event->id,
                    'organizer_id' => $event->user_id,
                    'total_earnings' => rand(1000, 10000),
                    'qricket_fee' => rand(50, 500),
                    'net_amount' => rand(950, 9500),
                    'status' => $status,
                    'notes' => "Sample payout request for {$event->title}",
                    'requested_at' => now()->subDays(rand(1, 7)),
                    'processed_at' => $status === 'paid' ? now()->subDays(rand(1, 3)) : null,
                ]);
            }
        }

        $this->command->info('Created ' . ($events->count() * 4) . ' sample payout requests.');
    }
}
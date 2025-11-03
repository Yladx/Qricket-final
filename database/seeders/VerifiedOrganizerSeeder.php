<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class VerifiedOrganizerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find existing users and verify them as organizers
        $users = User::where('email', 'like', '%@example.com')->get();
        
        foreach ($users as $user) {
            $user->verifyAsOrganizer();
        }
        
        // Create or update a specific verified organizer for testing
        $verifiedOrganizer = User::firstOrCreate(
            ['email' => 'organizer@qricket.com'],
            [
                'first_name' => 'Verified',
                'last_name' => 'Organizer',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        
        $verifiedOrganizer->verifyAsOrganizer();
        
        $this->command->info('Verified organizers created successfully!');
    }
}

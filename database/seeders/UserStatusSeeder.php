<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test users with different statuses...');

        // Create an active user
        $activeUser = User::firstOrCreate(
            ['email' => 'active@qricket.test'],
            [
                'first_name' => 'Active',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $this->command->info("✓ Created active user: active@qricket.test (password: password)");

        // Create a banned user
        $bannedUser = User::firstOrCreate(
            ['email' => 'banned@qricket.test'],
            [
                'first_name' => 'Banned',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'status' => 'banned',
                'banned_at' => now(),
                'banned_reason' => 'Test ban reason - abusive behavior',
                'email_verified_at' => now(),
            ]
        );
        $this->command->info("✓ Created banned user: banned@qricket.test (password: password)");

        // Create a deleted user
        $deletedUser = User::firstOrCreate(
            ['email' => 'deleted@qricket.test'],
            [
                'first_name' => 'Deleted',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'status' => 'deleted',
                'deleted_at' => now(),
                'email_verified_at' => now(),
            ]
        );
        $this->command->info("✓ Created deleted user: deleted@qricket.test (password: password)");

        // Create an organizer with active status
        $activeOrganizer = User::firstOrCreate(
            ['email' => 'organizer@qricket.test'],
            [
                'first_name' => 'Event',
                'last_name' => 'Organizer',
                'password' => Hash::make('password'),
                'status' => 'active',
                'organizer' => true,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info("✓ Created active organizer: organizer@qricket.test (password: password)");

        // Create an organizer with banned status
        $bannedOrganizer = User::firstOrCreate(
            ['email' => 'bannedorganizer@qricket.test'],
            [
                'first_name' => 'Banned',
                'last_name' => 'Organizer',
                'password' => Hash::make('password'),
                'status' => 'banned',
                'banned_at' => now(),
                'banned_reason' => 'Test ban reason - policy violation',
                'organizer' => true,
                'email_verified_at' => now(),
            ]
        );
        $this->command->info("✓ Created banned organizer: bannedorganizer@qricket.test (password: password)");

        $this->command->info('');
        $this->command->info('Test users created successfully!');
        $this->command->info('');
        $this->command->info('You can test by:');
        $this->command->info('1. Try logging in with active@qricket.test - should work');
        $this->command->info('2. Try logging in with banned@qricket.test - should be blocked');
        $this->command->info('3. Try logging in with deleted@qricket.test - should be blocked');
        $this->command->info('4. Try logging in with organizer@qricket.test - should work');
        $this->command->info('5. Try logging in with bannedorganizer@qricket.test - should be blocked');
    }
}


<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (organizer = true, verified_organizer = now)
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@qricket.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => true,
            'verified_organizer' => now(),
        ]);

        // Create organizer users
        User::create([
            'first_name' => 'John',
            'last_name' => 'Organizer',
            'email' => 'organizer@qricket.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => true,
            'verified_organizer' => now(),
        ]);

        User::create([
            'first_name' => 'Jane',
            'last_name' => 'Event Manager',
            'email' => 'jane@qricket.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => true,
            'verified_organizer' => now(),
        ]);

        User::create([
            'first_name' => 'Mike',
            'last_name' => 'Conference Host',
            'email' => 'mike@qricket.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => true,
            'verified_organizer' => now(),
        ]);

        // Create regular users
        User::create([
            'first_name' => 'Alice',
            'last_name' => 'Attendee',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => false,
        ]);

        User::create([
            'first_name' => 'Bob',
            'last_name' => 'Participant',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => false,
        ]);

        User::create([
            'first_name' => 'Carol',
            'last_name' => 'Guest',
            'email' => 'carol@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => false,
        ]);

        User::create([
            'first_name' => 'David',
            'last_name' => 'Visitor',
            'email' => 'david@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => false,
        ]);

        // Create staff users for testing
        User::create([
            'first_name' => 'Sarah',
            'last_name' => 'Staff',
            'email' => 'sarah@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => false,
        ]);

        User::create([
            'first_name' => 'Tom',
            'last_name' => 'Volunteer',
            'email' => 'tom@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => false,
        ]);

        User::create([
            'first_name' => 'Emma',
            'last_name' => 'Coordinator',
            'email' => 'emma@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => false,
        ]);

        User::create([
            'first_name' => 'Lisa',
            'last_name' => 'Manager',
            'email' => 'lisa@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'organizer' => false,
        ]);

        $this->command->info('Users seeded successfully!');
        $this->command->info('Admin: admin@qricket.com / password');
        $this->command->info('Organizers: organizer@qricket.com, jane@qricket.com, mike@qricket.com / password');
        $this->command->info('Users: alice@example.com, bob@example.com, carol@example.com, david@example.com / password');
        $this->command->info('Staff: sarah@example.com, tom@example.com, emma@example.com, lisa@example.com / password');
    }
}

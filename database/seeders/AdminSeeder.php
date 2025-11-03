<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin if it doesn't exist
        Admin::firstOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('admin123'),
                'pin' => '123456',
                'role' => 'superadmin',
                'failed_login_attempts' => 0,
            ]
        );

        echo "Admin seeded successfully!\n";
        echo "Username: admin\n";
        echo "Password: admin123\n";
        echo "PIN: 123456\n";
    }
}
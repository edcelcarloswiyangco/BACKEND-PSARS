<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First account
        User::updateOrCreate(
            ['email' => 'edcelcarloswiyangco6@gmail.com'],
            [
                'name' => 'edcel',
                'full_name' => 'edcel carlos wiyangco',
                'email_verified_at' => now(),
                'status' => 'active',
                'password' => Hash::make('Darkpekka2005@'),
                'contact_number' => '09615691997',
                'address' => 'cca',
            ]
        );

        // Second account
        User::updateOrCreate(
            ['email' => 'edcelcarloswiyangco@gmail.com'],
            [
                'name' => 'testuser',
                'full_name' => 'Test User',
                'email_verified_at' => now(),
                'status' => 'active',
                'password' => Hash::make('Darkpekka2005@'),
                'contact_number' => '09123456789',
                'address' => 'Angeles City',
            ]
        );
    }
}
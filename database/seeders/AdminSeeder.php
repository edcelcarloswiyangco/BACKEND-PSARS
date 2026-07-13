<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin account
        Admin::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin1234@')),
                'role' => 'admin',
            ]
        );

        // Create developer account
        Admin::query()->updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'password' => Hash::make('admin1234'),
                'role' => 'developer',
            ]
        );
    }
}
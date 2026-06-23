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
        User::updateOrCreate(
            ['email' => 'e@e'],
            [
                'name' => 'edcel',
                'full_name' => 'edcel',
                'password' => Hash::make('11111111'),
                'contact_number' => '09615691997',
                'address' => 'cca',
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'driver@vanservice.com'],
            [
                'name'           => 'Default Driver',
                'position'       => 'Van Driver',
                'password'       => Hash::make('driver123'),
                'role'           => 'driver',
                'account_status' => 'approved',
            ]
        );
    }
}

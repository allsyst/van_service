<?php
namespace Database\Seeders;
use App\Models\User;
use App\Models\Van;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin
        User::updateOrCreate(
            ['email' => 'admin@vanservice.com'],
            [
                'name' => 'Admin',
                'email' => 'admin@vanservice.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'account_status' => 'approved',
            ]
        );

        // Default driver
        $driver = User::updateOrCreate(
            ['email' => 'driver@vanservice.com'],
            [
                'name' => 'Juan Driver',
                'email' => 'driver@vanservice.com',
                'password' => Hash::make('driver123'),
                'role' => 'driver',
                'account_status' => 'approved',
            ]
        );

        // Default vans
        Van::updateOrCreate(
            ['van_plate' => 'ABC-1234'],
            [
                'van_model'     => 'Toyota HiAce (White)',
                'van_plate'     => 'ABC-1234',
                'fuel_capacity' => 70,
                'km_per_liter'  => 12,
                'total_seats'   => 15,
                'driver_id'     => $driver->id,
            ]
        );

        Van::updateOrCreate(
            ['van_plate' => 'XYZ-5678'],
            [
                'van_model'     => 'Nissan Urvan (Blue)',
                'van_plate'     => 'XYZ-5678',
                'fuel_capacity' => 60,
                'km_per_liter'  => 10,
                'total_seats'   => 12,
                'driver_id'     => null,
            ]
        );
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make van_model and booking_time nullable for trip-request-based bookings
        DB::statement('ALTER TABLE bookings MODIFY van_model VARCHAR(255) NULL');
        DB::statement('ALTER TABLE bookings MODIFY booking_time TIME NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings MODIFY van_model VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE bookings MODIFY booking_time TIME NOT NULL');
    }
};

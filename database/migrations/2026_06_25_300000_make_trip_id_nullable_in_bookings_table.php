<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal
        DB::statement('ALTER TABLE bookings MODIFY trip_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE bookings MODIFY trip_id BIGINT UNSIGNED NOT NULL');
    }
};

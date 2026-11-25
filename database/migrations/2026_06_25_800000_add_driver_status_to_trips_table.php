<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            // 'pending_driver' = waiting for driver to confirm/cancel
            // 'driver_confirmed' = driver confirmed availability
            // 'driver_cancelled' = driver cancelled (van not available)
            $table->string('driver_status')->default('driver_confirmed')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('driver_status');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('pickup_location')->nullable();
            $table->string('dropoff_location')->nullable();
            $table->foreignId('van_id')->nullable()->constrained('vans')->onDelete('set null');
            $table->string('van_model');
            $table->date('trip_date');
            $table->time('departure_time');
            $table->integer('total_seats')->default(12);
            $table->decimal('fuel_capacity', 8, 2)->default(0);
            $table->decimal('km_per_liter', 8, 2)->default(0);
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->decimal('estimated_fuel_needed', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};

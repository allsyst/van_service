<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_id')->constrained('vans')->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->date('fuel_date');
            $table->decimal('liters', 10, 2);
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->decimal('odometer_km', 12, 2)->nullable();
            $table->string('fuel_station')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['van_id', 'fuel_date']);
            $table->index('fuel_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_records');
    }
};

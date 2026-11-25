<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('van_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed', 8, 2)->nullable(); // km/h
            $table->decimal('heading', 5, 2)->nullable(); // degrees
            $table->boolean('is_online')->default(true);
            $table->string('driver_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('van_locations');
    }
};

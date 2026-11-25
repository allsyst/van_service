<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'dropoff_latitude')) {
                $table->decimal('dropoff_latitude', 10, 7)->nullable()->after('dropoff_location');
            }
            if (!Schema::hasColumn('trips', 'dropoff_longitude')) {
                $table->decimal('dropoff_longitude', 10, 7)->nullable()->after('dropoff_latitude');
            }
            if (!Schema::hasColumn('trips', 'arrival_geofence_radius_meters')) {
                $table->unsignedSmallInteger('arrival_geofence_radius_meters')->default(150)->after('dropoff_longitude');
            }
            if (!Schema::hasColumn('trips', 'arrived_at')) {
                $table->timestamp('arrived_at')->nullable()->after('arrival_geofence_radius_meters');
            }
            if (!Schema::hasColumn('trips', 'arrival_notified_at')) {
                $table->timestamp('arrival_notified_at')->nullable()->after('arrived_at');
            }
        });

        Schema::table('trip_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('trip_requests', 'destination_latitude')) {
                $table->decimal('destination_latitude', 10, 7)->nullable()->after('destination');
            }
            if (!Schema::hasColumn('trip_requests', 'destination_longitude')) {
                $table->decimal('destination_longitude', 10, 7)->nullable()->after('destination_latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $columns = ['dropoff_latitude', 'dropoff_longitude', 'arrival_geofence_radius_meters', 'arrived_at', 'arrival_notified_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('trips', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('trip_requests', function (Blueprint $table) {
            foreach (['destination_latitude', 'destination_longitude'] as $column) {
                if (Schema::hasColumn('trip_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

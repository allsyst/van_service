<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_name', 'pickup_location', 'dropoff_location', 'dropoff_latitude', 'dropoff_longitude',
        'arrival_geofence_radius_meters', 'arrived_at', 'arrival_notified_at', 'van_id', 'van_model',
        'trip_date', 'departure_time', 'departed_at', 'departure_notified_at', 'delay_notified_at', 'total_seats', 'fuel_capacity',
        'km_per_liter', 'distance_km', 'estimated_fuel_needed', 'eta', 'status', 'driver_status',
        'trip_request_id', 'driver_cancel_reason'
    ];

    protected $casts = [
        'trip_date' => 'date',
        'dropoff_latitude' => 'float',
        'dropoff_longitude' => 'float',
        'arrived_at' => 'datetime',
        'arrival_notified_at' => 'datetime',
        'departed_at' => 'datetime',
        'departure_notified_at' => 'datetime',
        'delay_notified_at' => 'datetime',
    ];

    public function van()
    {
        return $this->belongsTo(Van::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function fuelRecords()
    {
        return $this->hasMany(FuelRecord::class);
    }

    public function availableSeats()
    {
        $booked = $this->bookings()->whereIn('status', ['Pending', 'Confirmed'])->sum('seats_requested');
        return $this->total_seats - $booked;
    }
}

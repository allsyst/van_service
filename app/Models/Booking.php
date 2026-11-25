<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'trip_id', 'trip_request_id', 'van_model', 'pickup_location', 'dropoff_location',
        'dropoff_lat', 'dropoff_lng', 'booking_date', 'booking_time',
        'seats_requested', 'purpose', 'status', 'is_read'
    ];

    protected $casts = ['booking_date' => 'date', 'is_read' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripRequest()
    {
        return $this->belongsTo(\App\Models\TripRequest::class, 'trip_request_id');
    }
}

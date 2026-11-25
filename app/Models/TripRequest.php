<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'van_id',
        'request_no',
        'date_of_travel',
        'purpose',
        'destination',
        'destination_latitude',
        'destination_longitude',
        'no_of_passengers',
        'passenger_names',
        'status',
        'admin_remarks',
    ];

    protected $casts = [
        'passenger_names' => 'array',
        'date_of_travel'  => 'date',
        'destination_latitude' => 'float',
        'destination_longitude' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trip()
    {
        return $this->hasOne(\App\Models\Trip::class, 'trip_request_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'trip_request_id');
    }

    /**
     * Generate a unique request number like RQ-2026-001
     */
    public static function generateRequestNo(): string
    {
        $year  = now()->year;
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'RQ-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Van extends Model
{
    use HasFactory;

    protected $fillable = ['van_model', 'van_plate', 'fuel_capacity', 'km_per_liter', 'total_seats', 'driver_id', 'is_available'];

    protected $casts = ['is_available' => 'boolean'];

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function fuelRecords()
    {
        return $this->hasMany(FuelRecord::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function latestLocation()
    {
        return $this->hasOne(VanLocation::class)->latestOfMany();
    }

    public function locations()
    {
        return $this->hasMany(VanLocation::class);
    }
}

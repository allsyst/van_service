<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'van_id',
        'trip_id',
        'fuel_date',
        'liters',
        'total_cost',
        'odometer_km',
        'fuel_station',
        'notes',
    ];

    protected $casts = [
        'fuel_date' => 'date',
        'liters' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'odometer_km' => 'decimal:2',
    ];

    public function van()
    {
        return $this->belongsTo(Van::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VanLocation extends Model
{
    protected $fillable = [
        'van_id',
        'latitude',
        'longitude',
        'accuracy',
        'speed',
        'heading',
        'is_online',
        'driver_name',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
        'speed' => 'float',
        'heading' => 'float',
        'is_online' => 'boolean',
    ];

    public function van()
    {
        return $this->belongsTo(Van::class);
    }
}

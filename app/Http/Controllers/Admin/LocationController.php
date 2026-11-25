<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Van;
use App\Models\VanLocation;

class LocationController extends Controller
{
    /**
     * Show the administrator's live driver-location map.
     */
    public function liveMap()
    {
        $vans = Van::with('latestLocation')->get();

        return view('admin.live-map', compact('vans'));
    }

    /**
     * Return the latest GPS point for each van. A point is live only while
     * the driver app is sharing and it has sent an update in the last 45 s.
     */
    public function getLocations()
    {
        $locations = VanLocation::with(['van.driver'])
            ->orderByDesc('updated_at')
            ->get()
            ->unique('van_id')
            ->values();

        return response()->json($locations->map(function (VanLocation $location) {
            $isOnline = (bool) $location->is_online
                && $location->updated_at
                && $location->updated_at->greaterThan(now()->subSeconds(45));

            return [
                'van_id' => $location->van_id,
                'van_plate' => $location->van?->van_plate ?? 'Unknown',
                'van_model' => $location->van?->van_model ?? '',
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'speed' => $location->speed,
                'heading' => $location->heading,
                'accuracy' => $location->accuracy,
                'is_online' => $isOnline,
                'driver_name' => $location->van?->driver?->name ?? $location->driver_name,
                'updated_at' => $location->updated_at?->diffForHumans(),
                'updated_at_iso' => $location->updated_at?->toIso8601String(),
            ];
        }));
    }
}

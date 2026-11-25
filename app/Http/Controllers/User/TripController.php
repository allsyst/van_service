<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Van;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function create()
    {
        $vans = Van::all();
        return view('user.trips.create', compact('vans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'van_id'         => 'required|exists:vans,id',
            'trip_date'      => 'required|date|after_or_equal:today',
            'departure_time' => 'required',
        ]);

        $van = Van::findOrFail($request->van_id);
        $origin = $request->pickup_location ?? '';
        $dest   = $request->dropoff_location ?? '';
        $route_name = $origin && $dest
            ? strtoupper($origin) . ' TO ' . strtoupper($dest)
            : 'Route';

        Trip::create([
            'route_name'             => $route_name,
            'pickup_location'        => $origin,
            'dropoff_location'       => $dest,
            'van_id'                 => $van->id,
            'van_model'              => $van->van_model,
            'trip_date'              => $request->trip_date,
            'departure_time'         => $request->departure_time,
            'total_seats'            => $van->total_seats,
            'fuel_capacity'          => $van->fuel_capacity,
            'km_per_liter'           => $van->km_per_liter,
            'distance_km'            => $request->distance_km ?? 0,
            'estimated_fuel_needed'  => $request->estimated_fuel_needed ?? 0,
            'eta'                    => $request->eta ?? null,
        ]);

        return redirect()->route('user.dashboard')->with('success', 'Trip added successfully! It is now available for booking.');
    }
}

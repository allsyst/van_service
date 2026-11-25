<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Van;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(private VehicleAvailabilityService $vehicleAvailability)
    {
    }

    public function index() {
        $trips = Trip::with('van')->latest()->get();
        return view('admin.trips.index', compact('trips'));
    }
    public function create() {
        $vans = Van::all();
        return view('admin.trips.create', compact('vans'));
    }
    public function store(Request $request) {
        $request->validate([
            'van_id'=>'required', 'trip_date'=>'required|date', 'departure_time'=>'required', 'total_seats'=>'required|integer',
            'dropoff_latitude'=>'nullable|numeric|between:-90,90', 'dropoff_longitude'=>'nullable|numeric|between:-180,180',
            'arrival_geofence_radius_meters'=>'nullable|integer|min:50|max:1000',
        ]);
        $van = Van::findOrFail($request->van_id);
        $conflict = $this->vehicleAvailability->findScheduledConflict(
            $van->id,
            $request->trip_date,
            $request->departure_time,
            $request->eta
        );
        if ($conflict) {
            return back()->withInput()->with('error', $this->conflictMessage($van, $conflict));
        }
        $origin = $request->pickup_location ?? '';
        $dest = $request->dropoff_location ?? '';
        $route_name = $origin && $dest ? strtoupper($origin) . ' TO ' . strtoupper($dest) : 'Route';
        Trip::create([
            'route_name' => $route_name,
            'pickup_location' => $origin,
            'dropoff_location' => $dest,
            'dropoff_latitude' => $request->dropoff_latitude,
            'dropoff_longitude' => $request->dropoff_longitude,
            'arrival_geofence_radius_meters' => $request->arrival_geofence_radius_meters ?? 150,
            'van_id' => $van->id,
            'van_model' => $van->van_model,
            'trip_date' => $request->trip_date,
            'departure_time' => $request->departure_time,
            'total_seats' => $van->total_seats,
            'fuel_capacity' => $van->fuel_capacity,
            'km_per_liter' => $van->km_per_liter,
            'distance_km' => $request->distance_km ?? 0,
            'estimated_fuel_needed' => $request->estimated_fuel_needed ?? 0,
            'eta' => $request->eta ?? null,
        ]);
        return redirect()->route('admin.trips.index')->with('success','Trip added!');
    }
    public function edit(Trip $trip) {
        $vans = Van::all();
        return view('admin.trips.edit', compact('trip','vans'));
    }
    public function update(Request $request, Trip $trip) {
        $request->validate([
            'van_id'=>'required', 'trip_date'=>'required|date', 'departure_time'=>'required',
            'dropoff_latitude'=>'nullable|numeric|between:-90,90', 'dropoff_longitude'=>'nullable|numeric|between:-180,180',
            'arrival_geofence_radius_meters'=>'nullable|integer|min:50|max:1000',
        ]);
        $van = Van::findOrFail($request->van_id);
        $conflict = $this->vehicleAvailability->findScheduledConflict(
            $van->id,
            $request->trip_date,
            $request->departure_time,
            $request->eta,
            $trip->id
        );
        if ($conflict) {
            return back()->withInput()->with('error', $this->conflictMessage($van, $conflict));
        }
        $origin = $request->pickup_location ?? $trip->pickup_location;
        $dest = $request->dropoff_location ?? $trip->dropoff_location;
        $trip->update([
            'route_name' => strtoupper($origin) . ' TO ' . strtoupper($dest),
            'pickup_location' => $origin,
            'dropoff_location' => $dest,
            'dropoff_latitude' => $request->dropoff_latitude,
            'dropoff_longitude' => $request->dropoff_longitude,
            'arrival_geofence_radius_meters' => $request->arrival_geofence_radius_meters ?? $trip->arrival_geofence_radius_meters ?? 150,
            'van_id' => $van->id,
            'van_model' => $van->van_model,
            'trip_date' => $request->trip_date,
            'departure_time' => $request->departure_time,
            'total_seats' => $van->total_seats,
            'fuel_capacity' => $van->fuel_capacity,
            'km_per_liter' => $van->km_per_liter,
            'distance_km' => $request->distance_km ?? $trip->distance_km,
            'estimated_fuel_needed' => $request->estimated_fuel_needed ?? $trip->estimated_fuel_needed,
            'eta' => $request->eta ?? $trip->eta,
        ]);
        return redirect()->route('admin.trips.index')->with('success','Trip updated!');
    }
    public function destroy(Trip $trip) {
        $trip->delete();
        return redirect()->route('admin.trips.index')->with('success','Trip deleted!');
    }

    private function conflictMessage(Van $van, Trip $conflict): string
    {
        $date = $conflict->trip_date->format('M d, Y');
        $time = \Carbon\Carbon::parse($conflict->departure_time)->format('h:i A');
        return "{$van->van_model} ({$van->van_plate}) is already booked for {$conflict->route_name} on {$date} at {$time}. Choose a different vehicle or schedule.";
    }
}

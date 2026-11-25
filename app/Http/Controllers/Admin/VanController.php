<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Van;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VanController extends Controller
{
    public function index() {
        $vans = Van::latest()->get();
        return view('admin.vans.index', compact('vans'));
    }
    public function create() { return view('admin.vans.create'); }
    public function store(Request $request) {
        $request->validate(['van_model'=>'required','fuel_capacity'=>'required|numeric','km_per_liter'=>'required|numeric','total_seats'=>'required|integer']);
        Van::create($request->all());
        return redirect()->route('admin.vans.index')->with('success','Van added!');
    }
    public function edit(Van $van) { return view('admin.vans.edit', compact('van')); }
    public function update(Request $request, Van $van) {
        $request->validate(['van_model'=>'required','fuel_capacity'=>'required|numeric','km_per_liter'=>'required|numeric','total_seats'=>'required|integer']);
        $van->update($request->all());
        return redirect()->route('admin.vans.index')->with('success','Van updated!');
    }
    public function destroy(Van $van) {
        $van->delete();
        return redirect()->route('admin.vans.index')->with('success','Van deleted!');
    }
    public function getVanData(Van $van) {
        return response()->json($van);
    }

    // Toggle van availability
    public function toggleAvailability(Van $van) {
        $newValue = $van->is_available ? 0 : 1;
        $van->is_available = $newValue;
        $van->save();
        return response()->json([
            'is_available' => (bool) $newValue,
            'message'      => $newValue ? 'Van marked as Available' : 'Van marked as Not Available',
        ]);
    }

    // Return all trips for a van (past, present, future)
    public function trips(Van $van) {
        $today = Carbon::today();
        $trips = Trip::where('van_id', $van->id)
            ->orderBy('trip_date', 'desc')
            ->orderBy('departure_time', 'desc')
            ->get()
            ->map(function($trip) use ($today) {
                $tripDate = Carbon::parse($trip->trip_date);
                if ($tripDate->isToday()) {
                    $label = 'today';
                } elseif ($tripDate->isFuture()) {
                    $label = 'future';
                } else {
                    $label = 'past';
                }
                $bookedSeats = $trip->bookings()->whereIn('status', ['Pending', 'Confirmed'])->sum('seats_requested');
                return [
                    'id'             => $trip->id,
                    'route_name'     => $trip->route_name,
                    'trip_date'      => $tripDate->format('F d, Y'),
                    'departure_time' => $trip->departure_time ?? '—',
                    'eta'            => $trip->eta ?? '—',
                    'total_seats'    => $trip->total_seats,
                    'booked_seats'   => $bookedSeats,
                    'available_seats'=> $trip->total_seats - $bookedSeats,
                    'label'          => $label,
                ];
            });

        return response()->json([
            'van'   => $van->van_model . ' (' . ($van->van_plate ?? 'No Plate') . ')',
            'trips' => $trips,
        ]);
    }

    // Return all passengers for a specific trip
    public function tripPassengers(Trip $trip) {
        $bookings = Booking::where('trip_id', $trip->id)
            ->whereIn('status', ['Pending', 'Confirmed', 'Completed'])
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
        return view('admin.trips.passengers', compact('trip', 'bookings'));
    }

    // Return today's passengers for a specific van (legacy)
    public function passengers(Van $van) {
        $today = Carbon::today()->toDateString();
        $trips = Trip::where('van_id', $van->id)
            ->whereDate('trip_date', $today)
            ->with(['bookings' => function($q) {
                $q->whereIn('status', ['Pending', 'Confirmed'])->with('user');
            }])
            ->get();

        $passengers = [];
        foreach ($trips as $trip) {
            foreach ($trip->bookings as $booking) {
                $passengers[] = [
                    'name'         => $booking->user->name ?? 'N/A',
                    'position'     => $booking->user->position ?? '—',
                    'email'        => $booking->user->email ?? 'N/A',
                    'route'        => $trip->route_name,
                    'departure'    => $trip->departure_time,
                    'eta'          => $trip->eta ?? '—',
                    'dropoff'      => $booking->dropoff_location ?? '—',
                    'seats'        => $booking->seats_requested,
                    'status'       => $booking->status,
                    'booking_time' => $booking->booking_time,
                ];
            }
        }

        return response()->json([
            'van'        => $van->van_model . ' (' . $van->van_plate . ')',
            'date'       => Carbon::today()->format('F d, Y'),
            'passengers' => $passengers,
        ]);
    }
}

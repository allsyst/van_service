<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Van;
use App\Services\TripNotificationService;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private TripNotificationService $tripNotificationService,
        private VehicleAvailabilityService $vehicleAvailability
    )
    {
    }

    /**
     * Show the driver dashboard with assigned trips.
     */
    public function index()
    {
        $driver = Auth::user();

        // Get the van assigned to this driver
        $van = Van::where('driver_id', $driver->id)->first();

        $todayTrips = collect();
        $upcomingTrips = collect();
        $pastTrips = collect();

        if ($van) {
            $today = now()->toDateString();

            $todayTrips = Trip::where('van_id', $van->id)
                ->whereDate('trip_date', $today)
                ->withCount(['bookings as confirmed_count' => function ($q) {
                    $q->whereIn('status', ['Confirmed', 'Pending']);
                }])
                ->orderBy('departure_time')
                ->get();

            $upcomingTrips = Trip::where('van_id', $van->id)
                ->whereDate('trip_date', '>', $today)
                ->withCount(['bookings as confirmed_count' => function ($q) {
                    $q->whereIn('status', ['Confirmed', 'Pending']);
                }])
                ->orderBy('trip_date')
                ->orderBy('departure_time')
                ->get();

            $pastTrips = Trip::where('van_id', $van->id)
                ->whereDate('trip_date', '<', $today)
                ->withCount(['bookings as confirmed_count' => function ($q) {
                    $q->whereIn('status', ['Confirmed', 'Pending']);
                }])
                ->orderBy('trip_date', 'desc')
                ->limit(10)
                ->get();
        }

        // Stats
        $totalCompleted = 0;
        $totalCancelled = 0;
        $totalPassengers = 0;

        if ($van) {
            $tripIds = Trip::where('van_id', $van->id)->pluck('id');
            $totalCompleted = Booking::whereIn('trip_id', $tripIds)->where('status', 'Completed')->count();
            $totalCancelled = Booking::whereIn('trip_id', $tripIds)->where('status', 'Cancelled')->count();
            $totalPassengers = Booking::whereIn('trip_id', $tripIds)->whereIn('status', ['Confirmed', 'Completed'])->count();
        }

        return view('driver.dashboard', compact(
            'driver', 'van', 'todayTrips', 'upcomingTrips', 'pastTrips',
            'totalCompleted', 'totalCancelled', 'totalPassengers'
        ));
    }

    /**
     * Get passengers for a specific trip (AJAX).
     */
    public function tripPassengers($tripId)
    {
        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['error' => 'No van assigned'], 403);
        }

        $trip = Trip::where('id', $tripId)->where('van_id', $van->id)->firstOrFail();

        $bookings = Booking::with('user')
            ->where('trip_id', $trip->id)
            ->orderBy('status')
            ->orderBy('created_at')
            ->get()
            ->map(function ($b) {
                return [
                    'id'               => $b->id,
                    'passenger_name'   => $b->user->name ?? 'Unknown',
                    'passenger_email'  => $b->user->email ?? '',
                    'passenger_pos'    => $b->user->position ?? '',
                    'dropoff_location' => $b->dropoff_location ?? 'Not specified',
                    'seats'            => $b->seats_requested,
                    'status'           => $b->status,
                    'booking_date'     => $b->booking_date,
                    'booking_time'     => $b->booking_time,
                ];
            });

        return response()->json([
            'trip'     => [
                'id'             => $trip->id,
                'route_name'     => $trip->route_name,
                'trip_date'      => $trip->trip_date,
                'departure_time' => $trip->departure_time,
                'van_model'      => $trip->van_model,
            ],
            'bookings' => $bookings,
        ]);
    }

    /**
     * Update a booking status (Complete or Cancel).
     */
    public function updateBookingStatus(Request $request, $bookingId)
    {
        $request->validate([
            'status' => 'required|in:Completed,Cancelled,Confirmed',
        ]);

        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['error' => 'No van assigned to you.'], 403);
        }

        // Make sure this booking belongs to a trip of this driver's van
        $booking = Booking::with('trip')->findOrFail($bookingId);

        if ($booking->trip->van_id !== $van->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $booking->status = $request->status;
        $booking->save();

        return response()->json([
            'success' => true,
            'status'  => $booking->status,
            'message' => 'Booking marked as ' . $booking->status,
        ]);
    }

    /**
     * Mark ALL bookings on a trip as Completed.
     */
    public function completeTrip(Request $request, $tripId)
    {
        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['error' => 'No van assigned.'], 403);
        }

        $trip = Trip::where('id', $tripId)->where('van_id', $van->id)->firstOrFail();

        Booking::where('trip_id', $trip->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->update(['status' => 'Completed']);

        $trip->update(['status' => 'completed']);

        return response()->json(['success' => true, 'message' => 'All passengers marked as Completed.']);
    }

    /**
     * Let a confirmed driver record when the van physically begins the trip.
     */
    public function takeOff(Request $request, $tripId)
    {
        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['error' => 'No van assigned.'], 403);
        }

        $trip = Trip::where('id', $tripId)->where('van_id', $van->id)->firstOrFail();
        if ($trip->driver_status === 'pending_driver') {
            return response()->json(['error' => 'Confirm this trip before reporting takeoff.'], 422);
        }
        if (in_array($trip->status, ['cancelled', 'completed'])) {
            return response()->json(['error' => 'This trip is no longer active.'], 422);
        }

        $result = $this->tripNotificationService->markDeparted($trip, $driver);

        return response()->json([
            'success' => true,
            'already_departed' => !$result['newly_departed'],
            'message' => $result['newly_departed']
                ? 'Takeoff recorded. The administrator has been notified.'
                : 'This trip has already been marked as departed.',
        ]);
    }

    /**
     * Show the create trip form for the driver.
     */
    public function createTrip()
    {
        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return redirect()->route('driver.dashboard')->with('error', 'You need to be assigned a van before creating trips.');
        }

        return view('driver.create-trip', compact('driver', 'van'));
    }

    /**
     * Store a new trip created by the driver.
     */
    public function storeTrip(Request $request)
    {
        $request->validate([
            'route_name'     => 'required|string|max:255',
            'pickup_location' => 'required|string|max:255',
            'dropoff_location'=> 'required|string|max:255',
            'trip_date'      => 'required|date|after_or_equal:today',
            'departure_time' => 'required',
            'eta'            => 'nullable',
            'distance_km'    => 'nullable|numeric|min:0',
            'fuel_cost'      => 'nullable|numeric|min:0',
            'fuel_needed'    => 'nullable|numeric|min:0',
            'origin_lat'     => 'nullable|numeric',
            'origin_lng'     => 'nullable|numeric',
            'dest_lat'       => 'nullable|numeric',
            'dest_lng'       => 'nullable|numeric',
        ]);

        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return redirect()->route('driver.dashboard')->with('error', 'No van assigned to you.');
        }

        $conflict = $this->vehicleAvailability->findScheduledConflict(
            $van->id,
            $request->trip_date,
            $request->departure_time,
            $request->eta
        );
        if ($conflict) {
            $date = $conflict->trip_date->format('M d, Y');
            $time = \Carbon\Carbon::parse($conflict->departure_time)->format('h:i A');
            return back()->withInput()->with(
                'error',
                "Your assigned van is already booked for {$conflict->route_name} on {$date} at {$time}. Please choose another schedule."
            );
        }

        Trip::create([
            'van_id'                 => $van->id,
            'route_name'             => $request->route_name,
            'pickup_location'        => $request->pickup_location,
            'dropoff_location'       => $request->dropoff_location,
            'dropoff_latitude'       => $request->dest_lat,
            'dropoff_longitude'      => $request->dest_lng,
            'arrival_geofence_radius_meters' => 150,
            'trip_date'              => $request->trip_date,
            'departure_time'         => $request->departure_time,
            'eta'                    => $request->eta,
            'distance_km'            => $request->distance_km ?? 0,
            'estimated_fuel_needed'  => $request->fuel_needed ?? $request->fuel_cost ?? 0,
            'van_model'              => $van->van_model,
            'total_seats'            => $van->total_seats ?? 12,
            'fuel_capacity'          => $van->fuel_capacity ?? 0,
            'km_per_liter'           => $van->km_per_liter ?? 0,
            'status'                 => 'confirmed',
        ]);

        // Notify admin about new trip
        $newTripMsg = "Driver {$driver->name} created: {$request->route_name} on " . \Carbon\Carbon::parse($request->trip_date)->format('M d, Y') . " at " . \Carbon\Carbon::parse($request->departure_time)->format('h:i A') . ".";
        AdminNotification::create([
            'type'       => 'new_trip',
            'title'      => 'New Trip Created by Driver',
            'message'    => substr($newTripMsg, 0, 250),
            'related_id' => 0,
            'is_read'    => false,
        ]);

        return redirect()->route('driver.dashboard')->with('success', 'Trip created successfully! Passengers can now book this trip.');
    }

    /**
     * Cancel ALL bookings on a trip (van not available).
     */
    public function cancelTrip(Request $request, $tripId)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['error' => 'No van assigned.'], 403);
        }

                $trip = Trip::where('id', $tripId)->where('van_id', $van->id)->firstOrFail();
        $reason = $request->reason ?? 'Van not available';

        // Update the trip status
        $trip->update([
            'status'               => 'cancelled',
            'driver_status'        => 'driver_cancelled',
            'driver_cancel_reason' => $reason,
        ]);

        // Also update the linked trip request if any
        // First try direct link, then fallback to matching by destination + date
        $linkedRequestId = $trip->trip_request_id;
        if (!$linkedRequestId) {
            $matched = TripRequest::where('date_of_travel', $trip->trip_date)
                ->where('status', 'approved')
                ->where(function($q) use ($trip) {
                    $q->whereRaw('LOWER(destination) LIKE ?', ['%' . strtolower($trip->dropoff_location ?? '') . '%'])
                      ->orWhereRaw('LOWER(?) LIKE CONCAT(\'%\', LOWER(destination), \'%\')', [$trip->route_name ?? '']);
                })->first();
            $linkedRequestId = $matched ? $matched->id : null;
        }
        if ($linkedRequestId) {
            TripRequest::where('id', $linkedRequestId)
                ->update(['status' => 'cancelled', 'admin_remarks' => 'Cancelled by driver: ' . $reason]);
        }

        // Get all affected bookings BEFORE cancelling
        $affectedBookings = Booking::with('user')
            ->where('trip_id', $trip->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->get();

        // Cancel all active bookings on this trip
        Booking::where('trip_id', $trip->id)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->update(['status' => 'Cancelled']);

        $tripDate = \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y');
        $tripTime = \Carbon\Carbon::parse($trip->departure_time)->format('h:i A');
        $passengerCount = $affectedBookings->count();

        // Notify ADMIN
        $adminMsg = "Driver {$driver->name} cancelled: {$trip->route_name} on {$tripDate}. Reason: {$reason}. {$passengerCount} passenger(s) affected.";
        AdminNotification::create([
            'type'       => 'trip_cancelled',
            'title'      => 'Trip Cancelled by Driver',
            'message'    => substr($adminMsg, 0, 250),
            'related_id' => $trip->id,
            'is_read'    => false,
        ]);

        // Notify each PASSENGER
        foreach ($affectedBookings as $booking) {
            if ($booking->user) {
                UserNotification::create([
                    'user_id'    => $booking->user_id,
                    'type'       => 'trip_cancelled',
                    'title'      => 'Your Trip Has Been Cancelled',
                    'message'    => "Your booking for the trip {$trip->route_name} on {$tripDate} at {$tripTime} "
                                  . "has been cancelled by the driver. "
                                  . "Reason: {$reason}. "
                                  . "Please contact the admin for assistance.",
                    'related_id' => $booking->id,
                    'is_read'    => false,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Trip cancelled. {$passengerCount} passenger(s) notified. Reason: {$reason}",
        ]);
    }

    /**
     * Toggle the driver's own van availability.
     */
    public function toggleVanAvailability()
    {
        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['error' => 'No van assigned.'], 403);
        }

        $newValue = $van->is_available ? 0 : 1;
        $van->is_available = $newValue;
        $van->save();
        return response()->json([
            'is_available' => (bool) $newValue,
            'message'      => $newValue ? 'Van marked as Available' : 'Van marked as Not Available',
        ]);
    }

    /**
     * Driver confirms or cancels a trip assigned by admin (pending_driver status).
     */
    public function driverConfirmTrip(Request $request, $tripId)
    {
        $request->validate([
            'action' => 'required|in:confirm,cancel',
            'reason' => 'nullable|string|max:255',
        ]);

        $driver = Auth::user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['error' => 'No van assigned.'], 403);
        }

        $trip = Trip::where('id', $tripId)->where('van_id', $van->id)->firstOrFail();

        if ($request->action === 'confirm') {
            $trip->update(['driver_status' => 'driver_confirmed']);

            // Notify admin
            $confirmMsg = "Driver {$driver->name} confirmed: {$trip->route_name} on " . \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') . ".";
            AdminNotification::create([
                'type'       => 'driver_confirmed',
                'title'      => 'Driver Confirmed Trip',
                'message'    => substr($confirmMsg, 0, 250),
                'related_id' => $trip->id,
                'is_read'    => false,
            ]);

            // Notify booked passengers
            $bookings = Booking::with('user')->where('trip_id', $trip->id)->whereIn('status', ['Pending', 'Confirmed'])->get();
            foreach ($bookings as $booking) {
                if ($booking->user) {
                    UserNotification::create([
                        'user_id' => $booking->user_id,
                        'title'   => 'Trip Confirmed by Driver',
                        'message' => "Your trip {$trip->route_name} on " . \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') . " has been confirmed by the driver. See you on the trip!",
                        'type'    => 'success',
                        'is_read' => false,
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Trip confirmed! Passengers have been notified.']);

        } else {
            // Cancel
            $reason = $request->reason ?? 'Van not available';
            $trip->update([
                'driver_status'        => 'driver_cancelled',
                'status'               => 'cancelled',
                'driver_cancel_reason' => $reason,
            ]);
            // Also update the linked trip request status to cancelled
            // First try direct link, then fallback to matching by destination + date
            $linkedReqId = $trip->trip_request_id;
            if (!$linkedReqId) {
                $matchedReq = TripRequest::where('date_of_travel', $trip->trip_date)
                    ->where('status', 'approved')
                    ->where(function($q) use ($trip) {
                        $q->whereRaw('LOWER(destination) LIKE ?', ['%' . strtolower($trip->dropoff_location ?? '') . '%'])
                          ->orWhereRaw('LOWER(?) LIKE CONCAT(\'%\', LOWER(destination), \'%\')', [$trip->route_name ?? '']);
                    })->first();
                $linkedReqId = $matchedReq ? $matchedReq->id : null;
            }
            if ($linkedReqId) {
                TripRequest::where('id', $linkedReqId)
                    ->update(['status' => 'cancelled', 'admin_remarks' => 'Cancelled by driver: ' . $reason]);
            }

            // Cancel all active bookings
            $affectedBookings = Booking::with('user')
                ->where('trip_id', $trip->id)
                ->whereIn('status', ['Pending', 'Confirmed'])
                ->get();

            Booking::where('trip_id', $trip->id)
                ->whereIn('status', ['Pending', 'Confirmed'])
                ->update(['status' => 'Cancelled']);

            $tripDate = \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y');

            // Notify admin
            $cancelMsg = "Driver {$driver->name} cancelled: {$trip->route_name} on {$tripDate}. Reason: {$reason}. " . $affectedBookings->count() . " passenger(s) affected.";
            AdminNotification::create([
                'type'       => 'driver_cancelled',
                'title'      => 'Driver Cancelled Trip',
                'message'    => substr($cancelMsg, 0, 250),
                'related_id' => $trip->id,
                'is_read'    => false,
            ]);

            // Notify each passenger
            foreach ($affectedBookings as $booking) {
                if ($booking->user) {
                    UserNotification::create([
                        'user_id' => $booking->user_id,
                        'title'   => 'Trip Cancelled by Driver',
                        'message' => "Your booking for trip {$trip->route_name} on {$tripDate} has been cancelled by the driver. Reason: {$reason}. Please contact the admin.",
                        'type'    => 'danger',
                        'is_read' => false,
                    ]);
                }
            }

            return response()->json(['success' => true, 'message' => "Trip cancelled. {$affectedBookings->count()} passenger(s) notified."]);
        }
    }
}

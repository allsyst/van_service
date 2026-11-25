<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\UserNotification;
use App\Models\User;
use App\Models\Van;
use App\Models\VanLocation;
use App\Services\TripNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverMobileController extends Controller
{
    public function __construct(private TripNotificationService $tripNotificationService)
    {
    }

    /**
     * Issue a scoped Sanctum token for a driver with an assigned van.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $driver = User::where('email', $credentials['email'])->first();

        if (!$driver || !Hash::check($credentials['password'], $driver->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 422);
        }

        if ($driver->role !== 'driver') {
            return response()->json(['message' => 'This account is not a driver account.'], 403);
        }

        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json([
                'message' => 'No van is assigned to this driver account. Please contact an administrator.',
            ], 403);
        }

        $driver->tokens()->where('name', 'vanservice-driver-mobile')->delete();
        $token = $driver->createToken('vanservice-driver-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'email' => $driver->email,
            ],
            'van' => [
                'id' => $van->id,
                'plate' => $van->van_plate,
                'model' => $van->van_model,
            ],
        ]);
    }

    /**
     * Return the currently authenticated driver and assigned van.
     */
    public function profile(Request $request)
    {
        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();

        return response()->json([
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'email' => $driver->email,
            ],
            'van' => $van ? [
                'id' => $van->id,
                'plate' => $van->van_plate,
                'model' => $van->van_model,
            ] : null,
        ]);
    }

    /**
     * Return today's active trip and the destination used by foreground-only
     * arrival monitoring on the driver's phone or tablet.
     */
    public function activeTrip(Request $request)
    {
        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['trip' => null]);
        }

        $today = today()->toDateString();
        $trip = Trip::where('van_id', $van->id)
            ->whereDate('trip_date', '>=', $today)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderByRaw('CASE WHEN DATE(trip_date) = ? THEN 0 ELSE 1 END', [$today])
            ->orderBy('trip_date')
            ->orderBy('departure_time')
            ->first();

        if (!$trip) {
            return response()->json(['trip' => null]);
        }

        return response()->json([
            'trip' => [
                'id' => $trip->id,
                'route_name' => $trip->route_name,
                'destination_name' => $trip->dropoff_location,
                'trip_date' => optional($trip->trip_date)->toDateString(),
                'departure_time' => $trip->departure_time,
                'destination_latitude' => $trip->dropoff_latitude,
                'destination_longitude' => $trip->dropoff_longitude,
                'arrival_geofence_radius_meters' => $trip->arrival_geofence_radius_meters ?? 150,
                'arrived_at' => optional($trip->arrived_at)->toIso8601String(),
                'departed_at' => optional($trip->departed_at)->toIso8601String(),
                'requires_confirmation' => $trip->driver_status === 'pending_driver',
                'can_takeoff' => !$trip->departed_at && $trip->driver_status !== 'pending_driver',
            ],
        ]);
    }

    /**
     * Return the official driver trip-ticket record for the assigned van.
     * The native app renders this data locally while connected to Laravel.
     */
    public function tripTicket(Request $request, Trip $trip)
    {
        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();
        if (!$van || $trip->van_id !== $van->id) {
            return response()->json(['message' => 'This trip is not assigned to your van.'], 403);
        }

        $trip->load(['van.driver', 'fuelRecords']);

        $bookings = Booking::with('user')
            ->whereIn('status', ['Pending', 'Confirmed', 'Completed'])
            ->where(function ($query) use ($trip) {
                $query->where('trip_id', $trip->id);
                if ($trip->trip_request_id) {
                    $query->orWhere('trip_request_id', $trip->trip_request_id);
                }
            })
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'ticket' => [
                'trip_number' => 'TRIP-' . str_pad((string) $trip->id, 5, '0', STR_PAD_LEFT),
                'trip_id' => $trip->id,
                'route_name' => $trip->route_name,
                'pickup_location' => $trip->pickup_location,
                'dropoff_location' => $trip->dropoff_location,
                'trip_date' => optional($trip->trip_date)->toDateString(),
                'departure_time' => $trip->departure_time,
                'driver_name' => $trip->van?->driver?->name ?? $driver->name,
                'van_plate' => $trip->van?->van_plate,
                'van_model' => $trip->van?->van_model ?? $trip->van_model,
                'departed_at' => optional($trip->departed_at)->toIso8601String(),
                'arrived_at' => optional($trip->arrived_at)->toIso8601String(),
                'distance_km' => (float) ($trip->distance_km ?? 0),
                'fuel_liters' => (float) $trip->fuelRecords->sum('liters'),
                'passengers' => $bookings->map(fn ($booking) => [
                    'name' => $booking->user?->name ?? 'Passenger',
                    'requester_name' => $booking->user?->name ?? 'Passenger',
                    'dropoff_location' => $booking->dropoff_location ?: $trip->dropoff_location,
                    'seats' => (int) ($booking->seats_requested ?? 1),
                    'status' => $booking->status,
                ])->values(),
            ],
        ]);
    }

    /**
     * Let the assigned driver confirm an administrator-created trip before it
     * can be marked as departed from the mobile application.
     */
    public function confirmTrip(Request $request)
    {
        $data = $request->validate([
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
        ]);

        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();
        if (!$van) {
            return response()->json(['message' => 'No van is assigned to this driver account.'], 403);
        }

        $trip = Trip::where('id', $data['trip_id'])->where('van_id', $van->id)->first();
        if (!$trip) {
            return response()->json(['message' => 'This trip is not assigned to your van.'], 404);
        }
        if (in_array($trip->status, ['cancelled', 'completed'])) {
            return response()->json(['message' => 'This trip is no longer active.'], 422);
        }

        $newlyConfirmed = $trip->driver_status === 'pending_driver';
        if ($newlyConfirmed) {
            $trip->update(['driver_status' => 'driver_confirmed']);
            AdminNotification::create([
                'type' => 'driver_confirmed',
                'title' => 'Driver Confirmed Trip',
                'message' => substr("{$driver->name} confirmed {$trip->route_name} for " . optional($trip->trip_date)->format('M d, Y') . '.', 0, 250),
                'related_id' => $trip->id,
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'already_confirmed' => !$newlyConfirmed,
            'message' => $newlyConfirmed ? 'Trip confirmed. You can now report van takeoff.' : 'This trip has already been confirmed.',
        ]);
    }

    /**
     * Return pending bookings that belong to this driver's assigned van.
     */
    public function bookings(Request $request)
    {
        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();
        if (!$van) {
            return response()->json(['bookings' => []]);
        }

        $bookings = Booking::with(['user', 'trip', 'tripRequest'])
            ->where('status', 'Pending')
            ->where(function ($query) use ($van) {
                $query->whereHas('trip', fn ($tripQuery) => $tripQuery->where('van_id', $van->id))
                    ->orWhereHas('tripRequest', fn ($requestQuery) => $requestQuery->where('van_id', $van->id));
            })
            ->orderBy('booking_date')
            ->orderBy('booking_time')
            ->get()
            ->map(function (Booking $booking) {
                $tripLabel = $booking->trip?->route_name
                    ?? $booking->tripRequest?->request_no
                    ?? 'Assigned trip';

                return [
                    'id' => $booking->id,
                    'passenger_name' => $booking->user?->name ?? 'Passenger',
                    'passenger_email' => $booking->user?->email ?? '',
                    'trip_label' => $tripLabel,
                    'booking_date' => optional($booking->booking_date)->toDateString(),
                    'booking_time' => $booking->booking_time,
                    'seats_requested' => $booking->seats_requested,
                    'purpose' => $booking->purpose,
                ];
            });

        return response()->json(['bookings' => $bookings]);
    }

    /**
     * Confirm or decline a pending booking only when it belongs to the
     * authenticated driver's currently assigned van.
     */
    public function bookingDecision(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'action' => ['required', 'in:accept,decline'],
        ]);

        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();
        if (!$van) {
            return response()->json(['message' => 'No van is assigned to this driver account.'], 403);
        }

        $booking->load(['trip', 'tripRequest', 'user']);
        $belongsToVan = $booking->trip?->van_id === $van->id
            || $booking->tripRequest?->van_id === $van->id;
        if (!$belongsToVan) {
            return response()->json(['message' => 'This booking is not assigned to your van.'], 403);
        }
        if ($booking->status !== 'Pending') {
            return response()->json(['message' => 'Only pending bookings can be accepted or declined.'], 422);
        }

        $accepted = $data['action'] === 'accept';
        $booking->update(['status' => $accepted ? 'Confirmed' : 'Cancelled']);
        $tripLabel = $booking->trip?->route_name ?? $booking->tripRequest?->request_no ?? 'your assigned trip';
        $passengerName = $booking->user?->name ?? 'Passenger';

        if ($booking->user) {
            UserNotification::create([
                'user_id' => $booking->user_id,
                'type' => $accepted ? 'booking_confirmed' : 'booking_declined',
                'title' => $accepted ? 'Booking Accepted' : 'Booking Declined',
                'message' => $accepted
                    ? "Your booking for {$tripLabel} was accepted by the driver."
                    : "Your booking for {$tripLabel} was declined by the driver. Please contact the administrator for assistance.",
                'related_id' => $booking->id,
                'is_read' => false,
            ]);
        }

        AdminNotification::create([
            'type' => $accepted ? 'booking_accepted' : 'booking_declined',
            'title' => $accepted ? 'Driver Accepted Booking' : 'Driver Declined Booking',
            'message' => substr("{$driver->name} " . ($accepted ? 'accepted' : 'declined') . " {$passengerName}'s booking for {$tripLabel}.", 0, 250),
            'related_id' => $booking->id,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'status' => $booking->status,
            'message' => $accepted ? 'Booking accepted. The passenger and administrator have been notified.' : 'Booking declined. The passenger and administrator have been notified.',
        ]);
    }

    /**
     * Report a real-world van departure from the foreground driver app.
     */
    public function takeOff(Request $request)
    {
        $data = $request->validate([
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
        ]);

        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();
        if (!$van) {
            return response()->json(['message' => 'No van is assigned to this driver account.'], 403);
        }

        $trip = Trip::where('id', $data['trip_id'])
            ->where('van_id', $van->id)
            ->first();
        if (!$trip) {
            return response()->json(['message' => 'This trip is not assigned to your van.'], 404);
        }
        if ($trip->driver_status === 'pending_driver') {
            return response()->json(['message' => 'Confirm this trip before reporting takeoff.'], 422);
        }
        if (in_array($trip->status, ['cancelled', 'completed'])) {
            return response()->json(['message' => 'This trip is no longer active.'], 422);
        }

        $result = $this->tripNotificationService->markDeparted($trip, $driver);

        return response()->json([
            'success' => true,
            'already_departed' => !$result['newly_departed'],
            'departed_at' => optional($result['trip']->departed_at)->toIso8601String(),
        ]);
    }

    /**
     * Let a driver manually confirm that an already-departed active trip has
     * arrived. This remains useful when GPS accuracy is poor at a destination.
     */
    public function markArrived(Request $request)
    {
        $data = $request->validate([
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
        ]);

        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();
        if (!$van) {
            return response()->json(['message' => 'No van is assigned to this driver account.'], 403);
        }

        $result = DB::transaction(function () use ($data, $driver, $van) {
            $trip = Trip::where('id', $data['trip_id'])
                ->where('van_id', $van->id)
                ->lockForUpdate()
                ->first();

            if (!$trip) {
                return ['status' => 404, 'payload' => ['message' => 'This trip is not assigned to your van.']];
            }
            if (in_array($trip->status, ['cancelled', 'completed'])) {
                return ['status' => 422, 'payload' => ['message' => 'This trip is no longer active.']];
            }
            if ($trip->driver_status === 'pending_driver') {
                return ['status' => 422, 'payload' => ['message' => 'Confirm this trip before reporting arrival.']];
            }
            if (!$trip->departed_at) {
                return ['status' => 422, 'payload' => ['message' => 'Record van takeoff before reporting arrival.']];
            }
            if ($trip->arrival_notified_at) {
                return ['status' => 200, 'payload' => [
                    'success' => true,
                    'already_arrived' => true,
                    'arrived_at' => optional($trip->arrived_at)->toIso8601String(),
                ]];
            }

            $arrivedAt = now();
            $trip->update([
                'arrived_at' => $arrivedAt,
                'arrival_notified_at' => $arrivedAt,
                'status' => 'completed',
            ]);

            $completedBookingsQuery = Booking::whereIn('status', ['Pending', 'Confirmed'])
                ->where(function ($query) use ($trip) {
                    $query->where('trip_id', $trip->id);
                    if ($trip->trip_request_id) {
                        $query->orWhere('trip_request_id', $trip->trip_request_id);
                    }
                });
            $completedBookings = $completedBookingsQuery->update(['status' => 'Completed']);

            $message = "{$driver->name} manually confirmed arrival at {$trip->dropoff_location} for {$trip->route_name}.";
            AdminNotification::create([
                'type' => 'van_arrived',
                'title' => 'Van Arrived at Destination',
                'message' => substr($message, 0, 250),
                'related_id' => $trip->id,
                'is_read' => false,
            ]);

            return ['status' => 200, 'payload' => [
                'success' => true,
                'already_arrived' => false,
                'arrived_at' => $arrivedAt->toIso8601String(),
                'completed_bookings' => $completedBookings,
            ]];
        });

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Store the latest foreground-only location for the driver's assigned van.
     */
    public function updateLocation(Request $request)
    {
        $driver = $request->user();

        if ($driver->role !== 'driver') {
            return response()->json(['message' => 'Driver account required.'], 403);
        }

        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['message' => 'No van is assigned to this driver account.'], 403);
        }

        $data = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'speed_kmh' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'heading' => ['nullable', 'numeric', 'min:0', 'max:360'],
        ]);

        VanLocation::updateOrCreate(
            ['van_id' => $van->id],
            [
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'accuracy' => $data['accuracy'] ?? null,
                'speed' => $data['speed_kmh'] ?? null,
                'heading' => $data['heading'] ?? null,
                'is_online' => true,
                'driver_name' => $driver->name,
            ]
        );

        // Location updates provide the trusted foreground heartbeat used to
        // check a departed trip against its saved estimated arrival time.
        $this->tripNotificationService->notifyDelayIfNeededForVan($van, $driver);

        return response()->json([
            'success' => true,
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Revalidate a foreground client reading on the server and generate at
     * most one arrival notification for the trip.
     */
    public function checkArrival(Request $request)
    {
        $data = $request->validate([
            'trip_id' => ['required', 'integer', 'exists:trips,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
        ]);

        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();

        if (!$van) {
            return response()->json(['message' => 'No van is assigned to this driver account.'], 403);
        }

        $result = DB::transaction(function () use ($data, $driver, $van) {
            $trip = Trip::where('id', $data['trip_id'])
                ->where('van_id', $van->id)
                ->lockForUpdate()
                ->first();

            if (!$trip) {
                return ['status' => 404, 'payload' => ['message' => 'This trip is not assigned to your van.']];
            }

            if (is_null($trip->dropoff_latitude) || is_null($trip->dropoff_longitude)) {
                return ['status' => 422, 'payload' => ['message' => 'This trip does not have a saved destination coordinate yet.']];
            }

            $distance = $this->distanceInMeters(
                (float) $data['latitude'],
                (float) $data['longitude'],
                (float) $trip->dropoff_latitude,
                (float) $trip->dropoff_longitude,
            );
            $radius = max(50, (int) ($trip->arrival_geofence_radius_meters ?? 150));

            if ($distance > $radius) {
                return ['status' => 200, 'payload' => [
                    'arrived' => false,
                    'distance_meters' => round($distance),
                    'radius_meters' => $radius,
                ]];
            }

            if ($trip->arrival_notified_at) {
                return ['status' => 200, 'payload' => [
                    'arrived' => true,
                    'already_notified' => true,
                    'distance_meters' => round($distance),
                    'radius_meters' => $radius,
                ]];
            }

            $trip->update([
                'arrived_at' => now(),
                'arrival_notified_at' => now(),
                'status' => 'completed',
            ]);

            $completedBookingsQuery = Booking::whereIn('status', ['Pending', 'Confirmed'])
                ->where(function ($query) use ($trip) {
                    $query->where('trip_id', $trip->id);
                    if ($trip->trip_request_id) {
                        $query->orWhere('trip_request_id', $trip->trip_request_id);
                    }
                });
            $completedBookings = $completedBookingsQuery->update(['status' => 'Completed']);

            $message = "{$driver->name} arrived at {$trip->dropoff_location} for {$trip->route_name}.";
            AdminNotification::create([
                'type' => 'van_arrived',
                'title' => 'Van Arrived at Destination',
                'message' => substr($message, 0, 250),
                'related_id' => $trip->id,
                'is_read' => false,
            ]);

            return ['status' => 200, 'payload' => [
                'arrived' => true,
                'already_notified' => false,
                'distance_meters' => round($distance),
                'radius_meters' => $radius,
                'completed_bookings' => $completedBookings,
            ]];
        });

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Stop showing the van as live when sharing is intentionally ended.
     */
    public function stopSharing(Request $request)
    {
        $driver = $request->user();
        $van = Van::where('driver_id', $driver->id)->first();

        if ($van) {
            VanLocation::where('van_id', $van->id)->update(['is_online' => false]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Stop sharing and revoke the current mobile session.
     */
    public function logout(Request $request)
    {
        $this->stopSharing($request);

        if ($token = $request->user()->currentAccessToken()) {
            $token->delete();
        }

        return response()->json(['success' => true]);
    }

    private function distanceInMeters(float $latitudeA, float $longitudeA, float $latitudeB, float $longitudeB): float
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($latitudeB - $latitudeA);
        $longitudeDelta = deg2rad($longitudeB - $longitudeA);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitudeA)) * cos(deg2rad($latitudeB)) * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

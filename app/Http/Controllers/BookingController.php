<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Models\UserNotification;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingController extends Controller
{
    public function __construct(private VehicleAvailabilityService $vehicleAvailability)
    {
    }

    public function index()
    {
        // The dashboard's main lists deliberately exclude completed, cancelled,
        // rejected, and historical records. They only show active/pending work.
        $tripsQuery = Trip::query()
            ->whereDate('trip_date', '>=', now()->toDateString())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('trip_date')
            ->orderBy('departure_time');

        if (\Illuminate\Support\Facades\Schema::hasColumn('trips', 'status')) {
            $tripsQuery->where('status', 'confirmed');
        }

        // Older approved requests may have been created before the automatic
        // booking record was added. Backfill that accepted booking once so the
        // requester sees it in My Bookings as well.
        TripRequest::with('trip')
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->get()
            ->each(function (TripRequest $tripRequest) {
                if (!$tripRequest->trip || $tripRequest->bookings()->where('user_id', auth()->id())->exists()) {
                    return;
                }

                Booking::create([
                    'user_id' => auth()->id(),
                    'trip_id' => $tripRequest->trip->id,
                    'trip_request_id' => $tripRequest->id,
                    'van_model' => $tripRequest->trip->van_model,
                    'pickup_location' => $tripRequest->pickup_location ?? $tripRequest->trip->pickup_location,
                    'dropoff_location' => $tripRequest->destination,
                    'booking_date' => $tripRequest->date_of_travel,
                    'booking_time' => $tripRequest->trip->departure_time,
                    'seats_requested' => $tripRequest->no_of_passengers,
                    'purpose' => $tripRequest->purpose,
                    'status' => 'Confirmed',
                    'is_read' => false,
                ]);
            });

        // Do not keep a trip in Available Trips after this user has already
        // submitted a booking for it. A driver-approved booking is Confirmed,
        // so it must no longer be offered as an available trip.
        $activeUserTripIds = Booking::where('user_id', auth()->id())
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->whereNotNull('trip_id')
            ->pluck('trip_id');
        $trips = $tripsQuery
            ->whereNotIn('id', $activeUserTripIds)
            ->get();

        $confirmedRequestIds = Booking::where('user_id', auth()->id())
            ->where('status', 'Confirmed')
            ->whereNotNull('trip_request_id')
            ->pluck('trip_request_id');

        $approvedRequests = TripRequest::with(['user', 'trip'])
            ->where('status', 'approved')
            ->whereNotIn('id', $confirmedRequestIds)
            ->whereDate('date_of_travel', '>=', now()->toDateString())
            ->orderBy('date_of_travel')
            ->get()
            ->filter(fn (TripRequest $request) => !$request->trip || !in_array($request->trip->status, ['cancelled', 'completed'], true))
            ->values();

        $myBookings = Booking::with(['trip.van', 'tripRequest'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->latest()
            ->get();

        $bookingHistory = Booking::with(['trip.van', 'tripRequest'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['Completed', 'Cancelled'])
            ->latest('updated_at')
            ->get();

        $myRequests = TripRequest::with('trip')
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->whereNotIn('id', $confirmedRequestIds)
            ->orderByDesc('created_at')
            ->get();

        $userNotifications = UserNotification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        $unreadNotifCount = UserNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();

        return view('user.dashboard', compact(
            'trips',
            'approvedRequests',
            'myBookings',
            'bookingHistory',
            'myRequests',
            'userNotifications',
            'unreadNotifCount'
        ));
    }

    public function tripAvailability(Trip $trip)
    {
        $bookable = $this->vehicleAvailability->tripIsBookable($trip);
        $alreadyBooked = $this->vehicleAvailability->userHasActiveBookingForTrip(auth()->id(), $trip->id);

        $reason = null;
        if ($alreadyBooked) {
            $reason = 'You already have an active booking for this vehicle trip.';
        } elseif (!$bookable) {
            $reason = $trip->availableSeats() < 1
                ? 'This vehicle is already fully booked for this trip.'
                : 'This trip is no longer available for booking.';
        }

        return response()->json([
            'booking_allowed' => $bookable && !$alreadyBooked,
            'already_booked' => $alreadyBooked,
            'available_seats' => max(0, $trip->availableSeats()),
            'reason' => $reason,
        ]);
    }

    public function tripRequestAvailability(TripRequest $tripRequest)
    {
        $tripRequest->load('trip');
        $alreadyBooked = $this->vehicleAvailability->userHasActiveBookingForTripRequest(auth()->id(), $tripRequest->id);
        $activeTrip = !$tripRequest->trip || !in_array($tripRequest->trip->status, ['cancelled', 'completed'], true);
        $active = $tripRequest->status === 'approved' && $tripRequest->date_of_travel->gte(today()) && $activeTrip;

        $reason = null;
        if ($alreadyBooked) {
            $reason = 'You already have an active booking for this vehicle trip.';
        } elseif (!$active) {
            $reason = 'This vehicle trip is no longer active or available for booking.';
        }

        return response()->json([
            'booking_allowed' => $active && !$alreadyBooked,
            'already_booked' => $alreadyBooked,
            'reason' => $reason,
        ]);
    }

    public function store(Request $request)
    {
        // Booking from an approved trip request (no trip_id).
        if ($request->filled('trip_request_id') && !$request->filled('trip_id')) {
            $request->validate([
                'trip_request_id' => 'required|exists:trip_requests,id',
                'purpose' => 'required|string|max:255',
                'dropoff_location' => 'required|string|max:500',
            ]);

            try {
                $booking = DB::transaction(function () use ($request) {
                    $tripRequest = TripRequest::lockForUpdate()->findOrFail($request->trip_request_id);

                    if ($tripRequest->status !== 'approved' || $tripRequest->date_of_travel->lt(today())) {
                        throw new RuntimeException('This trip is no longer active or available for booking.');
                    }

                    if ($this->vehicleAvailability->userHasActiveBookingForTripRequest(auth()->id(), $tripRequest->id)) {
                        throw new RuntimeException('You already have an active booking for this vehicle trip.');
                    }

                    return Booking::create([
                        'user_id' => auth()->id(),
                        'trip_id' => null,
                        'trip_request_id' => $tripRequest->id,
                        'van_model' => $tripRequest->trip?->van_model,
                        'pickup_location' => $tripRequest->trip?->pickup_location,
                        'dropoff_location' => $request->dropoff_location,
                        'booking_date' => $tripRequest->date_of_travel,
                        'booking_time' => $tripRequest->trip?->departure_time,
                        'seats_requested' => $tripRequest->no_of_passengers,
                        'purpose' => $request->purpose,
                        'status' => 'Pending',
                        'is_read' => false,
                    ]);
                });
            } catch (RuntimeException $exception) {
                return back()->withInput()->with('error', $exception->getMessage());
            }

            $this->notifyAdminOfNewBooking($booking, 'approved trip request ' . $booking->tripRequest->request_no);

            return redirect()->route('user.dashboard')->with(
                'success',
                'Booking submitted for approved trip! REQ-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT) . ' is awaiting admin confirmation.'
            );
        }

        $request->validate([
            'trip_id' => 'required|exists:trips,id',
            'seats_requested' => 'required|integer|min:1',
            'purpose' => 'required|string|max:255',
            'dropoff_location' => 'required|string|max:500',
        ]);

        try {
            $booking = DB::transaction(function () use ($request) {
                // Serializing on the trip row prevents two simultaneous requests
                // from consuming the same remaining vehicle seats.
                $trip = Trip::lockForUpdate()->findOrFail($request->trip_id);

                if (!$this->vehicleAvailability->tripIsBookable($trip)) {
                    $message = $trip->availableSeats() < $request->seats_requested
                        ? 'This vehicle has insufficient available seats. Please select another trip.'
                        : 'This trip is no longer active or available for booking.';
                    throw new RuntimeException($message);
                }

                if ($this->vehicleAvailability->userHasActiveBookingForTrip(auth()->id(), $trip->id)) {
                    throw new RuntimeException('You already have an active booking for this vehicle trip.');
                }

                if ($trip->availableSeats() < $request->seats_requested) {
                    throw new RuntimeException('This vehicle has insufficient available seats. Please select another trip.');
                }

                return Booking::create([
                    'user_id' => auth()->id(),
                    'trip_id' => $trip->id,
                    'van_model' => $trip->van_model,
                    'pickup_location' => $trip->pickup_location,
                    'dropoff_location' => $request->dropoff_location ?? $trip->dropoff_location,
                    'dropoff_lat' => $request->dropoff_lat,
                    'dropoff_lng' => $request->dropoff_lng,
                    'booking_date' => $trip->trip_date,
                    'booking_time' => $trip->departure_time,
                    'seats_requested' => $request->seats_requested,
                    'purpose' => $request->purpose,
                    'status' => 'Pending',
                    'is_read' => false,
                ]);
            });
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        $this->notifyAdminOfNewBooking($booking, $booking->trip->route_name);

        return redirect()->route('user.dashboard')->with(
            'success',
            'Booking submitted! Request No. REQ-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT) . ' is awaiting admin approval.'
        );
    }

    public function ticket(Booking $booking)
    {
        abort_unless($booking->user_id === auth()->id(), 403);
        abort_unless($booking->status === 'Confirmed', 403, 'A printable ticket is available once the booking is confirmed.');

        $booking->load(['user', 'trip.van.driver', 'tripRequest']);

        return view('user.trip-ticket', compact('booking'));
    }

    public function cancelBooking($id)
    {
        $booking = Booking::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        if (!in_array($booking->status, ['Pending', 'Confirmed'])) {
            return redirect()->route('user.dashboard')->with('error', 'Only pending or confirmed bookings can be cancelled.');
        }
        $booking->update(['status' => 'Cancelled']);

        return redirect()->route('user.dashboard')->with('success', 'Booking REQ-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT) . ' has been cancelled.');
    }

    public function cancelRequest($id)
    {
        $request = TripRequest::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        if ($request->status !== 'pending') {
            return redirect()->route('user.dashboard')->with('error', 'Only pending trip requests can be cancelled.');
        }
        $request->update(['status' => 'cancelled']);

        return redirect()->route('user.dashboard')->with('success', 'Trip request ' . $request->request_no . ' has been cancelled.');
    }

    public function tripPassengers($tripId)
    {
        Trip::findOrFail($tripId);

        $passengers = Booking::with('user')
            ->where('trip_id', $tripId)
            ->whereIn('status', ['Pending', 'Confirmed', 'Completed'])
            ->get()
            ->map(fn (Booking $booking) => [
                'name' => $booking->user->name ?? 'Unknown',
                'status' => $booking->status,
            ]);

        return response()->json(['passengers' => $passengers]);
    }

    private function notifyAdminOfNewBooking(Booking $booking, string $tripLabel): void
    {
        $passengerName = auth()->user()->name ?? 'A user';
        $message = "{$passengerName} submitted a new booking for {$tripLabel} ({$booking->seats_requested} passenger(s)).";

        AdminNotification::create([
            'type' => 'new_booking',
            'title' => 'New Booking Submitted',
            'message' => substr($message, 0, 250),
            'related_id' => $booking->id,
            'is_read' => false,
        ]);
    }
}

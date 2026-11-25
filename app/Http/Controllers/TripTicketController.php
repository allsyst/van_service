<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripTicketController extends Controller
{
    public function web(Request $request, Trip $trip)
    {
        $user = $request->user();
        $isAdmin = $user?->role === 'admin';
        $isDriver = $user?->role === 'driver';
        $isAssignedDriver = $isDriver && $trip->van?->driver_id === $user->id;

        abort_unless($isAdmin || $isAssignedDriver, 403);

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

        return view('trip-tickets.driver', compact('trip', 'bookings'));
    }
}

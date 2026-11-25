<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Trip;
use App\Models\TripRequest;
use App\Models\UserNotification;
use App\Models\Van;
use App\Services\VehicleAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TripRequestController extends Controller
{
    public function __construct(private VehicleAvailabilityService $vehicleAvailability)
    {
    }

    public function index()
    {
        $requests = TripRequest::with(['user', 'trip'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'cancelled')")
            ->orderByDesc('created_at')
            ->get();

        $pendingCount = $requests->where('status', 'pending')->count();
        $approvedCount = $requests->where('status', 'approved')->count();
        $rejectedCount = $requests->where('status', 'rejected')->count();
        $cancelledCount = $requests->where('status', 'cancelled')->count();

        return view('admin.trip_requests.index', compact(
            'requests', 'pendingCount', 'approvedCount', 'rejectedCount', 'cancelledCount'
        ));
    }

    public function approve(Request $request, TripRequest $tripRequest)
    {
        $request->validate([
            'admin_remarks' => 'nullable|string|max:500',
            'van_id' => 'required|exists:vans,id',
        ]);

        try {
            [$tripRequest, $van] = DB::transaction(function () use ($request, $tripRequest) {
                $lockedRequest = TripRequest::lockForUpdate()->findOrFail($tripRequest->id);
                if ($lockedRequest->status !== 'pending') {
                    throw new RuntimeException('This trip request has already been processed.');
                }

                $van = Van::findOrFail($request->van_id);
                $conflict = $this->vehicleAvailability->findScheduledConflict(
                    $van->id,
                    $lockedRequest->date_of_travel,
                    '07:00:00',
                    null,
                    null,
                    true
                );

                if ($conflict) {
                    throw new RuntimeException($this->conflictMessage($van, $conflict));
                }

                $trip = Trip::create([
                    'van_id' => $van->id,
                    'van_model' => $van->van_model,
                    'route_name' => ($lockedRequest->destination ?? 'Trip Request') . ' (RQ: ' . $lockedRequest->request_no . ')',
                    'pickup_location' => $lockedRequest->pickup_location ?? '—',
                    'dropoff_location' => $lockedRequest->destination ?? '—',
                    'dropoff_latitude' => $lockedRequest->destination_latitude,
                    'dropoff_longitude' => $lockedRequest->destination_longitude,
                    'arrival_geofence_radius_meters' => 150,
                    'trip_date' => $lockedRequest->date_of_travel,
                    'departure_time' => '07:00:00',
                    'total_seats' => $van->total_seats ?? 12,
                    'fuel_capacity' => $van->fuel_capacity ?? 0,
                    'km_per_liter' => $van->km_per_liter ?? 0,
                    'distance_km' => 0,
                    'estimated_fuel_needed' => 0,
                    'status' => 'confirmed',
                    'driver_status' => 'pending_driver',
                    'trip_request_id' => $lockedRequest->id,
                ]);

                // An approved request is already an accepted booking for the
                // requester. Put it directly in My Bookings; it will be moved
                // to Booking History when the van arrives and the trip is
                // completed.
                Booking::updateOrCreate(
                    [
                        'user_id' => $lockedRequest->user_id,
                        'trip_request_id' => $lockedRequest->id,
                    ],
                    [
                        'trip_id' => $trip->id,
                        'van_model' => $van->van_model,
                        'pickup_location' => $lockedRequest->pickup_location ?? $trip->pickup_location,
                        'dropoff_location' => $lockedRequest->destination,
                        'booking_date' => $lockedRequest->date_of_travel,
                        'booking_time' => $trip->departure_time,
                        'seats_requested' => $lockedRequest->no_of_passengers,
                        'purpose' => $lockedRequest->purpose,
                        'status' => 'Confirmed',
                        'is_read' => false,
                    ]
                );

                $lockedRequest->update([
                    'status' => 'approved',
                    'admin_remarks' => $request->admin_remarks,
                    'van_id' => $van->id,
                ]);

                return [$lockedRequest->fresh(), $van];
            });
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        UserNotification::create([
            'user_id' => $tripRequest->user_id,
            'title' => 'Trip Request Approved',
            'message' => 'Your trip request ' . $tripRequest->request_no . ' has been approved. Van assigned: ' . $van->van_model . ' (' . ($van->van_plate ?? 'No Plate') . ').' .
                ($request->admin_remarks ? ' Remarks: ' . $request->admin_remarks : ''),
            'type' => 'trip_request_approved',
            'is_read' => false,
        ]);

        return back()->with('success', 'Trip request ' . $tripRequest->request_no . ' approved and van ' . $van->van_plate . ' assigned.');
    }

    public function reject(Request $request, TripRequest $tripRequest)
    {
        $request->validate(['admin_remarks' => 'nullable|string|max:500']);

        $tripRequest->update([
            'status' => 'rejected',
            'admin_remarks' => $request->admin_remarks,
        ]);

        UserNotification::create([
            'user_id' => $tripRequest->user_id,
            'title' => 'Trip Request Rejected',
            'message' => 'Your trip request ' . $tripRequest->request_no . ' has been rejected.' .
                ($request->admin_remarks ? ' Reason: ' . $request->admin_remarks : ''),
            'type' => 'trip_request_declined',
            'is_read' => false,
        ]);

        return back()->with('success', 'Trip request ' . $tripRequest->request_no . ' has been rejected.');
    }

    private function conflictMessage(Van $van, Trip $conflict): string
    {
        $date = $conflict->trip_date->format('M d, Y');
        $time = \Carbon\Carbon::parse($conflict->departure_time)->format('h:i A');

        return "{$van->van_model} ({$van->van_plate}) is already booked for {$conflict->route_name} on {$date} at {$time}. Choose a different vehicle or schedule.";
    }
}

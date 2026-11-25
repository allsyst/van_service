<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\TripRequest;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripRequestController extends Controller
{
    /**
     * Show the Request Trip form.
     */
    public function create()
    {
        return view('user.trip_requests.create');
    }

    /**
     * Store a new trip request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date_of_travel'   => 'required|date|after_or_equal:today',
            'purpose'          => 'required|string|max:255',
            'destination'      => 'required|string|max:255',
            'destination_latitude'  => 'nullable|numeric|between:-90,90',
            'destination_longitude' => 'nullable|numeric|between:-180,180',
            'no_of_passengers' => 'required|integer|min:1|max:100',
            'passenger_names'  => 'required|array|min:1',
            'passenger_names.*'=> 'required|string|max:100',
        ]);

        $tripRequest = TripRequest::create([
            'user_id'          => Auth::id(),
            'request_no'       => TripRequest::generateRequestNo(),
            'date_of_travel'   => $request->date_of_travel,
            'purpose'          => $request->purpose,
            'destination'      => $request->destination,
            'destination_latitude'  => $request->destination_latitude,
            'destination_longitude' => $request->destination_longitude,
            'no_of_passengers' => $request->no_of_passengers,
            'passenger_names'  => array_filter($request->passenger_names, fn($n) => trim($n) !== ''),
            'status'           => 'pending',
        ]);

        // Notify admin
        AdminNotification::create([
            'type'    => 'trip_request',
            'title'   => 'New Trip Request',
            'message' => Auth::user()->name . ' submitted a trip request (' . $tripRequest->request_no . ') to ' . $request->destination . ' on ' . \Carbon\Carbon::parse($request->date_of_travel)->format('M d, Y') . '.',
            'is_read' => false,
        ]);

        return redirect()->route('user.trip_requests.index')
            ->with('success', 'Trip request ' . $tripRequest->request_no . ' submitted successfully! Waiting for admin approval.');
    }

    /**
     * Show all trip requests for the current user.
     */
    public function index()
    {
        $requests = TripRequest::with('trip')->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('user.trip_requests.index', compact('requests'));
    }
}

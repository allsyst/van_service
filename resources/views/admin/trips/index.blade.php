@extends('layouts.admin')
@section('title','Trips')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-route me-2 text-primary"></i>All Trips</span>
        <span class="badge bg-light text-muted border" style="font-size:0.78rem;font-weight:600;">
            <i class="fas fa-info-circle me-1"></i>View only — Trips are created by users
        </span>
    </div>
    <div class="table-responsive">
        <table id="trips-table" class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Route</th>
                    <th class="d-none d-md-table-cell">Van</th>
                    <th class="d-none d-sm-table-cell">Date</th>
                    <th class="d-none d-lg-table-cell">Departure</th>
                    <th class="d-none d-lg-table-cell">ETA</th>
                    <th class="d-none d-xl-table-cell">Seats</th>
                    <th class="d-none d-xl-table-cell">Distance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trips as $trip)
                <tr>
                    <td class="text-muted" style="font-size:0.82rem;">{{ $trip->id }}</td>
                    <td>
                        <div class="fw-bold" style="font-size:0.87rem;">{{ $trip->route_name }}</div>
                        <div class="text-muted d-sm-none" style="font-size:0.75rem;">{{ $trip->trip_date->format('M d, Y') }}</div>
                    </td>
                    <td class="d-none d-md-table-cell" style="font-size:0.87rem;">{{ $trip->van_model }}</td>
                    <td class="d-none d-sm-table-cell" style="font-size:0.87rem;">{{ $trip->trip_date->format('M d, Y') }}</td>
                    <td class="d-none d-lg-table-cell" style="font-size:0.87rem;">{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}</td>
                    <td class="d-none d-lg-table-cell" style="font-size:0.87rem;">{{ $trip->eta ? \Carbon\Carbon::parse($trip->eta)->format('h:i A') : '—' }}</td>
                    <td class="d-none d-xl-table-cell" style="font-size:0.87rem;">
                        <span class="badge {{ $trip->availableSeats() > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                            {{ $trip->availableSeats() }}/{{ $trip->total_seats }}
                        </span>
                    </td>
                    <td class="d-none d-xl-table-cell" style="font-size:0.87rem;">{{ $trip->distance_km > 0 ? $trip->distance_km.' km' : '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.trips.passengers', $trip->id) }}" class="btn btn-sm btn-info text-white rounded-pill px-2" title="View Passengers">
                                <i class="fas fa-users"></i>
                            </a>
                            <a href="{{ route('admin.trips.ticket', $trip->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Driver's Trip Ticket">
                                <i class="fas fa-file-alt"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.trips.destroy', $trip->id) }}" onsubmit="return confirm('Delete this trip?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

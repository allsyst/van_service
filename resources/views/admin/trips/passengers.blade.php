@extends('layouts.admin')
@section('title', 'Trip Passengers')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i>Trip Passengers</h5>
        <small class="text-muted">{{ $trip->route_name }}</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.trips.ticket', $trip->id) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="fas fa-file-alt me-1"></i> Driver's Trip Ticket
        </a>
        <a href="{{ route('admin.trips.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Back to Trips
        </a>
    </div>
</div>

{{-- Trip Summary Card --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="card-body py-3 px-4">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">Date</div>
                <div class="fw-semibold">{{ \Carbon\Carbon::parse($trip->trip_date)->format('F d, Y') }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">Departure</div>
                <div class="fw-semibold">{{ $trip->departure_time ? \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') : '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">ETA</div>
                <div class="fw-semibold">{{ $trip->eta ? \Carbon\Carbon::parse($trip->eta)->format('h:i A') : '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">Seats</div>
                <div class="fw-semibold">
                    <span class="badge {{ $trip->availableSeats() > 0 ? 'bg-success' : 'bg-danger' }} rounded-pill">
                        {{ $trip->availableSeats() }} available / {{ $trip->total_seats }} total
                    </span>
                </div>
            </div>
        </div>
        <hr class="my-2">
        <div class="row g-2 text-center">
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">Pickup</div>
                <div style="font-size:0.88rem;">{{ $trip->pickup_location ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">Drop-off</div>
                <div style="font-size:0.88rem;">{{ $trip->dropoff_location ?? '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">Distance</div>
                <div style="font-size:0.88rem;">{{ $trip->distance_km > 0 ? $trip->distance_km.' km' : '—' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-muted" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:.05em;">Van</div>
                <div style="font-size:0.88rem;">{{ $trip->van_model ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Passengers Table --}}
<div class="card border-0 shadow-sm" style="border-radius:14px;">
    <div class="card-header bg-white border-0 px-4 pt-3 pb-2 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-semibold">
            <i class="fas fa-list me-2 text-info"></i>
            Passenger List
            <span class="badge bg-primary rounded-pill ms-2">{{ $bookings->count() }}</span>
        </h6>
        @if($bookings->count() > 0)
        <span class="text-muted" style="font-size:0.82rem;">
            {{ $bookings->where('status','Confirmed')->count() }} confirmed &bull;
            {{ $bookings->where('status','Pending')->count() }} pending &bull;
            {{ $bookings->where('status','Completed')->count() }} completed
        </span>
        @endif
    </div>
    <div class="card-body p-0">
        @if($bookings->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-user-slash fa-2x mb-3 d-block opacity-50"></i>
                No passengers have booked this trip yet.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Passenger</th>
                        <th class="d-none d-md-table-cell">Position</th>
                        <th class="d-none d-lg-table-cell">Email</th>
                        <th class="d-none d-md-table-cell">Drop-off</th>
                        <th class="text-center">Seats</th>
                        <th class="text-center">Status</th>
                        <th class="d-none d-xl-table-cell">Booked At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $i => $booking)
                    <tr>
                        <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width:34px;height:34px;font-size:0.85rem;font-weight:700;">
                                    {{ strtoupper(substr($booking->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell text-muted">{{ $booking->user->position ?? '—' }}</td>
                        <td class="d-none d-lg-table-cell text-muted">{{ $booking->user->email ?? 'N/A' }}</td>
                        <td class="d-none d-md-table-cell">{{ $booking->dropoff_location ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary rounded-pill">{{ $booking->seats_requested }}</span>
                        </td>
                        <td class="text-center">
                            @php
                                $statusClass = match($booking->status) {
                                    'Confirmed'  => 'bg-success',
                                    'Pending'    => 'bg-warning text-dark',
                                    'Completed'  => 'bg-info',
                                    'Cancelled'  => 'bg-danger',
                                    default      => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} rounded-pill">{{ $booking->status }}</span>
                        </td>
                        <td class="d-none d-xl-table-cell text-muted" style="font-size:0.82rem;">
                            {{ $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('M d, Y h:i A') : ($booking->created_at ? $booking->created_at->format('M d, Y h:i A') : '—') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection

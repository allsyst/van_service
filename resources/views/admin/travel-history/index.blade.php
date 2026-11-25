@extends('layouts.admin')

@section('title', 'Travel History')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h2 class="h4 fw-bold mb-1"><i class="fas fa-history text-primary me-2"></i>Travel History</h2>
        <p class="text-muted mb-0">Completed and cancelled passenger bookings from all trips.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.travel-history', ['status' => 'All']) }}" class="btn btn-sm {{ $status === 'All' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3">
            <i class="fas fa-list me-1"></i>All
        </a>
        <a href="{{ route('admin.travel-history', ['status' => 'Completed']) }}" class="btn btn-sm {{ $status === 'Completed' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3">
            <i class="fas fa-check-circle me-1"></i>Completed
        </a>
        <a href="{{ route('admin.travel-history', ['status' => 'Cancelled']) }}" class="btn btn-sm {{ $status === 'Cancelled' ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-3">
            <i class="fas fa-times-circle me-1"></i>Cancelled
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-route text-primary me-2"></i>{{ $status }} Travel Records</span>
        <span class="badge rounded-pill bg-light text-dark">{{ $history->count() }} record(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="travel-history-table">
            <thead>
                <tr>
                    <th>Requester</th>
                    <th>Trip / Route</th>
                    <th>Pick-up Location</th>
                    <th>Drop-off Location</th>
                    <th>Travel Date</th>
                    <th>Time</th>
                    <th>Driver / Van</th>
                    <th>Seats</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($history as $booking)
                    @php
                        $trip = $booking->trip ?? $booking->tripRequest?->trip;
                        $route = $trip?->route_name ?? $booking->tripRequest?->request_no ?? 'Trip request';
                        $pickup = $booking->pickup_location ?? $trip?->pickup_location ?? '—';
                        $driver = $trip?->van?->driver?->name ?? '—';
                        $van = $trip?->van?->van_plate ?? $booking->van_model ?? '—';
                        $travelDate = $booking->booking_date?->format('M d, Y') ?? $trip?->trip_date?->format('M d, Y') ?? $booking->tripRequest?->date_of_travel?->format('M d, Y') ?? '—';
                        $travelTime = $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') : ($trip?->departure_time ? \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') : '—');
                        $dropoff = $booking->dropoff_location ?? $trip?->dropoff_location ?? $booking->tripRequest?->destination ?? '—';
                        $recordStatus = $booking->history_status ?? 'Completed';
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $booking->user?->name ?? 'Unknown requester' }}</div>
                            <small class="text-muted">{{ $booking->user?->email ?? '' }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $route }}</div>
                            <small class="text-muted">REQ-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</small>
                        </td>
                        <td>{{ $pickup }}</td>
                        <td>{{ $dropoff }}</td>
                        <td>{{ $travelDate }}</td>
                        <td>{{ $travelTime }}</td>
                        <td>
                            <div>{{ $driver }}</div>
                            <small class="text-muted">{{ $van }}</small>
                        </td>
                        <td>{{ (int) $booking->seats_requested }}</td>
                        <td>
                            <span class="badge rounded-pill {{ $recordStatus === 'Completed' ? 'bg-primary' : 'bg-danger' }}">{{ $recordStatus }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center text-muted py-5"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>No {{ strtolower($status) }} travel records found.</td>
                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        if ($('#travel-history-table').length && !$.fn.DataTable.isDataTable('#travel-history-table')) {
            $('#travel-history-table').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[3, 'desc']],
                language: { emptyTable: 'No travel records found.', zeroRecords: 'No matching travel records found.' }
            });
        }
    });
</script>
@endpush

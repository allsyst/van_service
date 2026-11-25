@extends('layouts.admin')
@section('title','All Bookings')
@section('content')
@php
    $selectedBookingStatus = request('status');
@endphp

<!-- Filter Bar -->
<div class="card mb-4">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-bold text-muted me-2" style="font-size:0.85rem;"><i class="fas fa-filter me-1"></i>Filter:</span>
            <a href="{{ route('admin.bookings.index') }}" data-booking-filter="" class="booking-filter btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">All</a>
            <a href="{{ route('admin.bookings.index', ['status'=>'Pending']) }}" data-booking-filter="Pending" class="booking-filter btn btn-sm {{ request('status')=='Pending' ? 'btn-warning' : 'btn-outline-warning' }} rounded-pill px-3">Pending</a>
            <a href="{{ route('admin.bookings.index', ['status'=>'Confirmed']) }}" data-booking-filter="Confirmed" class="booking-filter btn btn-sm {{ request('status')=='Confirmed' ? 'btn-success' : 'btn-outline-success' }} rounded-pill px-3">Confirmed</a>
            <a href="{{ route('admin.bookings.index', ['status'=>'Completed']) }}" data-booking-filter="Completed" class="booking-filter btn btn-sm {{ request('status')=='Completed' ? 'btn-info text-white' : 'btn-outline-info' }} rounded-pill px-3">Completed</a>
            <a href="{{ route('admin.bookings.index', ['status'=>'Cancelled']) }}" data-booking-filter="Cancelled" class="booking-filter btn btn-sm {{ request('status')=='Cancelled' ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-3">Cancelled</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-calendar-check me-2 text-primary"></i>{{ $selectedBookingStatus ? $selectedBookingStatus . ' Bookings' : 'All Bookings' }}</span>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.travel-history') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                <i class="fas fa-history me-1"></i>Travel History
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                <i class="fas fa-download me-1"></i>Export
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table id="bookings-table" class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Req No.</th>
                    <th>Passenger</th>
                    <th class="d-none d-md-table-cell">Purpose</th>
                    <th class="d-none d-md-table-cell">Van</th>
                    <th class="d-none d-sm-table-cell">Route</th>
                    <th class="d-none d-lg-table-cell">Date</th>
                    <th class="d-none d-lg-table-cell">Time</th>
                    <th class="d-none d-xl-table-cell">Seats</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $booking)
                @if($selectedBookingStatus && $booking->status !== $selectedBookingStatus)
                    @continue
                @endif
                <tr>
                    <td style="font-size:0.82rem;"><span class="badge bg-light text-primary fw-bold">REQ-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</span></td>
                    <td>
                        <div class="fw-bold" style="font-size:0.87rem;">{{ $booking->user->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">{{ $booking->user->email ?? '' }}</div>
                    </td>
                    <td class="d-none d-md-table-cell" style="font-size:0.87rem;">{{ $booking->purpose ?? '-' }}</td>
                    <td class="d-none d-md-table-cell" style="font-size:0.87rem;">{{ $booking->van_model }}</td>
                    <td class="d-none d-sm-table-cell" style="font-size:0.87rem;">
                        @if($booking->trip)
                            {{ $booking->trip->route_name ?? 'N/A' }}
                        @elseif($booking->tripRequest)
                            {{ $booking->tripRequest->destination }}
                            <div class="text-muted" style="font-size:0.75rem;"><i class="fas fa-check-circle text-success"></i> Approved Request</div>
                        @else
                            {{ $booking->dropoff_location ?? 'N/A' }}
                        @endif
                    </td>
                    <td class="d-none d-lg-table-cell" style="font-size:0.87rem;">
                        @if($booking->booking_date)
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                        @elseif($booking->tripRequest && $booking->tripRequest->date_of_travel)
                            {{ $booking->tripRequest->date_of_travel->format('M d, Y') }}
                        @else
                            &mdash;
                        @endif
                    </td>
                    <td class="d-none d-lg-table-cell" style="font-size:0.87rem;">
                        {{ $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') : '&mdash;' }}
                    </td>
                    <td class="d-none d-xl-table-cell" style="font-size:0.87rem;">{{ $booking->seats_requested }}</td>
                    <td>
                        <select class="status-badge status-{{ $booking->status }}" data-id="{{ $booking->id }}">
                            <option value="Pending" {{ $booking->status=='Pending'?'selected':'' }}>Pending</option>
                            <option value="Confirmed" {{ $booking->status=='Confirmed'?'selected':'' }}>Confirmed</option>
                            <option value="Completed" {{ $booking->status=='Completed'?'selected':'' }}>Completed</option>
                            <option value="Cancelled" {{ $booking->status=='Cancelled'?'selected':'' }}>Cancelled</option>
                        </select>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            @if($booking->trip)
                                <a href="{{ route('admin.trips.ticket', $booking->trip->id) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Driver's Trip Ticket"><i class="fas fa-file-alt"></i></a>
                            @endif
                            <form method="POST" action="{{ route('admin.bookings.destroy', $booking->id) }}" onsubmit="return confirm('Delete this booking?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2"><i class="fas fa-trash"></i></button>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const applyBookingFilter = function (status) {
        if (!window.jQuery || !$.fn.DataTable || !$.fn.DataTable.isDataTable('#bookings-table')) {
            return;
        }
        // Status is the ninth column (zero-based index 8).
        $('#bookings-table').DataTable().column(8)
            .search(status ? '^' + status + '$' : '', true, false)
            .draw();
    };

    document.querySelectorAll('.booking-filter').forEach(function (button) {
        button.addEventListener('click', function () {
            applyBookingFilter(this.dataset.bookingFilter || '');
        });
    });

    const selectedStatus = new URLSearchParams(window.location.search).get('status') || '';
    applyBookingFilter(selectedStatus);
});
</script>
@endpush

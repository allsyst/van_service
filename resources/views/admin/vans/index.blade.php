@extends('layouts.admin')
@section('title','Fleet Management')
@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-shuttle-van me-2 text-primary"></i>Van Fleet</span>
        <a href="{{ route('admin.vans.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="fas fa-plus me-1"></i>Add Van
        </a>
    </div>
    <div class="px-3 pt-2 pb-0">
        <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i>Click on any van row to view its full schedule and passenger details.</p>
    </div>
    <div class="table-responsive">
        <table id="vans-table" class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Model</th>
                    <th class="d-none d-sm-table-cell">Plate</th>
                    <th class="d-none d-md-table-cell">Fuel Cap</th>
                    <th class="d-none d-md-table-cell">KM/L</th>
                    <th class="d-none d-sm-table-cell">Seats</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vans as $van)
                <tr class="van-row" data-url="{{ route('admin.vans.trips', $van->id) }}" style="cursor:pointer;">
                    <td class="text-muted" style="font-size:0.82rem;">{{ $van->id }}</td>
                    <td>
                        <div class="fw-bold" style="font-size:0.87rem;">{{ $van->van_model }}</div>
                        <div class="text-muted d-sm-none" style="font-size:0.75rem;">{{ $van->van_plate ?? 'N/A' }}</div>
                    </td>
                    <td class="d-none d-sm-table-cell" style="font-size:0.87rem;">
                        <span class="badge bg-light text-dark border">{{ $van->van_plate ?? 'N/A' }}</span>
                    </td>
                    <td class="d-none d-md-table-cell" style="font-size:0.87rem;">{{ $van->fuel_capacity }} L</td>
                    <td class="d-none d-md-table-cell" style="font-size:0.87rem;">{{ $van->km_per_liter }} km/L</td>
                    <td class="d-none d-sm-table-cell" style="font-size:0.87rem;">
                        <span class="badge bg-primary rounded-pill">{{ $van->total_seats }} seats</span>
                    </td>
                    <td onclick="event.stopPropagation()">
                        <div class="d-flex gap-1 align-items-center">
                            <button type="button"
                                class="btn btn-sm rounded-pill px-2 availability-toggle-btn {{ $van->is_available ? 'btn-success' : 'btn-danger' }}"
                                data-van-id="{{ $van->id }}"
                                data-available="{{ $van->is_available ? '1' : '0' }}"
                                title="{{ $van->is_available ? 'Available — click to mark Not Available' : 'Not Available — click to mark Available' }}"
                                style="font-size:0.78rem;font-weight:700;min-width:110px;">
                                <i class="fas {{ $van->is_available ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                <span>{{ $van->is_available ? 'Available' : 'Not Available' }}</span>
                            </button>
                            <a href="{{ route('admin.vans.edit', $van->id) }}" class="btn btn-sm btn-info text-white rounded-pill px-2" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.vans.destroy', $van->id) }}" onsubmit="return confirm('Delete this van?')">
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

{{-- Level 1: Van Trips Modal --}}
<div class="modal fade" id="vanTripsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;border:none;">
                <h5 class="modal-title"><i class="fas fa-shuttle-van me-2"></i><span id="trips-van-name"></span> — All Schedules</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="trips-loading" class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">Loading schedules...</p>
                </div>
                <div id="trips-content" style="display:none;">
                    <div id="no-trips-msg" class="alert alert-info m-3" style="display:none;">
                        <i class="fas fa-info-circle me-2"></i>No trips scheduled for this van.
                    </div>
                    <div id="trips-table-wrap" style="display:none;">
                        <div id="section-today" style="display:none;">
                            <div class="px-3 pt-3 pb-1"><span class="badge bg-success px-3 py-2" style="font-size:0.82rem;"><i class="fas fa-calendar-day me-1"></i>Today</span></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light"><tr><th>Route</th><th>Date</th><th>Departure</th><th>ETA</th><th>Seats</th><th>Booked</th><th>Available</th><th></th></tr></thead>
                                    <tbody id="tbody-today"></tbody>
                                </table>
                            </div>
                        </div>
                        <div id="section-future" style="display:none;">
                            <div class="px-3 pt-3 pb-1"><span class="badge bg-primary px-3 py-2" style="font-size:0.82rem;"><i class="fas fa-calendar-alt me-1"></i>Upcoming</span></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light"><tr><th>Route</th><th>Date</th><th>Departure</th><th>ETA</th><th>Seats</th><th>Booked</th><th>Available</th><th></th></tr></thead>
                                    <tbody id="tbody-future"></tbody>
                                </table>
                            </div>
                        </div>
                        <div id="section-past" style="display:none;">
                            <div class="px-3 pt-3 pb-1"><span class="badge bg-secondary px-3 py-2" style="font-size:0.82rem;"><i class="fas fa-history me-1"></i>Past</span></div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="table-light"><tr><th>Route</th><th>Date</th><th>Departure</th><th>ETA</th><th>Seats</th><th>Booked</th><th>Available</th><th></th></tr></thead>
                                    <tbody id="tbody-past"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <span class="me-auto text-muted small" id="trips-count-label"></span>
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Level 2: Trip Passengers Modal --}}
<div class="modal fade" id="tripPassengersModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);color:#fff;border:none;">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Passengers — <span id="pax-trip-name"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-sm-4"><small class="text-muted">Date:</small> <strong id="pax-date"></strong></div>
                    <div class="col-sm-4"><small class="text-muted">Departure:</small> <strong id="pax-departure"></strong></div>
                    <div class="col-sm-4"><small class="text-muted">ETA:</small> <strong id="pax-eta"></strong></div>
                </div>
                <div id="pax-loading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                    <p class="mt-2 text-muted">Loading passengers...</p>
                </div>
                <div id="pax-content" style="display:none;">
                    <div id="no-pax-msg" class="alert alert-info" style="display:none;">
                        <i class="fas fa-info-circle me-2"></i>No passengers booked for this trip.
                    </div>
                    <div id="pax-table-wrap" style="display:none;">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th class="d-none d-md-table-cell">Position</th>
                                        <th class="d-none d-sm-table-cell">Email</th>
                                        <th class="d-none d-lg-table-cell">Drop-off</th>
                                        <th>Seats</th>
                                        <th>Status</th>
                                        <th class="d-none d-xl-table-cell">Booked At</th>
                                    </tr>
                                </thead>
                                <tbody id="pax-tbody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <span class="me-auto text-muted" id="pax-count-label"></span>
                <button type="button" class="btn btn-outline-primary rounded-pill px-3" id="back-to-trips-btn">
                    <i class="fas fa-arrow-left me-1"></i>Back to Schedules
                </button>
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {

    function tripRow(t) {
        var bookedBadge = t.booked_seats > 0
            ? '<span class="badge bg-warning text-dark">' + t.booked_seats + '</span>'
            : '<span class="badge bg-light text-muted border">0</span>';
        var availBadge = t.available_seats > 0
            ? '<span class="badge bg-success">' + t.available_seats + '</span>'
            : '<span class="badge bg-danger">Full</span>';
        return '<tr class="trip-row" data-trip-id="' + t.id + '" style="cursor:pointer;" title="Click to view passengers">' +
            '<td><strong>' + t.route_name + '</strong></td>' +
            '<td>' + t.trip_date + '</td>' +
            '<td>' + t.departure_time + '</td>' +
            '<td>' + t.eta + '</td>' +
            '<td class="text-center">' + t.total_seats + '</td>' +
            '<td class="text-center">' + bookedBadge + '</td>' +
            '<td class="text-center">' + availBadge + '</td>' +
            '<td><button class="btn btn-sm btn-outline-success rounded-pill view-pax-btn" data-trip-id="' + t.id + '"><i class="fas fa-users me-1"></i>Passengers</button></td>' +
            '</tr>';
    }

    $(document).on('click', '.van-row', function() {
        var url = $(this).data('url');
        $('#trips-van-name').text('');
        $('#trips-loading').show();
        $('#trips-content').hide();
        $('#no-trips-msg').hide();
        $('#trips-table-wrap').hide();
        $('#section-today, #section-future, #section-past').hide();
        $('#tbody-today, #tbody-future, #tbody-past').empty();
        $('#trips-count-label').text('');
        var modal = new bootstrap.Modal(document.getElementById('vanTripsModal'));
        modal.show();
        $.ajax({
            url: url, method: 'GET',
            success: function(data) {
                $('#trips-van-name').text(data.van);
                $('#trips-loading').hide();
                $('#trips-content').show();
                if (data.trips.length === 0) { $('#no-trips-msg').show(); return; }
                $('#trips-table-wrap').show();
                $('#trips-count-label').text(data.trips.length + ' trip(s) total');
                var todayTrips = data.trips.filter(function(t) { return t.label === 'today'; });
                var futureTrips = data.trips.filter(function(t) { return t.label === 'future'; });
                var pastTrips = data.trips.filter(function(t) { return t.label === 'past'; });
                if (todayTrips.length > 0) { $('#section-today').show(); $.each(todayTrips, function(i,t){ $('#tbody-today').append(tripRow(t)); }); }
                if (futureTrips.length > 0) { $('#section-future').show(); $.each(futureTrips, function(i,t){ $('#tbody-future').append(tripRow(t)); }); }
                if (pastTrips.length > 0) { $('#section-past').show(); $.each(pastTrips, function(i,t){ $('#tbody-past').append(tripRow(t)); }); }
            },
            error: function() {
                $('#trips-loading').hide(); $('#trips-content').show();
                $('#no-trips-msg').show().html('<i class="fas fa-exclamation-triangle me-2"></i>Failed to load schedules.');
            }
        });
    });

    $(document).on('click', '.trip-row, .view-pax-btn', function(e) {
        e.stopPropagation();
        var tripId = $(this).data('trip-id');
        $('#pax-trip-name, #pax-date, #pax-departure, #pax-eta').text('');
        $('#pax-loading').show(); $('#pax-content').hide();
        $('#no-pax-msg').hide(); $('#pax-table-wrap').hide();
        $('#pax-tbody').empty(); $('#pax-count-label').text('');
        bootstrap.Modal.getInstance(document.getElementById('vanTripsModal'))?.hide();
        setTimeout(function() {
            new bootstrap.Modal(document.getElementById('tripPassengersModal')).show();
        }, 400);
        $.ajax({
            url: '/admin/trips/' + tripId + '/passengers', method: 'GET',
            success: function(data) {
                $('#pax-trip-name').text(data.trip);
                $('#pax-date').text(data.date);
                $('#pax-departure').text(data.departure);
                $('#pax-eta').text(data.eta);
                $('#pax-loading').hide(); $('#pax-content').show();
                if (data.passengers.length === 0) {
                    $('#no-pax-msg').show(); $('#pax-count-label').text('0 passengers');
                } else {
                    $('#pax-table-wrap').show();
                    $('#pax-count-label').text(data.passengers.length + ' passenger(s)');
                    $.each(data.passengers, function(i, p) {
                        var statusBadge = p.status === 'Confirmed'
                            ? '<span class="badge bg-success">' + p.status + '</span>'
                            : p.status === 'Completed'
                            ? '<span class="badge bg-primary">' + p.status + '</span>'
                            : p.status === 'Cancelled'
                            ? '<span class="badge bg-danger">' + p.status + '</span>'
                            : '<span class="badge bg-warning text-dark">' + p.status + '</span>';
                        $('#pax-tbody').append(
                            '<tr>' +
                            '<td>' + (i+1) + '</td>' +
                            '<td><strong>' + p.name + '</strong></td>' +
                            '<td class="d-none d-md-table-cell">' + p.position + '</td>' +
                            '<td class="d-none d-sm-table-cell">' + p.email + '</td>' +
                            '<td class="d-none d-lg-table-cell">' + p.dropoff + '</td>' +
                            '<td class="text-center">' + p.seats + '</td>' +
                            '<td>' + statusBadge + '</td>' +
                            '<td class="d-none d-xl-table-cell">' + p.booking_time + '</td>' +
                            '</tr>'
                        );
                    });
                }
            },
            error: function() {
                $('#pax-loading').hide(); $('#pax-content').show();
                $('#no-pax-msg').show().html('<i class="fas fa-exclamation-triangle me-2"></i>Failed to load passengers.');
            }
        });
    });

    $('#back-to-trips-btn').on('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('tripPassengersModal'))?.hide();
        setTimeout(function() { new bootstrap.Modal(document.getElementById('vanTripsModal')).show(); }, 400);
    });

    // Van availability toggle
    $(document).on('click', '.availability-toggle-btn', function() {
        var btn = $(this);
        var vanId = btn.data('van-id');
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        btn.prop('disabled', true);
        $.ajax({
            url: '/admin/vans/' + vanId + '/toggle-availability',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function(data) {
                btn.prop('disabled', false);
                if (data.is_available) {
                    btn.removeClass('btn-danger').addClass('btn-success');
                    btn.data('available', '1');
                    btn.attr('title', 'Available — click to mark Not Available');
                    btn.html('<i class="fas fa-check-circle me-1"></i><span>Available</span>');
                } else {
                    btn.removeClass('btn-success').addClass('btn-danger');
                    btn.data('available', '0');
                    btn.attr('title', 'Not Available — click to mark Available');
                    btn.html('<i class="fas fa-times-circle me-1"></i><span>Not Available</span>');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Failed to update availability. Please try again.');
            }
        });
    });
});
</script>
@endpush
@endsection

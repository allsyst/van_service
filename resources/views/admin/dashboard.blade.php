@extends('layouts.admin')
@section('title','Dashboard')
@section('content')

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#f6c23e,#f4a700);">
            <div class="stat-label">Pending Bookings</div>
            <div class="stat-value">{{ $pendingCount }}</div>
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1cc88a,#17a673);">
            <div class="stat-label">Confirmed</div>
            <div class="stat-value">{{ $confirmedCount }}</div>
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#36b9cc,#1a8fa1);">
            <div class="stat-label">Completed Trips</div>
            <div class="stat-value">{{ $completedCount }}</div>
            <div class="stat-icon"><i class="fas fa-flag-checkered"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#e74a3b,#c0392b);">
            <div class="stat-label">Pending Users</div>
            <div class="stat-value">{{ $pendingUsers }}</div>
            <div class="stat-icon"><i class="fas fa-user-clock"></i></div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</span>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-success rounded-pill px-3">
            <i class="fas fa-file-alt me-1"></i>Reports
        </a>
    </div>
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="{{ route('admin.vans.create') }}" class="btn btn-info text-white rounded-pill px-3">
            <i class="fas fa-shuttle-van me-1"></i>Add Van
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-warning rounded-pill px-3">
            <i class="fas fa-user-check me-1"></i>Approve Users
            @if($pendingUsers > 0)
                <span class="badge bg-white text-dark ms-1">{{ $pendingUsers }}</span>
            @endif
        </a>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary rounded-pill px-3">
            <i class="fas fa-list me-1"></i>All Bookings
        </a>
        <a href="{{ route('admin.live-map') }}" class="btn btn-primary rounded-pill px-3">
            <i class="fas fa-map-marker-alt me-1"></i>Live Tracking
        </a>
    </div>
</div>

<!-- Recent Bookings -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-2 text-primary"></i>Recent Bookings</span>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Passenger</th>
                    <th class="d-none d-md-table-cell">Van</th>
                    <th class="d-none d-sm-table-cell">Route</th>
                    <th class="d-none d-lg-table-cell">Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $booking)
                <tr>
                    <td class="text-muted" style="font-size:0.82rem;">{{ $booking->id }}</td>
                    <td>
                        <div class="fw-bold" style="font-size:0.87rem;">{{ $booking->user->name ?? 'N/A' }}</div>
                        <div class="text-muted" style="font-size:0.75rem;">{{ $booking->user->email ?? '' }}</div>
                    </td>
                    <td class="d-none d-md-table-cell" style="font-size:0.87rem;">{{ $booking->van_model }}</td>
                    <td class="d-none d-sm-table-cell" style="font-size:0.87rem;">{{ $booking->trip->route_name ?? 'N/A' }}</td>
                    <td class="d-none d-lg-table-cell" style="font-size:0.87rem;">{{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</td>
                    <td>
                        <select class="status-badge status-{{ $booking->status }}" data-id="{{ $booking->id }}">
                            <option value="Pending" {{ $booking->status=='Pending'?'selected':'' }}>Pending</option>
                            <option value="Confirmed" {{ $booking->status=='Confirmed'?'selected':'' }}>Confirmed</option>
                            <option value="Completed" {{ $booking->status=='Completed'?'selected':'' }}>Completed</option>
                            <option value="Cancelled" {{ $booking->status=='Cancelled'?'selected':'' }}>Cancelled</option>
                        </select>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.bookings.destroy', $booking->id) }}" onsubmit="return confirm('Delete this booking?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Driver Dashboard - VanService</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1b3a5c;
            --accent: #4e73df;
            --success: #1cc88a;
            --danger: #e74a3b;
            --warning: #f6c23e;
        }
        body { background: #f0f4f8; font-family: 'Segoe UI', sans-serif; }

        /* Topbar */
        .topbar {
            background: linear-gradient(135deg, var(--primary) 0%, #2c5282 100%);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .topbar-brand { color: #fff; font-weight: 800; font-size: 1.2rem; }
        .topbar-brand span { color: #4fc3f7; }
        .topbar-user { color: rgba(255,255,255,0.9); font-size: 0.9rem; }

        /* Stat cards */
        .stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            border-left: 4px solid var(--accent);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-card.green { border-left-color: var(--success); }
        .stat-card.red   { border-left-color: var(--danger); }
        .stat-card.yellow{ border-left-color: var(--warning); }
        .stat-number { font-size: 2rem; font-weight: 800; color: var(--primary); }
        .stat-label  { font-size: 0.82rem; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05rem; }

        /* Van info card */
        .van-card {
            background: linear-gradient(135deg, var(--primary), #2c5282);
            color: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 4px 15px rgba(27,58,92,0.3);
        }
        .van-card .van-plate { font-size: 1.5rem; font-weight: 800; letter-spacing: 0.1rem; }
        .van-card .van-model { opacity: 0.8; font-size: 0.9rem; }

        /* No van assigned */
        .no-van-box {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }

        /* ===== TRIP OVERVIEW CARD ===== */
        .overview-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        .overview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px 0 24px;
        }
        .overview-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .overview-title .icon-box {
            width: 32px; height: 32px;
            background: #eff6ff;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: var(--accent);
            font-size: 0.9rem;
        }

        /* Tab pills */
        .tab-pills {
            display: flex;
            gap: 4px;
            padding: 14px 24px 0 24px;
            border-bottom: 1px solid #e8ecf0;
        }
        .tab-pill {
            background: none;
            border: none;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: color 0.15s;
        }
        .tab-pill:hover { color: var(--accent); }
        .tab-pill.active { color: var(--accent); border-bottom-color: var(--accent); }
        .tab-pill .pill-count {
            background: var(--accent);
            color: #fff;
            border-radius: 20px;
            padding: 1px 8px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .tab-pill.active .pill-count { background: var(--accent); }
        .tab-pill:not(.active) .pill-count { background: #9ca3af; }

        /* Tab sections */
        .tab-section { display: none; }
        .tab-section.active { display: block; }

        /* Trip table */
        .trip-table { width: 100%; border-collapse: collapse; }
        .trip-table thead tr {
            border-bottom: 1px solid #e8ecf0;
        }
        .trip-table thead th {
            padding: 10px 16px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06rem;
            color: #9ca3af;
            background: #fff;
            white-space: nowrap;
        }
        .trip-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: background 0.12s;
            cursor: pointer;
        }
        .trip-table tbody tr:last-child { border-bottom: none; }
        .trip-table tbody tr:hover { background: #f9fafb; }
        .trip-table tbody tr.driver-cancelled { opacity: 0.5; }
        .trip-table td {
            padding: 14px 16px;
            font-size: 0.875rem;
            color: #374151;
            vertical-align: middle;
        }
        .trip-table td.row-num { color: #9ca3af; font-size: 0.82rem; width: 36px; }

        /* Route cell */
        .route-main { font-weight: 700; color: #111827; font-size: 0.9rem; }
        .route-sub  { font-size: 0.75rem; color: #6b7280; margin-top: 2px; }

        /* Status badge — green approved style */
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge-confirmed-driver { background: #d1fae5; color: #065f46; }
        .badge-pending-driver   { background: #fef9c3; color: #854d0e; }
        .badge-cancelled-driver { background: #fee2e2; color: #991b1b; }
        .badge-today-label      { background: #d1fae5; color: #065f46; }
        .badge-upcoming-label   { background: #dbeafe; color: #1e40af; }
        .badge-past-label       { background: #f3f4f6; color: #6b7280; }

        /* Action button — blue Book-style */
        .btn-view-passengers {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
        }
        .btn-view-passengers:hover { background: #3756c0; }
        .btn-tbl-takeoff {
            background: #0f766e;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 5px 13px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-tbl-takeoff:hover { background: #115e59; }
        .btn-tbl-confirm {
            background: #16a34a;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 5px 13px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.15s;
        }
        .btn-tbl-confirm:hover { background: #15803d; }
        .btn-tbl-cancel {
            background: var(--danger);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 5px 13px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.15s;
        }
        .btn-tbl-cancel:hover { background: #c0392b; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
            color: #9ca3af;
        }
        .empty-state i { font-size: 2.5rem; margin-bottom: 12px; opacity: 0.4; }
        .empty-state p { font-weight: 600; margin: 0; }

        /* Passenger modal */
        .passenger-row { transition: background 0.15s; }
        .passenger-row:hover { background: #f8f9fa; }
        .status-pill { border-radius: 20px; padding: 4px 14px; font-size: 0.78rem; font-weight: 700; display: inline-block; }
        .pill-pending   { background: #fff3cd; color: #856404; }
        .pill-confirmed { background: #d1e7dd; color: #0a3622; }
        .pill-completed { background: #cfe2ff; color: #084298; }
        .pill-cancelled { background: #f8d7da; color: #842029; }
        .btn-complete        { background: var(--success); color: #fff; border: none; border-radius: 8px; padding: 5px 14px; font-size: 0.8rem; font-weight: 700; }
        .btn-complete:hover   { background: #17a673; color: #fff; }
        .btn-cancel-booking  { background: var(--danger);  color: #fff; border: none; border-radius: 8px; padding: 5px 14px; font-size: 0.8rem; font-weight: 700; }
        .btn-cancel-booking:hover { background: #c0392b; color: #fff; }
        .btn-confirm-booking { background: var(--accent);  color: #fff; border: none; border-radius: 8px; padding: 5px 14px; font-size: 0.8rem; font-weight: 700; }
        .btn-confirm-booking:hover { background: #3756c0; color: #fff; }

        @media (max-width: 576px) {
            .topbar { padding: 12px 16px; }
            .stat-number { font-size: 1.5rem; }
            .overview-header { padding: 14px 16px 0 16px; }
            .tab-pills { padding: 10px 16px 0 16px; }
            .trip-table thead th,
            .trip-table td { padding: 10px 10px; font-size: 0.78rem; }
        }
    </style>
</head>
<body>

{{-- Topbar --}}
<div class="topbar">
    <div class="topbar-brand">
        <i class="fas fa-shuttle-van me-2"></i>Van<span>Service</span>
        <small style="font-size:0.7rem; opacity:0.7; margin-left:6px;">Driver Panel</small>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="topbar-user">
            <i class="fas fa-user-circle me-1"></i> {{ $driver->name }}
        </span>
        <form method="POST" action="{{ route('logout') }}" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light" style="border-radius:8px;">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </button>
        </form>
    </div>
</div>

<div class="container-fluid py-4 px-3 px-md-4" style="max-width:1100px; margin:0 auto;">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Van Info + Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            @if($van)
            <div class="van-card h-100">
                <div style="opacity:0.7; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.08rem; margin-bottom:6px;">
                    <i class="fas fa-shuttle-van me-1"></i> Assigned Van
                </div>
                <div class="van-plate">{{ $van->van_plate ?? 'N/A' }}</div>
                <div class="van-model">{{ $van->van_model }}</div>
                <hr style="border-color:rgba(255,255,255,0.2); margin:12px 0;">
                <div style="font-size:0.85rem;">
                    <i class="fas fa-users me-1"></i> {{ $van->total_seats }} seats &nbsp;|&nbsp;
                    <i class="fas fa-gas-pump me-1"></i> {{ $van->fuel_capacity }}L
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <span class="btn btn-sm disabled" aria-disabled="true"
                       style="background:rgba(255,255,255,0.2); color:#fff; border-radius:8px; font-size:0.82rem; opacity:1; cursor:default;">
                        <i class="fas fa-mobile-alt me-1"></i> Share location in Driver App
                    </span>
                    <button id="vanAvailBtn" onclick="toggleVanAvailability()"
                        class="btn btn-sm"
                        style="border-radius:8px; font-size:0.82rem; font-weight:700; border:none;
                               background:{{ $van->is_available ? '#1cc88a' : '#e74a3b' }}; color:#fff;">
                        <i class="fas {{ $van->is_available ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                        <span id="vanAvailLabel">{{ $van->is_available ? 'Available' : 'Not Available' }}</span>
                    </button>
                </div>
            </div>
            @else
            <div class="no-van-box h-100">
                <i class="fas fa-shuttle-van fa-3x text-muted mb-3"></i>
                <h6 class="fw-bold text-muted">No Van Assigned</h6>
                <p class="text-muted small">Please contact admin to assign a van to your account.</p>
            </div>
            @endif
        </div>

        <div class="col-md-8">
            <div class="row g-3 h-100">
                <div class="col-6 col-md-4">
                    <div class="stat-card h-100">
                        <div class="stat-number">{{ $todayTrips->count() }}</div>
                        <div class="stat-label">Today's Trips</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-card green h-100">
                        <div class="stat-number">{{ $totalPassengers }}</div>
                        <div class="stat-label">Total Passengers</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-card h-100">
                        <div class="stat-number">{{ $upcomingTrips->count() }}</div>
                        <div class="stat-label">Upcoming Trips</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-card green h-100">
                        <div class="stat-number">{{ $totalCompleted }}</div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-card red h-100">
                        <div class="stat-number">{{ $totalCancelled }}</div>
                        <div class="stat-label">Cancelled</div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="stat-card yellow h-100">
                        <div class="stat-number">{{ $pastTrips->count() }}</div>
                        <div class="stat-label">Past Trips</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($van)
    {{-- ===== TRIP OVERVIEW CARD ===== --}}
    <div class="overview-card">

        {{-- Header --}}
        <div class="overview-header">
            <div class="overview-title">
                <div class="icon-box"><i class="fas fa-th-large"></i></div>
                Trip Overview
            </div>
        </div>

        {{-- Tab Pills --}}
        <div class="tab-pills">
            <button class="tab-pill active" onclick="switchTab('today', this)">
                <i class="fas fa-calendar-day"></i> Today
                <span class="pill-count">{{ $todayTrips->count() }}</span>
            </button>
            <button class="tab-pill" onclick="switchTab('upcoming', this)">
                <i class="fas fa-calendar-alt"></i> Upcoming
                <span class="pill-count">{{ $upcomingTrips->count() }}</span>
            </button>
            <button class="tab-pill" onclick="switchTab('past', this)">
                <i class="fas fa-history"></i> Past Trips
                <span class="pill-count">{{ $pastTrips->count() }}</span>
            </button>
        </div>

        {{-- ===== TODAY ===== --}}
        <div class="tab-section active" id="tab-today">
            @if($todayTrips->isEmpty())
                <div class="empty-state">
                    <div><i class="fas fa-calendar-day"></i></div>
                    <p>No trips scheduled for today</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="trip-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Route / Destination</th>
                            <th>Date</th>
                            <th>Departure</th>
                            <th>Passengers</th>
                            <th>Driver Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todayTrips as $i => $trip)
                        <tr class="{{ $trip->driver_status === 'driver_cancelled' ? 'driver-cancelled' : '' }}"
                            onclick="loadPassengers({{ $trip->id }})">
                            <td class="row-num">{{ $i + 1 }}</td>
                            <td>
                                <span class="badge-status badge-today-label">
                                    <i class="fas fa-check-circle"></i> Today
                                </span>
                            </td>
                            <td>
                                <div class="route-main">{{ $trip->route_name }}</div>
                                @if($trip->eta)
                                    <div class="route-sub"><i class="fas fa-flag-checkered me-1"></i>ETA: {{ $trip->eta }}</div>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}</td>
                            <td>{{ $trip->confirmed_count }}</td>
                            <td>
                                @if($trip->driver_status === 'pending_driver')
                                    <span class="badge-status badge-pending-driver">
                                        <i class="fas fa-exclamation-circle"></i> Awaiting Confirmation
                                    </span>
                                @elseif($trip->driver_status === 'driver_confirmed')
                                    <span class="badge-status badge-confirmed-driver">
                                        <i class="fas fa-check-circle"></i> Confirmed
                                    </span>
                                @elseif($trip->driver_status === 'driver_cancelled')
                                    <span class="badge-status badge-cancelled-driver">
                                        <i class="fas fa-times-circle"></i> Cancelled
                                    </span>
                                @endif
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($trip->driver_status === 'pending_driver')
                                    <div class="d-flex gap-1 flex-wrap">
                                        <button class="btn-tbl-confirm" onclick="driverConfirmTrip({{ $trip->id }}, '{{ addslashes($trip->route_name) }}')">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                        <button class="btn-tbl-cancel" onclick="driverCancelTrip({{ $trip->id }}, '{{ addslashes($trip->route_name) }}')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                @else
                                    @if($trip->driver_status === 'driver_confirmed' && !$trip->departed_at && !in_array($trip->status, ['cancelled', 'completed']))
                                        <button class="btn-tbl-takeoff" onclick="takeOff({{ $trip->id }})">
                                            <i class="fas fa-play"></i> Take Off
                                        </button>
                                    @endif
                                    <button class="btn-view-passengers" onclick="loadPassengers({{ $trip->id }})">
                                        <i class="fas fa-users"></i> View Passengers
                                    </button>
                                    <a class="btn btn-sm btn-outline-primary rounded-pill" target="_blank" href="{{ route('driver.trips.ticket', $trip->id) }}">
                                        <i class="fas fa-file-alt"></i> Trip Ticket
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ===== UPCOMING ===== --}}
        <div class="tab-section" id="tab-upcoming">
            @if($upcomingTrips->isEmpty())
                <div class="empty-state">
                    <div><i class="fas fa-calendar-alt"></i></div>
                    <p>No upcoming trips</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="trip-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Route / Destination</th>
                            <th>Date</th>
                            <th>Departure</th>
                            <th>Passengers</th>
                            <th>Driver Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingTrips as $i => $trip)
                        <tr class="{{ $trip->driver_status === 'driver_cancelled' ? 'driver-cancelled' : '' }}"
                            onclick="loadPassengers({{ $trip->id }})">
                            <td class="row-num">{{ $i + 1 }}</td>
                            <td>
                                <span class="badge-status badge-upcoming-label">
                                    <i class="fas fa-calendar-alt"></i> Upcoming
                                </span>
                            </td>
                            <td>
                                <div class="route-main">{{ $trip->route_name }}</div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}</td>
                            <td>{{ $trip->confirmed_count }}</td>
                            <td>
                                @if($trip->driver_status === 'pending_driver')
                                    <span class="badge-status badge-pending-driver">
                                        <i class="fas fa-exclamation-circle"></i> Awaiting Confirmation
                                    </span>
                                @elseif($trip->driver_status === 'driver_confirmed')
                                    <span class="badge-status badge-confirmed-driver">
                                        <i class="fas fa-check-circle"></i> Confirmed
                                    </span>
                                @elseif($trip->driver_status === 'driver_cancelled')
                                    <span class="badge-status badge-cancelled-driver">
                                        <i class="fas fa-times-circle"></i> Cancelled
                                    </span>
                                @endif
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($trip->driver_status === 'pending_driver')
                                    <div class="d-flex gap-1 flex-wrap">
                                        <button class="btn-tbl-confirm" onclick="driverConfirmTrip({{ $trip->id }}, '{{ addslashes($trip->route_name) }}')">
                                            <i class="fas fa-check"></i> Confirm
                                        </button>
                                        <button class="btn-tbl-cancel" onclick="driverCancelTrip({{ $trip->id }}, '{{ addslashes($trip->route_name) }}')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                @else
                                    <button class="btn-view-passengers" onclick="loadPassengers({{ $trip->id }})">
                                        <i class="fas fa-users"></i> View Passengers
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ===== PAST TRIPS ===== --}}
        <div class="tab-section" id="tab-past">
            @if($pastTrips->isEmpty())
                <div class="empty-state">
                    <div><i class="fas fa-history"></i></div>
                    <p>No past trips</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="trip-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Route / Destination</th>
                            <th>Date</th>
                            <th>Departure</th>
                            <th>Passengers</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pastTrips as $i => $trip)
                        <tr onclick="loadPassengers({{ $trip->id }})">
                            <td class="row-num">{{ $i + 1 }}</td>
                            <td>
                                <span class="badge-status badge-past-label">
                                    <i class="fas fa-history"></i> Past
                                </span>
                            </td>
                            <td>
                                <div class="route-main" style="color:#6b7280;">{{ $trip->route_name }}</div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}</td>
                            <td>{{ $trip->confirmed_count }}</td>
                            <td onclick="event.stopPropagation()">
                                <button class="btn-view-passengers" onclick="loadPassengers({{ $trip->id }})">
                                    <i class="fas fa-users"></i> View Passengers
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>{{-- end overview-card --}}
    @endif

</div>

{{-- Driver Confirm Trip Modal --}}
<div class="modal fade" id="driverConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);color:#fff;border-bottom:none;">
                <h5 class="modal-title" style="font-weight:800;"><i class="fas fa-check-circle me-2"></i>Confirm Trip</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:0.88rem;color:#333;">Confirm that your van is <strong>available</strong> for:</p>
                <p style="font-weight:700;color:#1b3a5c;" id="driverConfirmTripName"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Back</button>
                <button type="button" class="btn btn-success btn-sm" id="driverConfirmSubmitBtn"><i class="fas fa-check me-1"></i>Yes, I'm Available</button>
            </div>
        </div>
    </div>
</div>

{{-- Driver Cancel Trip Modal --}}
<div class="modal fade" id="driverCancelModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#e74a3b,#c0392b);color:#fff;border-bottom:none;">
                <h5 class="modal-title" style="font-weight:800;"><i class="fas fa-times-circle me-2"></i>Cancel Trip</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:0.88rem;color:#333;">Cancel trip: <strong id="driverCancelTripName"></strong>?</p>
                <div class="mb-2">
                    <label style="font-size:0.8rem;font-weight:700;color:#555;">Reason (optional)</label>
                    <textarea id="driverCancelReason" class="form-control form-control-sm" rows="2" placeholder="e.g. Van not available, mechanical issue...">Van not available</textarea>
                </div>
                <p style="font-size:0.78rem;color:#e74a3b;"><i class="fas fa-exclamation-triangle me-1"></i>This will notify the admin and the user.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Back</button>
                <button type="button" class="btn btn-danger btn-sm" id="driverCancelSubmitBtn"><i class="fas fa-times me-1"></i>Cancel Trip</button>
            </div>
        </div>
    </div>
</div>

{{-- Passenger Modal --}}
<div class="modal fade" id="passengerModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #1b3a5c, #2c5282); color:#fff;">
                <div>
                    <h5 class="modal-title mb-0" id="modalTripTitle">Passengers</h5>
                    <small id="modalTripMeta" style="opacity:0.8;"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="px-3 pt-3 pb-2 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background:#f8f9fa; border-bottom:1px solid #e0e0e0;">
                    <span class="fw-bold text-muted small" id="passengerCount">Loading...</span>
                    <div class="d-flex gap-2" id="tripActionBtns" style="display:none !important;">
                        <button class="btn btn-success btn-sm" id="completeAllBtn" onclick="completeAllPassengers()" style="border-radius:8px; display:none;">
                            <i class="fas fa-check-double me-1"></i> Complete All
                        </button>
                        <button class="btn btn-danger btn-sm" id="cancelTripBtn" onclick="showCancelTripModal()" style="border-radius:8px; display:none;">
                            <i class="fas fa-ban me-1"></i> Cancel Trip (Van Unavailable)
                        </button>
                    </div>
                </div>
                <div id="passengerLoading" class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <div class="mt-2 text-muted">Loading passengers...</div>
                </div>
                <div id="passengerTableWrapper" style="display:none;">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f0f4f8;">
                                <tr>
                                    <th style="font-size:0.82rem; color:#666; font-weight:700; padding:12px 16px;">#</th>
                                    <th style="font-size:0.82rem; color:#666; font-weight:700;">Passenger</th>
                                    <th style="font-size:0.82rem; color:#666; font-weight:700;">Drop-off</th>
                                    <th style="font-size:0.82rem; color:#666; font-weight:700;">Seats</th>
                                    <th style="font-size:0.82rem; color:#666; font-weight:700;">Status</th>
                                    <th style="font-size:0.82rem; color:#666; font-weight:700;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="passengerTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div id="noPassengers" class="text-center py-5 text-muted" style="display:none;">
                    <i class="fas fa-users fa-3x mb-3 opacity-50"></i>
                    <div class="fw-bold">No passengers booked for this trip</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentTripId = null;
    const passengerModal = new bootstrap.Modal(document.getElementById('passengerModal'));

    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-section').forEach(s => s.classList.remove('active'));
        document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + tab).classList.add('active');
        btn.classList.add('active');
    }

    function loadPassengers(tripId) {
        currentTripId = tripId;
        document.getElementById('passengerLoading').style.display = 'block';
        document.getElementById('passengerTableWrapper').style.display = 'none';
        document.getElementById('noPassengers').style.display = 'none';
        document.getElementById('completeAllBtn').style.display = 'none';
        document.getElementById('cancelTripBtn').style.display = 'none';
        document.getElementById('passengerCount').textContent = 'Loading...';
        document.getElementById('modalTripTitle').textContent = 'Passengers';
        document.getElementById('modalTripMeta').textContent = '';
        passengerModal.show();

        fetch(`/driver/trips/${tripId}/passengers`, {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('passengerLoading').style.display = 'none';
            const trip = data.trip;
            document.getElementById('modalTripTitle').textContent = trip.route_name;
            document.getElementById('modalTripMeta').textContent =
                formatDate(trip.trip_date) + ' · ' + formatTime(trip.departure_time) + ' · ' + trip.van_model;
            const bookings = data.bookings;
            document.getElementById('passengerCount').textContent = bookings.length + ' passenger(s)';
            if (bookings.length === 0) {
                document.getElementById('noPassengers').style.display = 'block';
                return;
            }
            const hasActive = bookings.some(b => b.status === 'Pending' || b.status === 'Confirmed');
            if (hasActive) {
                document.getElementById('completeAllBtn').style.display = 'inline-block';
                document.getElementById('cancelTripBtn').style.display = 'inline-block';
                document.getElementById('tripActionBtns').style.display = 'flex';
            }
            document.getElementById('passengerTableWrapper').style.display = 'block';
            const tbody = document.getElementById('passengerTableBody');
            tbody.innerHTML = bookings.map((b, i) => `
                <tr class="passenger-row" id="row-${b.id}">
                    <td style="padding:12px 16px; color:#999; font-size:0.85rem;">${i+1}</td>
                    <td>
                        <div style="font-weight:700; color:#1b3a5c; font-size:0.9rem;">${b.passenger_name}</div>
                        <div style="font-size:0.78rem; color:#888;">${b.passenger_pos || b.passenger_email}</div>
                    </td>
                    <td style="font-size:0.85rem; color:#555;">${b.dropoff_location}</td>
                    <td style="font-size:0.85rem; text-align:center;">${b.seats_requested}</td>
                    <td><span class="status-pill ${getPillClass(b.status)}" id="status-${b.id}">${b.status}</span></td>
                    <td>${getActionButtons(b)}</td>
                </tr>
            `).join('');
        })
        .catch(err => {
            document.getElementById('passengerLoading').style.display = 'none';
            document.getElementById('passengerCount').textContent = 'Error loading passengers';
            console.error(err);
        });
    }

    function getActionButtons(b) {
        if (b.status === 'Completed' || b.status === 'Cancelled') {
            return `<span style="font-size:0.78rem; color:#aaa;">No action</span>`;
        }
        return `
            <div class="d-flex gap-1 flex-wrap">
                ${b.status !== 'Confirmed' ? `<button class="btn-confirm-booking" onclick="updateStatus(${b.id}, 'Confirmed')"><i class="fas fa-check me-1"></i>Confirm</button>` : ''}
                <button class="btn-complete" onclick="updateStatus(${b.id}, 'Completed')"><i class="fas fa-check-double me-1"></i>Done</button>
                <button class="btn-cancel-booking" onclick="updateStatus(${b.id}, 'Cancelled')"><i class="fas fa-times me-1"></i>Cancel</button>
            </div>
        `;
    }

    function getPillClass(status) {
        const map = { Pending: 'pill-pending', Confirmed: 'pill-confirmed', Completed: 'pill-completed', Cancelled: 'pill-cancelled' };
        return map[status] || 'pill-pending';
    }

    function updateStatus(bookingId, status) {
        const label = status === 'Completed' ? 'mark as completed' : status === 'Cancelled' ? 'cancel' : 'confirm';
        if (!confirm(`Are you sure you want to ${label} this booking?`)) return;
        fetch(`/driver/bookings/${bookingId}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ status: status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const pill = document.getElementById('status-' + bookingId);
                if (pill) { pill.className = 'status-pill ' + getPillClass(data.status); pill.textContent = data.status; }
                const row = document.getElementById('row-' + bookingId);
                if (row) { const lastCell = row.querySelector('td:last-child'); if (lastCell) lastCell.innerHTML = `<span style="font-size:0.78rem; color:#aaa;">No action</span>`; }
                showToast(data.message, 'success');
            } else { showToast(data.error || 'Error updating status', 'danger'); }
        })
        .catch(() => showToast('Network error', 'danger'));
    }

    function completeAllPassengers() {
        if (!confirm('Mark ALL passengers on this trip as Completed?')) return;
        fetch(`/driver/trips/${currentTripId}/complete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) { showToast(data.message, 'success'); setTimeout(() => loadPassengers(currentTripId), 500); }
            else { showToast(data.error || 'Error', 'danger'); }
        })
        .catch(() => showToast('Network error', 'danger'));
    }

    function formatDate(dateStr) {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatTime(timeStr) {
        const [h, m] = timeStr.split(':');
        const d = new Date(); d.setHours(parseInt(h), parseInt(m));
        return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }

    function showCancelTripModal() {
        const reason = prompt('Reason for cancelling the trip (optional):', 'Van not available');
        if (reason === null) return;
        cancelTrip(reason || 'Van not available');
    }

    function cancelTrip(reason) {
        if (!confirm('Cancel this entire trip? All passenger bookings will be marked as Cancelled.')) return;
        fetch(`/driver/trips/${currentTripId}/cancel`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ reason: reason })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                document.getElementById('completeAllBtn').style.display = 'none';
                document.getElementById('cancelTripBtn').style.display = 'none';
                setTimeout(() => loadPassengers(currentTripId), 600);
            } else { showToast(data.error || 'Error cancelling trip', 'danger'); }
        })
        .catch(() => showToast('Network error', 'danger'));
    }

    // Van availability toggle
    var vanId = {{ $van ? $van->id : 'null' }};
    var vanIsAvailable = {{ $van && $van->is_available ? 'true' : 'false' }};

    function toggleVanAvailability() {
        if (!vanId) return;
        var btn = document.getElementById('vanAvailBtn');
        btn.disabled = true;
        fetch('/driver/van/toggle-availability', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.success !== undefined || data.is_available !== undefined) {
                vanIsAvailable = data.is_available;
                document.getElementById('vanAvailLabel').textContent = vanIsAvailable ? 'Available' : 'Not Available';
                btn.style.background = vanIsAvailable ? '#1cc88a' : '#e74a3b';
                btn.querySelector('i').className = 'fas ' + (vanIsAvailable ? 'fa-check-circle' : 'fa-times-circle') + ' me-1';
                showToast(vanIsAvailable ? 'Van marked as Available' : 'Van marked as Not Available', vanIsAvailable ? 'success' : 'danger');
            } else { showToast('Error updating availability', 'danger'); }
        })
        .catch(() => { btn.disabled = false; showToast('Network error', 'danger'); });
    }

    // Driver confirm/cancel trip modals
    let driverActionTripId = null;

    function driverConfirmTrip(tripId, tripName) {
        driverActionTripId = tripId;
        document.getElementById('driverConfirmTripName').textContent = tripName;
        new bootstrap.Modal(document.getElementById('driverConfirmModal')).show();
    }

    function takeOff(tripId) {
        if (!confirm('Mark this van as having taken off? The administrator will be notified.')) return;
        fetch('/driver/trips/' + tripId + '/takeoff', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({})
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Takeoff recorded.', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.error || 'Unable to record takeoff.', 'danger');
            }
        })
        .catch(() => showToast('Network error', 'danger'));
    }

    function driverCancelTrip(tripId, tripName) {
        driverActionTripId = tripId;
        document.getElementById('driverCancelTripName').textContent = tripName;
        new bootstrap.Modal(document.getElementById('driverCancelModal')).show();
    }

    document.getElementById('driverConfirmSubmitBtn').addEventListener('click', function() {
        if (!driverActionTripId) return;
        var btn = this; btn.disabled = true;
        fetch('/driver/trips/' + driverActionTripId + '/driver-confirm', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ action: 'confirm' })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            bootstrap.Modal.getInstance(document.getElementById('driverConfirmModal')).hide();
            if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
            else { showToast(data.error || 'Error', 'danger'); }
        })
        .catch(() => { btn.disabled = false; showToast('Network error', 'danger'); });
    });

    document.getElementById('driverCancelSubmitBtn').addEventListener('click', function() {
        if (!driverActionTripId) return;
        var btn = this; btn.disabled = true;
        var reason = document.getElementById('driverCancelReason').value || 'Van not available';
        fetch('/driver/trips/' + driverActionTripId + '/driver-confirm', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ action: 'cancel', reason: reason })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            bootstrap.Modal.getInstance(document.getElementById('driverCancelModal')).hide();
            if (data.success) { showToast(data.message, 'success'); setTimeout(() => location.reload(), 800); }
            else { showToast(data.error || 'Error', 'danger'); }
        })
        .catch(() => { btn.disabled = false; showToast('Network error', 'danger'); });
    });

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; bottom: 20px; right: 20px; z-index: 9999;
            background: ${type === 'success' ? '#1cc88a' : '#e74a3b'};
            color: white; padding: 12px 20px; border-radius: 10px;
            font-weight: 700; font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
</script>
</body>
</html>

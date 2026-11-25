<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Van Service - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f2f8; font-family: 'Nunito', sans-serif; margin: 0; padding: 0; }

        /* Navbar */
        .top-navbar {
            background: linear-gradient(90deg, #1a1f36, #2d3561);
            padding: 0.65rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
            position: sticky; top: 0; z-index: 1000;
        }
        .top-navbar .brand { font-size: 1.1rem; font-weight: 800; color: #fff; text-decoration: none; }
        .top-navbar .brand i { color: #4e73df; }

        /* Page header */
        .page-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #fff; padding: 1.5rem 2rem 1.25rem;
        }
        .page-header h2 { font-size: 1.4rem; font-weight: 800; margin: 0; }
        .page-header p { margin: 0.2rem 0 0; opacity: 0.85; font-size: 0.88rem; }

        /* Stats row */
        .stat-card {
            background: #fff; border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            display: flex; align-items: center; gap: 1rem;
        }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .stat-label { font-size: 0.75rem; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .stat-value { font-size: 1.5rem; font-weight: 800; color: #2d3748; line-height: 1; }

        /* Section card */
        .section-card {
            background: #fff; border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            margin-bottom: 1.5rem; overflow: hidden;
        }
        .section-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .section-card-header h5 {
            margin: 0; font-weight: 800; font-size: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
        }

        /* Tables */
        .dash-table { width: 100%; border-collapse: collapse; }
        .dash-table thead th {
            background: #f8f9fc; color: #888;
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.06em;
            padding: 0.7rem 1rem; border-bottom: 2px solid #eee;
            white-space: nowrap;
        }
        .dash-table tbody tr {
            border-bottom: 1px solid #f5f5f5;
            transition: background .12s;
            cursor: pointer;
        }
        .dash-table tbody tr:hover { background: #f0f4ff; }
        .dash-table tbody tr:last-child { border-bottom: none; }
        .dash-table tbody td {
            padding: 0.75rem 1rem;
            font-size: 0.88rem; color: #333; vertical-align: middle;
        }
        .dash-table tbody td.muted { color: #888; }

        /* Badges */
        .badge-pill-custom {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 0.25rem 0.7rem; border-radius: 2rem;
            font-size: 0.72rem; font-weight: 700; white-space: nowrap;
        }
        .badge-available   { background: #d1f5e0; color: #155724; }
        .badge-full        { background: #f8d7da; color: #721c24; }
        .badge-pending-req { background: #fff3cd; color: #856404; }
        .badge-approved    { background: #d1f5e0; color: #155724; }
        .badge-rejected    { background: #f8d7da; color: #721c24; }
        .badge-Pending, .badge-pending     { background: #fff3cd; color: #856404; }
        .badge-Confirmed, .badge-confirmed   { background: #d1f5e0; color: #155724; }
        .badge-Completed, .badge-completed   { background: #cce5ff; color: #004085; }
        .badge-Cancelled, .badge-cancelled   { background: #f8d7da; color: #721c24; }

        /* Route label */
        .route-label { font-weight: 700; color: #2d3748; }
        .route-sub { font-size: 0.78rem; color: #888; margin-top: 2px; }

        /* Empty state */
        .empty-state { text-align: center; padding: 2.5rem 1rem; color: #aaa; }
        .empty-state i { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; }
        .empty-state p { font-size: 0.88rem; margin: 0; }

        /* Notification bell */
        .notif-btn {
            background: none; border: none; color: #fff;
            font-size: 1.15rem; position: relative; padding: 0 4px;
            cursor: pointer;
        }
        .notif-dot {
            position: absolute; top: -4px; right: -4px;
            width: 16px; height: 16px;
            background: #e74a3b; border-radius: 50%;
            font-size: 0.6rem; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
        }

        /* Action button in table */
        .btn-book-sm {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff; border: none; border-radius: 0.4rem;
            padding: 0.3rem 0.75rem; font-size: 0.78rem; font-weight: 700;
            white-space: nowrap;
        }
        .btn-book-sm:hover { opacity: 0.9; color: #fff; }
        .btn-book-sm:disabled { opacity: 0.5; cursor: not-allowed; }

        /* Tab nav */
        .dash-tabs { display: flex; gap: 0; border-bottom: 2px solid #eee; }
        .dash-tab {
            padding: 0.65rem 1.25rem; font-size: 0.88rem; font-weight: 700;
            color: #888; cursor: pointer; border-bottom: 2px solid transparent;
            margin-bottom: -2px; transition: color .15s, border-color .15s;
            background: none; border-top: none; border-left: none; border-right: none;
        }
        .dash-tab.active { color: #4e73df; border-bottom-color: #4e73df; }
        .dash-tab:hover:not(.active) { color: #555; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="top-navbar">
    <a class="brand" href="#"><i class="fas fa-shuttle-van mr-2"></i>VanService</a>
    <div class="d-flex align-items-center" style="gap:0.5rem;">
        <!-- Notification Bell -->
        <div class="dropdown mr-1">
            <button class="notif-btn dropdown-toggle" id="notifBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background:none;border:none;">
                <i class="fas fa-bell"></i>
                @if($unreadNotifCount > 0)
                    <span class="notif-dot">{{ $unreadNotifCount }}</span>
                @endif
            </button>
            <div class="dropdown-menu dropdown-menu-right shadow" style="width:320px;max-height:380px;overflow-y:auto;border-radius:0.75rem;" aria-labelledby="notifBtn">
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <strong style="font-size:0.88rem;"><i class="fas fa-bell mr-1 text-primary"></i>Notifications</strong>
                    @if($unreadNotifCount > 0)
                        <a href="#" onclick="return markAllNotifsRead()" class="text-muted" style="font-size:0.75rem;">Mark all read</a>
                    @endif
                </div>
                @forelse($userNotifications as $notif)
                    @php
                        $userNotifStyles = match($notif->type) {
                            'trip_request_approved' => ['#16a34a', 'fa-check-circle'],
                            'trip_request_declined', 'trip_cancelled' => ['#e74a3b', 'fa-times-circle'],
                            default => ['#4e73df', 'fa-info-circle'],
                        };
                    @endphp
                    <div class="dropdown-item py-2 px-3 notif-item {{ $notif->is_read ? '' : 'bg-light' }}" style="white-space:normal;cursor:pointer;border-bottom:1px solid #f0f0f0;" onclick="markNotifRead({{ $notif->id }}, this)">
                        <div class="d-flex align-items-start">
                            <span class="mr-2 mt-1" style="color:{{ $userNotifStyles[0] }};">
                                <i class="fas {{ $userNotifStyles[1] }}"></i>
                            </span>
                            <div style="flex:1;">
                                <div style="font-weight:{{ $notif->is_read ? '400' : '700' }};font-size:0.82rem;color:#333;">{{ $notif->title }}</div>
                                <div style="font-size:0.78rem;color:#666;margin-top:2px;">{{ $notif->message }}</div>
                                <div style="font-size:0.72rem;color:#aaa;margin-top:3px;">{{ $notif->created_at->diffForHumans() }}</div>
                            </div>
                            @if(!$notif->is_read)
                                <span style="width:8px;height:8px;background:#e74a3b;border-radius:50%;display:inline-block;margin-top:4px;flex-shrink:0;"></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4" style="font-size:0.85rem;">
                        <i class="fas fa-bell-slash fa-2x mb-2 d-block"></i>No notifications yet
                    </div>
                @endforelse
            </div>
        </div>
        <span class="text-white d-none d-md-inline" style="font-size:0.85rem;opacity:.85;">
            <i class="fas fa-user-circle mr-1"></i>{{ auth()->user()->name }}
        </span>
        <a href="{{ route('user.trip_requests.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-paper-plane mr-1"></i>Request Trip
        </a>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt mr-1"></i>Logout
            </button>
        </form>
    </div>
</nav>

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</h2>
    <p>Welcome back, <strong>{{ auth()->user()->name }}</strong>! View available trips and track your requests below.</p>
</div>

<div class="container-fluid px-3 px-md-4 py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:.75rem;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:.75rem;">
            {{ session('error') }}<button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#eef2ff;">
                    <i class="fas fa-route" style="color:#4e73df;"></i>
                </div>
                <div>
                    <div class="stat-label">Available Trips</div>
                    <div class="stat-value">{{ $trips->count() + ($approvedRequests ?? collect([]))->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f8f5;">
                    <i class="fas fa-calendar-check" style="color:#1cc88a;"></i>
                </div>
                <div>
                    <div class="stat-label">My Bookings</div>
                    <div class="stat-value">{{ $myBookings->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff8e1;">
                    <i class="fas fa-paper-plane" style="color:#f6c23e;"></i>
                </div>
                <div>
                    <div class="stat-label">My Requests</div>
                    <div class="stat-value">{{ $myRequests->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fde8e8;">
                    <i class="fas fa-clock" style="color:#e74a3b;"></i>
                </div>
                <div>
                    <div class="stat-label">Pending</div>
                    <div class="stat-value">{{ $myBookings->where('status','Pending')->count() + $myRequests->where('status','pending')->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card with Tabs -->
    <div class="section-card">
        <div class="section-card-header" style="flex-direction:column;align-items:flex-start;gap:0;padding-bottom:0;">
            <div class="d-flex justify-content-between align-items-center w-100 pb-2">
                <h5 style="margin:0;font-weight:800;font-size:1rem;">
                    <i class="fas fa-table mr-2 text-primary"></i>Trip Overview
                </h5>
                <a href="{{ route('user.trip_requests.create') }}" class="btn btn-sm btn-primary" style="border-radius:0.5rem;font-size:0.8rem;">
                    <i class="fas fa-plus mr-1"></i>New Request
                </a>
            </div>
            <!-- Tabs -->
            <div class="dash-tabs w-100">
                <button class="dash-tab active" onclick="switchTab('tab-trips', this)">
                    <i class="fas fa-route mr-1"></i>Available Trips
                    <span class="badge badge-primary ml-1" style="border-radius:20px;font-size:0.65rem;">{{ $trips->count() + ($approvedRequests ?? collect([]))->count() }}</span>
                </button>
                <button class="dash-tab" onclick="switchTab('tab-requests', this)">
                    <i class="fas fa-paper-plane mr-1"></i>My Requests
                    <span class="badge badge-warning ml-1" style="border-radius:20px;font-size:0.65rem;">{{ $myRequests->count() }}</span>
                </button>
                <button class="dash-tab" onclick="switchTab('tab-bookings', this)">
                    <i class="fas fa-calendar-check mr-1"></i>My Bookings
                    <span class="badge badge-success ml-1" style="border-radius:20px;font-size:0.65rem;">{{ $myBookings->count() }}</span>
                </button>
                <button class="dash-tab" onclick="switchTab('tab-history', this)">
                    <i class="fas fa-history mr-1"></i>Booking History
                    <span class="badge badge-secondary ml-1" style="border-radius:20px;font-size:0.65rem;">{{ $bookingHistory->count() }}</span>
                </button>
            </div>
        </div>

        <!-- Tab: Available Trips -->
        <div id="tab-trips" class="tab-pane active">
            @php
                $approvedRequests = $approvedRequests ?? collect([]);
                $totalAvailable = $trips->count() + $approvedRequests->count();
            @endphp
            @if($totalAvailable === 0)
                <div class="empty-state">
                    <i class="fas fa-route text-muted"></i>
                    <p>No trips available at the moment.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Route / Destination</th>
                            <th>Date</th>
                            <th>Purpose</th>
                            <th>Passengers</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowNum = 1; @endphp
                        {{-- Driver/Admin confirmed trips --}}
                        @foreach($trips as $trip)
                        <tr onclick="openBookModal({{ $trip->id }}, '{{ addslashes($trip->route_name) }}', '{{ addslashes($trip->pickup_location ?? '') }}', '{{ addslashes($trip->dropoff_location ?? '') }}', '{{ \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}', '{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}', {{ $trip->availableSeats() }})" title="Click to book this trip" style="cursor:pointer;">
                            <td class="muted">{{ $rowNum++ }}</td>
                            <td><span class="badge-pill-custom" style="background:#e8f0fe;color:#4e73df;font-size:0.7rem;"><i class="fas fa-bus mr-1"></i>Van Trip</span></td>
                            <td>
                                <div class="route-label">{{ $trip->route_name }}</div>
                                <div class="route-sub">
                                    <i class="fas fa-map-marker-alt mr-1" style="color:#e74a3b;"></i>{{ $trip->pickup_location ?? 'N/A' }}
                                    &rarr; {{ $trip->dropoff_location ?? 'N/A' }}
                                </div>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}</td>
                            <td class="muted">—</td>
                            <td>
                                <span style="font-weight:700;color:{{ $trip->availableSeats() > 0 ? '#1cc88a' : '#e74a3b' }};">
                                    {{ $trip->availableSeats() }}
                                </span>
                                <span class="muted"> / {{ $trip->total_seats }}</span>
                            </td>
                            <td>
                                @if($trip->availableSeats() > 0)
                                    <span class="badge-pill-custom badge-available"><i class="fas fa-check-circle"></i> Open</span>
                                @else
                                    <span class="badge-pill-custom badge-full"><i class="fas fa-ban"></i> Full</span>
                                @endif
                            </td>
                            <td onclick="event.stopPropagation();">
                                @if($trip->availableSeats() > 0)
                                    <button class="btn-book-sm" onclick="openBookModal({{ $trip->id }}, '{{ addslashes($trip->route_name) }}', '{{ addslashes($trip->pickup_location ?? '') }}', '{{ addslashes($trip->dropoff_location ?? '') }}', '{{ \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}', '{{ \Carbon\Carbon::parse($trip->departure_time)->format('h:i A') }}', {{ $trip->availableSeats() }})">
                                        <i class="fas fa-ticket-alt mr-1"></i>Book
                                    </button>
                                @else
                                    <button class="btn-book-sm" disabled>Full</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        {{-- Approved trip requests (visible to all users) --}}
                        @foreach($approvedRequests as $req)
                        <tr onclick="openBookRequestModal({{ $req->id }}, '{{ addslashes($req->request_no) }}', '{{ addslashes($req->destination) }}', '{{ $req->date_of_travel->format('M d, Y') }}', '{{ addslashes($req->purpose) }}', {{ $req->no_of_passengers }})" title="Click to book this trip" style="cursor:pointer;">
                            <td class="muted">{{ $rowNum++ }}</td>
                            <td><span class="badge-pill-custom" style="background:#e8faf0;color:#1cc88a;font-size:0.7rem;"><i class="fas fa-check-circle mr-1"></i>Approved</span></td>
                            <td>
                                <div class="route-label">{{ $req->destination }}</div>
                                <div class="route-sub">
                                    <i class="fas fa-user mr-1" style="color:#4e73df;"></i>Requested by {{ $req->user->name ?? 'User' }}
                                </div>
                            </td>
                            <td>{{ $req->date_of_travel->format('M d, Y') }}</td>
                            <td>{{ $req->purpose }}</td>
                            <td>{{ $req->no_of_passengers }}</td>
                            <td><span class="badge-pill-custom badge-approved"><i class="fas fa-check-circle"></i> Approved</span></td>
                            <td onclick="event.stopPropagation();">
                                <button class="btn-book-sm" onclick="openBookRequestModal({{ $req->id }}, '{{ addslashes($req->request_no) }}', '{{ addslashes($req->destination) }}', '{{ $req->date_of_travel->format('M d, Y') }}', '{{ addslashes($req->purpose) }}', {{ $req->no_of_passengers }})">
                                    <i class="fas fa-ticket-alt mr-1"></i>Book
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Tab: My Requests -->
        <div id="tab-requests" class="tab-pane">
            @if($myRequests->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-paper-plane text-muted"></i>
                    <p>You haven't submitted any trip requests yet.</p>
                    <a href="{{ route('user.trip_requests.create') }}" class="btn btn-primary btn-sm mt-2" style="border-radius:0.5rem;">
                        <i class="fas fa-plus mr-1"></i>Request a Trip
                    </a>
                </div>
            @else
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Request No.</th>
                            <th>Date of Travel</th>
                            <th>Purpose</th>
                            <th>Destination</th>
                            <th>Passengers</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myRequests as $req)
                        <tr onclick="openRequestModal({{ $req->id }})" title="Click to view details">
                            <td><strong style="color:#4e73df;">{{ $req->request_no }}</strong></td>
                            <td>{{ $req->date_of_travel->format('M d, Y') }}</td>
                            <td>{{ $req->purpose }}</td>
                            <td>{{ $req->destination }}</td>
                            <td>{{ $req->no_of_passengers }}</td>
                            <td>
                                @if($req->status === 'approved')
                                    <span class="badge-pill-custom badge-approved"><i class="fas fa-check-circle"></i> Approved</span>
                                @elseif($req->status === 'rejected')
                                    <span class="badge-pill-custom badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span>
                                @elseif($req->status === 'cancelled')
                                    <span class="badge-pill-custom badge-Cancelled"><i class="fas fa-ban"></i> Cancelled</span>
                                    @php
                                        $cancelReason = $req->admin_remarks
                                            ?: ($req->trip && $req->trip->driver_cancel_reason
                                                ? 'Cancelled by driver: ' . $req->trip->driver_cancel_reason
                                                : null);
                                    @endphp
                                    @if($cancelReason)
                                        <div style="font-size:0.7rem;color:#dc2626;margin-top:2px;">
                                            <i class="fas fa-info-circle"></i> {{ $cancelReason }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge-pill-custom badge-pending-req"><i class="fas fa-clock"></i> Pending</span>
                                @endif
                            </td>
                            <td class="muted">{{ $req->created_at->format('M d, Y') }}</td>
                            <td onclick="event.stopPropagation()">
                                @if($req->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;padding:0.25rem 0.6rem;border-radius:0.4rem;" onclick="confirmCancelRequest({{ $req->id }}, '{{ $req->request_no }}')">
                                        <i class="fas fa-times mr-1"></i>Cancel
                                    </button>
                                @else
                                    <span style="color:#ccc;font-size:0.78rem;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Tab: My Bookings -->
        <div id="tab-bookings" class="tab-pane">
            @if($myBookings->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-calendar-times text-muted"></i>
                    <p>No bookings yet. Book a trip from the Available Trips tab.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Req No.</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Seats</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myBookings as $booking)
                        @php
                            // Determine route/destination from either trip or trip request
                            if ($booking->trip) {
                                $routeLabel = $booking->trip->route_name ?? ($booking->trip->pickup_location . ' → ' . $booking->trip->dropoff_location);
                                $bookingDateStr = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : '—';
                                $bookingTimeStr = $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('h:i A') : '—';
                            } elseif ($booking->tripRequest) {
                                $routeLabel = $booking->tripRequest->destination;
                                $bookingDateStr = $booking->tripRequest->date_of_travel ? $booking->tripRequest->date_of_travel->format('M d, Y') : '—';
                                $bookingTimeStr = '—';
                            } else {
                                $routeLabel = $booking->dropoff_location ?? 'N/A';
                                $bookingDateStr = $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') : '—';
                                $bookingTimeStr = '—';
                            }
                        @endphp
                        <tr>
                            <td><strong style="color:#4e73df;">REQ-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>
                                <div class="route-label">{{ $routeLabel }}</div>
                                @if($booking->tripRequest)
                                    <div class="route-sub"><i class="fas fa-check-circle mr-1" style="color:#1cc88a;"></i>Approved Request</div>
                                @endif
                            </td>
                            <td>{{ $bookingDateStr }}</td>
                            <td>{{ $bookingTimeStr }}</td>
                            <td>{{ $booking->seats_requested }}</td>
                            <td>{{ $booking->purpose ?? '—' }}</td>
                            <td>
                                <span class="badge-pill-custom badge-{{ $booking->status }}">
                                    {{ $booking->status }}
                                </span>
                            </td>
                            <td onclick="event.stopPropagation()">
                                @if($booking->status === 'Confirmed')
                                    <a href="{{ route('bookings.ticket', $booking) }}" target="_blank" class="btn btn-sm btn-outline-primary mb-1" style="font-size:0.75rem;padding:0.25rem 0.6rem;border-radius:0.4rem;">
                                        <i class="fas fa-print mr-1"></i>Ticket
                                    </a>
                                @endif
                                @if(in_array($booking->status, ['Pending', 'Confirmed']))
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;padding:0.25rem 0.6rem;border-radius:0.4rem;" onclick="confirmCancelBooking({{ $booking->id }}, 'REQ-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}')">
                                        <i class="fas fa-times mr-1"></i>Cancel
                                    </button>
                                @else
                                    <span style="color:#ccc;font-size:0.78rem;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        <!-- Tab: Booking History -->
        <div id="tab-history" class="tab-pane">
            @if($bookingHistory->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-history text-muted"></i>
                    <p>No completed or cancelled bookings yet.</p>
                </div>
            @else
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Booking No.</th>
                            <th>Route</th>
                            <th>Travel Date</th>
                            <th>Seats</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookingHistory as $booking)
                        @php
                            $historyRoute = $booking->trip?->route_name
                                ?? $booking->tripRequest?->destination
                                ?? $booking->dropoff_location
                                ?? 'N/A';
                            $historyDate = $booking->trip?->trip_date
                                ?? $booking->tripRequest?->date_of_travel
                                ?? $booking->booking_date;
                        @endphp
                        <tr>
                            <td><strong style="color:#6b7280;">REQ-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>
                                <div class="route-label">{{ $historyRoute }}</div>
                                @if($booking->trip?->dropoff_location)
                                    <div class="route-sub">{{ $booking->trip->pickup_location }} → {{ $booking->trip->dropoff_location }}</div>
                                @endif
                            </td>
                            <td>{{ $historyDate ? \Carbon\Carbon::parse($historyDate)->format('M d, Y') : '—' }}</td>
                            <td>{{ $booking->seats_requested }}</td>
                            <td>{{ $booking->purpose ?? '—' }}</td>
                            <td><span class="badge-pill-custom badge-{{ $booking->status }}">{{ $booking->status }}</span></td>
                            <td>
                                @if($booking->status === 'Completed')
                                    <a href="{{ route('bookings.ticket', $booking) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;padding:0.25rem 0.6rem;border-radius:0.4rem;">
                                        <i class="fas fa-print mr-1"></i>Ticket
                                    </a>
                                @else
                                    <span style="color:#ccc;font-size:0.78rem;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Cancel Booking Confirmation Modal -->
<div class="modal fade" id="cancelBookingModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#e74a3b,#c0392b);color:#fff;border-radius:1rem 1rem 0 0;">
                <h5 class="modal-title mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Cancel Booking</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center py-3">
                <p style="font-size:0.9rem;margin-bottom:0.5rem;">Are you sure you want to cancel</p>
                <strong id="cancelBookingLabel" style="color:#e74a3b;"></strong>
                <p class="text-muted mt-2" style="font-size:0.82rem;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pb-3 justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Keep It</button>
                <form id="cancelBookingForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times mr-1"></i>Yes, Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Request Confirmation Modal -->
<div class="modal fade" id="cancelRequestModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#e74a3b,#c0392b);color:#fff;border-radius:1rem 1rem 0 0;">
                <h5 class="modal-title mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Cancel Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center py-3">
                <p style="font-size:0.9rem;margin-bottom:0.5rem;">Are you sure you want to cancel</p>
                <strong id="cancelRequestLabel" style="color:#e74a3b;"></strong>
                <p class="text-muted mt-2" style="font-size:0.82rem;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pb-3 justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Keep It</button>
                <form id="cancelRequestForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times mr-1"></i>Yes, Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Book Trip Modal -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;border-radius:1rem 1rem 0 0;">
                <div>
                    <h5 class="modal-title mb-0"><i class="fas fa-ticket-alt mr-2"></i>Book a Trip</h5>
                    <small id="modal-route-name" style="opacity:0.85;font-size:0.82rem;"></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('bookings.store') }}" id="tripBookingForm">
                @csrf
                <input type="hidden" name="trip_id" id="modal-trip-id">
                <div class="modal-body px-4 py-3">
                    <div class="alert alert-primary py-2 mb-3" style="border-radius:0.5rem;font-size:0.85rem;">
                        <i class="fas fa-info-circle mr-1"></i><span id="modal-trip-summary"></span>
                    </div>
                    <div id="booking-availability-prompt" class="alert py-2 mb-3" role="status" style="display:none;border-radius:0.5rem;font-size:0.85rem;"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight:600;font-size:0.88rem;"><i class="fas fa-hashtag mr-1 text-muted"></i>Request No.</label>
                                <input type="text" class="form-control" value="Auto-generated upon submission" readonly style="background:#f8f9fc;color:#888;border-radius:0.5rem;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight:600;font-size:0.88rem;"><i class="fas fa-calendar mr-1 text-success"></i>Date of Travel</label>
                                <input type="text" id="modal-date-display" class="form-control" readonly style="background:#f8f9fc;color:#555;border-radius:0.5rem;">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight:600;font-size:0.88rem;"><i class="fas fa-tag mr-1 text-warning"></i>Purpose <span class="text-danger">*</span></label>
                                <input type="text" name="purpose" class="form-control" placeholder="e.g. Official Business, Field Trip..." required style="border-radius:0.5rem;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-weight:600;font-size:0.88rem;"><i class="fas fa-users mr-1 text-info"></i>Number of Passengers <span class="text-danger">*</span></label>
                                <input type="number" name="seats_requested" class="form-control" min="1" value="1" id="modal-seats-input" required style="border-radius:0.5rem;">
                                <small class="text-muted">Available: <strong id="modal-avail-seats" class="text-success"></strong></small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600;font-size:0.88rem;"><i class="fas fa-map-marker-alt mr-1 text-danger"></i>Drop-off Point <span class="text-danger">*</span></label>
                        <input type="text" name="dropoff_location" id="modal-dropoff-input" class="form-control" placeholder="Type the complete drop-off address..." required maxlength="500" style="border-radius:0.5rem;">
                        <small class="text-muted">Type the exact address where you want to be dropped off. This address will appear below your name on the trip ticket.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="trip-book-submit" class="btn btn-primary px-4" style="border-radius:0.5rem;font-weight:700;">
                        <i class="fas fa-check mr-1"></i>Submit Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Book Trip Request Modal -->
<div class="modal fade" id="bookReqModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);color:#fff;border-radius:1rem 1rem 0 0;">
                <h5 class="modal-title mb-0"><i class="fas fa-ticket-alt mr-2"></i>Book This Trip</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('bookings.store') }}" id="requestBookingForm">
                @csrf
                <input type="hidden" name="trip_request_id" id="bookReq-id">
                <input type="hidden" name="trip_id" value="">
                <input type="hidden" name="seats_requested" value="1">
                <div class="modal-body px-4 py-3">
                    <!-- Trip details read-only -->
                    <table style="width:100%;border-collapse:collapse;margin-bottom:1rem;">
                        <tr>
                            <td style="padding:0.45rem 0;color:#888;font-size:0.8rem;font-weight:700;width:140px;">REQUEST NO.</td>
                            <td style="padding:0.45rem 0;font-weight:800;color:#1cc88a;" id="bookReq-reqno"></td>
                        </tr>
                        <tr>
                            <td style="padding:0.45rem 0;color:#888;font-size:0.8rem;font-weight:700;">DATE OF TRAVEL</td>
                            <td style="padding:0.45rem 0;font-weight:600;" id="bookReq-date-display"></td>
                        </tr>
                        <tr>
                            <td style="padding:0.45rem 0;color:#888;font-size:0.8rem;font-weight:700;">DESTINATION</td>
                            <td style="padding:0.45rem 0;font-weight:600;" id="bookReq-dest-display"></td>
                        </tr>
                        <tr>
                            <td style="padding:0.45rem 0;color:#888;font-size:0.8rem;font-weight:700;">TRIP PURPOSE</td>
                            <td style="padding:0.45rem 0;font-weight:600;" id="bookReq-purpose-display"></td>
                        </tr>
                        <tr>
                            <td style="padding:0.45rem 0;color:#888;font-size:0.8rem;font-weight:700;">NO. OF PASSENGERS</td>
                            <td style="padding:0.45rem 0;font-weight:600;" id="bookReq-pax-display"></td>
                        </tr>
                        <tr>
                            <td style="padding:0.45rem 0;color:#888;font-size:0.8rem;font-weight:700;">STATUS</td>
                            <td style="padding:0.45rem 0;"><span style="background:#d1f5e0;color:#155724;padding:0.2rem 0.65rem;border-radius:2rem;font-size:0.78rem;font-weight:700;"><i class="fas fa-check-circle mr-1"></i>Approved</span></td>
                        </tr>
                    </table>
                    <div id="request-booking-availability-prompt" class="alert py-2 mb-3" role="status" style="display:none;border-radius:0.5rem;font-size:0.85rem;"></div>
                    <hr style="margin:0.75rem 0;">
                    <!-- Only user input -->
                    <div class="form-group mb-0">
                        <label style="font-weight:700;font-size:0.88rem;"><i class="fas fa-align-left mr-1 text-secondary"></i>Your Purpose / Note <span class="text-danger">*</span></label>
                        <input type="text" name="purpose" class="form-control mt-1" placeholder="e.g. Joining this educational tour..." required style="border-radius:0.5rem;">
                    </div>
                    <div class="form-group mt-3 mb-0">
                        <label style="font-weight:700;font-size:0.88rem;"><i class="fas fa-map-marker-alt mr-1 text-danger"></i>Your Drop-off Address <span class="text-danger">*</span></label>
                        <input type="text" name="dropoff_location" class="form-control mt-1" placeholder="Type the complete drop-off address..." required maxlength="500" style="border-radius:0.5rem;">
                        <small class="text-muted">This address will appear below your name on the trip ticket.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" id="request-book-submit" class="btn btn-success px-4" style="border-radius:0.5rem;font-weight:700;">
                        <i class="fas fa-check mr-1"></i>Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Detail Modal -->
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;">
                <h5 class="modal-title"><i class="fas fa-file-alt mr-2"></i>Trip Request Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="requestModalBody">
                <div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Request data for JS -->
<script>
var REQUEST_DATA = {
    @foreach($myRequests as $req)
    {{ $req->id }}: {
        request_no: '{{ $req->request_no }}',
        date: '{{ $req->date_of_travel->format('F d, Y') }}',
        purpose: '{{ addslashes($req->purpose) }}',
        destination: '{{ addslashes($req->destination) }}',
        no_of_passengers: {{ $req->no_of_passengers }},
        passenger_names: {!! json_encode($req->passenger_names) !!},
        status: '{{ $req->status }}',
        remarks: '{{ addslashes($req->admin_remarks ?? '') }}',
        submitted: '{{ $req->created_at->format('M d, Y h:i A') }}'
    },
    @endforeach
};
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tab switching
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.dash-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    btn.classList.add('active');
}

// Open book modal
function openBookModal(tripId, route, origin, dest, date, time, seats) {
    if (seats <= 0) return;
    setBookingPrompt('booking-availability-prompt', 'Checking vehicle availability…', 'info');
    document.getElementById('trip-book-submit').disabled = true;
    document.getElementById('modal-trip-id').value = tripId;
    document.getElementById('modal-route-name').textContent = route;
    document.getElementById('modal-avail-seats').textContent = seats;
    document.getElementById('modal-seats-input').setAttribute('max', seats);
    document.getElementById('modal-date-display').value = date + ' · ' + time;
    document.getElementById('modal-trip-summary').innerHTML = '<strong>' + route + '</strong> &nbsp;|&nbsp; ' + date + ' at ' + time;
    document.getElementById('modal-dropoff-input').value = '';
    $('#bookModal').modal('show');
    checkBookingAvailability('{{ route('bookings.availability', ['trip' => '__trip__']) }}'.replace('__trip__', tripId), 'booking-availability-prompt', 'trip-book-submit', seats);
}

function setBookingPrompt(elementId, message, state) {
    var prompt = document.getElementById(elementId);
    prompt.style.display = 'block';
    prompt.className = 'alert py-2 mb-3 alert-' + (state === 'allowed' ? 'success' : (state === 'info' ? 'info' : 'warning'));
    prompt.textContent = message;
}

function checkBookingAvailability(url, promptId, buttonId, fallbackSeats) {
    fetch(url, {headers: {'Accept': 'application/json'}})
        .then(function(response) { if (!response.ok) throw new Error('Availability check unavailable'); return response.json(); })
        .then(function(data) {
            var button = document.getElementById(buttonId);
            if (data.booking_allowed) {
                if (typeof data.available_seats !== 'undefined') {
                    document.getElementById('modal-avail-seats').textContent = data.available_seats;
                    document.getElementById('modal-seats-input').setAttribute('max', data.available_seats || fallbackSeats);
                }
                button.disabled = false;
                setBookingPrompt(promptId, 'Vehicle is available. You can submit your booking.', 'allowed');
            } else {
                button.disabled = true;
                setBookingPrompt(promptId, data.reason || 'This vehicle is not available for booking.', 'blocked');
            }
        })
        .catch(function() {
            document.getElementById(buttonId).disabled = true;
            setBookingPrompt(promptId, 'We could not confirm vehicle availability. Please try again.', 'blocked');
        });
}

// Open request detail modal
function openRequestModal(id) {
    var req = REQUEST_DATA[id];
    if (!req) return;
    var statusColor = req.status === 'approved' ? '#155724' : (req.status === 'rejected' ? '#721c24' : '#856404');
    var statusBg = req.status === 'approved' ? '#d1f5e0' : (req.status === 'rejected' ? '#f8d7da' : '#fff3cd');
    var statusIcon = req.status === 'approved' ? 'fa-check-circle' : (req.status === 'rejected' ? 'fa-times-circle' : 'fa-clock');
    var statusLabel = req.status.charAt(0).toUpperCase() + req.status.slice(1);

    var passengerHtml = '';
    if (req.passenger_names && req.passenger_names.length) {
        req.passenger_names.forEach(function(n, i) {
            passengerHtml += '<li style="padding:3px 0;font-size:0.88rem;">' + (i+1) + '. ' + n + '</li>';
        });
    }

    var remarksHtml = req.remarks ? '<div style="background:#fff8e1;border-left:3px solid #ffc107;border-radius:0.4rem;padding:0.6rem 0.85rem;font-size:0.85rem;color:#856404;margin-top:0.75rem;"><i class="fas fa-comment-alt mr-1"></i><strong>Admin Remarks:</strong> ' + req.remarks + '</div>' : '';

    var html = '<table style="width:100%;border-collapse:collapse;">' +
        '<tr><td style="padding:0.5rem 0;color:#888;font-size:0.82rem;font-weight:700;width:140px;">REQUEST NO.</td><td style="padding:0.5rem 0;font-weight:800;color:#4e73df;">' + req.request_no + '</td></tr>' +
        '<tr><td style="padding:0.5rem 0;color:#888;font-size:0.82rem;font-weight:700;">DATE OF TRAVEL</td><td style="padding:0.5rem 0;font-weight:600;">' + req.date + '</td></tr>' +
        '<tr><td style="padding:0.5rem 0;color:#888;font-size:0.82rem;font-weight:700;">PURPOSE</td><td style="padding:0.5rem 0;font-weight:600;">' + req.purpose + '</td></tr>' +
        '<tr><td style="padding:0.5rem 0;color:#888;font-size:0.82rem;font-weight:700;">DESTINATION</td><td style="padding:0.5rem 0;font-weight:600;">' + req.destination + '</td></tr>' +
        '<tr><td style="padding:0.5rem 0;color:#888;font-size:0.82rem;font-weight:700;">PASSENGERS</td><td style="padding:0.5rem 0;font-weight:600;">' + req.no_of_passengers + '</td></tr>' +
        '<tr><td style="padding:0.5rem 0;color:#888;font-size:0.82rem;font-weight:700;vertical-align:top;">NAMES</td><td style="padding:0.5rem 0;"><ul style="margin:0;padding-left:1.2rem;">' + passengerHtml + '</ul></td></tr>' +
        '<tr><td style="padding:0.5rem 0;color:#888;font-size:0.82rem;font-weight:700;">STATUS</td><td style="padding:0.5rem 0;"><span style="background:' + statusBg + ';color:' + statusColor + ';padding:0.25rem 0.75rem;border-radius:2rem;font-size:0.78rem;font-weight:700;"><i class="fas ' + statusIcon + ' mr-1"></i>' + statusLabel + '</span></td></tr>' +
        '<tr><td style="padding:0.5rem 0;color:#888;font-size:0.82rem;font-weight:700;">SUBMITTED</td><td style="padding:0.5rem 0;color:#888;font-size:0.85rem;">' + req.submitted + '</td></tr>' +
        '</table>' + remarksHtml;

    document.getElementById('requestModalBody').innerHTML = html;
    $('#requestModal').modal('show');
}

// Open book request modal
function openBookRequestModal(id, reqNo, destination, date, purpose, passengers) {
    setBookingPrompt('request-booking-availability-prompt', 'Checking vehicle availability…', 'info');
    document.getElementById('request-book-submit').disabled = true;
    document.getElementById('bookReq-id').value = id;
    document.getElementById('bookReq-reqno').textContent = reqNo;
    document.getElementById('bookReq-dest-display').textContent = destination;
    document.getElementById('bookReq-date-display').textContent = date;
    document.getElementById('bookReq-purpose-display').textContent = purpose;
    document.getElementById('bookReq-pax-display').textContent = passengers;
    $('#bookReqModal').modal('show');
    checkBookingAvailability('{{ route('bookings.request-availability', ['tripRequest' => '__request__']) }}'.replace('__request__', id), 'request-booking-availability-prompt', 'request-book-submit');
}

// Cancel booking confirmation
function confirmCancelBooking(id, label) {
    document.getElementById('cancelBookingLabel').textContent = label + '?';
    document.getElementById('cancelBookingForm').action = '/bookings/' + id + '/cancel';
    $('#cancelBookingModal').modal('show');
}

// Cancel request confirmation
function confirmCancelRequest(id, label) {
    document.getElementById('cancelRequestLabel').textContent = label + '?';
    document.getElementById('cancelRequestForm').action = '/trip-requests/' + id + '/cancel';
    $('#cancelRequestModal').modal('show');
}

// Notifications
function markNotifRead(id, el) {
    fetch('/user/notifications/'+id+'/read',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json'}}).then(()=>{el.classList.remove('bg-light');const dot=el.querySelector('span[style*="border-radius:50%"]');if(dot)dot.remove();const title=el.querySelector('div[style*="font-weight"]');if(title)title.style.fontWeight='400';const badge=document.querySelector('.notif-dot');if(badge){const c=parseInt(badge.textContent)-1;if(c<=0)badge.remove();else badge.textContent=c;}});
}
function markAllNotifsRead() {
    fetch('/user/notifications/read-all',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Content-Type':'application/json'}}).then(()=>{document.querySelectorAll('.notif-item').forEach(el=>{el.classList.remove('bg-light');const dot=el.querySelector('span[style*="border-radius:50%"]');if(dot)dot.remove();const title=el.querySelector('div[style*="font-weight"]');if(title)title.style.fontWeight='400';});const badge=document.querySelector('.notif-dot');if(badge)badge.remove();});
    return false;
}
</script>
</body>
</html>

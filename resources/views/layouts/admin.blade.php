<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VanService Admin — @yield('title','Dashboard')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #4e73df;
            --primary-dark: #224abe;
            --success: #1cc88a;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --info: #36b9cc;
        }
        * { box-sizing: border-box; }
        body { background: #f0f2f8; font-family: 'Nunito', sans-serif; margin: 0; }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: linear-gradient(180deg, #1b2a4a 0%, #2c3e6b 100%);
            position: fixed;
            top: 0; left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar-brand {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .sidebar-brand .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--primary), #6f9fff);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff; flex-shrink: 0;
        }
        .sidebar-brand .brand-text h5 { color: #fff; font-weight: 800; margin: 0; font-size: 1rem; }
        .sidebar-brand .brand-text small { color: rgba(255,255,255,.45); font-size: 0.72rem; }
        .sidebar-section { padding: 1rem 0 0.25rem; }
        .sidebar-heading {
            color: rgba(255,255,255,.35);
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.12rem;
            text-transform: uppercase;
            padding: 0.25rem 1.25rem 0.5rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.7);
            padding: 0.65rem 1.25rem;
            font-size: 0.84rem;
            font-weight: 600;
            border-radius: 0;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            transition: all 0.2s;
            margin: 1px 0.5rem;
            border-radius: 8px;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: linear-gradient(90deg, var(--primary), #6f9fff);
            box-shadow: 0 4px 12px rgba(78,115,223,.4);
        }
        .sidebar .nav-link .nav-icon {
            width: 20px; text-align: center; font-size: 0.9rem;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1039;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background: #fff;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            position: sticky;
            top: 0;
            z-index: 100;
            gap: 1rem;
        }
        .topbar-left { display: flex; align-items: center; gap: 0.75rem; }
        .topbar-right { display: flex; align-items: center; gap: 0.75rem; }
        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #555;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .hamburger-btn:hover { background: #f0f2f8; }
        .page-title { font-size: 1.1rem; font-weight: 800; color: #2d3748; margin: 0; }
        .topbar-user {
            display: flex; align-items: center; gap: 0.5rem;
            background: #f8f9fc;
            border-radius: 50px;
            padding: 0.35rem 0.75rem;
            font-size: 0.84rem;
            font-weight: 700;
            color: #555;
        }
        .topbar-user .avatar {
            width: 30px; height: 30px;
            background: linear-gradient(135deg, var(--primary), #6f9fff);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 0.75rem; font-weight: 800;
        }

        /* ===== PAGE BODY ===== */
        .page-body { padding: 1.5rem; flex: 1; }

        /* ===== CARDS ===== */
        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            overflow: hidden;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #edf0f7;
            font-weight: 700;
            padding: 1rem 1.25rem;
        }
        .stat-card {
            border-radius: 14px;
            padding: 1.25rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .stat-card .stat-icon {
            position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%);
            font-size: 3rem; opacity: 0.2;
        }
        .stat-card .stat-label { font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08rem; opacity: 0.85; }
        .stat-card .stat-value { font-size: 1.8rem; font-weight: 800; margin: 0.25rem 0 0; }

        /* ===== STATUS BADGES ===== */
        .status-badge {
            border: none;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
        }
        .status-badge option { color: #333; background: #fff; }
        .status-Pending { background: var(--warning); color: #333 !important; }
        .status-Confirmed { background: var(--success); }
        .status-Completed { background: var(--primary); }
        .status-Cancelled { background: var(--danger); }

        /* ===== TABLE ===== */
        .table-responsive { border-radius: 0 0 14px 14px; }
        .table th { font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05rem; color: #8a94a6; border-top: none; }
        .table td { vertical-align: middle; font-size: 0.87rem; }

        /* ===== ALERTS ===== */
        .alert { border: none; border-radius: 10px; font-weight: 600; }

        /* ===== NOTIFICATION BELL ===== */
        .notif-btn {
            position: relative;
            background: #f0f2f8;
            border: none;
            width: 38px; height: 38px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem; color: #555;
            cursor: pointer;
            transition: background 0.2s;
        }
        .notif-btn:hover { background: #e2e6f0; }
        .notif-badge {
            position: absolute;
            top: -3px; right: -3px;
            background: var(--danger);
            color: #fff;
            font-size: 0.6rem;
            font-weight: 800;
            border-radius: 50%;
            width: 17px; height: 17px;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff;
        }
        .notif-dropdown {
            min-width: 340px;
            max-height: 480px;
            overflow-y: auto;
            border: none;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0,0,0,.15);
            padding: 0;
        }
        .notif-header {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #edf0f7;
            font-weight: 800;
            font-size: 0.9rem;
            color: #2d3748;
            display: flex; align-items: center; gap: 0.5rem;
        }
        .notif-tab-bar {
            display: flex;
            border-bottom: 1px solid #edf0f7;
            background: #f8f9fc;
        }
        .notif-tab-btn {
            flex: 1;
            padding: 0.6rem;
            border: none;
            background: none;
            font-size: 0.78rem;
            font-weight: 700;
            color: #8a94a6;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .notif-tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-overlay.open {
                display: block;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .hamburger-btn {
                display: flex;
                align-items: center;
            }
            .topbar-user span.user-name { display: none; }
        }
        @media (max-width: 575.98px) {
            .page-body { padding: 1rem; }
            .topbar { padding: 0.6rem 1rem; }
            .stat-card .stat-value { font-size: 1.4rem; }
            .notif-dropdown { min-width: 290px; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-shuttle-van"></i></div>
        <div class="brand-text">
            <h5>VanService</h5>
            <small>Admin Panel</small>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-heading">Main</div>
        <ul class="nav flex-column px-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span> Dashboard
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-heading">Management</div>
        <ul class="nav flex-column px-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.trips.*') ? 'active' : '' }}" href="{{ route('admin.trips.index') }}">
                    <span class="nav-icon"><i class="fas fa-route"></i></span> Trips
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.vans.*') ? 'active' : '' }}" href="{{ route('admin.vans.index') }}">
                    <span class="nav-icon"><i class="fas fa-shuttle-van"></i></span> Fleet (Vans)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
                    <span class="nav-icon"><i class="fas fa-calendar-check"></i></span> Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.travel-history') ? 'active' : '' }}" href="{{ route('admin.travel-history') }}">
                    <span class="nav-icon"><i class="fas fa-history"></i></span> Travel History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.trip_requests.*') ? 'active' : '' }}" href="{{ route('admin.trip_requests.index') }}">
                    <span class="nav-icon"><i class="fas fa-paper-plane"></i></span> Trip Requests
                    @php $pendingReqCount = \App\Models\TripRequest::where('status','pending')->count(); @endphp
                    @if($pendingReqCount > 0)
                        <span style="background:#e74a3b;color:#fff;border-radius:2rem;padding:0.1rem 0.45rem;font-size:0.68rem;font-weight:700;margin-left:auto;">{{ $pendingReqCount }}</span>
                    @endif
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                    <span class="nav-icon"><i class="fas fa-users"></i></span> Users
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-heading">Tracking</div>
        <ul class="nav flex-column px-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.live-map') ? 'active' : '' }}" href="{{ route('admin.live-map') }}">
                    <span class="nav-icon"><i class="fas fa-map-marker-alt"></i></span>
                    Live Tracking
                    <span style="background:#00e676;color:#000;border-radius:8px;padding:1px 6px;font-size:0.6rem;font-weight:800;margin-left:auto;">LIVE</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section">
        <div class="sidebar-heading">Reports</div>
        <ul class="nav flex-column px-1">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.fuel-records.*') ? 'active' : '' }}" href="{{ route('admin.fuel-records.index') }}">
                    <span class="nav-icon"><i class="fas fa-gas-pump"></i></span> Fuel Consumption
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                    <span class="nav-icon"><i class="fas fa-file-alt"></i></span> Reports
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-section mt-auto pb-3">
        <div class="sidebar-heading">Account</div>
        <ul class="nav flex-column px-1">
            <li class="nav-item">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link btn btn-link text-start w-100" style="color:rgba(255,255,255,.7); text-decoration:none;">
                        <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <button class="hamburger-btn" id="hamburgerBtn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="page-title">@yield('title','Dashboard')</h1>
        </div>
        <div class="topbar-right">
            <!-- Notification Bell -->
            <div class="dropdown">
                <button class="notif-btn" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="notifBell">
                    <i class="fas fa-bell"></i>
                    @if($unreadCount > 0)
                        <span class="notif-badge" id="notif-badge">{{ $unreadCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-dropdown">
                    <div class="notif-header">
                        <i class="fas fa-bell text-primary"></i> Notifications
                    </div>
                    <div class="notif-tab-bar">
                        <button class="notif-tab-btn active" data-tab="users">
                            <i class="fas fa-bell me-1"></i>System Alerts
                            @if($userUnreadCount > 0)<span class="badge bg-danger ms-1">{{ $userUnreadCount }}</span>@endif
                        </button>
                        <button class="notif-tab-btn" data-tab="bookings">
                            <i class="fas fa-calendar-check me-1"></i>Bookings
                        </button>
                    </div>

                    <!-- System Alerts Tab -->
                    <div class="notif-tab-content" id="tab-users">
                        @forelse($userNotifications as $notif)
                        @php
                            $adminNotifMeta = match($notif->type) {
                                'new_booking' => [route('admin.bookings.index'), 'fa-calendar-plus', 'var(--success)'],
                                'van_takeoff' => [route('admin.live-map'), 'fa-shuttle-van', '#0f766e'],
                                'van_arrived' => [route('admin.live-map'), 'fa-map-marker-alt', '#16a34a'],
                                'van_delayed' => [route('admin.live-map'), 'fa-clock', '#d97706'],
                                'driver_confirmed' => [route('admin.live-map'), 'fa-check-circle', '#2563eb'],
                                'driver_cancelled' => [route('admin.live-map'), 'fa-times-circle', '#dc2626'],
                                default => [route('admin.users.index'), 'fa-user-plus', 'var(--primary)'],
                            };
                        @endphp
                        <a class="dropdown-item d-flex align-items-start py-2 px-3 user-notif-item {{ !$notif->is_read ? 'fw-bold bg-light' : '' }}"
                           href="{{ $adminNotifMeta[0] }}" data-id="{{ $notif->id }}" style="white-space:normal;">
                            <div class="me-2 mt-1">
                                <div style="width:34px;height:34px;border-radius:50%;background:{{ !$notif->is_read ? $adminNotifMeta[2] : '#d1d3e2' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas {{ $adminNotifMeta[1] }} text-white" style="font-size:0.7rem;"></i>
                                </div>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="small text-muted">{{ $notif->created_at->diffForHumans() }}</div>
                                <div style="font-size:0.83rem;">{{ $notif->message }}</div>
                            </div>
                            @if(!$notif->is_read)<span style="width:7px;height:7px;border-radius:50%;background:var(--primary);display:inline-block;margin-top:6px;flex-shrink:0;"></span>@endif
                        </a>
                        @empty
                        <div class="text-center text-muted py-3 small"><i class="fas fa-check-circle me-1"></i>No system alerts</div>
                        @endforelse
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('admin.markAllUserNotifRead') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-center small text-primary py-2"><i class="fas fa-check-double me-1"></i>Mark All Alerts as Read</button>
                        </form>
                    </div>

                    <!-- Bookings Tab -->
                    <div class="notif-tab-content" id="tab-bookings" style="display:none;">
                        @forelse($notifications as $notif)
                        <a class="dropdown-item d-flex align-items-start py-2 px-3 notification-item {{ !$notif->is_read ? 'fw-bold bg-light' : '' }}"
                           href="#" data-id="{{ $notif->id }}"
                           data-user="{{ $notif->user->name ?? 'N/A' }}"
                           data-email="{{ $notif->user->email ?? 'N/A' }}"
                           data-van="{{ $notif->van_model }}"
                           data-route="{{ $notif->trip->route_name ?? 'N/A' }}"
                           data-date="{{ $notif->booking_date }}"
                           data-time="{{ $notif->booking_time }}"
                           data-seats="{{ $notif->seats_requested }}"
                           data-status="{{ $notif->status }}"
                           data-booking-id="{{ $notif->id }}"
                           data-bs-toggle="modal" data-bs-target="#bookingModal"
                           style="white-space:normal;">
                            <div class="me-2 mt-1">
                                <div style="width:34px;height:34px;border-radius:50%;background:{{ !$notif->is_read ? 'var(--success)' : '#d1d3e2' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-calendar-check text-white" style="font-size:0.7rem;"></i>
                                </div>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="small text-muted">{{ $notif->created_at->diffForHumans() }}</div>
                                <div style="font-size:0.83rem;">{{ $notif->user->name ?? 'Unknown' }} booked {{ $notif->trip->route_name ?? 'a trip' }}</div>
                            </div>
                        </a>
                        @empty
                        <div class="text-center text-muted py-3 small"><i class="fas fa-check-circle me-1"></i>No booking notifications</div>
                        @endforelse
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('admin.markAllRead') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-center small text-primary py-2"><i class="fas fa-check-double me-1"></i>Mark All as Read</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- User -->
            <div class="topbar-user">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <span class="user-name">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    <div class="px-4 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <div class="page-body">
        @yield('content')
    </div>
</div>

<!-- Booking Detail Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--primary),#6f9fff);color:#fff;border:none;">
                <h5 class="modal-title"><i class="fas fa-calendar-check me-2"></i>Booking Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-borderless mb-0" style="font-size:0.9rem;">
                    <tr><th class="ps-3 text-muted" style="width:35%;">Passenger</th><td id="modal-user" class="fw-bold"></td></tr>
                    <tr><th class="ps-3 text-muted">Email</th><td id="modal-email"></td></tr>
                    <tr><th class="ps-3 text-muted">Van</th><td id="modal-van"></td></tr>
                    <tr><th class="ps-3 text-muted">Route</th><td id="modal-route"></td></tr>
                    <tr><th class="ps-3 text-muted">Date</th><td id="modal-date"></td></tr>
                    <tr><th class="ps-3 text-muted">Time</th><td id="modal-time"></td></tr>
                    <tr><th class="ps-3 text-muted">Seats</th><td id="modal-seats"></td></tr>
                    <tr><th class="ps-3 text-muted">Status</th><td id="modal-status"></td></tr>
                </table>
            </div>
            <div class="modal-footer border-0" id="modal-actions"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    // Sidebar toggle
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('open');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
    }

    $(document).ready(function() {
        // DataTables
        var dtTables = ['#vans-table', '#trips-table', '#bookings-table', '#users-table'];
        dtTables.forEach(function(selector) {
            if ($(selector).length && !$.fn.DataTable.isDataTable(selector)) {
                var $table = $(selector);
                var colCount = $table.find('thead tr th').length;
                $table.DataTable({
                    columnDefs: [{ orderable: false, targets: colCount - 1 }],
                    responsive: true,
                    pageLength: 10,
                    language: { emptyTable: 'No records found.', zeroRecords: 'No matching records found.' }
                });
            }
        });

        // Notification tab switching
        $('.notif-tab-btn').on('click', function(e) {
            e.stopPropagation();
            var tab = $(this).data('tab');
            $('.notif-tab-btn').removeClass('active');
            $(this).addClass('active');
            $('.notif-tab-content').hide();
            $('#tab-'+tab).show();
        });

        // Mark user notification as read
        $('.user-notif-item').on('click', function() {
            var id = $(this).data('id');
            $.post('/admin/mark-user-notif-read/'+id, {_token: '{{ csrf_token() }}'});
            $(this).removeClass('fw-bold bg-light');
            $(this).find('span[style*="border-radius:50%"]').remove();
        });

        // Booking notification modal
        $('.notification-item').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('#modal-user').text($(this).data('user'));
            $('#modal-email').text($(this).data('email'));
            $('#modal-van').text($(this).data('van'));
            $('#modal-route').text($(this).data('route'));
            $('#modal-date').text($(this).data('date'));
            $('#modal-time').text($(this).data('time'));
            $('#modal-seats').text($(this).data('seats'));
            var status = $(this).data('status');
            $('#modal-status').html('<span class="badge rounded-pill" style="background:' + (status==='Pending'?'#f6c23e':status==='Confirmed'?'#1cc88a':status==='Completed'?'#4e73df':'#e74a3b') + ';color:#fff;padding:5px 12px;">'+status+'</span>');
            var bookingId = $(this).data('booking-id');
            var actions = '';
            if (status === 'Pending') {
                actions = '<form method="POST" action="/admin/bookings/'+bookingId+'/status" class="me-2">' +
                    '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                    '<input type="hidden" name="_method" value="PATCH">' +
                    '<input type="hidden" name="status" value="Confirmed">' +
                    '<button type="submit" class="btn btn-success btn-sm rounded-pill px-3"><i class="fas fa-check me-1"></i>Confirm</button></form>' +
                    '<form method="POST" action="/admin/bookings/'+bookingId+'/status">' +
                    '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                    '<input type="hidden" name="_method" value="PATCH">' +
                    '<input type="hidden" name="status" value="Cancelled">' +
                    '<button type="submit" class="btn btn-danger btn-sm rounded-pill px-3"><i class="fas fa-times me-1"></i>Cancel</button></form>';
            } else {
                actions = '<button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>';
            }
            $('#modal-actions').html(actions);
            $.post('/admin/mark-read/'+id, {_token: '{{ csrf_token() }}'});
            $(this).removeClass('fw-bold').find('i').removeClass('text-primary');
        });

        // Status select color update
        $(document).on('change', '.status-badge', function() {
            var val = $(this).val();
            $(this).removeClass('status-Pending status-Confirmed status-Completed status-Cancelled').addClass('status-'+val);
            var id = $(this).data('id');
            $.ajax({
                url: '/admin/bookings/'+id+'/status',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', _method: 'PATCH', status: val },
                error: function() { alert('Error updating status.'); }
            });
        });
    });
</script>
@stack('scripts')
</body>
</html>

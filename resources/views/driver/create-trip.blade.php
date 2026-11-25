<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Trip — VanService</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        :root {
            --primary: #1b3a5c;
            --accent: #2563eb;
            --success: #16a34a;
            --light-bg: #f0f4f8;
            --card-shadow: 0 4px 24px rgba(27,58,92,0.10);
        }

        * { box-sizing: border-box; }
        body { background: var(--light-bg); font-family: 'Segoe UI', sans-serif; min-height: 100vh; }

        /* ── Topbar ── */
        .topbar {
            background: linear-gradient(135deg, var(--primary) 0%, #2c5282 100%);
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-brand { color: #fff; font-weight: 800; font-size: 1.15rem; letter-spacing: -0.01em; }
        .topbar-brand span { color: #4fc3f7; }
        .topbar-brand small { font-size: 0.65rem; opacity: 0.65; margin-left: 6px; font-weight: 400; }

        /* ── Page hero ── */
        .page-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #2563eb 100%);
            color: #fff;
            padding: 36px 28px 28px;
            margin-bottom: 0;
        }
        .page-hero h2 { font-weight: 800; font-size: 1.6rem; margin-bottom: 4px; }
        .page-hero p  { opacity: 0.8; font-size: 0.9rem; margin: 0; }

        /* ── Van badge ── */
        .van-badge {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 12px;
            padding: 12px 18px;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            margin-top: 18px;
            backdrop-filter: blur(4px);
        }
        .van-badge .van-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }
        .van-badge .van-name { font-weight: 700; font-size: 1rem; }
        .van-badge .van-meta { font-size: 0.8rem; opacity: 0.8; }

        /* ── Main content ── */
        .content-wrap {
            max-width: 820px;
            margin: 0 auto;
            padding: 0 16px 48px;
            position: relative;
            top: -16px;
        }

        /* ── Section cards ── */
        .section-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
            overflow: visible; /* must NOT be hidden — allows dropdown to escape card bounds */
        }
        .section-card-header {
            border-radius: 16px 16px 0 0;
            padding: 16px 22px 14px;
            border-bottom: 1px solid #f0f4f8;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-card-header .icon-wrap {
            width: 34px; height: 34px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        .icon-blue  { background: #eff6ff; color: #2563eb; }
        .icon-green { background: #f0fdf4; color: #16a34a; }
        .icon-amber { background: #fffbeb; color: #d97706; }
        .icon-purple{ background: #f5f3ff; color: #7c3aed; }

        .section-card-header h6 {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary);
            margin: 0;
        }
        .section-card-header p {
            font-size: 0.78rem;
            color: #94a3b8;
            margin: 0;
        }
        .section-card-body { padding: 20px 22px; }

        /* ── Form controls ── */
        .form-label {
            font-weight: 600;
            font-size: 0.8rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }
        .form-control, .form-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px 0 0 10px;
            color: #64748b;
        }
        .input-group .form-control { border-radius: 0 10px 10px 0; border-left: none; }
        .input-group .form-control:focus { border-left: none; }

        /* ── Map ── */
        #trip-map {
            height: 300px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            z-index: 0;
        }

        /* ── Route info pill ── */
        .route-result {
            display: none;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 10px 16px;
            margin-top: 12px;
            font-size: 0.88rem;
            color: #15803d;
        }
        .route-result.show { display: flex; align-items: center; gap: 10px; }

        /* ── Fuel result card ── */
        .fuel-result-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 14px;
        }
        .fuel-stat {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            text-align: center;
        }
        .fuel-stat .fs-val {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.1;
        }
        .fuel-stat .fs-unit { font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
        .fuel-stat .fs-label { font-size: 0.78rem; color: #64748b; margin-top: 4px; }

        /* ── Autocomplete ── */
        .autocomplete-wrap { position: relative; }
        .autocomplete-list {
            position: fixed;
            z-index: 99999 !important;
            background: #fff;
            border: 1.5px solid #bfdbfe;
            border-radius: 12px;
            min-width: 260px;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 0 12px 32px rgba(37,99,235,0.15), 0 2px 8px rgba(0,0,0,0.08);
        }
        .autocomplete-list .ac-group-label {
            padding: 8px 14px 4px;
            font-size: 0.72rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: #f8fafc;
            border-bottom: 1px solid #f0f4f8;
        }
        .autocomplete-list .ac-item {
            padding: 10px 14px;
            cursor: pointer;
            font-size: 0.88rem;
            border-bottom: 1px solid #f8fafc;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.12s;
        }
        .autocomplete-list .ac-item:last-child { border-bottom: none; }
        .autocomplete-list .ac-item:hover { background: #eff6ff; }
        .autocomplete-list .ac-item.preset {
            color: #1e40af;
            font-weight: 600;
        }
        .autocomplete-list .ac-item.preset:hover { background: #dbeafe; }
        .autocomplete-list .ac-item .ac-icon { font-size: 1rem; flex-shrink: 0; }

        /* ── Btn ── */
        .btn-primary-custom {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            border: none;
            color: #fff;
            border-radius: 10px;
            padding: 11px 28px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: opacity 0.2s, transform 0.15s;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
        }
        .btn-primary-custom:hover { opacity: 0.92; transform: translateY(-1px); color: #fff; }
        .btn-calc {
            background: #eff6ff;
            color: #2563eb;
            border: 1.5px solid #bfdbfe;
            border-radius: 10px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: background 0.2s;
        }
        .btn-calc:hover { background: #dbeafe; }

        /* ── Responsive ── */
        @media (max-width: 576px) {
            .page-hero { padding: 24px 16px 20px; }
            .page-hero h2 { font-size: 1.3rem; }
            .fuel-result-grid { grid-template-columns: 1fr 1fr; }
            .topbar { padding: 12px 16px; }
        }
    </style>
</head>
<body>

<!-- Topbar -->
<div class="topbar">
    <div class="topbar-brand">
        <i class="fas fa-shuttle-van me-2"></i>Van<span>Service</span>
        <small>Driver Panel</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="text-white d-none d-sm-inline" style="font-size:0.88rem; opacity:0.85;">
            <i class="fas fa-user-circle me-1"></i>{{ $driver->name }}
        </span>
        <a href="{{ route('driver.dashboard') }}" class="btn btn-sm btn-outline-light" style="border-radius:8px;">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<!-- Hero -->
<div class="page-hero">
    <div style="max-width:820px; margin:0 auto; padding:0 16px;">
        <h2><i class="fas fa-route me-2" style="opacity:0.85;"></i>Create New Trip</h2>
        <p>Fill in the trip details below. Passengers can book this trip once it is published.</p>

        <!-- Van Badge -->
        <div class="van-badge">
            <div class="van-icon"><i class="fas fa-shuttle-van"></i></div>
            <div>
                <div class="van-name">{{ $van->van_model }}</div>
                <div class="van-meta">
                    <i class="fas fa-id-card me-1"></i>{{ $van->van_plate }}
                    &nbsp;&bull;&nbsp;
                    <i class="fas fa-users me-1"></i>{{ $van->total_seats }} Seats
                    @if($van->fuel_capacity)
                    &nbsp;&bull;&nbsp;
                    <i class="fas fa-gas-pump me-1"></i>{{ $van->fuel_capacity }}L Tank
                    @endif
                    @if($van->km_per_liter)
                    &nbsp;&bull;&nbsp;
                    <i class="fas fa-tachometer-alt me-1"></i>{{ $van->km_per_liter }} km/L
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="content-wrap">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" style="border-left:4px solid #dc2626;">
        <i class="fas fa-exclamation-triangle me-2"></i>
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('driver.trips.store') }}" id="tripForm">
        @csrf

        <!-- ── Section 1: Route Info ── -->
        <div class="section-card">
            <div class="section-card-header">
                <div class="icon-wrap icon-blue"><i class="fas fa-map-signs"></i></div>
                <div>
                    <h6>Route Information</h6>
                    <p>Set the pickup and drop-off locations for this trip</p>
                </div>
            </div>
            <div class="section-card-body">

                <!-- Route Name -->
                <div class="mb-4">
                    <label class="form-label">Route Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                        <input type="text" name="route_name" id="route_name" class="form-control"
                               placeholder="e.g. NISU Main Campus → NISU Ajuy Campus"
                               value="{{ old('route_name', '') }}" required>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Origin -->
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-map-marker-alt text-success me-1"></i>Origin (Pickup)</label>
                        <div class="autocomplete-wrap" id="origin_wrap">
                            <input type="text" id="origin_input" class="form-control"
                                   placeholder="Select or type a campus..." autocomplete="off"
                                   value="{{ old('pickup_location', '') }}" required>
                            <input type="hidden" name="pickup_location" id="pickup_location">
                            <input type="hidden" name="origin_lat" id="origin_lat">
                            <input type="hidden" name="origin_lng" id="origin_lng">
                            <div id="origin_list" class="autocomplete-list" style="display:none;"></div>
                        </div>
                    </div>
                    <!-- Destination -->
                    <div class="col-md-6">
                        <label class="form-label"><i class="fas fa-map-marker-alt text-danger me-1"></i>Destination (Drop-off)</label>
                        <div class="autocomplete-wrap" id="dest_wrap">
                            <input type="text" id="dest_input" class="form-control"
                                   placeholder="Select or type a campus..." autocomplete="off"
                                   value="{{ old('dropoff_location', '') }}" required>
                            <input type="hidden" name="dropoff_location" id="dropoff_location">
                            <input type="hidden" name="dest_lat" id="dest_lat">
                            <input type="hidden" name="dest_lng" id="dest_lng">
                            <div id="dest_list" class="autocomplete-list" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Section 2: Route Map ── -->
        <div class="section-card">
            <div class="section-card-header">
                <div class="icon-wrap icon-blue"><i class="fas fa-map"></i></div>
                <div>
                    <h6>Route Map</h6>
                    <p>Visualize and calculate the driving route</p>
                </div>
            </div>
            <div class="section-card-body">
                <div id="trip-map"></div>

                <div class="d-flex align-items-center gap-3 mt-3 flex-wrap">
                    <button type="button" class="btn btn-calc" onclick="calculateRoute()">
                        <i class="fas fa-route me-1"></i>Calculate Route
                    </button>
                    <span class="text-muted small">Select origin &amp; destination first, then click to calculate.</span>
                </div>

                <div class="route-result" id="route_result">
                    <i class="fas fa-check-circle fs-5"></i>
                    <div>
                        <strong id="route_dist_text">—</strong>
                        <span class="text-muted ms-2" id="route_dur_text"></span>
                    </div>
                </div>

                <!-- Hidden fields -->
                <input type="hidden" name="distance_km" id="distance_km">
                <input type="hidden" name="fuel_cost" id="fuel_cost">
            </div>
        </div>

        <!-- ── Section 3: Schedule ── -->
        <div class="section-card">
            <div class="section-card-header">
                <div class="icon-wrap icon-amber"><i class="fas fa-calendar-alt"></i></div>
                <div>
                    <h6>Trip Schedule</h6>
                    <p>Set the date, departure time, and estimated arrival</p>
                </div>
            </div>
            <div class="section-card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label">Date of Trip</label>
                        <input type="date" name="trip_date" class="form-control"
                               value="{{ old('trip_date', '') }}" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">Departure Time</label>
                        <input type="time" name="departure_time" class="form-control"
                               value="{{ old('departure_time', '') }}" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">ETA (Estimated Arrival)</label>
                        <input type="time" name="eta" class="form-control"
                               value="{{ old('eta', '') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Section 4: Fuel Details ── -->
        <div class="section-card">
            <div class="section-card-header">
                <div class="icon-wrap icon-green"><i class="fas fa-gas-pump"></i></div>
                <div>
                    <h6>Fuel Details</h6>
                    <p>Van fuel specs and estimated consumption for this trip</p>
                </div>
            </div>
            <div class="section-card-body">

                <!-- Van Fuel Specs (read-only display) -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-4">
                        <label class="form-label">Fuel Capacity</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tint"></i></span>
                            <input type="text" class="form-control" value="{{ $van->fuel_capacity ?? '—' }}" readonly style="background:#f8fafc;">
                            <span class="input-group-text" style="border-radius:0 10px 10px 0; border-left:none;">L</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">Fuel Efficiency</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                            <input type="text" class="form-control" value="{{ $van->km_per_liter ?? '—' }}" readonly style="background:#f8fafc;">
                            <span class="input-group-text" style="border-radius:0 10px 10px 0; border-left:none;">km/L</span>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">Fuel Price per Liter</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-peso-sign"></i></span>
                            <input type="number" id="fuel_price_input" class="form-control"
                                   placeholder="e.g. 65.00" min="0" step="0.01"
                                   value="{{ old('fuel_price', 65) }}"
                                   oninput="recalcFuel()">
                            <span class="input-group-text" style="border-radius:0 10px 10px 0; border-left:none;">₱/L</span>
                        </div>
                    </div>
                </div>

                <!-- Estimated Fuel Needed (manual override or auto) -->
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label">Estimated Fuel Needed <span class="text-muted fw-normal">(auto-filled after route calc)</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-gas-pump"></i></span>
                            <input type="number" name="fuel_needed" id="fuel_needed_input" class="form-control"
                                   placeholder="Liters needed" min="0" step="0.01"
                                   value="{{ old('fuel_needed', '') }}"
                                   oninput="recalcFuelCost()">
                            <span class="input-group-text" style="border-radius:0 10px 10px 0; border-left:none;">L</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label">Estimated Fuel Cost</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-coins"></i></span>
                            <input type="number" name="fuel_cost_display" id="fuel_cost_display" class="form-control"
                                   placeholder="Auto-calculated" min="0" step="0.01"
                                   value="{{ old('fuel_cost_display', '') }}"
                                   oninput="syncFuelCost()">
                            <span class="input-group-text" style="border-radius:0 10px 10px 0; border-left:none;">₱</span>
                        </div>
                    </div>
                </div>

                <!-- Fuel Summary Cards (shown after route calc) -->
                <div id="fuel_summary" style="display:none;">
                    <div class="fuel-result-grid">
                        <div class="fuel-stat">
                            <div class="fs-val" id="fs_distance">—</div>
                            <div class="fs-unit">km</div>
                            <div class="fs-label">Route Distance</div>
                        </div>
                        <div class="fuel-stat">
                            <div class="fs-val" id="fs_liters">—</div>
                            <div class="fs-unit">Liters</div>
                            <div class="fs-label">Fuel Needed</div>
                        </div>
                        <div class="fuel-stat">
                            <div class="fs-val" id="fs_cost">—</div>
                            <div class="fs-unit">₱</div>
                            <div class="fs-label">Estimated Cost</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Submit ── -->
        <div class="section-card">
            <div class="section-card-body">
                <div class="d-flex gap-3 flex-wrap align-items-center">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-paper-plane me-2"></i>Publish Trip
                    </button>
                    <a href="{{ route('driver.dashboard') }}" class="btn btn-outline-secondary" style="border-radius:10px; padding:10px 22px; font-weight:600;">
                        <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <span class="text-muted small ms-auto d-none d-sm-inline">
                        <i class="fas fa-info-circle me-1"></i>Passengers will be notified once this trip is live.
                    </span>
                </div>
            </div>
        </div>

    </form>
</div><!-- /content-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── NISU Campus Presets ──────────────────────────────────────────────────────
const PRESETS = [
    { name: 'NISU Main Campus',              label: 'NISU Main Campus — Estancia, Iloilo',                lat: 11.4489, lng: 123.1517 },
    { name: 'NISU West Campus',              label: 'NISU West Campus — Estancia, Iloilo',                lat: 11.4480, lng: 123.1500 },
    { name: 'NISU Ajuy Campus',              label: 'NISU Ajuy Campus — Ajuy, Iloilo',                    lat: 11.1736, lng: 122.9278 },
    { name: 'NISU Barotac Viejo Campus',     label: 'NISU Barotac Viejo Campus — Barotac Viejo, Iloilo',  lat: 11.0583, lng: 122.8583 },
    { name: 'NISU Batad Campus',             label: 'NISU Batad Campus — Batad, Iloilo',                  lat: 11.3667, lng: 123.0500 },
    { name: 'NISU Concepcion Campus',        label: 'NISU Concepcion Campus — Concepcion, Iloilo',        lat: 11.2167, lng: 123.1333 },
    { name: 'NISU Lemery Campus',            label: 'NISU Lemery Campus — Lemery, Iloilo',                lat: 11.3667, lng: 122.8833 },
    { name: 'NISU Victorino Salcedo Campus', label: 'NISU Victorino Salcedo Campus (VSSC) — Sara, Iloilo',lat: 11.2667, lng: 122.9167 },
];

// Van fuel specs (passed from PHP)
const VAN_KM_PER_LITER = {{ $van->km_per_liter ?? 'null' }};
const VAN_FUEL_CAPACITY = {{ $van->fuel_capacity ?? 'null' }};

// ── Map ──────────────────────────────────────────────────────────────────────
const map = L.map('trip-map').setView([11.3667, 122.9500], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 18
}).addTo(map);

let originMarker = null, destMarker = null, routeLayer = null;

const greenIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    iconSize: [25,41], iconAnchor: [12,41], popupAnchor: [1,-34], shadowSize: [41,41]
});
const redIcon = L.icon({
    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    iconSize: [25,41], iconAnchor: [12,41], popupAnchor: [1,-34], shadowSize: [41,41]
});

// ── Autocomplete ─────────────────────────────────────────────────────────────
function positionList(input, list) {
    const rect = input.getBoundingClientRect();
    list.style.top   = (rect.bottom + window.scrollY + 4) + 'px';
    list.style.left  = (rect.left + window.scrollX) + 'px';
    list.style.width = rect.width + 'px';
}

function setupAutocomplete(inputId, listId, latId, lngId, hiddenId) {
    const input = document.getElementById(inputId);
    const list  = document.getElementById(listId);

    function showPresets() {
        const val = input.value.trim().toLowerCase();
        const matched = val
            ? PRESETS.filter(p => p.name.toLowerCase().includes(val) || p.label.toLowerCase().includes(val))
            : PRESETS;
        positionList(input, list);
        renderList(matched, list, input, latId, lngId, hiddenId, true);
    }

    input.addEventListener('focus', showPresets);
    input.addEventListener('click', () => {
        positionList(input, list);
        if (list.style.display === 'none' || list.style.display === '') showPresets();
    });
    input.addEventListener('input', () => {
        const val = input.value.trim().toLowerCase();
        positionList(input, list);
        if (!val) { renderList(PRESETS, list, input, latId, lngId, hiddenId, true); return; }
        const matched = PRESETS.filter(p => p.name.toLowerCase().includes(val) || p.label.toLowerCase().includes(val));
        renderList(matched, list, input, latId, lngId, hiddenId, true);
        if (val.length >= 3) searchNominatim(val, matched, list, input, latId, lngId, hiddenId);
    });
    // Reposition on scroll/resize
    window.addEventListener('scroll', () => { if (list.style.display !== 'none') positionList(input, list); }, true);
    window.addEventListener('resize', () => { if (list.style.display !== 'none') positionList(input, list); });
    document.addEventListener('mousedown', e => {
        if (!input.contains(e.target) && !list.contains(e.target)) list.style.display = 'none';
    });
}

function renderList(items, list, input, latId, lngId, hiddenId, isPreset) {
    list.innerHTML = '';
    if (!items.length) { list.style.display = 'none'; return; }

    // Group label
    const label = document.createElement('div');
    label.className = 'ac-group-label';
    label.textContent = isPreset ? '🏫 NISU Campuses' : '📍 Locations';
    list.appendChild(label);

    items.forEach(item => {
        const div = document.createElement('div');
        div.className = 'ac-item' + (isPreset ? ' preset' : '');
        const icon = isPreset ? '🏫' : '📍';
        const displayText = item.label || item.display_name;
        div.innerHTML = `<span class="ac-icon">${icon}</span><span>${displayText}</span>`;
        div.onmousedown = (e) => {
            e.preventDefault(); // prevent blur before click fires
            input.value = displayText;
            document.getElementById(hiddenId).value = displayText;
            document.getElementById(latId).value  = item.lat;
            document.getElementById(lngId).value  = item.lng;
            list.style.display = 'none';
            autoFillRouteName();
            updateMapMarker(inputId, item.lat, item.lng);
        };
        list.appendChild(div);
    });
    list.style.display = 'block';
}

function searchNominatim(query, presets, list, input, latId, lngId, hiddenId) {
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=4&countrycodes=ph`)
        .then(r => r.json())
        .then(results => {
            list.innerHTML = '';

            // Always show matching NISU presets first
            if (presets.length) {
                const grpLabel = document.createElement('div');
                grpLabel.className = 'ac-group-label';
                grpLabel.textContent = '🏫 NISU Campuses';
                list.appendChild(grpLabel);
                presets.forEach(item => appendItem(item, true));
            }

            // Then show Nominatim results
            if (results.length) {
                const grpLabel2 = document.createElement('div');
                grpLabel2.className = 'ac-group-label';
                grpLabel2.textContent = '📍 Other Locations';
                list.appendChild(grpLabel2);
                results.forEach(r => {
                    appendItem({ label: r.display_name, lat: parseFloat(r.lat), lng: parseFloat(r.lon) }, false);
                });
            }

            list.style.display = (presets.length || results.length) ? 'block' : 'none';

            function appendItem(item, isPreset) {
                const div = document.createElement('div');
                div.className = 'ac-item' + (isPreset ? ' preset' : '');
                const icon = isPreset ? '🏫' : '📍';
                const displayText = item.label || item.display_name;
                div.innerHTML = `<span class="ac-icon">${icon}</span><span>${displayText}</span>`;
                div.onmousedown = (e) => {
                    e.preventDefault();
                    input.value = displayText;
                    document.getElementById(hiddenId).value = displayText;
                    document.getElementById(latId).value  = item.lat;
                    document.getElementById(lngId).value  = item.lng;
                    list.style.display = 'none';
                    autoFillRouteName();
                    updateMapMarker(inputId, item.lat, item.lng);
                };
                list.appendChild(div);
            }
        }).catch(() => {});
}

function updateMapMarker(inputId, lat, lng) {
    if (inputId === 'origin_input') {
        if (originMarker) map.removeLayer(originMarker);
        originMarker = L.marker([lat, lng], { icon: greenIcon, title: 'Origin' })
            .addTo(map).bindPopup('<b>Origin</b>').openPopup();
    } else {
        if (destMarker) map.removeLayer(destMarker);
        destMarker = L.marker([lat, lng], { icon: redIcon, title: 'Destination' })
            .addTo(map).bindPopup('<b>Destination</b>');
    }

    const oLat = document.getElementById('origin_lat').value;
    const dLat = document.getElementById('dest_lat').value;

    if (oLat && dLat) {
        // Both points set — auto-draw route immediately
        calculateRoute(true);
    } else {
        // Only one point — just pan to it
        map.setView([lat, lng], 13);
    }
}

function autoFillRouteName() {
    const origin = document.getElementById('origin_input').value;
    const dest   = document.getElementById('dest_input').value;
    const field  = document.getElementById('route_name');
    if (origin && dest && !field.value) {
        const o = origin.split('—')[0].trim().replace(/🏫|📍/g, '').trim();
        const d = dest.split('—')[0].trim().replace(/🏫|📍/g, '').trim();
        field.value = o + ' → ' + d;
    }
}

// ── Route Calculation ─────────────────────────────────────────────────────────
function calculateRoute(silent) {
    const oLat = document.getElementById('origin_lat').value;
    const oLng = document.getElementById('origin_lng').value;
    const dLat = document.getElementById('dest_lat').value;
    const dLng = document.getElementById('dest_lng').value;

    if (!oLat || !dLat) {
        if (!silent) alert('Please select both Origin and Destination from the dropdown list first.');
        return;
    }

    const btn = document.querySelector('.btn-calc');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Calculating...';
    btn.disabled = true;

    if (routeLayer)  map.removeLayer(routeLayer);

    // Re-place markers (keep them in sync)
    if (originMarker) map.removeLayer(originMarker);
    if (destMarker)   map.removeLayer(destMarker);
    originMarker = L.marker([oLat, oLng], { icon: greenIcon, title: 'Origin' })
        .addTo(map).bindPopup('<b>Origin</b>').openPopup();
    destMarker = L.marker([dLat, dLng], { icon: redIcon, title: 'Destination' })
        .addTo(map).bindPopup('<b>Destination</b>');

    fetch(`https://router.project-osrm.org/route/v1/driving/${oLng},${oLat};${dLng},${dLat}?overview=full&geometries=geojson`)
        .then(r => r.json())
        .then(data => {
            btn.innerHTML = '<i class="fas fa-route me-1"></i>Recalculate Route';
            btn.disabled = false;

            if (data.routes && data.routes.length) {
                const route   = data.routes[0];
                const distKm  = (route.distance / 1000).toFixed(2);
                const durMin  = Math.round(route.duration / 60);
                const durText = durMin >= 60
                    ? `${Math.floor(durMin/60)}h ${durMin%60}m`
                    : `${durMin} min`;

                document.getElementById('distance_km').value = distKm;

                // Route line
                routeLayer = L.geoJSON(route.geometry, {
                    style: { color: '#2563eb', weight: 5, opacity: 0.85 }
                }).addTo(map);
                map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });

                // Show result pill
                document.getElementById('route_dist_text').textContent = `${distKm} km`;
                document.getElementById('route_dur_text').textContent  = `· ${durText} drive`;
                document.getElementById('route_result').classList.add('show');

                // Auto-fill fuel
                autoFillFuel(parseFloat(distKm));
            }
        }).catch(() => {
            btn.innerHTML = '<i class="fas fa-route me-1"></i>Recalculate Route';
            btn.disabled = false;
            // Fallback straight line
            routeLayer = L.polyline([[oLat, oLng], [dLat, dLng]], {
                color: '#2563eb', weight: 4, dashArray: '8,6'
            }).addTo(map);
            map.fitBounds([[oLat, oLng], [dLat, dLng]], { padding: [50, 50] });
        });
}

// ── Fuel Calculations ─────────────────────────────────────────────────────────
function autoFillFuel(distKm) {
    const kml   = VAN_KM_PER_LITER;
    const price = parseFloat(document.getElementById('fuel_price_input').value) || 0;

    if (kml && kml > 0) {
        const liters = (distKm / kml).toFixed(2);
        const cost   = (liters * price).toFixed(2);

        document.getElementById('fuel_needed_input').value  = liters;
        document.getElementById('fuel_cost_display').value  = cost;
        document.getElementById('fuel_cost').value          = cost;

        // Summary cards
        document.getElementById('fs_distance').textContent = distKm;
        document.getElementById('fs_liters').textContent   = liters;
        document.getElementById('fs_cost').textContent     = parseFloat(cost).toLocaleString('en-PH', { minimumFractionDigits: 2 });
        document.getElementById('fuel_summary').style.display = 'block';
    }
}

function recalcFuel() {
    const distKm = parseFloat(document.getElementById('distance_km').value);
    if (distKm) autoFillFuel(distKm);
}

function recalcFuelCost() {
    const liters = parseFloat(document.getElementById('fuel_needed_input').value) || 0;
    const price  = parseFloat(document.getElementById('fuel_price_input').value)  || 0;
    const cost   = (liters * price).toFixed(2);
    document.getElementById('fuel_cost_display').value = cost;
    document.getElementById('fuel_cost').value         = cost;
    document.getElementById('fs_liters').textContent   = liters.toFixed(2);
    document.getElementById('fs_cost').textContent     = parseFloat(cost).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    if (liters > 0) document.getElementById('fuel_summary').style.display = 'block';
}

function syncFuelCost() {
    document.getElementById('fuel_cost').value = document.getElementById('fuel_cost_display').value;
}

// ── Form Validation ───────────────────────────────────────────────────────────
document.getElementById('tripForm').addEventListener('submit', function(e) {
    const oLat = document.getElementById('origin_lat').value;
    const dLat = document.getElementById('dest_lat').value;
    if (!oLat || !dLat) {
        e.preventDefault();
        alert('Please select Origin and Destination from the dropdown list.');
        return;
    }
    // Sync hidden fields
    document.getElementById('pickup_location').value  = document.getElementById('origin_input').value;
    document.getElementById('dropoff_location').value = document.getElementById('dest_input').value;
    document.getElementById('fuel_cost').value        = document.getElementById('fuel_cost_display').value;
});

// ── Init ──────────────────────────────────────────────────────────────────────
setupAutocomplete('origin_input', 'origin_list', 'origin_lat', 'origin_lng', 'pickup_location');
setupAutocomplete('dest_input',   'dest_list',   'dest_lat',   'dest_lng',   'dropoff_location');
</script>
</body>
</html>

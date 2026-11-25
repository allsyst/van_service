<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add Trip - VanService</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f0f2f8; font-family: 'Nunito', sans-serif; margin: 0; padding: 0; }
        .top-bar { background: linear-gradient(90deg, #1a1f36, #2d3561); color: #fff; padding: 0.75rem 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,.25); }
        .top-bar .brand { font-size: 1.15rem; font-weight: 800; letter-spacing: 0.5px; }
        .top-bar .brand i { color: #4e73df; }
        .page-hero { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); color: #fff; padding: 2rem 2rem 1.5rem; }
        .page-hero h2 { font-size: 1.6rem; font-weight: 800; margin-bottom: 0.25rem; }
        .page-hero p { opacity: 0.85; margin-bottom: 1rem; font-size: 0.9rem; }
        .van-badge { display: inline-flex; align-items: center; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 2rem; padding: 0.4rem 1rem; font-size: 0.85rem; gap: 0.5rem; }
        .section-card { background: #fff; border-radius: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,.07); margin-bottom: 1.5rem; overflow: visible; }
        .section-card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 0.75rem; }
        .section-card-header .icon-wrap { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
        .section-card-header h5 { margin: 0; font-weight: 700; font-size: 1rem; }
        .section-card-header p { margin: 0; font-size: 0.78rem; color: #888; }
        .section-card-body { padding: 1.5rem; }
        .field-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #4e73df; margin-bottom: 0.4rem; display: block; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 0.85rem; z-index: 2; }
        .input-icon-wrap input, .input-icon-wrap select { padding-left: 2.2rem; border-radius: 0.5rem; border: 1.5px solid #e0e4ef; font-size: 0.9rem; height: 42px; }
        .input-icon-wrap input:focus, .input-icon-wrap select:focus { border-color: #4e73df; box-shadow: 0 0 0 3px rgba(78,115,223,.12); outline: none; }
        .input-icon-wrap input[readonly] { background: #f8f9fc; }
        #map { height: 340px; border-radius: 0.75rem; border: 2px solid #e0e4ef; z-index: 0; }
        .leaflet-routing-container { display: none !important; }
        .route-result { display: none; background: linear-gradient(90deg, #1cc88a, #17a673); color: #fff; border-radius: 0.75rem; padding: 0.75rem 1.25rem; margin-top: 0.75rem; font-size: 0.88rem; font-weight: 600; }
        .fuel-stat-card { background: #f8f9fc; border: 1.5px solid #e0e4ef; border-radius: 0.75rem; padding: 1rem; text-align: center; }
        .fuel-stat-card .stat-val { font-size: 1.4rem; font-weight: 800; color: #4e73df; }
        .fuel-stat-card .stat-lbl { font-size: 0.72rem; color: #888; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }
        .btn-save { background: linear-gradient(135deg, #4e73df, #224abe); color: #fff; border: none; border-radius: 0.6rem; padding: 0.65rem 2rem; font-weight: 700; font-size: 0.95rem; transition: opacity .2s; }
        .btn-save:hover { opacity: 0.9; color: #fff; }
        .btn-cancel { border-radius: 0.6rem; padding: 0.65rem 1.5rem; font-weight: 600; }
        /* Autocomplete */
        .autocomplete-wrap { position: relative; }
        .autocomplete-list { position: fixed; z-index: 99999; background: #fff; border: 1.5px solid #4e73df; border-radius: 0.5rem; box-shadow: 0 8px 24px rgba(78,115,223,.18); max-height: 260px; overflow-y: auto; display: none; }
        .autocomplete-list .ac-header { padding: 6px 14px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em; color: #4e73df; background: #f0f4ff; border-bottom: 1px solid #e0e8ff; }
        .autocomplete-list .ac-item { padding: 9px 14px; cursor: pointer; font-size: 0.85rem; border-bottom: 1px solid #f5f5f5; display: flex; align-items: center; gap: 8px; }
        .autocomplete-list .ac-item:hover { background: #eef2ff; }
        .autocomplete-list .ac-item i { color: #4e73df; font-size: 0.8rem; flex-shrink: 0; }
        .alert-validation { background: #fff3cd; border: 1px solid #ffc107; border-radius: 0.5rem; padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.88rem; }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="brand"><i class="fas fa-shuttle-van mr-2"></i>VanService</div>
        <div class="d-flex align-items-center gap-2">
            <span class="text-white mr-3" style="font-size:0.88rem;opacity:.85;"><i class="fas fa-user-circle mr-1"></i>{{ auth()->user()->name }}</span>
            <a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-outline-light mr-2"><i class="fas fa-arrow-left mr-1"></i>Back</a>
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt mr-1"></i>Logout</button>
            </form>
        </div>
    </div>

    <!-- Page Hero -->
    <div class="page-hero">
        <h2><i class="fas fa-plus-circle mr-2"></i>Schedule New Trip</h2>
        <p>Fill in the trip details. Passengers will be able to book this trip once it is created.</p>
    </div>

    <div class="container-fluid px-3 px-md-4 py-4" style="max-width:960px;">

        @if($errors->any())
        <div class="alert-validation">
            <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('user.trips.store') }}" id="tripForm">
            @csrf

            <!-- Section 1: Van Selection -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="icon-wrap" style="background:#eef2ff;"><i class="fas fa-shuttle-van" style="color:#4e73df;"></i></div>
                    <div>
                        <h5>Van Selection</h5>
                        <p>Choose the van for this trip</p>
                    </div>
                </div>
                <div class="section-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="field-label">Select Van</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-shuttle-van input-icon"></i>
                                <select name="van_id" id="van_id" class="form-control @error('van_id') is-invalid @enderror" required>
                                    <option value="">-- Select a Van --</option>
                                    @foreach($vans as $van)
                                    <option value="{{ $van->id }}"
                                        data-fuel="{{ $van->fuel_capacity }}"
                                        data-kml="{{ $van->km_per_liter }}"
                                        data-seats="{{ $van->total_seats }}"
                                        {{ old('van_id') == $van->id ? 'selected' : '' }}>
                                        {{ $van->van_model }} ({{ $van->van_plate ?? 'No Plate' }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('van_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="vanDetails" style="display:none; margin-top:1.5rem;">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="fuel-stat-card">
                                            <div class="stat-val" id="disp-fuel">-</div>
                                            <div class="stat-lbl">Fuel Cap (L)</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="fuel-stat-card">
                                            <div class="stat-val" id="disp-kml">-</div>
                                            <div class="stat-lbl">KM / Liter</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="fuel-stat-card">
                                            <div class="stat-val" id="disp-seats">-</div>
                                            <div class="stat-lbl">Seats</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Route Information -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="icon-wrap" style="background:#fff0f0;"><i class="fas fa-route" style="color:#e74a3b;"></i></div>
                    <div>
                        <h5>Route Information</h5>
                        <p>Set the pickup and drop-off locations for this trip</p>
                    </div>
                </div>
                <div class="section-card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="field-label"><i class="fas fa-circle mr-1" style="color:#1cc88a;font-size:0.6rem;"></i> Origin (Pickup)</label>
                            <div class="autocomplete-wrap">
                                <div class="input-icon-wrap">
                                    <i class="fas fa-map-marker-alt input-icon" style="color:#1cc88a;"></i>
                                    <input type="text" id="origin" name="pickup_location"
                                        class="form-control"
                                        placeholder="Select or type a campus..."
                                        value="{{ old('pickup_location', '') }}"
                                        autocomplete="off" required>
                                </div>
                                <div class="autocomplete-list" id="origin_list"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="field-label"><i class="fas fa-circle mr-1" style="color:#e74a3b;font-size:0.6rem;"></i> Destination (Drop-off)</label>
                            <div class="autocomplete-wrap">
                                <div class="input-icon-wrap">
                                    <i class="fas fa-map-marker-alt input-icon" style="color:#e74a3b;"></i>
                                    <input type="text" id="destination" name="dropoff_location"
                                        class="form-control"
                                        placeholder="Select or type a campus..."
                                        value="{{ old('dropoff_location', '') }}"
                                        autocomplete="off" required>
                                </div>
                                <div class="autocomplete-list" id="dest_list"></div>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="field-label">Route Name</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-tag input-icon"></i>
                                <input type="text" name="route_name" id="route_name"
                                    class="form-control @error('route_name') is-invalid @enderror"
                                    placeholder="e.g. NISU Main Campus → NISU Ajuy Campus"
                                    value="{{ old('route_name', '') }}" required>
                                @error('route_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Route Map -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="icon-wrap" style="background:#e8f8f5;"><i class="fas fa-map" style="color:#1cc88a;"></i></div>
                    <div>
                        <h5>Route Map</h5>
                        <p>Visualize and calculate the driving route</p>
                    </div>
                </div>
                <div class="section-card-body">
                    <div id="map"></div>
                    <div class="d-flex align-items-center mt-3 flex-wrap gap-2">
                        <button type="button" class="btn btn-sm mr-3" id="calcRouteBtn"
                            style="background:#eef2ff;color:#4e73df;border:1.5px solid #4e73df;border-radius:2rem;padding:0.4rem 1.2rem;font-weight:700;">
                            <i class="fas fa-route mr-1"></i> Calculate Route
                        </button>
                        <div id="routeResult" class="route-result">
                            <i class="fas fa-check-circle mr-1"></i>
                            <span id="routeResultText"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Trip Schedule -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="icon-wrap" style="background:#fff8e1;"><i class="fas fa-calendar-alt" style="color:#f6c23e;"></i></div>
                    <div>
                        <h5>Trip Schedule</h5>
                        <p>Set the date and time for this trip</p>
                    </div>
                </div>
                <div class="section-card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="field-label">Date of Trip</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-calendar input-icon"></i>
                                <input type="date" name="trip_date"
                                    class="form-control @error('trip_date') is-invalid @enderror"
                                    value="{{ old('trip_date', '') }}" required>
                                @error('trip_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="field-label">Departure Time</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-clock input-icon"></i>
                                <input type="time" name="departure_time"
                                    class="form-control @error('departure_time') is-invalid @enderror"
                                    value="{{ old('departure_time', '') }}" required>
                                @error('departure_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="field-label">ETA (Estimated Arrival)</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-flag-checkered input-icon"></i>
                                <input type="time" name="eta" id="eta"
                                    class="form-control"
                                    value="{{ old('eta', '') }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="field-label">Total Seats</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-users input-icon"></i>
                                <input type="number" name="total_seats" id="total_seats"
                                    class="form-control"
                                    value="{{ old('total_seats', 12) }}" min="1" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Fuel Details -->
            <div class="section-card">
                <div class="section-card-header">
                    <div class="icon-wrap" style="background:#fef0e6;"><i class="fas fa-gas-pump" style="color:#fd7e14;"></i></div>
                    <div>
                        <h5>Fuel Details</h5>
                        <p>Fuel consumption estimate for this trip</p>
                    </div>
                </div>
                <div class="section-card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="field-label">Fuel Capacity (L)</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-tint input-icon"></i>
                                <input type="number" step="0.01" id="fuel_capacity_disp" class="form-control" readonly placeholder="Select van first">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="field-label">Fuel Efficiency (km/L)</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-tachometer-alt input-icon"></i>
                                <input type="number" step="0.01" id="kml_disp" class="form-control" readonly placeholder="Select van first">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="field-label">Distance (km)</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-road input-icon"></i>
                                <input type="number" step="0.01" id="distance_km" name="distance_km" class="form-control" value="{{ old('distance_km', 0) }}" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="field-label">Fuel Price (₱/L)</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-peso-sign input-icon"></i>
                                <input type="number" step="0.01" id="fuel_price" class="form-control" value="65.00" placeholder="65.00">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="field-label">Estimated Fuel Needed (L)</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-gas-pump input-icon"></i>
                                <input type="number" step="0.01" id="fuel_needed" name="estimated_fuel_needed" class="form-control" value="{{ old('estimated_fuel_needed', 0) }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="field-label">Estimated Fuel Cost (₱)</label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-money-bill input-icon"></i>
                                <input type="number" step="0.01" id="fuel_cost" class="form-control" readonly placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3" id="fuelSummaryWrap" style="display:none;">
                            <label class="field-label">&nbsp;</label>
                            <div class="fuel-stat-card" style="background:linear-gradient(135deg,#fd7e14,#e8590c);color:#fff;border:none;">
                                <div class="stat-val" id="fuelSummaryVal" style="color:#fff;">-</div>
                                <div class="stat-lbl" style="color:rgba(255,255,255,.8);">Total Estimated Cost</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex align-items-center gap-3 mb-5">
                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save mr-2"></i>Save Trip
                </button>
                <a href="{{ route('user.dashboard') }}" class="btn btn-secondary btn-cancel">
                    <i class="fas fa-times mr-1"></i>Cancel
                </a>
            </div>

        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
    <script>
    // ── NISU Campus Presets ──────────────────────────────────────────────
    var PRESETS = [
        { name: 'NISU Main Campus — Estancia, Iloilo',                   lat: 11.4583, lon: 123.1561 },
        { name: 'NISU West Campus — Estancia, Iloilo',                   lat: 11.4560, lon: 123.1520 },
        { name: 'NISU Ajuy Campus — Ajuy, Iloilo',                       lat: 11.1733, lon: 122.9167 },
        { name: 'NISU Barotac Viejo Campus — Barotac Viejo, Iloilo',     lat: 11.0500, lon: 122.8667 },
        { name: 'NISU Batad Campus — Batad, Iloilo',                     lat: 11.3833, lon: 123.0167 },
        { name: 'NISU Concepcion Campus — Concepcion, Iloilo',           lat: 11.2167, lon: 123.1000 },
        { name: 'NISU Lemery Campus — Lemery, Iloilo',                   lat: 11.3667, lon: 122.8833 },
        { name: 'NISU Victorino Salcedo Campus — Sara, Iloilo',          lat: 11.2600, lon: 123.0200 },
    ];

    // ── Map ──────────────────────────────────────────────────────────────
    var map = L.map('map').setView([11.3, 123.0], 8);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    var originMarker = null, destMarker = null, routingControl = null;
    var vanKml = 0;

    function makeIcon(color) {
        return L.icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + color + '.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
        });
    }

    function updateMapMarker(type, lat, lon, label) {
        if (type === 'origin') {
            if (originMarker) map.removeLayer(originMarker);
            originMarker = L.marker([lat, lon], { icon: makeIcon('green') }).addTo(map).bindPopup('<b>Pickup:</b> ' + label).openPopup();
        } else {
            if (destMarker) map.removeLayer(destMarker);
            destMarker = L.marker([lat, lon], { icon: makeIcon('red') }).addTo(map).bindPopup('<b>Drop-off:</b> ' + label).openPopup();
        }
        if (originMarker && destMarker) {
            autoCalcRoute();
        }
    }

    // ── Autocomplete ─────────────────────────────────────────────────────
    function positionList(input, list) {
        var rect = input.getBoundingClientRect();
        list.style.left   = rect.left + 'px';
        list.style.top    = (rect.bottom + window.scrollY) + 'px';
        list.style.width  = rect.width + 'px';
    }

    function showPresets(input, list) {
        list.innerHTML = '';
        var hdr = document.createElement('div');
        hdr.className = 'ac-header';
        hdr.textContent = '🏫 NISU Campuses';
        list.appendChild(hdr);
        PRESETS.forEach(function(p) {
            var item = document.createElement('div');
            item.className = 'ac-item';
            item.innerHTML = '<i class="fas fa-university"></i>' + p.name;
            item.onmousedown = function(e) {
                e.preventDefault();
                input.value = p.name;
                list.style.display = 'none';
                var type = input.id === 'origin' ? 'origin' : 'dest';
                updateMapMarker(type, p.lat, p.lon, p.name);
                autoFillRouteName();
            };
            list.appendChild(item);
        });
        positionList(input, list);
        list.style.display = 'block';
    }

    function setupAutocomplete(inputId, listId) {
        var input = document.getElementById(inputId);
        var list  = document.getElementById(listId);
        var timer = null;

        input.addEventListener('focus', function() { showPresets(input, list); });
        input.addEventListener('click', function() { showPresets(input, list); });

        input.addEventListener('input', function() {
            var q = this.value.trim();
            clearTimeout(timer);
            if (!q) { showPresets(input, list); return; }

            var matched = PRESETS.filter(function(p) {
                return p.name.toLowerCase().indexOf(q.toLowerCase()) !== -1;
            });

            list.innerHTML = '';
            if (matched.length) {
                var hdr = document.createElement('div');
                hdr.className = 'ac-header';
                hdr.textContent = '🏫 NISU Campuses';
                list.appendChild(hdr);
                matched.forEach(function(p) {
                    var item = document.createElement('div');
                    item.className = 'ac-item';
                    item.innerHTML = '<i class="fas fa-university"></i>' + p.name;
                    item.onmousedown = function(e) {
                        e.preventDefault();
                        input.value = p.name;
                        list.style.display = 'none';
                        var type = inputId === 'origin' ? 'origin' : 'dest';
                        updateMapMarker(type, p.lat, p.lon, p.name);
                        autoFillRouteName();
                    };
                    list.appendChild(item);
                });
                positionList(input, list);
                list.style.display = 'block';
            }

            if (q.length >= 3) {
                timer = setTimeout(function() {
                    fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(q) + '&limit=4&countrycodes=ph')
                        .then(function(r) { return r.json(); })
                        .then(function(results) {
                            if (!results.length) return;
                            var hdr2 = document.createElement('div');
                            hdr2.className = 'ac-header';
                            hdr2.textContent = '📍 Other Locations';
                            list.appendChild(hdr2);
                            results.forEach(function(r) {
                                var item = document.createElement('div');
                                item.className = 'ac-item';
                                item.innerHTML = '<i class="fas fa-map-marker-alt"></i>' + r.display_name;
                                item.onmousedown = function(e) {
                                    e.preventDefault();
                                    input.value = r.display_name;
                                    list.style.display = 'none';
                                    var type = inputId === 'origin' ? 'origin' : 'dest';
                                    updateMapMarker(type, parseFloat(r.lat), parseFloat(r.lon), r.display_name);
                                    autoFillRouteName();
                                };
                                list.appendChild(item);
                            });
                            positionList(input, list);
                            list.style.display = 'block';
                        }).catch(function() {});
                }, 400);
            }
        });

        input.addEventListener('blur', function() {
            setTimeout(function() { list.style.display = 'none'; }, 150);
        });

        window.addEventListener('scroll', function() {
            if (list.style.display === 'block') positionList(input, list);
        });
        window.addEventListener('resize', function() {
            if (list.style.display === 'block') positionList(input, list);
        });
    }

    setupAutocomplete('origin', 'origin_list');
    setupAutocomplete('destination', 'dest_list');

    // ── Auto-fill Route Name ─────────────────────────────────────────────
    function autoFillRouteName() {
        var o = document.getElementById('origin').value.trim();
        var d = document.getElementById('destination').value.trim();
        if (o && d) {
            document.getElementById('route_name').value = o + ' → ' + d;
        }
    }

    // ── Route Calculation ────────────────────────────────────────────────
    function geocode(str) {
        var lower = str.toLowerCase();
        for (var i = 0; i < PRESETS.length; i++) {
            if (PRESETS[i].name.toLowerCase().indexOf(lower) !== -1 || lower.indexOf(PRESETS[i].name.toLowerCase().split(' —')[0].toLowerCase()) !== -1) {
                return Promise.resolve({ lat: PRESETS[i].lat, lon: PRESETS[i].lon });
            }
        }
        return fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(str) + '&limit=1&countrycodes=ph')
            .then(function(r) { return r.json(); })
            .then(function(res) { return res[0] ? { lat: parseFloat(res[0].lat), lon: parseFloat(res[0].lon) } : null; });
    }

    function calcFuel(distKm) {
        if (vanKml > 0 && distKm > 0) {
            var fuel = (distKm / vanKml).toFixed(2);
            document.getElementById('fuel_needed').value = fuel;
            var price = parseFloat(document.getElementById('fuel_price').value) || 65;
            var cost = (fuel * price).toFixed(2);
            document.getElementById('fuel_cost').value = cost;
            document.getElementById('fuelSummaryVal').textContent = '₱' + parseFloat(cost).toLocaleString('en-PH', {minimumFractionDigits:2});
            document.getElementById('fuelSummaryWrap').style.display = 'block';
        }
    }

    function drawRoute(oLat, oLon, dLat, dLon, oLabel, dLabel) {
        if (routingControl) { map.removeControl(routingControl); routingControl = null; }
        routingControl = L.Routing.control({
            waypoints: [L.latLng(oLat, oLon), L.latLng(dLat, dLon)],
            routeWhileDragging: false, addWaypoints: false, draggableWaypoints: false,
            fitSelectedRoutes: true, show: false,
            lineOptions: { styles: [{ color: '#4e73df', weight: 5, opacity: 0.85 }] },
            createMarker: function(i, wp) {
                return L.marker(wp.latLng, { icon: makeIcon(i === 0 ? 'green' : 'red') })
                    .bindPopup(i === 0 ? '<b>Pickup:</b> ' + oLabel : '<b>Drop-off:</b> ' + dLabel);
            }
        }).addTo(map);
        routingControl.on('routesfound', function(e) {
            var dist = (e.routes[0].summary.totalDistance / 1000).toFixed(2);
            var mins = Math.round(e.routes[0].summary.totalTime / 60);
            document.getElementById('distance_km').value = dist;
            calcFuel(parseFloat(dist));
            var btn = document.getElementById('calcRouteBtn');
            btn.innerHTML = '<i class="fas fa-route mr-1"></i> Recalculate Route';
            btn.disabled = false;
            var res = document.getElementById('routeResult');
            document.getElementById('routeResultText').textContent = dist + ' km · ~' + mins + ' min drive';
            res.style.display = 'inline-flex';
        });
    }

    function autoCalcRoute() {
        var o = document.getElementById('origin').value.trim();
        var d = document.getElementById('destination').value.trim();
        if (!o || !d) return;
        Promise.all([geocode(o), geocode(d)]).then(function(res) {
            if (res[0] && res[1]) drawRoute(res[0].lat, res[0].lon, res[1].lat, res[1].lon, o, d);
        });
    }

    document.getElementById('calcRouteBtn').addEventListener('click', function() {
        var o = document.getElementById('origin').value.trim();
        var d = document.getElementById('destination').value.trim();
        if (!o || !d) { alert('Please enter both Origin and Destination first.'); return; }
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Calculating...';
        autoCalcRoute();
    });

    document.getElementById('distance_km').addEventListener('input', function() { calcFuel(parseFloat(this.value)); });
    document.getElementById('fuel_price').addEventListener('input', function() {
        var dist = parseFloat(document.getElementById('distance_km').value) || 0;
        if (dist > 0) calcFuel(dist);
    });
    document.getElementById('fuel_needed').addEventListener('input', function() {
        var price = parseFloat(document.getElementById('fuel_price').value) || 65;
        var cost = (parseFloat(this.value) * price).toFixed(2);
        document.getElementById('fuel_cost').value = cost;
        document.getElementById('fuelSummaryVal').textContent = '₱' + parseFloat(cost).toLocaleString('en-PH', {minimumFractionDigits:2});
        document.getElementById('fuelSummaryWrap').style.display = 'block';
    });

    // ── Van Selection ────────────────────────────────────────────────────
    document.getElementById('van_id').addEventListener('change', function() {
        var opt = this.options[this.selectedIndex];
        var fuel  = opt.getAttribute('data-fuel');
        var kml   = opt.getAttribute('data-kml');
        var seats = opt.getAttribute('data-seats');
        if (fuel) {
            document.getElementById('vanDetails').style.display = 'block';
            document.getElementById('disp-fuel').textContent = fuel;
            document.getElementById('disp-kml').textContent  = kml;
            document.getElementById('disp-seats').textContent = seats;
            document.getElementById('fuel_capacity_disp').value = fuel;
            document.getElementById('kml_disp').value = kml;
            document.getElementById('total_seats').value = seats;
            vanKml = parseFloat(kml);
            var dist = parseFloat(document.getElementById('distance_km').value) || 0;
            if (dist > 0) calcFuel(dist);
        } else {
            document.getElementById('vanDetails').style.display = 'none';
            vanKml = 0;
        }
    });
    </script>
</body>
</html>

@extends('layouts.admin')
@section('title','Add Trip')
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<style>
#map { height: 350px; border-radius: 0.5rem; border: 1px solid #dee2e6; z-index: 0; }
.fuel-card { background: linear-gradient(135deg, #4e73df, #224abe); color: #fff; border-radius: 0.5rem; padding: 1rem; }
.leaflet-routing-container { display: none !important; }
.autocomplete-wrapper { position: relative; }
.autocomplete-list { position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; background: #fff; border: 1px solid #ced4da; border-top: none; border-radius: 0 0 0.35rem 0.35rem; max-height: 200px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,.1); }
.autocomplete-list .autocomplete-item { padding: 8px 12px; cursor: pointer; font-size: 0.875rem; border-bottom: 1px solid #f0f0f0; }
.autocomplete-list .autocomplete-item:hover { background: #e8f0fe; }
.autocomplete-list .autocomplete-item:last-child { border-bottom: none; }
</style>
@endpush
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-plus mr-2"></i>Schedule New Trip</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.trips.store') }}" id="tripForm">
            @csrf
            <div class="row">
                <!-- Van Selection -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-shuttle-van mr-1"></i>Select Van</label>
                        <select name="van_id" id="van_id" class="form-control @error('van_id') is-invalid @enderror" required>
                            <option value="">-- Select Van --</option>
                            @foreach($vans as $van)
                            <option value="{{ $van->id }}"
                                data-fuel="{{ $van->fuel_capacity }}"
                                data-kml="{{ $van->km_per_liter }}"
                                data-seats="{{ $van->total_seats }}"
                                {{ old('van_id')==$van->id?'selected':'' }}>
                                {{ $van->van_model }} ({{ $van->van_plate ?? 'No Plate' }})
                            </option>
                            @endforeach
                        </select>
                        @error('van_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <!-- Van Details Display -->
                <div class="col-md-6">
                    <div class="fuel-card mt-2" id="vanDetails" style="display:none">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="small">Fuel Cap</div>
                                <strong id="disp-fuel">-</strong> L
                            </div>
                            <div class="col-4">
                                <div class="small">KM/L</div>
                                <strong id="disp-kml">-</strong>
                            </div>
                            <div class="col-4">
                                <div class="small">Seats</div>
                                <strong id="disp-seats">-</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Origin -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt mr-1 text-success"></i>Origin (Pickup)</label>
                        <div class="autocomplete-wrapper">
                            <input type="text" id="origin" name="pickup_location" class="form-control" placeholder="Type to search location..." value="{{ old('pickup_location') }}" autocomplete="off" required>
                            <div class="autocomplete-list" id="origin-list" style="display:none"></div>
                        </div>
                    </div>
                </div>
                <!-- Destination -->
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt mr-1 text-danger"></i>Destination (Drop-off)</label>
                        <div class="autocomplete-wrapper">
                            <input type="text" id="destination" name="dropoff_location" class="form-control" placeholder="Type to search location..." value="{{ old('dropoff_location') }}" autocomplete="off" required>
                            <input type="hidden" id="dropoff_latitude" name="dropoff_latitude" value="{{ old('dropoff_latitude') }}">
                            <input type="hidden" id="dropoff_longitude" name="dropoff_longitude" value="{{ old('dropoff_longitude') }}">
                            <input type="hidden" name="arrival_geofence_radius_meters" value="150">
                            <div class="autocomplete-list" id="destination-list" style="display:none"></div>
                        </div>
                    </div>
                </div>

                <!-- Search Route Button -->
                <div class="col-12 mb-2">
                    <button type="button" class="btn btn-info btn-sm" id="calcRouteBtn">
                        <i class="fas fa-search-location mr-1"></i>Calculate Route & Distance
                    </button>
                    <small class="text-muted ml-2">Enter origin and destination above, then click to calculate.</small>
                </div>

                <!-- Map -->
                <div class="col-12 mb-3">
                    <div id="map"></div>
                </div>

                <!-- Fuel Calculation -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-road mr-1"></i>Distance (KM)</label>
                        <input type="number" step="0.01" id="distance_km" name="distance_km" class="form-control" value="{{ old('distance_km',0) }}">
                        <small class="text-muted">Auto-filled after calculating route, or enter manually.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-gas-pump mr-1"></i>Fuel Needed (L)</label>
                        <input type="number" step="0.01" id="fuel_needed" name="estimated_fuel_needed" class="form-control" value="{{ old('estimated_fuel_needed',0) }}" readonly>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-calendar mr-1"></i>Trip Date</label>
                        <input type="date" name="trip_date" class="form-control @error('trip_date') is-invalid @enderror" value="{{ old('trip_date') }}" required>
                        @error('trip_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-clock mr-1"></i>Departure Time</label>
                        <input type="time" name="departure_time" class="form-control @error('departure_time') is-invalid @enderror" value="{{ old('departure_time') }}" required>
                        @error('departure_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-flag-checkered mr-1"></i>Estimated Time of Arrival (ETA)</label>
                        <input type="time" name="eta" id="eta" class="form-control" value="{{ old('eta') }}" placeholder="e.g. 14:30">
                        <small class="text-muted">Expected arrival time at destination.</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Total Seats</label>
                        <input type="number" name="total_seats" id="total_seats" class="form-control" value="{{ old('total_seats',12) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Route Name</label>
                        <input type="text" name="route_name" class="form-control @error('route_name') is-invalid @enderror" placeholder="e.g. Manila to Cebu" value="{{ old('route_name') }}" required>
                        @error('route_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Trip</button>
            <a href="{{ route('admin.trips.index') }}" class="btn btn-secondary ml-2">Cancel</a>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
var tripMap, routingControl;
var vanKml = 0;
var autocompleteTimers = {};

// Preset locations — All NISU Campuses
var PRESET_LOCATIONS = [
    { name: 'NISU Main Campus - Estancia, Iloilo', lat: 11.4583, lon: 123.1561 },
    { name: 'NISU West Campus - Estancia, Iloilo', lat: 11.4560, lon: 123.1520 },
    { name: 'NISU Ajuy Campus - Barangay San Antonio, Ajuy, Iloilo', lat: 11.1733, lon: 122.9167 },
    { name: 'NISU Barotac Viejo Campus - Barangay Puerto Princesa, Barotac Viejo, Iloilo', lat: 11.0500, lon: 122.8667 },
    { name: 'NISU Batad Campus - Batad, Iloilo', lat: 11.3833, lon: 123.0167 },
    { name: 'NISU Concepcion Campus - Concepcion, Iloilo', lat: 11.2167, lon: 123.1000 },
    { name: 'NISU Lemery Campus - Lemery, Iloilo', lat: 11.3667, lon: 122.8833 },
    { name: 'NISU Victorino Salcedo Campus - Barangay Anoring, Sara, Iloilo', lat: 11.2600, lon: 123.0200 },
];

// Nominatim autocomplete function with preset locations
function setupAutocomplete(inputId, listId) {
    var input = document.getElementById(inputId);
    var list = document.getElementById(listId);

    // Show presets when input is focused and empty
    input.addEventListener('focus', function() {
        if (this.value.trim().length === 0) {
            showPresets(list, input);
        }
    });

    input.addEventListener('input', function() {
        var query = this.value.trim();
        clearTimeout(autocompleteTimers[inputId]);
        if (query.length === 0) { showPresets(list, input); return; }
        if (query.length < 3) { list.style.display = 'none'; return; }

        // Filter presets matching query
        var matchedPresets = PRESET_LOCATIONS.filter(function(p) {
            return p.name.toLowerCase().indexOf(query.toLowerCase()) !== -1;
        });

        autocompleteTimers[inputId] = setTimeout(function() {
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query) + '&limit=5&addressdetails=1&countrycodes=ph')
                .then(r => r.json())
                .then(function(results) {
                    list.innerHTML = '';
                    // Show matched presets first
                    matchedPresets.forEach(function(p) {
                        var item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        item.innerHTML = '<span style="color:#4e73df;font-weight:700;">&#9733; </span>' + p.name;
                        item.addEventListener('click', function() {
                            input.value = p.name;
                            list.style.display = 'none';
                        });
                        list.appendChild(item);
                    });
                    // Then Nominatim results
                    results.forEach(function(result) {
                        var item = document.createElement('div');
                        item.className = 'autocomplete-item';
                        item.textContent = result.display_name;
                        item.addEventListener('click', function() {
                            input.value = result.display_name;
                            list.style.display = 'none';
                        });
                        list.appendChild(item);
                    });
                    if (list.children.length > 0) list.style.display = 'block';
                    else list.style.display = 'none';
                })
                .catch(function() { list.style.display = 'none'; });
        }, 400);
    });
    // Hide list when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !list.contains(e.target)) {
            list.style.display = 'none';
        }
    });
}

function showPresets(list, input) {
    list.innerHTML = '';
    var header = document.createElement('div');
    header.style.cssText = 'padding:6px 12px; font-size:0.75rem; color:#888; font-weight:700; text-transform:uppercase; letter-spacing:0.05rem; background:#f8f9fa; border-bottom:1px solid #eee;';
    header.textContent = 'Common Locations';
    list.appendChild(header);
    PRESET_LOCATIONS.forEach(function(p) {
        var item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.innerHTML = '<span style="color:#4e73df;font-weight:700;">&#9733; </span>' + p.name;
        item.addEventListener('click', function() {
            input.value = p.name;
            list.style.display = 'none';
        });
        list.appendChild(item);
    });
    list.style.display = 'block';
}

// Initialize autocomplete for both fields
setupAutocomplete('origin', 'origin-list');
setupAutocomplete('destination', 'destination-list');

// Initialize Leaflet map
tripMap = L.map('map').setView([11.0, 122.5], 7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(tripMap);

// Geocode using presets first, then Nominatim fallback
function geocodeForRoute(locationStr) {
    var lower = locationStr.toLowerCase();
    for (var i = 0; i < PRESET_LOCATIONS.length; i++) {
        var presetLower = PRESET_LOCATIONS[i].name.toLowerCase();
        if (lower === presetLower ||
            lower.indexOf(presetLower.split(' - ')[0].toLowerCase()) !== -1 ||
            presetLower.indexOf(lower) !== -1) {
            return Promise.resolve([{ lat: PRESET_LOCATIONS[i].lat, lon: PRESET_LOCATIONS[i].lon }]);
        }
    }
    return fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(locationStr) + '&limit=1&countrycodes=ph').then(r => r.json());
}

// Calculate route using preset coords or Nominatim geocoding + Leaflet Routing Machine
document.getElementById('calcRouteBtn').addEventListener('click', function() {
    var origin = document.getElementById('origin').value.trim();
    var dest = document.getElementById('destination').value.trim();
    if (!origin || !dest) {
        alert('Please enter both Origin and Destination first.');
        return;
    }

    var btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Calculating...';

    // Geocode both locations (presets first, then Nominatim)
    Promise.all([
        geocodeForRoute(origin),
        geocodeForRoute(dest)
    ]).then(function(results) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search-location mr-1"></i>Calculate Route & Distance';

        var originResult = results[0][0];
        var destResult = results[1][0];

        if (!originResult) { alert('Could not find origin location. Try a more specific address.'); return; }
        if (!destResult) { alert('Could not find destination location. Try a more specific address.'); return; }

        var originLatLng = L.latLng(parseFloat(originResult.lat), parseFloat(originResult.lon));
        var destLatLng = L.latLng(parseFloat(destResult.lat), parseFloat(destResult.lon));
        document.getElementById('dropoff_latitude').value = parseFloat(destResult.lat).toFixed(7);
        document.getElementById('dropoff_longitude').value = parseFloat(destResult.lon).toFixed(7);

        // Remove old routing
        if (routingControl) { tripMap.removeControl(routingControl); }

        routingControl = L.Routing.control({
            waypoints: [originLatLng, destLatLng],
            routeWhileDragging: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            show: false,
            lineOptions: {
                styles: [{ color: '#4e73df', weight: 5, opacity: 0.8 }]
            },
            createMarker: function(i, waypoint) {
                var label = i === 0 ? '<b>Pickup:</b> ' + origin : '<b>Drop-off:</b> ' + dest;
                var color = i === 0 ? 'green' : 'red';
                var icon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-' + color + '.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
                });
                return L.marker(waypoint.latLng, { icon: icon }).bindPopup(label);
            }
        }).addTo(tripMap);

        routingControl.on('routesfound', function(e) {
            var routes = e.routes;
            if (routes && routes.length > 0) {
                var distKm = (routes[0].summary.totalDistance / 1000).toFixed(2);
                document.getElementById('distance_km').value = distKm;
                calcFuel(distKm);
            }
        });

    }).catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search-location mr-1"></i>Calculate Route & Distance';
        alert('Error calculating route. Please check your internet connection and try again.');
    });
});

// Manual distance input also recalculates fuel
document.getElementById('distance_km').addEventListener('input', function() {
    calcFuel(this.value);
});

function calcFuel(distKm) {
    if (vanKml > 0 && distKm > 0) {
        var fuel = (distKm / vanKml).toFixed(2);
        document.getElementById('fuel_needed').value = fuel;
    }
}

document.getElementById('van_id').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    var fuel = opt.getAttribute('data-fuel');
    var kml = opt.getAttribute('data-kml');
    var seats = opt.getAttribute('data-seats');
    if (fuel) {
        document.getElementById('vanDetails').style.display = 'block';
        document.getElementById('disp-fuel').textContent = fuel;
        document.getElementById('disp-kml').textContent = kml;
        document.getElementById('disp-seats').textContent = seats;
        document.getElementById('total_seats').value = seats;
        vanKml = parseFloat(kml);
        var dist = document.getElementById('distance_km').value;
        if (dist > 0) calcFuel(dist);
    } else {
        document.getElementById('vanDetails').style.display = 'none';
    }
});
</script>
@endpush

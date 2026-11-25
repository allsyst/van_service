<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Van Driver - Share Location</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0d1b2a 0%, #1b3a5c 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            padding: 15px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .header h4 { margin: 0; font-weight: 700; font-size: 1.2rem; }
        .header p { margin: 0; font-size: 0.8rem; opacity: 0.7; }
        .container-main { padding: 20px; max-width: 500px; margin: 0 auto; }
        .card-dark {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            backdrop-filter: blur(10px);
        }
        .form-label { font-weight: 600; font-size: 0.9rem; opacity: 0.9; }
        .form-control, .form-select {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.4); }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.15);
            border-color: #4fc3f7;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(79,195,247,0.2);
        }
        .form-select option { background: #1b3a5c; color: #fff; }
        .btn-start {
            background: linear-gradient(135deg, #00c853, #00e676);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 14px;
            border-radius: 12px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,200,83,0.4);
        }
        .btn-start:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,200,83,0.5); }
        .btn-stop {
            background: linear-gradient(135deg, #f44336, #ef5350);
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 14px;
            border-radius: 12px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(244,67,54,0.4);
            display: none;
        }
        .btn-stop:hover { transform: translateY(-2px); }
        .status-box {
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-bottom: 16px;
            display: none;
        }
        .status-online {
            background: rgba(0,200,83,0.15);
            border: 1px solid rgba(0,200,83,0.4);
        }
        .status-offline {
            background: rgba(244,67,54,0.15);
            border: 1px solid rgba(244,67,54,0.4);
        }
        .status-dot {
            width: 12px; height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 1.5s infinite;
        }
        .dot-green { background: #00e676; }
        .dot-red { background: #f44336; animation: none; }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0,230,118,0.6); }
            70% { box-shadow: 0 0 0 8px rgba(0,230,118,0); }
            100% { box-shadow: 0 0 0 0 rgba(0,230,118,0); }
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            font-size: 0.9rem;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { opacity: 0.6; }
        .info-value { font-weight: 600; }
        #map-preview {
            height: 200px;
            border-radius: 12px;
            overflow: hidden;
            display: none;
            margin-top: 12px;
        }
        .setup-section { display: block; }
        .tracking-section { display: none; }
        .error-msg {
            background: rgba(244,67,54,0.15);
            border: 1px solid rgba(244,67,54,0.4);
            border-radius: 10px;
            padding: 12px;
            font-size: 0.85rem;
            display: none;
            margin-top: 10px;
        }
        .update-count {
            font-size: 0.75rem;
            opacity: 0.6;
            text-align: center;
            margin-top: 8px;
        }
    </style>
</head>
<body>

<div class="header">
    <h4>🚐 VanService Driver</h4>
    <p>Location Sharing</p>
</div>

<div class="container-main">

    {{-- Setup Section --}}
    <div class="setup-section" id="setupSection">
        <div class="card-dark">
            <h6 class="mb-3" style="font-weight:700;">Start Sharing Your Location</h6>

            <div class="mb-3">
                <label class="form-label">Select Your Van</label>
                <select class="form-select" id="vanSelect">
                    <option value="">-- Select Van --</option>
                    @foreach($vans as $van)
                        <option value="{{ $van->id }}">{{ $van->van_plate }} — {{ $van->van_model }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Driver Name (Optional)</label>
                <input type="text" class="form-control" id="driverName" placeholder="Enter your name">
            </div>

            <div class="error-msg" id="errorMsg"></div>

            <button class="btn-start" id="startBtn" onclick="startTracking()">
                📍 Start Sharing Location
            </button>
        </div>

        <div class="card-dark" style="font-size:0.85rem; opacity:0.8;">
            <strong>How it works:</strong><br>
            1. Select your van from the list<br>
            2. Tap "Start Sharing Location"<br>
            3. Allow location access when prompted<br>
            4. Keep this page open while driving<br>
            5. Tap "Stop" when trip is done
        </div>
    </div>

    {{-- Tracking Section --}}
    <div class="tracking-section" id="trackingSection">

        <div class="status-box status-online" id="statusBox" style="display:block;">
            <span class="status-dot dot-green"></span>
            <strong>Location Sharing Active</strong>
            <div style="font-size:0.8rem; opacity:0.7; margin-top:4px;">Admin can see your location live</div>
        </div>

        <div class="card-dark">
            <div class="info-row">
                <span class="info-label">Van</span>
                <span class="info-value" id="infoVan">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">Driver</span>
                <span class="info-value" id="infoDriver">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">Latitude</span>
                <span class="info-value" id="infoLat">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">Longitude</span>
                <span class="info-value" id="infoLng">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">Speed</span>
                <span class="info-value" id="infoSpeed">—</span>
            </div>
            <div class="info-row">
                <span class="info-label">Last Update</span>
                <span class="info-value" id="infoTime">—</span>
            </div>

            <div id="map-preview"></div>
            <div class="update-count" id="updateCount"></div>
        </div>

        <button class="btn-stop" id="stopBtn" style="display:block;" onclick="stopTracking()">
            ⏹ Stop Sharing Location
        </button>
    </div>

</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let watchId = null;
    let updateInterval = null;
    let currentLat = null;
    let currentLng = null;
    let currentSpeed = null;
    let currentHeading = null;
    let updateCount = 0;
    let map = null;
    let marker = null;
    let selectedVanId = null;
    let driverName = null;

    function showError(msg) {
        const el = document.getElementById('errorMsg');
        el.textContent = msg;
        el.style.display = 'block';
    }

    function hideError() {
        document.getElementById('errorMsg').style.display = 'none';
    }

    function startTracking() {
        hideError();
        selectedVanId = document.getElementById('vanSelect').value;
        driverName = document.getElementById('driverName').value.trim();

        if (!selectedVanId) {
            showError('Please select a van first.');
            return;
        }

        if (!navigator.geolocation) {
            showError('GPS is not supported on this device.');
            return;
        }

        // Show tracking section
        document.getElementById('setupSection').style.display = 'none';
        document.getElementById('trackingSection').style.display = 'block';

        // Set van info
        const vanSelect = document.getElementById('vanSelect');
        document.getElementById('infoVan').textContent = vanSelect.options[vanSelect.selectedIndex].text;
        document.getElementById('infoDriver').textContent = driverName || 'Not specified';

        // Start watching GPS
        watchId = navigator.geolocation.watchPosition(
            onPositionUpdate,
            onPositionError,
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );

        // Send update every 5 seconds
        updateInterval = setInterval(sendLocation, 5000);
    }

    function onPositionUpdate(position) {
        currentLat = position.coords.latitude;
        currentLng = position.coords.longitude;
        currentSpeed = position.coords.speed ? (position.coords.speed * 3.6).toFixed(1) : 0; // m/s to km/h
        currentHeading = position.coords.heading;

        // Update display
        document.getElementById('infoLat').textContent = currentLat.toFixed(6);
        document.getElementById('infoLng').textContent = currentLng.toFixed(6);
        document.getElementById('infoSpeed').textContent = currentSpeed + ' km/h';
        document.getElementById('infoTime').textContent = new Date().toLocaleTimeString();

        // Update map
        updateMap(currentLat, currentLng);

        // Send immediately on first fix
        if (updateCount === 0) {
            sendLocation();
        }
    }

    function onPositionError(error) {
        console.error('GPS error:', error.message);
        document.getElementById('statusBox').innerHTML =
            '<span class="status-dot dot-red"></span><strong>GPS Error</strong><div style="font-size:0.8rem;opacity:0.7;margin-top:4px;">' + error.message + '</div>';
    }

    function updateMap(lat, lng) {
        const mapDiv = document.getElementById('map-preview');
        mapDiv.style.display = 'block';

        if (!map) {
            map = L.map('map-preview', { zoomControl: false, attributionControl: false }).setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            const vanIcon = L.divIcon({
                html: '<div style="font-size:28px;">🚐</div>',
                className: '',
                iconAnchor: [14, 14]
            });
            marker = L.marker([lat, lng], { icon: vanIcon }).addTo(map);
        } else {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 16);
        }
    }

    function sendLocation() {
        if (!currentLat || !currentLng || !selectedVanId) return;

        fetch('{{ route("driver.location.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                van_id: selectedVanId,
                latitude: currentLat,
                longitude: currentLng,
                speed: currentSpeed,
                heading: currentHeading,
                driver_name: driverName
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCount++;
                document.getElementById('updateCount').textContent = 'Updates sent: ' + updateCount;
            }
        })
        .catch(err => console.error('Send error:', err));
    }

    function stopTracking() {
        if (watchId) navigator.geolocation.clearWatch(watchId);
        if (updateInterval) clearInterval(updateInterval);

        // Tell server this van is offline
        fetch('{{ route("driver.location.offline") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ van_id: selectedVanId })
        });

        // Go back to setup
        document.getElementById('trackingSection').style.display = 'none';
        document.getElementById('setupSection').style.display = 'block';
        currentLat = null; currentLng = null; updateCount = 0; map = null; marker = null;
    }

    // Keep screen awake if supported
    if ('wakeLock' in navigator) {
        navigator.wakeLock.request('screen').catch(() => {});
    }
</script>
</body>
</html>

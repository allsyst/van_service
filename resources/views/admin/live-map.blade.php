@extends('layouts.admin')

@section('title', 'Live Driver Locations')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="font-weight-bold mb-1" style="color:#1b3a5c;">
                <i class="fas fa-map-marker-alt text-danger mr-2"></i> Live Driver Locations
            </h4>
            <p class="text-muted mb-0 small">Exact GPS locations shared by drivers while the VanService Driver app is open — refreshes every 5 seconds</p>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge badge-success px-3 py-2 mr-2" id="onlineCount" style="font-size:0.85rem;">
                ● 0 Online
            </span>
            <span class="badge badge-secondary px-3 py-2 mr-2" id="offlineCount" style="font-size:0.85rem;">
                0 Offline
            </span>
            <span class="badge badge-light border px-3 py-2" style="font-size:0.8rem;">
                <i class="fas fa-mobile-alt text-primary mr-1"></i> VanService Driver app
            </span>
        </div>
    </div>

    <div class="row">

        {{-- Map Column --}}
        <div class="col-lg-8 mb-3">
            <div class="card shadow" style="border-radius:12px; overflow:hidden;">
                <div class="card-body p-0">
                    <div id="liveMap" style="height: 520px; width: 100%;"></div>
                </div>
                <div class="card-footer bg-white py-2 px-3">
                    <small class="text-muted">
                        <i class="fas fa-sync-alt mr-1"></i>
                        Map refreshes every <strong>5 seconds</strong> &nbsp;|&nbsp;
                        Last update: <span id="lastUpdateTime">—</span>
                    </small>
                </div>
            </div>
        </div>

        {{-- Van List Column --}}
        <div class="col-lg-4 mb-3">
            <div class="card shadow" style="border-radius:12px;">
                <div class="card-header bg-white py-3">
                    <h6 class="font-weight-bold mb-0" style="color:#1b3a5c;">
                        <i class="fas fa-shuttle-van mr-2"></i>Driver Location Status
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="vanList" style="max-height: 380px; overflow-y: auto;">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm mr-2"></div>
                            Loading van locations...
                        </div>
                    </div>
                </div>
            </div>

            {{-- Driver app instructions --}}
            <div class="card shadow mt-3" style="border-radius:12px;">
                <div class="card-body p-3">
                    <h6 class="font-weight-bold mb-2" style="color:#1b3a5c;">
                        <i class="fas fa-mobile-alt mr-2"></i>Driver App Instructions
                    </h6>
                    <p class="text-muted mb-2" style="font-size:0.82rem;">
                        Drivers sign in to the VanService Driver app with their existing driver account, then tap <strong>Start Sharing</strong>. The map marks a van offline if sharing stops or no update is received for 45 seconds.
                    </p>
                    <div class="alert alert-light border mb-0 py-2 px-3" style="font-size:0.78rem;">
                        <i class="fas fa-shield-alt text-success mr-1"></i> Location updates are securely associated with the driver's assigned van.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .van-item {
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
    }
    .van-item:hover { background: #f8f9fa; }
    .van-item:last-child { border-bottom: none; }
    .van-plate {
        font-weight: 700;
        font-size: 0.9rem;
        color: #1b3a5c;
    }
    .van-model { font-size: 0.78rem; color: #666; }
    .van-driver { font-size: 0.78rem; color: #444; margin-top: 2px; }
    .van-speed { font-size: 0.78rem; color: #888; }
    .van-time { font-size: 0.72rem; color: #bbb; margin-top: 2px; }
    .van-coords { font-size: 0.72rem; color: #aaa; margin-top: 1px; }
    .online-badge {
        background: #d4edda;
        color: #155724;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .offline-badge {
        background: #f5f5f5;
        color: #9e9e9e;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .pulse-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: #28a745;
        display: inline-block;
        margin-right: 4px;
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(40,167,69,0.6); }
        70% { box-shadow: 0 0 0 6px rgba(40,167,69,0); }
        100% { box-shadow: 0 0 0 0 rgba(40,167,69,0); }
    }
    .no-vans-msg {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    .no-vans-msg .icon { font-size: 2.5rem; margin-bottom: 10px; }
</style>

<script>
    let map = null;
    let markers = {};

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Leaflet map centered on Philippines
        map = L.map('liveMap').setView([8.0, 124.0], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Load immediately then every 5 seconds
        loadLocations();
        setInterval(loadLocations, 5000);
    });

    function loadLocations() {
        fetch('{{ route("admin.locations.get") }}')
            .then(res => res.json())
            .then(data => {
                updateMap(data);
                updateVanList(data);
                document.getElementById('lastUpdateTime').textContent = new Date().toLocaleTimeString();
            })
            .catch(err => console.error('Error fetching locations:', err));
    }

    function updateMap(locations) {
        const onlineVans = locations.filter(l => l.is_online);
        const offlineVans = locations.filter(l => !l.is_online);

        document.getElementById('onlineCount').textContent = '● ' + onlineVans.length + ' Online';
        document.getElementById('offlineCount').textContent = offlineVans.length + ' Offline';

        locations.forEach(loc => {
            if (!loc.is_online) {
                if (markers[loc.van_id]) {
                    map.removeLayer(markers[loc.van_id]);
                    delete markers[loc.van_id];
                }
                return;
            }

            const lat = parseFloat(loc.latitude);
            const lng = parseFloat(loc.longitude);

            const iconHtml = `
                <div style="text-align:center;">
                    <div style="
                        background: #1b3a5c;
                        color: white;
                        border-radius: 50%;
                        width: 36px; height: 36px;
                        display: flex; align-items: center; justify-content: center;
                        font-size: 18px;
                        border: 3px solid #28a745;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.4);
                        margin: 0 auto;
                    ">🚐</div>
                    <div style="
                        background: #1b3a5c;
                        color: white;
                        border-radius: 4px;
                        padding: 1px 5px;
                        font-size: 9px;
                        font-weight: bold;
                        margin-top: 2px;
                        white-space: nowrap;
                        box-shadow: 0 1px 4px rgba(0,0,0,0.3);
                    ">${loc.van_plate}</div>
                </div>
            `;

            const vanIcon = L.divIcon({
                html: iconHtml,
                className: '',
                iconAnchor: [18, 18],
                iconSize: [60, 52]
            });

            const popupContent = `
                <div style="min-width:180px; font-family:sans-serif;">
                    <strong style="color:#1b3a5c;">🚐 ${loc.van_plate}</strong><br>
                    <span style="color:#666; font-size:0.85rem;">${loc.van_model}</span><br>
                    <hr style="margin:6px 0;">
                    <b>Driver:</b> ${loc.driver_name || 'Unknown'}<br>
                    <b>Speed:</b> ${loc.speed || 0} km/h<br>
                    <b>Updated:</b> ${loc.updated_at}<br>
                    ${loc.accuracy ? `<b>Accuracy:</b> ±${Math.round(loc.accuracy)} m<br>` : ''}
                    <small style="color:#aaa;">${lat.toFixed(5)}, ${lng.toFixed(5)}</small>
                </div>
            `;

            if (markers[loc.van_id]) {
                markers[loc.van_id].setLatLng([lat, lng]);
                markers[loc.van_id].setPopupContent(popupContent);
            } else {
                markers[loc.van_id] = L.marker([lat, lng], { icon: vanIcon })
                    .addTo(map)
                    .bindPopup(popupContent);
            }
        });

        // Fit map to show all online vans
        if (onlineVans.length > 0) {
            const bounds = onlineVans.map(l => [parseFloat(l.latitude), parseFloat(l.longitude)]);
            if (bounds.length === 1) {
                map.setView(bounds[0], 15);
            } else {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    }

    function updateVanList(locations) {
        const listEl = document.getElementById('vanList');

        if (locations.length === 0) {
            listEl.innerHTML = `
                <div class="no-vans-msg">
                    <div class="icon">🚐</div>
                    <div class="font-weight-bold">No van location data yet</div>
                    <small class="text-muted">Drivers need to open the Driver Page on their phone and share their location.</small>
                </div>
            `;
            return;
        }

        // Sort: online first
        locations.sort((a, b) => b.is_online - a.is_online);

        listEl.innerHTML = locations.map(loc => `
            <div class="van-item" onclick="focusVan(${loc.van_id}, ${loc.latitude}, ${loc.longitude}, ${loc.is_online})">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="flex:1; min-width:0;">
                        <div class="van-plate">
                            ${loc.is_online ? '<span class="pulse-dot"></span>' : ''}
                            ${loc.van_plate}
                        </div>
                        <div class="van-model">${loc.van_model}</div>
                        ${loc.driver_name ? `<div class="van-driver"><i class="fas fa-user mr-1" style="font-size:0.7rem;"></i>${loc.driver_name}</div>` : ''}
                        ${loc.is_online ? `<div class="van-speed"><i class="fas fa-tachometer-alt mr-1" style="font-size:0.7rem;"></i>${loc.speed || 0} km/h${loc.accuracy ? ` &nbsp;·&nbsp; ±${Math.round(loc.accuracy)} m` : ''}</div>` : ''}
                        <div class="van-time"><i class="fas fa-clock mr-1" style="font-size:0.7rem;"></i>${loc.updated_at}</div>
                        ${loc.is_online ? `<div class="van-coords">${parseFloat(loc.latitude).toFixed(5)}, ${parseFloat(loc.longitude).toFixed(5)}</div>` : ''}
                    </div>
                    <span class="${loc.is_online ? 'online-badge' : 'offline-badge'} ml-2">
                        ${loc.is_online ? '● Online' : '○ Offline'}
                    </span>
                </div>
            </div>
        `).join('');
    }

    function focusVan(vanId, lat, lng, isOnline) {
        if (!isOnline || !lat || !lng) return;
        map.setView([parseFloat(lat), parseFloat(lng)], 16);
        if (markers[vanId]) {
            markers[vanId].openPopup();
        }
    }

    function copyDriverLink() {
        const input = document.getElementById('driverLink');
        input.select();
        document.execCommand('copy');
        document.getElementById('copyMsg').style.display = 'block';
        setTimeout(() => document.getElementById('copyMsg').style.display = 'none', 2000);
    }
</script>
@endpush

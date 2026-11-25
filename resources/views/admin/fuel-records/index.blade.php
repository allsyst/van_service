@extends('layouts.admin')
@section('title', 'Fuel Consumption')

@push('styles')
<style>
    .fuel-stat { min-height: 128px; }
    .fuel-stat .stat-note { font-size: .74rem; opacity: .82; margin-top: .15rem; }
    .chart-wrap { height: 360px; position: relative; }
    .record-note { max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-gas-pump text-primary me-2"></i>Fuel Consumption Analytics</h4>
        <p class="text-muted mb-0">Actual fuel records and completed travel from {{ $start->format('F Y') }} to {{ $end->format('F Y') }}.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('admin.fuel-records.report') }}" class="btn btn-outline-primary rounded-pill px-3">
            <i class="fas fa-file-invoice-dollar me-1"></i>Daily / Monthly / Yearly Reports
        </a>
        <a href="{{ route('admin.fuel-records.create') }}" class="btn btn-primary rounded-pill px-3">
            <i class="fas fa-plus me-1"></i>Add Fuel Record
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card fuel-stat" style="background:linear-gradient(135deg,#4e73df,#224abe);">
            <div class="stat-label">Fuel Recorded</div>
            <div class="stat-value">{{ number_format($totalLiters, 2) }} <small style="font-size:.8rem">L</small></div>
            <div class="stat-note">Actual gasoline consumption</div>
            <div class="stat-icon"><i class="fas fa-gas-pump"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card fuel-stat" style="background:linear-gradient(135deg,#1cc88a,#13855c);">
            <div class="stat-label">Completed Travels</div>
            <div class="stat-value">{{ number_format($totalTrips) }}</div>
            <div class="stat-note">Completed trips in the period</div>
            <div class="stat-icon"><i class="fas fa-route"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card fuel-stat" style="background:linear-gradient(135deg,#36b9cc,#1a8fa1);">
            <div class="stat-label">Distance Travelled</div>
            <div class="stat-value">{{ number_format($totalDistance, 1) }} <small style="font-size:.8rem">km</small></div>
            <div class="stat-note">From completed trip distances</div>
            <div class="stat-icon"><i class="fas fa-road"></i></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card fuel-stat" style="background:linear-gradient(135deg,#f6c23e,#d4a017);">
            <div class="stat-label">Average Consumption</div>
            <div class="stat-value">{{ $litersPer100Km !== null ? number_format($litersPer100Km, 2) : '—' }} <small style="font-size:.8rem">L/100 km</small></div>
            <div class="stat-note">Based on entered fuel and travel</div>
            <div class="stat-icon"><i class="fas fa-tachometer-alt"></i></div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-chart-line text-primary me-2"></i>Three-Month Fuel and Travel Trend</span>
        <span class="small text-muted"><i class="fas fa-info-circle me-1"></i>Each measure uses its own scale for readability.</span>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="chart-wrap"><canvas id="fuelTrendChart"></canvas></div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span><i class="fas fa-list text-primary me-2"></i>Fuel Consumption Records</span>
        <span class="badge bg-light text-dark border">{{ $records->total() }} {{ Str::plural('record', $records->total()) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="fuel-records-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Van</th>
                    <th class="d-none d-lg-table-cell">Linked Travel</th>
                    <th>Fuel</th>
                    <th class="d-none d-md-table-cell">Trip Distance</th>
                    <th class="d-none d-xl-table-cell">Cost</th>
                    <th class="d-none d-xl-table-cell">Odometer</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $record->fuel_date->format('M d, Y') }}</div>
                            @if($record->fuel_station)<div class="small text-muted">{{ $record->fuel_station }}</div>@endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $record->van->van_model ?? 'Deleted van' }}</div>
                            <div class="small text-muted">{{ $record->van->van_plate ?? 'No plate' }}</div>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            @if($record->trip)
                                <div class="fw-bold">{{ $record->trip->route_name }}</div>
                                <div class="small text-muted">{{ $record->trip->trip_date?->format('M d, Y') }}</div>
                            @else
                                <span class="text-muted">Not linked to a trip</span>
                            @endif
                        </td>
                        <td><span class="badge bg-primary rounded-pill px-3">{{ number_format($record->liters, 2) }} L</span></td>
                        <td class="d-none d-md-table-cell">{{ $record->trip && $record->trip->distance_km > 0 ? number_format($record->trip->distance_km, 1).' km' : '—' }}</td>
                        <td class="d-none d-xl-table-cell">{{ $record->total_cost !== null ? '₱'.number_format($record->total_cost, 2) : '—' }}</td>
                        <td class="d-none d-xl-table-cell">{{ $record->odometer_km !== null ? number_format($record->odometer_km, 1).' km' : '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.fuel-records.edit', $record) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Edit fuel record"><i class="fas fa-pen"></i></a>
                                <form method="POST" action="{{ route('admin.fuel-records.destroy', $record) }}" onsubmit="return confirm('Delete this fuel record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete fuel record"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-gas-pump d-block mb-2" style="font-size:1.7rem;"></i>
                            No fuel records yet. Add an actual gasoline entry to start the analysis.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
        <div class="card-body border-top">{{ $records->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    const fuelTrendData = @json($chartData);
    const trendCanvas = document.getElementById('fuelTrendChart');

    new Chart(trendCanvas, {
        type: 'line',
        data: {
            labels: fuelTrendData.labels,
            datasets: [
                {
                    label: 'Gasoline Consumption (L)',
                    data: fuelTrendData.fuelLiters,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, .12)',
                    yAxisID: 'yFuel',
                    tension: .35,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Completed Travels',
                    data: fuelTrendData.tripCounts,
                    borderColor: '#1cc88a',
                    backgroundColor: '#1cc88a',
                    yAxisID: 'yTrips',
                    tension: .35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Distance Travelled (km)',
                    data: fuelTrendData.distanceKm,
                    borderColor: '#f6c23e',
                    backgroundColor: '#f6c23e',
                    yAxisID: 'yDistance',
                    tension: .35,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${Number(context.parsed.y).toLocaleString(undefined, {maximumFractionDigits: 2})}` } },
            },
            scales: {
                yFuel: { type: 'linear', position: 'left', beginAtZero: true, title: { display: true, text: 'Liters' }, grid: { color: 'rgba(0,0,0,.05)' } },
                yTrips: { type: 'linear', position: 'right', beginAtZero: true, ticks: { precision: 0 }, title: { display: true, text: 'Travels' }, grid: { drawOnChartArea: false } },
                yDistance: { type: 'linear', position: 'right', offset: true, beginAtZero: true, title: { display: true, text: 'Kilometres' }, grid: { drawOnChartArea: false } },
                x: { grid: { display: false } },
            },
        },
    });
</script>
@endpush

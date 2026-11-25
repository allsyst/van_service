@extends('layouts.admin')
@section('title', 'Fuel Reports')

@push('styles')
<style>
    .report-chart { height: 340px; position: relative; }
    .report-stat .stat-value { font-size: 1.55rem; }
    @media print {
        .sidebar, .topbar, .sidebar-overlay, .page-body > .d-flex:first-child, .no-print, .alert { display: none !important; }
        .main-content { margin-left: 0 !important; }
        .page-body { padding: 0 !important; }
        body { background: #fff !important; }
        .card { box-shadow: none !important; border: 1px solid #dfe3ea !important; margin-bottom: 16px !important; }
        .stat-card { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4 no-print">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Fuel Consumption Reports</h4>
        <p class="text-muted mb-0">Choose a period to view, print, or download actual refueling and completed-travel data.</p>
    </div>
    <a href="{{ route('admin.fuel-records.index') }}" class="btn btn-outline-secondary rounded-pill px-3"><i class="fas fa-chart-line me-1"></i>Fuel Dashboard</a>
</div>

<div class="card mb-4 no-print">
    <div class="card-header"><i class="fas fa-filter text-primary me-2"></i>Build a Fuel Report</div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.fuel-records.report') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Report Type</label>
                <select class="form-select" id="period" name="period">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div class="col-md-4 period-input" data-period="daily">
                <label class="form-label fw-semibold">Date</label>
                <input class="form-control" type="date" name="date" value="{{ $period === 'daily' ? $reference : now()->toDateString() }}">
            </div>
            <div class="col-md-4 period-input" data-period="monthly">
                <label class="form-label fw-semibold">Month</label>
                <input class="form-control" type="month" name="month" value="{{ $period === 'monthly' ? $reference : now()->format('Y-m') }}">
            </div>
            <div class="col-md-4 period-input" data-period="yearly">
                <label class="form-label fw-semibold">Year</label>
                <input class="form-control" type="number" min="2020" max="2100" name="year" value="{{ $period === 'yearly' ? $reference : now()->year }}">
            </div>
            <div class="col-md-auto"><button class="btn btn-primary px-4" type="submit"><i class="fas fa-chart-bar me-1"></i>View Report</button></div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
    <div>
        <h4 class="mb-1 fw-bold">{{ $heading }}</h4>
        <p class="text-muted mb-0">{{ $start->format('M d, Y') }} to {{ $end->format('M d, Y') }}</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('admin.fuel-records.download-csv', request()->except('print')) }}" class="btn btn-outline-success rounded-pill px-3"><i class="fas fa-file-csv me-1"></i>CSV</a>
        <button type="button" onclick="window.print()" class="btn btn-outline-dark rounded-pill px-3"><i class="fas fa-print me-1"></i>Print</button>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="stat-card report-stat" style="background:linear-gradient(135deg,#4e73df,#224abe);"><div class="stat-label">Fuel Recorded</div><div class="stat-value">{{ number_format($summary['totalLiters'], 2) }} <small style="font-size:.75rem">L</small></div><div class="stat-icon"><i class="fas fa-gas-pump"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="stat-card report-stat" style="background:linear-gradient(135deg,#1cc88a,#13855c);"><div class="stat-label">Fuel Cost</div><div class="stat-value">₱{{ number_format($summary['totalFuelCost'], 2) }}</div><div class="stat-icon"><i class="fas fa-coins"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="stat-card report-stat" style="background:linear-gradient(135deg,#36b9cc,#1a8fa1);"><div class="stat-label">Completed Travels</div><div class="stat-value">{{ number_format($summary['totalTrips']) }}</div><div class="stat-icon"><i class="fas fa-route"></i></div></div></div>
    <div class="col-6 col-xl-3"><div class="stat-card report-stat" style="background:linear-gradient(135deg,#f6c23e,#d4a017);"><div class="stat-label">Distance Travelled</div><div class="stat-value">{{ number_format($summary['totalDistance'], 1) }} <small style="font-size:.75rem">km</small></div><div class="stat-icon"><i class="fas fa-road"></i></div></div></div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"><span><i class="fas fa-chart-line text-primary me-2"></i>Fuel and Travel Graph</span><span class="small text-muted">Fuel, cost, completed trips, and distance shown on independent scales.</span></div>
    <div class="card-body"><div class="report-chart"><canvas id="fuelReportChart"></canvas></div></div>
</div>

<div class="card">
    <div class="card-header"><i class="fas fa-list text-primary me-2"></i>Refueling Records <span class="badge bg-light text-dark border ms-1">{{ $records->count() }}</span></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Van</th><th class="d-none d-md-table-cell">Trip</th><th>Liters</th><th class="d-none d-lg-table-cell">Cost</th><th class="d-none d-lg-table-cell">Odometer</th><th class="d-none d-xl-table-cell">Station / Notes</th></tr></thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->fuel_date->format('M d, Y') }}</td>
                        <td><div class="fw-bold">{{ $record->van->van_model ?? 'Deleted van' }}</div><small class="text-muted">{{ $record->van->van_plate ?? 'No plate' }}</small></td>
                        <td class="d-none d-md-table-cell">{{ $record->trip->route_name ?? 'Not linked to a trip' }}</td>
                        <td><span class="badge bg-primary rounded-pill">{{ number_format($record->liters, 2) }} L</span></td>
                        <td class="d-none d-lg-table-cell">{{ $record->total_cost !== null ? '₱'.number_format($record->total_cost, 2) : '—' }}</td>
                        <td class="d-none d-lg-table-cell">{{ $record->odometer_km !== null ? number_format($record->odometer_km, 1).' km' : '—' }}</td>
                        <td class="d-none d-xl-table-cell"><div>{{ $record->fuel_station ?? '—' }}</div>@if($record->notes)<small class="text-muted">{{ $record->notes }}</small>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-gas-pump d-block mb-2" style="font-size:1.6rem"></i>No fuel entries in this report period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const reportData = @json($summary['chartData']);
new Chart(document.getElementById('fuelReportChart'), {
    type: 'line',
    data: { labels: reportData.labels, datasets: [
        { label: 'Fuel (L)', data: reportData.fuelLiters, borderColor:'#4e73df', backgroundColor:'rgba(78,115,223,.12)', yAxisID:'fuel', fill:true, tension:.3, pointRadius:3 },
        { label: 'Fuel Cost (₱)', data: reportData.fuelCost, borderColor:'#e74a3b', backgroundColor:'#e74a3b', yAxisID:'cost', tension:.3, pointRadius:3 },
        { label: 'Completed Travels', data: reportData.tripCounts, borderColor:'#1cc88a', backgroundColor:'#1cc88a', yAxisID:'trips', tension:.3, pointRadius:3 },
        { label: 'Distance (km)', data: reportData.distanceKm, borderColor:'#f6c23e', backgroundColor:'#f6c23e', yAxisID:'distance', tension:.3, pointRadius:3 },
    ]},
    options: { responsive:true, maintainAspectRatio:false, interaction:{mode:'index',intersect:false}, plugins:{legend:{position:'bottom',labels:{usePointStyle:true,padding:18}}}, scales:{
        fuel:{type:'linear',position:'left',beginAtZero:true,title:{display:true,text:'Liters'},grid:{color:'rgba(0,0,0,.05)'}},
        cost:{type:'linear',position:'right',beginAtZero:true,title:{display:true,text:'Fuel cost (₱)'},grid:{drawOnChartArea:false}},
        trips:{type:'linear',position:'right',offset:true,beginAtZero:true,ticks:{precision:0},title:{display:true,text:'Travels'},grid:{drawOnChartArea:false}},
        distance:{type:'linear',position:'right',offset:true,beginAtZero:true,display:false,grid:{drawOnChartArea:false}}, x:{grid:{display:false}}
    }}
});
const selector = document.getElementById('period');
function togglePeriodInput() { document.querySelectorAll('.period-input').forEach(el => el.style.display = el.dataset.period === selector.value ? '' : 'none'); }
selector.addEventListener('change', togglePeriodInput); togglePeriodInput();
@if($printable) window.addEventListener('load', () => window.print()); @endif
</script>
@endpush

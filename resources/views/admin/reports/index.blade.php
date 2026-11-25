@extends('layouts.admin')
@section('title','Reports')
@section('content')

<style>
.report-card { border: none; border-radius: 1rem; box-shadow: 0 0.15rem 1.75rem rgba(58,59,69,.1); }
.stat-box { border-radius: 0.75rem; padding: 1.2rem 1.5rem; color: #fff; display: flex; align-items: center; justify-content: space-between; }
.stat-box .stat-icon { font-size: 2rem; opacity: 0.4; }
.stat-box .stat-num { font-size: 1.8rem; font-weight: 800; }
.stat-box .stat-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
.period-btn { border-radius: 2rem; padding: 0.45rem 1.2rem; font-weight: 600; font-size: 0.85rem; border: 2px solid #4e73df; color: #4e73df; background: #fff; transition: all 0.2s; cursor: pointer; }
.period-btn:hover, .period-btn.active { background: #4e73df; color: #fff; }
.status-btn { border-radius: 2rem; padding: 0.4rem 1.1rem; font-weight: 600; font-size: 0.82rem; border: 2px solid; transition: all 0.2s; cursor: pointer; background: #fff; }
.status-btn.all    { border-color: #5a5c69; color: #5a5c69; }
.status-btn.pending  { border-color: #f6c23e; color: #f6c23e; }
.status-btn.confirmed { border-color: #1cc88a; color: #1cc88a; }
.status-btn.completed { border-color: #4e73df; color: #4e73df; }
.status-btn.cancelled { border-color: #e74a3b; color: #e74a3b; }
.status-btn.all.active    { background: #5a5c69; color: #fff; }
.status-btn.pending.active  { background: #f6c23e; color: #fff; }
.status-btn.confirmed.active { background: #1cc88a; color: #fff; }
.status-btn.completed.active { background: #4e73df; color: #fff; }
.status-btn.cancelled.active { background: #e74a3b; color: #fff; }
.download-btn { border-radius: 0.5rem; padding: 0.6rem 1.5rem; font-weight: 700; font-size: 0.9rem; }
</style>

<div class="mb-4">
    <h4 class="font-weight-bold text-gray-800"><i class="fas fa-file-alt mr-2 text-primary"></i>Booking Reports</h4>
    <p class="text-muted mb-0">Download booking reports filtered by status and time period.</p>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md col-sm-6 mb-3">
        <div class="stat-box" style="background: linear-gradient(135deg,#5a5c69,#3a3b45)">
            <div>
                <div class="stat-num">{{ $summary['all'] }}</div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <i class="fas fa-list stat-icon"></i>
        </div>
    </div>
    <div class="col-md col-sm-6 mb-3">
        <div class="stat-box" style="background: linear-gradient(135deg,#f6c23e,#d4a017)">
            <div>
                <div class="stat-num">{{ $summary['pending'] }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <i class="fas fa-clock stat-icon"></i>
        </div>
    </div>
    <div class="col-md col-sm-6 mb-3">
        <div class="stat-box" style="background: linear-gradient(135deg,#1cc88a,#13855c)">
            <div>
                <div class="stat-num">{{ $summary['confirmed'] }}</div>
                <div class="stat-label">Confirmed</div>
            </div>
            <i class="fas fa-check-circle stat-icon"></i>
        </div>
    </div>
    <div class="col-md col-sm-6 mb-3">
        <div class="stat-box" style="background: linear-gradient(135deg,#4e73df,#224abe)">
            <div>
                <div class="stat-num">{{ $summary['completed'] }}</div>
                <div class="stat-label">Completed</div>
            </div>
            <i class="fas fa-flag-checkered stat-icon"></i>
        </div>
    </div>
    <div class="col-md col-sm-6 mb-3">
        <div class="stat-box" style="background: linear-gradient(135deg,#e74a3b,#be2617)">
            <div>
                <div class="stat-num">{{ $summary['cancelled'] }}</div>
                <div class="stat-label">Cancelled</div>
            </div>
            <i class="fas fa-times-circle stat-icon"></i>
        </div>
    </div>
</div>

<!-- Report Builder -->
<div class="card report-card">
    <div class="card-header" style="background: linear-gradient(135deg,#4e73df,#224abe); color:#fff; border-radius: 1rem 1rem 0 0;">
        <i class="fas fa-filter mr-2"></i>Build Your Report
    </div>
    <div class="card-body p-4">

        <!-- Step 1: Status -->
        <div class="mb-4">
            <h6 class="font-weight-bold text-gray-700 mb-2"><span class="badge badge-primary mr-2">1</span>Select Status</h6>
            <div class="d-flex flex-wrap gap-2" style="gap: 0.5rem">
                <button class="status-btn all active" data-status="All">All Bookings</button>
                <button class="status-btn pending" data-status="Pending">Pending</button>
                <button class="status-btn confirmed" data-status="Confirmed">Confirmed</button>
                <button class="status-btn completed" data-status="Completed">Completed</button>
                <button class="status-btn cancelled" data-status="Cancelled">Cancelled</button>
            </div>
        </div>

        <hr>

        <!-- Step 2: Period -->
        <div class="mb-4">
            <h6 class="font-weight-bold text-gray-700 mb-2"><span class="badge badge-primary mr-2">2</span>Select Time Period</h6>
            <div class="d-flex flex-wrap" style="gap: 0.5rem">
                <button class="period-btn" data-period="daily"><i class="fas fa-calendar-day mr-1"></i>Daily</button>
                <button class="period-btn" data-period="weekly"><i class="fas fa-calendar-week mr-1"></i>Weekly</button>
                <button class="period-btn active" data-period="monthly"><i class="fas fa-calendar-alt mr-1"></i>Monthly</button>
                <button class="period-btn" data-period="yearly"><i class="fas fa-calendar mr-1"></i>Yearly</button>
            </div>
            <div id="period-desc" class="mt-2 text-muted" style="font-size:0.85rem">
                <i class="fas fa-info-circle mr-1"></i><span id="period-desc-text">This month's bookings</span>
            </div>
        </div>

        <hr>

        <!-- Step 3: Download -->
        <div class="mb-2">
            <h6 class="font-weight-bold text-gray-700 mb-3"><span class="badge badge-primary mr-2">3</span>Download Report</h6>
            <div class="d-flex flex-wrap" style="gap: 0.75rem">
                <a id="csv-btn" href="#" class="btn btn-success download-btn">
                    <i class="fas fa-file-csv mr-2"></i>Download CSV
                </a>
                <a id="pdf-btn" href="#" class="btn btn-danger download-btn">
                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                </a>
            </div>
            <small class="text-muted mt-2 d-block">
                <i class="fas fa-info-circle mr-1"></i>
                CSV can be opened in Excel. PDF is a printable report.
            </small>
        </div>

        <!-- Preview info -->
        <div class="alert alert-light border mt-4 mb-0" id="preview-info">
            <i class="fas fa-eye mr-2 text-primary"></i>
            Report will include: <strong id="preview-status">All</strong> bookings for <strong id="preview-period">this month</strong>.
        </div>

    </div>
</div>

<script>
var selectedStatus = 'All';
var selectedPeriod = 'monthly';

var periodDescriptions = {
    daily:   "Today's bookings (" + new Date().toLocaleDateString('en-US', {month:'long', day:'numeric', year:'numeric'}) + ")",
    weekly:  "This week's bookings",
    monthly: "This month's bookings (" + new Date().toLocaleDateString('en-US', {month:'long', year:'numeric'}) + ")",
    yearly:  "This year's bookings (" + new Date().getFullYear() + ")",
};

var periodLabels = {
    daily: 'today', weekly: 'this week', monthly: 'this month', yearly: 'this year'
};

function updateLinks() {
    var base = '{{ url("/admin/reports") }}';
    var params = '?status=' + encodeURIComponent(selectedStatus) + '&period=' + selectedPeriod;
    document.getElementById('csv-btn').href = base + '/csv' + params;
    document.getElementById('pdf-btn').href = base + '/pdf' + params;
    document.getElementById('preview-status').textContent = selectedStatus;
    document.getElementById('preview-period').textContent = periodLabels[selectedPeriod];
    document.getElementById('period-desc-text').textContent = periodDescriptions[selectedPeriod];
}

// Status buttons
document.querySelectorAll('.status-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.status-btn').forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        selectedStatus = this.getAttribute('data-status');
        updateLinks();
    });
});

// Period buttons
document.querySelectorAll('.period-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.period-btn').forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        selectedPeriod = this.getAttribute('data-period');
        updateLinks();
    });
});

// Init
updateLinks();
</script>
@endsection

@extends('layouts.admin')
@section('title', 'Trip Requests')
@section('content')

<style>
    .badge-pill-req {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 0.28rem 0.75rem; border-radius: 2rem;
        font-size: 0.72rem; font-weight: 700; white-space: nowrap;
    }
    .badge-pending-req { background: #fff3cd; color: #856404; }
    .badge-approved    { background: #d1f5e0; color: #155724; }
    .badge-rejected    { background: #f8d7da; color: #721c24; }

    .action-btn { border: none; border-radius: 0.4rem; padding: 0.3rem 0.7rem; font-size: 0.78rem; font-weight: 700; cursor: pointer; }
    .btn-approve { background: #d1f5e0; color: #155724; }
    .btn-approve:hover { background: #1cc88a; color: #fff; }
    .btn-reject  { background: #f8d7da; color: #721c24; }
    .btn-reject:hover  { background: #e74a3b; color: #fff; }
    .btn-view    { background: #eef2ff; color: #4e73df; }
    .btn-view:hover    { background: #4e73df; color: #fff; }

    .stat-card { background: #fff; border-radius: 0.75rem; padding: 1rem 1.25rem; box-shadow: 0 2px 8px rgba(0,0,0,.06); display: flex; align-items: center; gap: 1rem; }
    .stat-icon { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
    .stat-label { font-size: 0.72rem; color: #888; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .stat-value { font-size: 1.5rem; font-weight: 800; color: #2d3748; line-height: 1; }

    .detail-table td { padding: 0.5rem 0.75rem; font-size: 0.88rem; border-bottom: 1px solid #f5f5f5; vertical-align: top; }
    .detail-table td:first-child { color: #888; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; width: 150px; white-space: nowrap; }
    .detail-table tr:last-child td { border-bottom: none; }

    .requests-table th { background: #f8f9fc; color: #888; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; padding: 0.75rem 1rem; border-bottom: 2px solid #eee; white-space: nowrap; }
    .requests-table td { padding: 0.8rem 1rem; font-size: 0.88rem; vertical-align: middle; border-bottom: 1px solid #f5f5f5; }
    .requests-table tbody tr:hover { background: #f8f9fc; }
    .requests-table tbody tr:last-child td { border-bottom: none; }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:.75rem;">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:.75rem;">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-bs-dismiss="alert">&times;</button>
    </div>
@endif

<!-- Stats -->
<div class="row mb-4">
    <div class="col-6 col-xl-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eef2ff;"><i class="fas fa-list" style="color:#4e73df;"></i></div>
            <div><div class="stat-label">Total</div><div class="stat-value">{{ $requests->count() }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1;"><i class="fas fa-clock" style="color:#f6c23e;"></i></div>
            <div><div class="stat-label">Pending</div><div class="stat-value">{{ $pendingCount }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f8f5;"><i class="fas fa-check-circle" style="color:#1cc88a;"></i></div>
            <div><div class="stat-label">Approved</div><div class="stat-value">{{ $approvedCount }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fde8e8;"><i class="fas fa-times-circle" style="color:#e74a3b;"></i></div>
            <div><div class="stat-label">Rejected</div><div class="stat-value">{{ $rejectedCount }}</div></div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card" style="border-radius:1rem;overflow:hidden;border:none;box-shadow:0 2px 12px rgba(0,0,0,.07);">
    <div class="card-header" style="background:#fff;border-bottom:1px solid #f0f0f0;padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
        <h5 style="margin:0;font-weight:800;font-size:1rem;display:flex;align-items:center;gap:0.5rem;">
            <i class="fas fa-paper-plane text-primary mr-2"></i>User Trip Requests
            <span style="font-size:0.78rem;font-weight:600;color:#888;">({{ $requests->count() }} total)</span>
        </h5>
    </div>

    @if($requests->isEmpty())
        <div style="text-align:center;padding:3rem;color:#aaa;">
            <i class="fas fa-inbox" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
            <p>No trip requests submitted yet.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="requests-table w-100" id="requestsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Request No.</th>
                        <th>Submitted By</th>
                        <th>Date of Travel</th>
                        <th>Purpose</th>
                        <th>Destination</th>
                        <th style="text-align:center;">Passengers</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $i => $req)
                    <tr>
                        <td style="color:#888;font-size:0.82rem;">{{ $i + 1 }}</td>
                        <td><strong style="color:#4e73df;">{{ $req->request_no }}</strong></td>
                        <td>
                            <div style="font-weight:700;">{{ $req->user->name ?? '—' }}</div>
                            <div style="font-size:0.78rem;color:#888;">{{ $req->user->email ?? '' }}</div>
                        </td>
                        <td>{{ $req->date_of_travel->format('M d, Y') }}</td>
                        <td>{{ $req->purpose }}</td>
                        <td>{{ $req->destination }}</td>
                        <td style="text-align:center;font-weight:700;">{{ $req->no_of_passengers }}</td>
                        <td>
                            @if($req->status === 'approved')
                                <span class="badge-pill-req badge-approved"><i class="fas fa-check-circle"></i> Approved</span>
                            @elseif($req->status === 'rejected')
                                <span class="badge-pill-req badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span>
                            @elseif($req->status === 'cancelled')
                                <span class="badge-pill-req" style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;"><i class="fas fa-ban"></i> Cancelled</span>
                                @php
                                    $cancelReason = $req->admin_remarks
                                        ?: ($req->trip && $req->trip->driver_cancel_reason
                                            ? 'Cancelled by driver: ' . $req->trip->driver_cancel_reason
                                            : null);
                                @endphp
                                @if($cancelReason)
                                    <div style="font-size:0.72rem;color:#dc2626;margin-top:4px;">
                                        <i class="fas fa-info-circle"></i> {{ $cancelReason }}
                                    </div>
                                @endif
                            @else
                                <span class="badge-pill-req badge-pending-req"><i class="fas fa-clock"></i> Pending</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                <button class="action-btn btn-view" onclick="viewRequest({{ $req->id }})">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                                @if($req->status === 'pending')
                                <button class="action-btn btn-approve" onclick="openApprove({{ $req->id }}, '{{ $req->request_no }}')">
                                    <i class="fas fa-check mr-1"></i>Approve
                                </button>
                                <button class="action-btn btn-reject" onclick="openReject({{ $req->id }}, '{{ $req->request_no }}')">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- View Detail Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;">
                <h5 class="modal-title"><i class="fas fa-file-alt mr-2"></i>Trip Request Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <table class="detail-table w-100" id="viewDetailBody"></table>
            </div>
            <div class="modal-footer border-0 pb-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#1cc88a,#13855c);color:#fff;border-bottom:none;">
                <h5 class="modal-title" style="font-weight:800;"><i class="fas fa-check-circle mr-2"></i>Approve &amp; Assign Van</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p style="font-size:0.88rem;color:#333;margin-bottom:1rem;">Approving trip request <strong id="approveReqNo" style="color:#4e73df;"></strong></p>

                    {{-- Van availability list --}}
                    <div class="mb-3">
                        <label style="font-size:0.8rem;font-weight:700;color:#555;display:block;margin-bottom:0.4rem;"><i class="fas fa-shuttle-van mr-1"></i>Assign Van <span style="color:#e74a3b;">*</span></label>
                        <select name="van_id" id="approveVanSelect" class="form-control form-control-sm" required>
                            <option value="">— Select a van —</option>
                            @foreach(\App\Models\Van::orderBy('van_model')->get() as $v)
                            <option value="{{ $v->id }}"
                                data-available="{{ $v->is_available ? '1' : '0' }}"
                                style="{{ !$v->is_available ? 'color:#e74a3b;' : 'color:#155724;' }}">
                                {{ $v->van_model }} ({{ $v->van_plate ?? 'No Plate' }}) — {{ $v->is_available ? '✅ Available' : '🔴 Not Available' }}
                            </option>
                            @endforeach
                        </select>
                        <div id="vanWarning" class="mt-1" style="display:none;font-size:0.78rem;color:#e74a3b;font-weight:700;">
                            <i class="fas fa-exclamation-triangle mr-1"></i>This van is currently marked as <strong>Not Available</strong>. You can still assign it, but please confirm.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label style="font-size:0.8rem;font-weight:700;color:#555;">Remarks (optional)</label>
                        <textarea name="admin_remarks" class="form-control form-control-sm" rows="2" placeholder="Add a note for the user..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check mr-1"></i>Approve &amp; Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:#f8d7da;border-bottom:none;">
                <h5 class="modal-title" style="color:#721c24;font-weight:800;"><i class="fas fa-times-circle mr-2"></i>Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <p style="font-size:0.88rem;color:#333;">Reject trip request <strong id="rejectReqNo" style="color:#4e73df;"></strong>?</p>
                    <div class="form-group mb-0">
                        <label style="font-size:0.8rem;font-weight:700;color:#555;">Reason (optional)</label>
                        <textarea name="admin_remarks" class="form-control form-control-sm" rows="2" placeholder="Give a reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times mr-1"></i>Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
var REQUEST_DATA = {
    @foreach($requests as $req)
    {{ $req->id }}: {
        request_no: '{{ $req->request_no }}',
        user_name: '{{ addslashes($req->user->name ?? '—') }}',
        user_email: '{{ addslashes($req->user->email ?? '') }}',
        date: '{{ $req->date_of_travel->format('F d, Y') }}',
        purpose: '{{ addslashes($req->purpose) }}',
        destination: '{{ addslashes($req->destination) }}',
        no_of_passengers: {{ $req->no_of_passengers }},
        passenger_names: {!! json_encode($req->passenger_names ?? []) !!},
        status: '{{ $req->status }}',
        remarks: '{{ addslashes($req->admin_remarks ?? '') }}',
        submitted: '{{ $req->created_at->format('M d, Y h:i A') }}'
    },
    @endforeach
};

function viewRequest(id) {
    var req = REQUEST_DATA[id];
    if (!req) return;
    var statusColor = req.status === 'approved' ? '#155724' : (req.status === 'rejected' ? '#721c24' : '#856404');
    var statusBg    = req.status === 'approved' ? '#d1f5e0' : (req.status === 'rejected' ? '#f8d7da' : '#fff3cd');
    var statusIcon  = req.status === 'approved' ? 'fa-check-circle' : (req.status === 'rejected' ? 'fa-times-circle' : 'fa-clock');
    var statusLabel = req.status.charAt(0).toUpperCase() + req.status.slice(1);

    var passengerHtml = '';
    if (req.passenger_names && req.passenger_names.length) {
        req.passenger_names.forEach(function(n, i) {
            passengerHtml += '<li style="padding:2px 0;">' + (i+1) + '. ' + n + '</li>';
        });
        passengerHtml = '<ul style="margin:0;padding-left:1.2rem;">' + passengerHtml + '</ul>';
    } else {
        passengerHtml = '<span style="color:#aaa;">—</span>';
    }

    var rows = [
        ['Request No.', '<strong style="color:#4e73df;">' + req.request_no + '</strong>'],
        ['Submitted By', '<strong>' + req.user_name + '</strong><br><span style="color:#888;font-size:0.82rem;">' + req.user_email + '</span>'],
        ['Date of Travel', '<strong>' + req.date + '</strong>'],
        ['Purpose', req.purpose],
        ['Destination', req.destination],
        ['No. of Passengers', '<strong>' + req.no_of_passengers + '</strong>'],
        ['Passenger Names', passengerHtml],
        ['Status', '<span style="background:' + statusBg + ';color:' + statusColor + ';padding:.28rem .75rem;border-radius:2rem;font-size:.78rem;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><i class=\'fas ' + statusIcon + '\'></i> ' + statusLabel + '</span>'],
        ['Submitted', '<span style="color:#888;">' + req.submitted + '</span>'],
    ];
    if (req.remarks) {
        rows.push(['Admin Remarks', '<span style="color:#856404;background:#fff8e1;padding:0.3rem 0.6rem;border-radius:0.4rem;display:inline-block;">' + req.remarks + '</span>']);
    }

    var html = rows.map(function(r) {
        return '<tr><td>' + r[0] + '</td><td>' + r[1] + '</td></tr>';
    }).join('');
    document.getElementById('viewDetailBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function openApprove(id, reqNo) {
    document.getElementById('approveReqNo').textContent = reqNo;
    document.getElementById('approveForm').action = '/admin/trip-requests/' + id + '/approve';
    document.getElementById('approveVanSelect').value = '';
    document.getElementById('vanWarning').style.display = 'none';
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('approveVanSelect').addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        var isAvailable = selected && selected.getAttribute('data-available') === '1';
        document.getElementById('vanWarning').style.display = (selected.value && !isAvailable) ? 'block' : 'none';
    });
});

function openReject(id, reqNo) {
    document.getElementById('rejectReqNo').textContent = reqNo;
    document.getElementById('rejectForm').action = '/admin/trip-requests/' + id + '/reject';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endpush

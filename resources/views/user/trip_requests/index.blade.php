<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Trip Requests — VanService</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { background: #f0f2f8; font-family: 'Nunito', sans-serif; margin: 0; }

        .top-navbar {
            background: linear-gradient(90deg, #1a1f36, #2d3561);
            padding: 0.65rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,.25);
            position: sticky; top: 0; z-index: 1000;
        }
        .top-navbar .brand { font-size: 1.1rem; font-weight: 800; color: #fff; text-decoration: none; }
        .top-navbar .brand i { color: #4e73df; }

        .page-header {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: #fff; padding: 1.5rem 2rem 1.25rem;
        }
        .page-header h2 { font-size: 1.4rem; font-weight: 800; margin: 0; }
        .page-header p { margin: 0.2rem 0 0; opacity: 0.85; font-size: 0.88rem; }

        .section-card {
            background: #fff; border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,.07);
            overflow: hidden;
        }
        .section-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .section-card-header h5 {
            margin: 0; font-weight: 800; font-size: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
        }

        .dash-table { width: 100%; border-collapse: collapse; }
        .dash-table thead th {
            background: #f8f9fc; color: #888;
            font-size: 0.72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.06em;
            padding: 0.75rem 1.25rem; border-bottom: 2px solid #eee;
            white-space: nowrap;
        }
        .dash-table tbody tr {
            border-bottom: 1px solid #f5f5f5;
            transition: background .12s;
            cursor: pointer;
        }
        .dash-table tbody tr:hover { background: #f0f4ff; }
        .dash-table tbody tr:last-child { border-bottom: none; }
        .dash-table tbody td {
            padding: 0.85rem 1.25rem;
            font-size: 0.88rem; color: #333; vertical-align: middle;
        }
        .dash-table tbody td.muted { color: #888; font-size: 0.82rem; }

        .badge-pill-custom {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 0.28rem 0.75rem; border-radius: 2rem;
            font-size: 0.72rem; font-weight: 700; white-space: nowrap;
        }
        .badge-pending-req { background: #fff3cd; color: #856404; }
        .badge-approved    { background: #d1f5e0; color: #155724; }
        .badge-rejected    { background: #f8d7da; color: #721c24; }

        .empty-state { text-align: center; padding: 3rem 1rem; color: #aaa; }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .empty-state p { font-size: 0.9rem; margin: 0 0 1rem; }

        .detail-table { width: 100%; border-collapse: collapse; }
        .detail-table td { padding: 0.6rem 1.25rem; font-size: 0.88rem; border-bottom: 1px solid #f5f5f5; vertical-align: top; }
        .detail-table td:first-child { color: #888; font-weight: 700; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; width: 150px; white-space: nowrap; }
        .detail-table tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>

<nav class="top-navbar">
    <a class="brand" href="{{ route('user.dashboard') }}"><i class="fas fa-shuttle-van mr-2"></i>VanService</a>
    <div class="d-flex align-items-center" style="gap:0.5rem;">
        <span class="text-white d-none d-md-inline" style="font-size:0.85rem;opacity:.85;">
            <i class="fas fa-user-circle mr-1"></i>{{ auth()->user()->name }}
        </span>
        <a href="{{ route('user.trip_requests.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus mr-1"></i>New Request
        </a>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left mr-1"></i>Dashboard
        </a>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt mr-1"></i>Logout
            </button>
        </form>
    </div>
</nav>

<div class="page-header">
    <h2><i class="fas fa-paper-plane mr-2"></i>My Trip Requests</h2>
    <p>Track the status of all your submitted trip requests. Click any row to view full details.</p>
</div>

<div class="container-fluid px-3 px-md-4 py-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" style="border-radius:.75rem;">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div style="background:#fff;border-radius:.75rem;padding:1rem 1.25rem;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;"><i class="fas fa-list" style="color:#4e73df;"></i></div>
                <div><div style="font-size:.72rem;color:#888;font-weight:700;text-transform:uppercase;">Total</div><div style="font-size:1.5rem;font-weight:800;color:#2d3748;line-height:1;">{{ $requests->count() }}</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div style="background:#fff;border-radius:.75rem;padding:1rem 1.25rem;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;border-radius:50%;background:#fff8e1;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;"><i class="fas fa-clock" style="color:#f6c23e;"></i></div>
                <div><div style="font-size:.72rem;color:#888;font-weight:700;text-transform:uppercase;">Pending</div><div style="font-size:1.5rem;font-weight:800;color:#2d3748;line-height:1;">{{ $requests->where('status','pending')->count() }}</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div style="background:#fff;border-radius:.75rem;padding:1rem 1.25rem;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;border-radius:50%;background:#e8f8f5;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;"><i class="fas fa-check-circle" style="color:#1cc88a;"></i></div>
                <div><div style="font-size:.72rem;color:#888;font-weight:700;text-transform:uppercase;">Approved</div><div style="font-size:1.5rem;font-weight:800;color:#2d3748;line-height:1;">{{ $requests->where('status','approved')->count() }}</div></div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div style="background:#fff;border-radius:.75rem;padding:1rem 1.25rem;box-shadow:0 2px 8px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;">
                <div style="width:44px;height:44px;border-radius:50%;background:#fde8e8;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;"><i class="fas fa-times-circle" style="color:#e74a3b;"></i></div>
                <div><div style="font-size:.72rem;color:#888;font-weight:700;text-transform:uppercase;">Rejected</div><div style="font-size:1.5rem;font-weight:800;color:#2d3748;line-height:1;">{{ $requests->where('status','rejected')->count() }}</div></div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="section-card">
        <div class="section-card-header">
            <h5><i class="fas fa-table mr-2 text-primary"></i>All Requests
                <span style="font-size:0.78rem;font-weight:600;color:#888;">({{ $requests->count() }} total)</span>
            </h5>
            <a href="{{ route('user.trip_requests.create') }}" class="btn btn-primary btn-sm" style="border-radius:0.5rem;font-size:0.8rem;">
                <i class="fas fa-plus mr-1"></i>New Request
            </a>
        </div>

        @if($requests->isEmpty())
            <div class="empty-state">
                <i class="fas fa-paper-plane text-muted"></i>
                <p>You haven't submitted any trip requests yet.</p>
                <a href="{{ route('user.trip_requests.create') }}" class="btn btn-primary btn-sm" style="border-radius:0.5rem;">
                    <i class="fas fa-plus mr-1"></i>Submit Your First Request
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Request No.</th>
                            <th>Date of Travel</th>
                            <th>Purpose</th>
                            <th>Destination</th>
                            <th style="text-align:center;">Passengers</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $i => $req)
                        <tr onclick="openDetail({{ $req->id }})" title="Click to view full details">
                            <td class="muted">{{ $i + 1 }}</td>
                            <td><strong style="color:#4e73df;">{{ $req->request_no }}</strong></td>
                            <td>{{ $req->date_of_travel->format('M d, Y') }}</td>
                            <td>{{ $req->purpose }}</td>
                            <td>{{ $req->destination }}</td>
                            <td style="text-align:center;font-weight:700;">{{ $req->no_of_passengers }}</td>
                            <td>
                                @if($req->status === 'approved')
                                    <span class="badge-pill-custom badge-approved"><i class="fas fa-check-circle"></i> Approved</span>
                                @elseif($req->status === 'rejected')
                                    <span class="badge-pill-custom badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span>
                                @elseif($req->status === 'cancelled')
                                    <span class="badge-pill-custom badge-Cancelled"><i class="fas fa-ban"></i> Cancelled</span>
                                    @php
                                        $cancelReason = $req->admin_remarks
                                            ?: ($req->trip && $req->trip->driver_cancel_reason
                                                ? 'Cancelled by driver: ' . $req->trip->driver_cancel_reason
                                                : null);
                                    @endphp
                                    @if($cancelReason)
                                        <div style="font-size:0.7rem;color:#dc2626;margin-top:2px;">
                                            <i class="fas fa-info-circle"></i> {{ $cancelReason }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge-pill-custom badge-pending-req"><i class="fas fa-clock"></i> Pending</span>
                                @endif
                            </td>
                            <td class="muted">{{ $req->created_at->format('M d, Y') }}</td>
                            <td onclick="event.stopPropagation()">
                                @if($req->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;padding:0.25rem 0.6rem;border-radius:0.4rem;" onclick="confirmCancel({{ $req->id }}, '{{ $req->request_no }}')">
                                        <i class="fas fa-times mr-1"></i>Cancel
                                    </button>
                                @else
                                    <span style="color:#ccc;font-size:0.78rem;">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#e74a3b,#c0392b);color:#fff;border-radius:1rem 1rem 0 0;">
                <h5 class="modal-title mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Cancel Request</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center py-3">
                <p style="font-size:0.9rem;margin-bottom:0.5rem;">Are you sure you want to cancel</p>
                <strong id="cancelLabel" style="color:#e74a3b;"></strong>
                <p class="text-muted mt-2" style="font-size:0.82rem;">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0 pb-3 justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Keep It</button>
                <form id="cancelForm" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times mr-1"></i>Yes, Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:1rem;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#4e73df,#224abe);color:#fff;border-radius:1rem 1rem 0 0;">
                <div>
                    <h5 class="modal-title mb-0"><i class="fas fa-file-alt mr-2"></i>Trip Request Details</h5>
                    <small id="detail-req-no" style="opacity:0.85;font-size:0.82rem;"></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <table class="detail-table" id="detail-table-body"></table>
            </div>
            <div class="modal-footer border-0 pb-3">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmCancel(id, label) {
    document.getElementById('cancelLabel').textContent = label + '?';
    document.getElementById('cancelForm').action = '/trip-requests/' + id + '/cancel';
    $('#cancelModal').modal('show');
}

var REQUEST_DATA = {
    @foreach($requests as $req)
    {{ $req->id }}: {
        request_no: '{{ $req->request_no }}',
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

function openDetail(id) {
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

    document.getElementById('detail-req-no').textContent = req.request_no;
    document.getElementById('detail-table-body').innerHTML = html;
    $('#detailModal').modal('show');
}
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

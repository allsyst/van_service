@extends('layouts.admin')
@section('title','User Management')
@section('content')

<!-- Pending Users Alert -->
@if($pendingUsers->count() > 0)
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-warning text-white fw-bold">
        <i class="fas fa-user-clock me-2"></i>Pending Account Requests ({{ $pendingUsers->count() }})
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th class="d-none d-md-table-cell">Position</th>
                        <th class="d-none d-sm-table-cell">Email</th>
                        <th class="d-none d-lg-table-cell">Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingUsers as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td class="d-none d-md-table-cell">{{ $user->position ?? '—' }}</td>
                        <td class="d-none d-sm-table-cell">{{ $user->email }}</td>
                        <td class="d-none d-lg-table-cell">{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.users.approve', $user->id) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 me-1">
                                    <i class="fas fa-check me-1"></i>Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.reject', $user->id) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">
                                    <i class="fas fa-times me-1"></i>Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Tabs -->
<ul class="nav nav-tabs mb-0" id="userTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="passengers-tab" data-bs-toggle="tab" data-bs-target="#tab-passengers" type="button" role="tab">
            <i class="fas fa-users me-1"></i> Passengers
            <span class="badge bg-primary ms-1">{{ $allUsers->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="drivers-tab" data-bs-toggle="tab" data-bs-target="#tab-drivers" type="button" role="tab">
            <i class="fas fa-id-badge me-1"></i> Drivers
            <span class="badge bg-info ms-1">{{ $drivers->count() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="userTabsContent">

    <!-- Passengers Tab -->
    <div class="tab-pane fade show active" id="tab-passengers" role="tabpanel">
        <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
            <div class="card-header fw-bold">
                <i class="fas fa-users me-2 text-primary"></i>All Passengers
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th class="d-none d-md-table-cell">Position</th>
                            <th class="d-none d-sm-table-cell">Email</th>
                            <th>Status</th>
                            <th class="d-none d-lg-table-cell">Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td class="d-none d-md-table-cell">{{ $user->position ?? '—' }}</td>
                            <td class="d-none d-sm-table-cell" style="font-size:0.85rem;">{{ $user->email }}</td>
                            <td>
                                @if($user->account_status === 'approved')
                                    <span class="badge bg-success rounded-pill">Approved</span>
                                @elseif($user->account_status === 'pending')
                                    <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                @else
                                    <span class="badge bg-danger rounded-pill">Rejected</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell" style="font-size:0.85rem;">{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline" onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Drivers Tab -->
    <div class="tab-pane fade" id="tab-drivers" role="tabpanel">
        <div class="card border-0 shadow-sm" style="border-top-left-radius:0;">
            <div class="card-header d-flex justify-content-between align-items-center fw-bold">
                <span><i class="fas fa-id-badge me-2 text-info"></i>Driver Accounts</span>
                <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#createDriverModal">
                    <i class="fas fa-plus me-1"></i> Add Driver
                </button>
            </div>
            <div class="card-body p-0">
                @if($drivers->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-id-badge fa-3x mb-3 opacity-50"></i>
                        <div>No driver accounts yet. Click <strong>Add Driver</strong> to create one.</div>
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th class="d-none d-sm-table-cell">Email</th>
                                <th>Assigned Van</th>
                                <th>Assign Van</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($drivers as $driver)
                            @php
                                $assignedVan = $vans->firstWhere('driver_id', $driver->id);
                            @endphp
                            <tr>
                                <td>{{ $driver->id }}</td>
                                <td>
                                    <strong>{{ $driver->name }}</strong><br>
                                    <small class="text-muted">{{ $driver->position ?? 'Driver' }}</small>
                                </td>
                                <td class="d-none d-sm-table-cell" style="font-size:0.85rem;">{{ $driver->email }}</td>
                                <td>
                                    @if($assignedVan)
                                        <span class="badge bg-success rounded-pill">{{ $assignedVan->van_plate }} — {{ $assignedVan->van_model }}</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill">Not assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.drivers.assignVan', $driver->id) }}" class="d-flex" style="gap:6px;">
                                        @csrf
                                        <select name="van_id" class="form-select form-select-sm" style="min-width:160px;">
                                            <option value="">-- No Van --</option>
                                            @foreach($vans as $van)
                                                <option value="{{ $van->id }}"
                                                    {{ $van->driver_id == $driver->id ? 'selected' : '' }}
                                                    {{ $van->driver_id && $van->driver_id != $driver->id ? 'disabled' : '' }}>
                                                    {{ $van->van_plate }} — {{ $van->van_model }}
                                                    {{ $van->driver_id && $van->driver_id != $driver->id ? '(taken)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-info text-white rounded-pill px-3">
                                            <i class="fas fa-link me-1"></i>Assign
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.destroy', $driver->id) }}" style="display:inline" onsubmit="return confirm('Delete this driver?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Create Driver Modal -->
<div class="modal fade" id="createDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-id-badge me-2"></i>Create Driver Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.drivers.create') }}">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Driver's full name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="driver@example.com" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 6 characters" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Position / Title</label>
                        <input type="text" name="position" class="form-control" placeholder="e.g. Van Driver" value="Van Driver">
                    </div>
                    <div class="alert alert-info small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Driver accounts are automatically approved and can log in immediately.
                        After creating, go to <strong>Assign Van</strong> to link them to a van.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-plus me-1"></i>Create Driver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('createDriverModal'));
        modal.show();
        // Switch to drivers tab
        var driversTab = document.getElementById('drivers-tab');
        if (driversTab) {
            var tab = new bootstrap.Tab(driversTab);
            tab.show();
        }
    });
</script>
@endif

@endsection

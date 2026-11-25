@extends('layouts.admin')

@php($isEdit = $fuelRecord->exists)
@section('title', $isEdit ? 'Edit Fuel Record' : 'Add Fuel Record')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="fas fa-gas-pump text-primary me-2"></i>{{ $isEdit ? 'Edit Fuel Consumption Record' : 'Add Fuel Consumption Record' }}</h4>
        <p class="text-muted mb-0">Enter actual gasoline use. Link the entry to a completed travel when available.</p>
    </div>
    <a href="{{ route('admin.fuel-records.index') }}" class="btn btn-outline-secondary rounded-pill px-3"><i class="fas fa-arrow-left me-1"></i>Back to Analytics</a>
</div>

<div class="card">
    <div class="card-header"><i class="fas fa-clipboard-list me-2 text-primary"></i>Fuel Entry Details</div>
    <div class="card-body p-4">
        <form method="POST" action="{{ $isEdit ? route('admin.fuel-records.update', $fuelRecord) : route('admin.fuel-records.store') }}">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="trip_id" class="form-label fw-semibold">Linked Travel <span class="text-muted fw-normal">(optional)</span></label>
                    <select id="trip_id" name="trip_id" class="form-select @error('trip_id') is-invalid @enderror">
                        <option value="">-- Fuel not linked to a specific travel --</option>
                        @foreach($trips as $trip)
                            <option value="{{ $trip->id }}" data-van-id="{{ $trip->van_id }}" {{ (string) old('trip_id', $fuelRecord->trip_id) === (string) $trip->id ? 'selected' : '' }}>
                                {{ $trip->trip_date?->format('M d, Y') }} — {{ $trip->route_name }} ({{ $trip->van_model }})
                            </option>
                        @endforeach
                    </select>
                    @error('trip_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Selecting a travel automatically selects its assigned van.</div>
                </div>

                <div class="col-md-6">
                    <label for="van_id" class="form-label fw-semibold">Van <span class="text-danger">*</span></label>
                    <select id="van_id" name="van_id" class="form-select @error('van_id') is-invalid @enderror" required>
                        <option value="">-- Select van --</option>
                        @foreach($vans as $van)
                            <option value="{{ $van->id }}" {{ (string) old('van_id', $fuelRecord->van_id) === (string) $van->id ? 'selected' : '' }}>
                                {{ $van->van_model }}{{ $van->van_plate ? ' — '.$van->van_plate : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('van_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="fuel_date" class="form-label fw-semibold">Fuel Date <span class="text-danger">*</span></label>
                    <input id="fuel_date" type="date" name="fuel_date" class="form-control @error('fuel_date') is-invalid @enderror" value="{{ old('fuel_date', optional($fuelRecord->fuel_date)->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" required>
                    @error('fuel_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label for="liters" class="form-label fw-semibold">Gasoline Consumed (Liters) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input id="liters" type="number" name="liters" class="form-control @error('liters') is-invalid @enderror" value="{{ old('liters', $fuelRecord->liters) }}" min="0.01" max="10000" step="0.01" placeholder="e.g. 18.50" required>
                        <span class="input-group-text">L</span>
                        @error('liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="total_cost" class="form-label fw-semibold">Total Cost <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="input-group">
                        <span class="input-group-text">₱</span>
                        <input id="total_cost" type="number" name="total_cost" class="form-control @error('total_cost') is-invalid @enderror" value="{{ old('total_cost', $fuelRecord->total_cost) }}" min="0" step="0.01" placeholder="e.g. 1200.00">
                        @error('total_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="odometer_km" class="form-label fw-semibold">Odometer Reading <span class="text-muted fw-normal">(optional)</span></label>
                    <div class="input-group">
                        <input id="odometer_km" type="number" name="odometer_km" class="form-control @error('odometer_km') is-invalid @enderror" value="{{ old('odometer_km', $fuelRecord->odometer_km) }}" min="0" step="0.01" placeholder="e.g. 12540.50">
                        <span class="input-group-text">km</span>
                        @error('odometer_km')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="fuel_station" class="form-label fw-semibold">Fuel Station <span class="text-muted fw-normal">(optional)</span></label>
                    <input id="fuel_station" type="text" name="fuel_station" class="form-control @error('fuel_station') is-invalid @enderror" value="{{ old('fuel_station', $fuelRecord->fuel_station) }}" maxlength="255" placeholder="e.g. Petron Estancia">
                    @error('fuel_station')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" maxlength="2000" placeholder="Fuel receipt number, trip remarks, or other details">{{ old('notes', $fuelRecord->notes) }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fas fa-save me-1"></i>{{ $isEdit ? 'Save Changes' : 'Save Fuel Record' }}</button>
                <a href="{{ route('admin.fuel-records.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('trip_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const vanId = selected.dataset.vanId;
        if (vanId) document.getElementById('van_id').value = vanId;
    });
</script>
@endpush

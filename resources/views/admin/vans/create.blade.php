@extends('layouts.admin')
@section('title','Add Van')
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-plus mr-2"></i>Add New Van</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.vans.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Van Model</label>
                        <input type="text" name="van_model" class="form-control @error('van_model') is-invalid @enderror" value="{{ old('van_model') }}" required>
                        @error('van_model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Plate Number</label>
                        <input type="text" name="van_plate" class="form-control" value="{{ old('van_plate') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fuel Capacity (Liters)</label>
                        <input type="number" step="0.01" name="fuel_capacity" class="form-control @error('fuel_capacity') is-invalid @enderror" value="{{ old('fuel_capacity') }}" required>
                        @error('fuel_capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Average KM per Liter</label>
                        <input type="number" step="0.01" name="km_per_liter" class="form-control @error('km_per_liter') is-invalid @enderror" value="{{ old('km_per_liter') }}" required>
                        @error('km_per_liter')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Total Seats</label>
                        <input type="number" name="total_seats" class="form-control @error('total_seats') is-invalid @enderror" value="{{ old('total_seats',12) }}" required>
                        @error('total_seats')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Save Van</button>
            <a href="{{ route('admin.vans.index') }}" class="btn btn-secondary ml-2">Cancel</a>
        </form>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title','Edit Van')
@section('content')
<div class="card">
    <div class="card-header"><i class="fas fa-edit mr-2"></i>Edit Van: {{ $van->van_model }}</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.vans.update', $van->id) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Van Model</label>
                        <input type="text" name="van_model" class="form-control" value="{{ old('van_model',$van->van_model) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Plate Number</label>
                        <input type="text" name="van_plate" class="form-control" value="{{ old('van_plate',$van->van_plate) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Fuel Capacity (Liters)</label>
                        <input type="number" step="0.01" name="fuel_capacity" class="form-control" value="{{ old('fuel_capacity',$van->fuel_capacity) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Average KM per Liter</label>
                        <input type="number" step="0.01" name="km_per_liter" class="form-control" value="{{ old('km_per_liter',$van->km_per_liter) }}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Total Seats</label>
                        <input type="number" name="total_seats" class="form-control" value="{{ old('total_seats',$van->total_seats) }}" required>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Update Van</button>
            <a href="{{ route('admin.vans.index') }}" class="btn btn-secondary ml-2">Cancel</a>
        </form>
    </div>
</div>
@endsection

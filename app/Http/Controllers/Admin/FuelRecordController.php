<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FuelRecord;
use App\Models\Trip;
use App\Models\Van;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FuelRecordController extends Controller
{
    public function index()
    {
        $start = now()->startOfMonth()->subMonths(2);
        $end = now()->endOfMonth();
        $summary = $this->fuelSummary($start, $end, 'month');

        $records = FuelRecord::with(['van', 'trip'])
            ->orderByDesc('fuel_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.fuel-records.index', [
            'records' => $records,
            'chartData' => $summary['chartData'],
            'totalLiters' => $summary['totalLiters'],
            'totalTrips' => $summary['totalTrips'],
            'totalDistance' => $summary['totalDistance'],
            'totalFuelCost' => $summary['totalFuelCost'],
            'litersPer100Km' => $summary['litersPer100Km'],
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function report(Request $request)
    {
        [$period, $start, $end, $heading, $seriesMode, $reference] = $this->reportPeriod($request);
        $summary = $this->fuelSummary($start, $end, $seriesMode);
        $records = FuelRecord::with(['van', 'trip'])
            ->whereBetween('fuel_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('fuel_date')
            ->orderByDesc('id')
            ->get();

        return view('admin.fuel-records.report', [
            'period' => $period,
            'start' => $start,
            'end' => $end,
            'heading' => $heading,
            'reference' => $reference,
            'summary' => $summary,
            'records' => $records,
            'printable' => $request->boolean('print'),
        ]);
    }

    public function downloadCsv(Request $request)
    {
        [$period, $start, $end, $heading] = $this->reportPeriod($request);
        $filename = 'fuel_consumption_' . $period . '_' . $start->format('Y_m_d') . '_' . $end->format('Y_m_d') . '.csv';

        $records = FuelRecord::with(['van', 'trip'])
            ->whereBetween('fuel_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('fuel_date')
            ->orderBy('id')
            ->get();

        return response()->streamDownload(function () use ($records, $heading, $start, $end) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['VanService Fuel Consumption Report']);
            fputcsv($file, ['Period', $heading]);
            fputcsv($file, ['Date Range', $start->format('M d, Y') . ' - ' . $end->format('M d, Y')]);
            fputcsv($file, ['Generated', now()->format('M d, Y h:i A')]);
            fputcsv($file, []);
            fputcsv($file, ['Fuel Date', 'Van', 'Plate', 'Linked Trip', 'Trip Distance (km)', 'Liters', 'Total Cost', 'Odometer (km)', 'Fuel Station', 'Notes']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->fuel_date->format('Y-m-d'),
                    $record->van->van_model ?? 'Deleted van',
                    $record->van->van_plate ?? '',
                    $record->trip->route_name ?? '',
                    $record->trip?->distance_km ?? '',
                    number_format((float) $record->liters, 2, '.', ''),
                    $record->total_cost !== null ? number_format((float) $record->total_cost, 2, '.', '') : '',
                    $record->odometer_km !== null ? number_format((float) $record->odometer_km, 2, '.', '') : '',
                    $record->fuel_station ?? '',
                    $record->notes ?? '',
                ]);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function create()
    {
        $fuelRecord = new FuelRecord(['fuel_date' => today()->toDateString()]);

        return view('admin.fuel-records.form', $this->formData($fuelRecord));
    }

    public function store(Request $request)
    {
        FuelRecord::create($this->validatedData($request));

        return redirect()->route('admin.fuel-records.index')
            ->with('success', 'Fuel consumption record added.');
    }

    public function edit(FuelRecord $fuelRecord)
    {
        return view('admin.fuel-records.form', $this->formData($fuelRecord));
    }

    public function update(Request $request, FuelRecord $fuelRecord)
    {
        $fuelRecord->update($this->validatedData($request));

        return redirect()->route('admin.fuel-records.index')
            ->with('success', 'Fuel consumption record updated.');
    }

    public function destroy(FuelRecord $fuelRecord)
    {
        $fuelRecord->delete();

        return redirect()->route('admin.fuel-records.index')
            ->with('success', 'Fuel consumption record deleted.');
    }

    private function reportPeriod(Request $request): array
    {
        $period = $request->input('period', 'monthly');
        abort_unless(in_array($period, ['daily', 'monthly', 'yearly'], true), 422, 'Invalid fuel report period.');

        if ($period === 'daily') {
            $reference = Carbon::parse($request->input('date', today()->toDateString()));
            return ['daily', $reference->copy()->startOfDay(), $reference->copy()->endOfDay(), 'Daily Fuel Report — ' . $reference->format('F d, Y'), 'van', $reference->format('Y-m-d')];
        }

        if ($period === 'yearly') {
            $reference = Carbon::createFromFormat('Y', (string) $request->input('year', now()->year));
            return ['yearly', $reference->copy()->startOfYear(), $reference->copy()->endOfYear(), 'Yearly Fuel Report — ' . $reference->format('Y'), 'month', $reference->format('Y')];
        }

        $reference = Carbon::parse($request->input('month', now()->format('Y-m')) . '-01');
        return ['monthly', $reference->copy()->startOfMonth(), $reference->copy()->endOfMonth(), 'Monthly Fuel Report — ' . $reference->format('F Y'), 'day', $reference->format('Y-m')];
    }

    private function fuelSummary(Carbon $start, Carbon $end, string $seriesMode): array
    {
        $records = FuelRecord::with('van')
            ->whereBetween('fuel_date', [$start->toDateString(), $end->toDateString()])
            ->get();
        $trips = Trip::query()
            ->where('status', 'completed')
            ->whereBetween('trip_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $series = $this->seriesBuckets($start, $end, $seriesMode, $records, $trips);
        $totalLiters = (float) $records->sum('liters');
        $totalFuelCost = (float) $records->sum(fn (FuelRecord $record) => (float) ($record->total_cost ?? 0));
        $totalTrips = $trips->count();
        $totalDistance = (float) $trips->sum('distance_km');

        return [
            'chartData' => $series,
            'totalLiters' => $totalLiters,
            'totalFuelCost' => $totalFuelCost,
            'totalTrips' => $totalTrips,
            'totalDistance' => $totalDistance,
            'litersPer100Km' => $totalDistance > 0 ? ($totalLiters / $totalDistance) * 100 : null,
        ];
    }

    private function seriesBuckets(Carbon $start, Carbon $end, string $mode, $records, $trips): array
    {
        $buckets = collect();

        if ($mode === 'van') {
            $vanIds = $records->pluck('van_id')->merge($trips->pluck('van_id'))->filter()->unique()->values();
            $vans = Van::whereIn('id', $vanIds)->get()->keyBy('id');

            foreach ($vanIds as $vanId) {
                $van = $vans->get($vanId);
                $buckets->put((string) $vanId, [
                    'label' => trim(($van?->van_model ?? 'Van ' . $vanId) . ($van?->van_plate ? ' — ' . $van->van_plate : '')),
                    'fuelLiters' => 0,
                    'fuelCost' => 0,
                    'tripCounts' => 0,
                    'distanceKm' => 0,
                ]);
            }
        } else {
            $cursor = $start->copy();
            $format = $mode === 'month' ? 'Y-m' : 'Y-m-d';

            while ($cursor->lte($end)) {
                $key = $cursor->format($format);
                $buckets->put($key, [
                    'label' => $mode === 'month' ? $cursor->format('M') : $cursor->format('d'),
                    'fuelLiters' => 0,
                    'fuelCost' => 0,
                    'tripCounts' => 0,
                    'distanceKm' => 0,
                ]);
                $cursor = $mode === 'month' ? $cursor->addMonth() : $cursor->addDay();
            }
        }

        foreach ($records as $record) {
            $key = $mode === 'van' ? (string) $record->van_id : $record->fuel_date->format($mode === 'month' ? 'Y-m' : 'Y-m-d');
            if ($buckets->has($key)) {
                $bucket = $buckets->get($key);
                $bucket['fuelLiters'] += (float) $record->liters;
                $bucket['fuelCost'] += (float) ($record->total_cost ?? 0);
                $buckets->put($key, $bucket);
            }
        }

        foreach ($trips as $trip) {
            $key = $mode === 'van' ? (string) $trip->van_id : $trip->trip_date->format($mode === 'month' ? 'Y-m' : 'Y-m-d');
            if ($buckets->has($key)) {
                $bucket = $buckets->get($key);
                $bucket['tripCounts']++;
                $bucket['distanceKm'] += (float) $trip->distance_km;
                $buckets->put($key, $bucket);
            }
        }

        return [
            'labels' => $buckets->pluck('label')->values(),
            'fuelLiters' => $buckets->pluck('fuelLiters')->map(fn ($value) => round($value, 2))->values(),
            'fuelCost' => $buckets->pluck('fuelCost')->map(fn ($value) => round($value, 2))->values(),
            'tripCounts' => $buckets->pluck('tripCounts')->values(),
            'distanceKm' => $buckets->pluck('distanceKm')->map(fn ($value) => round($value, 2))->values(),
        ];
    }

    private function formData(FuelRecord $fuelRecord): array
    {
        return [
            'fuelRecord' => $fuelRecord,
            'vans' => Van::orderBy('van_model')->orderBy('van_plate')->get(),
            'trips' => Trip::with('van')->orderByDesc('trip_date')->orderByDesc('departure_time')->limit(250)->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'van_id' => 'required|exists:vans,id',
            'trip_id' => 'nullable|exists:trips,id',
            'fuel_date' => 'required|date|before_or_equal:today',
            'liters' => 'required|numeric|gt:0|max:10000',
            'total_cost' => 'nullable|numeric|min:0|max:9999999999.99',
            'odometer_km' => 'nullable|numeric|min:0|max:9999999999.99',
            'fuel_station' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (!empty($data['trip_id'])) {
            $trip = Trip::findOrFail($data['trip_id']);
            if ($trip->van_id && (int) $trip->van_id !== (int) $data['van_id']) {
                abort(422, 'The selected trip belongs to a different van.');
            }
        }

        return $data;
    }
}

<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        // Summary counts for the report page
        $summary = [
            'all'       => Booking::count(),
            'pending'   => Booking::where('status', 'Pending')->count(),
            'confirmed' => Booking::where('status', 'Confirmed')->count(),
            'completed' => Booking::where('status', 'Completed')->count(),
            'cancelled' => Booking::where('status', 'Cancelled')->count(),
        ];
        return view('admin.reports.index', compact('summary'));
    }

    /**
     * Download report as CSV
     * Query params: status (All|Pending|Confirmed|Completed|Cancelled), period (daily|weekly|monthly|yearly)
     */
    public function downloadCsv(Request $request)
    {
        [$bookings, $label] = $this->getFilteredBookings($request);

        $status   = $request->get('status', 'All');
        $filename = 'bookings_' . strtolower($status) . '_' . $label . '_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($bookings, $status, $label) {
            $file = fopen('php://output', 'w');

            // Report header info
            fputcsv($file, ['VanService Booking Report']);
            fputcsv($file, ['Status Filter:', $status]);
            fputcsv($file, ['Period:', ucfirst($label)]);
            fputcsv($file, ['Generated:', now()->format('F d, Y h:i A')]);
            fputcsv($file, ['Total Records:', count($bookings)]);
            fputcsv($file, []);

            // Column headers
            fputcsv($file, ['#', 'Booking ID', 'Passenger Name', 'Email', 'Van', 'Route', 'Pickup Location', 'Drop-off Location', 'Date', 'Time', 'Seats', 'Status', 'Booked At']);

            $i = 1;
            foreach ($bookings as $b) {
                fputcsv($file, [
                    $i++,
                    $b->id,
                    $b->user->name ?? 'N/A',
                    $b->user->email ?? 'N/A',
                    $b->van_model,
                    $b->trip->route_name ?? 'N/A',
                    $b->pickup_location ?? 'N/A',
                    $b->dropoff_location ?? 'N/A',
                    $b->booking_date,
                    Carbon::parse($b->booking_time)->format('h:i A'),
                    $b->seats_requested,
                    $b->status,
                    $b->created_at ? $b->created_at->format('M d, Y h:i A') : 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download report as PDF (HTML-based, printable)
     */
    public function downloadPdf(Request $request)
    {
        [$bookings, $label] = $this->getFilteredBookings($request);

        $status = $request->get('status', 'All');
        $period = $request->get('period', 'monthly');

        $html = $this->buildPdfHtml($bookings, $status, $label);

        $filename = 'bookings_' . strtolower($status) . '_' . $label . '_' . now()->format('Y_m_d') . '.pdf';

        // Use manus-md-to-pdf via a temp HTML file
        $tmpHtml = tempnam(sys_get_temp_dir(), 'report_') . '.html';
        file_put_contents($tmpHtml, $html);

        $tmpPdf = tempnam(sys_get_temp_dir(), 'report_') . '.pdf';

        // Use wkhtmltopdf if available, otherwise weasyprint
        $cmd = "wkhtmltopdf --quiet --page-size A4 --orientation Landscape '$tmpHtml' '$tmpPdf' 2>/dev/null";
        exec($cmd, $out, $ret);

        if ($ret !== 0 || !file_exists($tmpPdf) || filesize($tmpPdf) === 0) {
            // Fallback: weasyprint
            $cmd2 = "weasyprint '$tmpHtml' '$tmpPdf' 2>/dev/null";
            exec($cmd2, $out2, $ret2);
        }

        if (file_exists($tmpPdf) && filesize($tmpPdf) > 0) {
            return response()->download($tmpPdf, $filename, ['Content-Type' => 'application/pdf'])
                ->deleteFileAfterSend(true);
        }

        // Final fallback: return HTML as downloadable file
        @unlink($tmpHtml);
        return response($html, 200, [
            'Content-Type'        => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . str_replace('.pdf', '.html', $filename) . '"',
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function getFilteredBookings(Request $request): array
    {
        $status = $request->get('status', 'All');
        $period = $request->get('period', 'monthly');

        $query = Booking::with(['user', 'trip']);

        // Status filter
        if ($status !== 'All') {
            $query->where('status', $status);
        }

        // Period filter
        $now   = Carbon::now();
        $label = '';
        switch ($period) {
            case 'daily':
                $query->whereDate('booking_date', $now->toDateString());
                $label = 'daily_' . $now->format('Y_m_d');
                break;
            case 'weekly':
                $query->whereBetween('booking_date', [
                    $now->startOfWeek()->toDateString(),
                    $now->copy()->endOfWeek()->toDateString(),
                ]);
                $label = 'weekly_' . $now->format('Y_W');
                break;
            case 'monthly':
                $query->whereYear('booking_date', $now->year)
                      ->whereMonth('booking_date', $now->month);
                $label = 'monthly_' . $now->format('Y_m');
                break;
            case 'yearly':
                $query->whereYear('booking_date', $now->year);
                $label = 'yearly_' . $now->year;
                break;
            default:
                $label = 'all';
        }

        return [$query->latest()->get(), $label];
    }

    private function buildPdfHtml($bookings, string $status, string $label): string
    {
        $rows = '';
        $i    = 1;
        foreach ($bookings as $b) {
            $statusColor = match ($b->status) {
                'Pending'   => '#f6c23e',
                'Confirmed' => '#1cc88a',
                'Completed' => '#4e73df',
                'Cancelled' => '#e74a3b',
                default     => '#aaa',
            };
            $rows .= '<tr>
                <td>' . $i++ . '</td>
                <td>' . ($b->user->name ?? 'N/A') . '<br><small>' . ($b->user->email ?? '') . '</small></td>
                <td>' . htmlspecialchars($b->van_model) . '</td>
                <td>' . htmlspecialchars($b->trip->route_name ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($b->pickup_location ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($b->dropoff_location ?? 'N/A') . '</td>
                <td>' . $b->booking_date . '</td>
                <td>' . Carbon::parse($b->booking_time)->format('h:i A') . '</td>
                <td>' . $b->seats_requested . '</td>
                <td><span style="background:' . $statusColor . ';color:#fff;padding:2px 8px;border-radius:10px;font-size:11px">' . $b->status . '</span></td>
            </tr>';
        }

        $periodLabel = str_replace('_', ' ', ucwords($label, '_'));
        $total       = count($bookings);

        return '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
  h2 { color: #4e73df; margin-bottom: 4px; }
  .meta { color: #666; font-size: 11px; margin-bottom: 16px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #4e73df; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
  td { padding: 6px 8px; border-bottom: 1px solid #e3e6f0; font-size: 11px; vertical-align: top; }
  tr:nth-child(even) td { background: #f8f9fc; }
  .summary { display: flex; gap: 16px; margin-bottom: 16px; }
  .badge { padding: 4px 12px; border-radius: 20px; color: #fff; font-size: 11px; font-weight: bold; }
</style>
</head>
<body>
<h2>VanService Booking Report</h2>
<div class="meta">
  <strong>Status:</strong> ' . $status . ' &nbsp;|&nbsp;
  <strong>Period:</strong> ' . $periodLabel . ' &nbsp;|&nbsp;
  <strong>Total Records:</strong> ' . $total . ' &nbsp;|&nbsp;
  <strong>Generated:</strong> ' . now()->format('F d, Y h:i A') . '
</div>
<table>
  <thead>
    <tr>
      <th>#</th><th>Passenger</th><th>Van</th><th>Route</th><th>Pickup</th><th>Drop-off</th><th>Date</th><th>Time</th><th>Seats</th><th>Status</th>
    </tr>
  </thead>
  <tbody>
    ' . ($rows ?: '<tr><td colspan="10" style="text-align:center;color:#aaa">No records found.</td></tr>') . '
  </tbody>
</table>
</body>
</html>';
    }
}

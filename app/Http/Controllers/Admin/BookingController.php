<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request) {
        $status = $request->query('status');
        $allowedStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];

        $bookingsQuery = Booking::with(['user','trip','tripRequest'])->latest();
        if (in_array($status, $allowedStatuses, true)) {
            $bookingsQuery->where('status', $status);
        } else {
            $status = null;
        }

        $bookings = $bookingsQuery->get();
        return view('admin.bookings.index', compact('bookings', 'status'));
    }

    public function history(Request $request)
    {
        $status = $request->query('status', 'All');
        $allowedStatuses = ['All', 'Completed', 'Cancelled'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'All';
        }

        $history = Booking::with([
            'user',
            'trip.van.driver',
            'tripRequest.trip.van.driver',
        ])
            ->where(function ($query) {
                $query->whereIn('status', ['Completed', 'Cancelled'])
                    ->orWhereHas('trip', function ($tripQuery) {
                        $tripQuery->whereIn('status', ['completed', 'cancelled', 'Completed', 'Cancelled']);
                    })
                    ->orWhereHas('tripRequest.trip', function ($tripQuery) {
                        $tripQuery->whereIn('status', ['completed', 'cancelled', 'Completed', 'Cancelled']);
                    });
            })
            ->latest('updated_at')
            ->get();

        $history = $history->filter(function ($booking) use ($status) {
            $trip = $booking->trip ?? $booking->tripRequest?->trip;
            $recordStatus = ($booking->status === 'Cancelled' || in_array($trip?->status, ['cancelled', 'Cancelled'], true))
                ? 'Cancelled'
                : 'Completed';
            $booking->history_status = $recordStatus;
            return $status === 'All' || $status === $recordStatus;
        })->values();

        return view('admin.travel-history.index', compact('history', 'status'));
    }
    public function updateStatus(Request $request, Booking $booking) {
        $request->validate(['status' => 'required|in:Pending,Confirmed,Completed,Cancelled']);
        $booking->update(['status' => $request->status]);
        if ($request->ajax()) return response()->json(['success' => true]);
        return redirect()->back()->with('success', 'Status updated!');
    }
    public function destroy(Booking $booking) {
        $booking->delete();
        return redirect()->back()->with('success', 'Booking deleted!');
    }
    public function show(Booking $booking) {
        $booking->update(['is_read' => true]);
        return response()->json($booking->load(['user','trip','tripRequest']));
    }
}

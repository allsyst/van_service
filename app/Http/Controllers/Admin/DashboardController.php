<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\AdminNotification;

class DashboardController extends Controller
{
    public function index() {
        $pendingCount = Booking::where('status','Pending')->count();
        $confirmedCount = Booking::where('status','Confirmed')->count();
        $completedCount = Booking::where('status','Completed')->count();
        $pendingUsers = User::where('account_status','pending')->count();
        $recentBookings = Booking::with(['user','trip'])->latest()->take(20)->get();
        $notifications = Booking::with(['user','trip'])->latest()->take(5)->get();
        $unreadCount = Booking::where('is_read', false)->count()
                     + AdminNotification::where('is_read', false)->count();
        $userNotifications = AdminNotification::orderByDesc('created_at')->take(5)->get();
        $userUnreadCount = AdminNotification::where('is_read', false)->count();
        return view('admin.dashboard', compact(
            'pendingCount','confirmedCount','completedCount','pendingUsers',
            'recentBookings','notifications','unreadCount',
            'userNotifications','userUnreadCount'
        ));
    }

    public function markRead($id) {
        Booking::where('id',$id)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllRead() {
        Booking::where('is_read', false)->update(['is_read' => true]);
        return redirect()->back()->with('success','All booking notifications marked as read.');
    }

    public function markUserNotifRead($id) {
        AdminNotification::where('id',$id)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    }

    public function markAllUserNotifRead() {
        AdminNotification::where('is_read', false)->update(['is_read' => true]);
        return redirect()->back()->with('success','All system alerts marked as read.');
    }

    public function generateReport() {
        $bookings = Booking::with(['user','trip'])->where('status','Completed')->latest()->get();
        $filename = 'completed_bookings_'.now()->format('Y_m_d').'.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="'.$filename.'"'];
        $callback = function() use ($bookings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID','User','Email','Van','Route','Date','Time','Seats','Status']);
            foreach ($bookings as $b) {
                fputcsv($file, [
                    $b->id,
                    $b->user->name ?? 'N/A',
                    $b->user->email ?? 'N/A',
                    $b->van_model,
                    $b->trip->route_name ?? 'N/A',
                    $b->booking_date,
                    $b->booking_time,
                    $b->seats_requested,
                    $b->status,
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}

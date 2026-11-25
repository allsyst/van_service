<?php
namespace App\Http\View\Composers;

use App\Models\Booking;
use App\Models\AdminNotification;
use Illuminate\View\View;

class AdminComposer
{
    public function compose(View $view): void
    {
        // Booking notifications (existing)
        $bookingNotifications = Booking::with(['user', 'trip'])
            ->latest()
            ->take(5)
            ->get();

        $bookingUnread = Booking::where('is_read', false)->count();

        // System alerts include account activity and van-arrival notifications.
        $userNotifications = AdminNotification::orderByDesc('created_at')
            ->take(5)
            ->get();

        $userUnread = AdminNotification::where('is_read', false)->count();

        $view->with([
            'notifications'      => $bookingNotifications,
            'unreadCount'        => $bookingUnread + $userUnread,
            'userNotifications'  => $userNotifications,
            'userUnreadCount'    => $userUnread,
        ]);
    }
}

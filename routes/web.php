<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VanController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\FuelRecordController;
use App\Http\Controllers\Admin\TripRequestController as AdminTripRequestController;
use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\User\TripController as UserTripController;
use App\Http\Controllers\User\TripRequestController;
use App\Http\Controllers\TripTicketController;

Route::get('/', function () { return redirect()->route('login'); });

// Smart redirect after login based on role
Route::get('/home-redirect', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->role === 'driver') {
            return redirect()->route('driver.dashboard');
        }
        return redirect()->route('user.dashboard');
    }
    return redirect()->route('login');
})->middleware('auth')->name('home.redirect');

require __DIR__.'/auth.php';

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/mark-read/{id}', [DashboardController::class, 'markRead'])->name('markRead');
    Route::post('/mark-all-read', [DashboardController::class, 'markAllRead'])->name('markAllRead');
    Route::post('/mark-user-notif-read/{id}', [DashboardController::class, 'markUserNotifRead'])->name('markUserNotifRead');
    Route::post('/mark-all-user-notif-read', [DashboardController::class, 'markAllUserNotifRead'])->name('markAllUserNotifRead');
    Route::get('/generate-report', [DashboardController::class, 'generateReport'])->name('generateReport');
    // Enhanced reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/csv', [ReportController::class, 'downloadCsv'])->name('reports.csv');
    Route::get('/reports/pdf', [ReportController::class, 'downloadPdf'])->name('reports.pdf');
    Route::get('/fuel-records/report', [FuelRecordController::class, 'report'])->name('fuel-records.report');
    Route::get('/fuel-records/report/csv', [FuelRecordController::class, 'downloadCsv'])->name('fuel-records.download-csv');
    Route::resource('fuel-records', FuelRecordController::class)->except(['show']);
    Route::resource('vans', VanController::class);
    Route::get('/vans/{van}/data', [VanController::class, 'getVanData'])->name('vans.data');
    Route::post('/vans/{van}/toggle-availability', [VanController::class, 'toggleAvailability'])->name('vans.toggleAvailability');
    Route::get('/vans/{van}/passengers', [VanController::class, 'passengers'])->name('vans.passengers');
    Route::get('/vans/{van}/trips', [VanController::class, 'trips'])->name('vans.trips');
    Route::resource('trips', TripController::class);
    Route::get('/trips/{trip}/ticket', [TripTicketController::class, 'web'])->name('trips.ticket');
    Route::get('/trips/{trip}/passengers', [VanController::class, 'tripPassengers'])->name('trips.passengers');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/travel-history', [AdminBookingController::class, 'history'])->name('travel-history');
    Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.updateStatus');
    Route::delete('/bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy');
    Route::get('/bookings/{booking}/show', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::patch('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/drivers/create', [UserController::class, 'createDriver'])->name('drivers.create');
    Route::post('/drivers/{driver}/assign-van', [UserController::class, 'assignVan'])->name('drivers.assignVan');
    // Trip Requests management
    Route::get('/trip-requests', [AdminTripRequestController::class, 'index'])->name('trip_requests.index');
    Route::patch('/trip-requests/{tripRequest}/approve', [AdminTripRequestController::class, 'approve'])->name('trip_requests.approve');
    Route::patch('/trip-requests/{tripRequest}/reject', [AdminTripRequestController::class, 'reject'])->name('trip_requests.reject');
    // Live van tracking
    Route::get('/live-map', [LocationController::class, 'liveMap'])->name('live-map');
    Route::get('/locations', [LocationController::class, 'getLocations'])->name('locations.get');
});

// Driver Dashboard (requires auth + driver role)
Route::prefix('driver')->name('driver.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DriverDashboardController::class, 'index'])->name('dashboard');
    Route::get('/trips/{trip}/passengers', [DriverDashboardController::class, 'tripPassengers'])->name('trips.passengers');
    Route::get('/trips/{trip}/ticket', [TripTicketController::class, 'web'])->name('trips.ticket');
    Route::post('/bookings/{booking}/status', [DriverDashboardController::class, 'updateBookingStatus'])->name('bookings.status');
    Route::post('/trips/{trip}/complete', [DriverDashboardController::class, 'completeTrip'])->name('trips.complete');
    Route::post('/trips/{trip}/takeoff', [DriverDashboardController::class, 'takeOff'])->name('trips.takeoff');
    Route::post('/trips/{trip}/cancel', [DriverDashboardController::class, 'cancelTrip'])->name('trips.cancel');
    Route::post('/trips/{trip}/driver-confirm', [DriverDashboardController::class, 'driverConfirmTrip'])->name('trips.driverConfirm');
    Route::post('/van/toggle-availability', [DriverDashboardController::class, 'toggleVanAvailability'])->name('van.toggleAvailability');
    Route::get('/trips/create', [DriverDashboardController::class, 'createTrip'])->name('trips.create');
    Route::post('/trips', [DriverDashboardController::class, 'storeTrip'])->name('trips.store');
});

// User routes
Route::middleware(['auth', 'approved'])->group(function () {
    Route::get('/dashboard', [BookingController::class, 'index'])->name('user.dashboard');
    Route::get('/bookings/trips/{trip}/availability', [BookingController::class, 'tripAvailability'])->name('bookings.availability');
    Route::get('/bookings/requests/{tripRequest}/availability', [BookingController::class, 'tripRequestAvailability'])->name('bookings.request-availability');
    Route::post('/book', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/ticket', [BookingController::class, 'ticket'])->name('bookings.ticket');
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancelBooking'])->name('bookings.cancel');
    Route::post('/trip-requests/{id}/cancel', [BookingController::class, 'cancelRequest'])->name('user.trip_requests.cancel');
    Route::get('/bookings/trip-passengers/{tripId}', [BookingController::class, 'tripPassengers'])->name('bookings.tripPassengers');
    // User trip requests
    Route::get('/user/trip-requests', [TripRequestController::class, 'index'])->name('user.trip_requests.index');
    Route::get('/user/trip-requests/create', [TripRequestController::class, 'create'])->name('user.trip_requests.create');
    Route::post('/user/trip-requests', [TripRequestController::class, 'store'])->name('user.trip_requests.store');
    // User notifications
    Route::post('/user/notifications/{id}/read', function ($id) {
        \App\Models\UserNotification::where('id', $id)->where('user_id', auth()->id())->update(['is_read' => true]);
        return response()->json(['success' => true]);
    })->name('user.notifications.read');
    Route::post('/user/notifications/read-all', function () {
        \App\Models\UserNotification::where('user_id', auth()->id())->update(['is_read' => true]);
        return response()->json(['success' => true]);
    })->name('user.notifications.readAll');
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DriverMobileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Used only to verify an expected local Laravel host before the app exposes
// its sign-in form. It returns no account, location, or trip data.
Route::get('/driver/mobile/health', function () {
    return response()->json([
        'service' => 'vanservice-driver-api',
        'version' => 1,
    ]);
})->middleware('throttle:20,1');

/*
|--------------------------------------------------------------------------
| Driver Mobile App API
|--------------------------------------------------------------------------
| The native app sends a Bearer token. Location updates are tied to the
| driver's assigned van on the server, never to a van ID supplied by the app.
*/
Route::prefix('driver/mobile')->group(function () {
    Route::post('/login', [DriverMobileController::class, 'login'])->middleware('throttle:6,1');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::get('/profile', [DriverMobileController::class, 'profile']);
        Route::get('/active-trip', [DriverMobileController::class, 'activeTrip']);
        Route::get('/trip-ticket/{trip}', [DriverMobileController::class, 'tripTicket']);
        Route::post('/confirm-trip', [DriverMobileController::class, 'confirmTrip']);
        Route::get('/bookings', [DriverMobileController::class, 'bookings']);
        Route::post('/bookings/{booking}/decision', [DriverMobileController::class, 'bookingDecision']);
        Route::post('/takeoff', [DriverMobileController::class, 'takeOff']);
        Route::post('/arrive', [DriverMobileController::class, 'markArrived']);
        Route::post('/location', [DriverMobileController::class, 'updateLocation']);
        Route::post('/arrival-check', [DriverMobileController::class, 'checkArrival']);
        Route::post('/stop-sharing', [DriverMobileController::class, 'stopSharing']);
        Route::post('/logout', [DriverMobileController::class, 'logout']);
    });
});

<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Trip;
use App\Models\User;
use App\Models\Van;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TripNotificationService
{
    /**
     * Record one driver-confirmed departure and create at most one admin alert.
     * The row lock makes repeated phone or web requests idempotent.
     */
    public function markDeparted(Trip $trip, User $driver): array
    {
        return DB::transaction(function () use ($trip, $driver) {
            $lockedTrip = Trip::whereKey($trip->id)->lockForUpdate()->firstOrFail();

            if ($lockedTrip->departed_at) {
                return ['trip' => $lockedTrip, 'newly_departed' => false];
            }

            $departedAt = now();
            $lockedTrip->update([
                'departed_at' => $departedAt,
                'departure_notified_at' => $departedAt,
            ]);

            $tripDate = Carbon::parse($lockedTrip->trip_date)->format('M d, Y');
            $departureTime = Carbon::parse($lockedTrip->departure_time)->format('h:i A');
            $message = "Van {$lockedTrip->van_model} driven by {$driver->name} took off for {$lockedTrip->route_name} on {$tripDate} at {$departureTime}.";

            AdminNotification::create([
                'type' => 'van_takeoff',
                'title' => 'Van Took Off',
                'message' => substr($message, 0, 250),
                'related_id' => $lockedTrip->id,
                'is_read' => false,
            ]);

            return ['trip' => $lockedTrip->fresh(), 'newly_departed' => true];
        });
    }

    /**
     * Check the active departed trip on each trusted location upload. A delay
     * alert is created only once, after the trip's saved ETA has passed.
     */
    public function notifyDelayIfNeededForVan(Van $van, User $driver): ?Trip
    {
        return DB::transaction(function () use ($van, $driver) {
            $trip = Trip::where('van_id', $van->id)
                ->whereDate('trip_date', today())
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->whereNotNull('departed_at')
                ->whereNull('arrived_at')
                ->whereNull('delay_notified_at')
                ->whereNotNull('eta')
                ->orderBy('departure_time')
                ->lockForUpdate()
                ->first();

            if (!$trip) {
                return null;
            }

            $estimatedArrival = Carbon::parse($trip->trip_date->format('Y-m-d') . ' ' . $trip->eta);
            if (now()->lessThanOrEqualTo($estimatedArrival)) {
                return null;
            }

            $delayedAt = now();
            $delayMinutes = $estimatedArrival->diffInMinutes($delayedAt);
            $trip->update(['delay_notified_at' => $delayedAt]);

            $message = "Van {$trip->van_model} driven by {$driver->name} is delayed for {$trip->route_name}. Estimated arrival was {$estimatedArrival->format('h:i A')} ({$delayMinutes} minute(s) overdue).";
            AdminNotification::create([
                'type' => 'van_delayed',
                'title' => 'Van Delayed Past Estimated Arrival',
                'message' => substr($message, 0, 250),
                'related_id' => $trip->id,
                'is_read' => false,
            ]);

            return $trip->fresh();
        });
    }
}

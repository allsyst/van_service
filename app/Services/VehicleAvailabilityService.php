<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class VehicleAvailabilityService
{
    /**
     * Return the first active trip whose scheduled time overlaps the proposed
     * trip for the same van. An ETA is used as the end time; when an ETA is
     * unavailable the trip is reserved for a conservative two-hour window.
     */
    public function findScheduledConflict(
        int $vanId,
        string|Carbon $tripDate,
        string $departureTime,
        ?string $eta = null,
        ?int $ignoreTripId = null,
        bool $lockForUpdate = false,
    ): ?Trip {
        $date = Carbon::parse($tripDate)->toDateString();

        $query = Trip::query()
            ->where('van_id', $vanId)
            ->whereDate('trip_date', $date)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('departure_time');

        if ($ignoreTripId) {
            $query->whereKeyNot($ignoreTripId);
        }

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->get()->first(function (Trip $scheduledTrip) use ($date, $departureTime, $eta) {
            return $this->timesOverlap(
                $date,
                $departureTime,
                $eta,
                (string) $scheduledTrip->departure_time,
                $scheduledTrip->eta ? (string) $scheduledTrip->eta : null,
            );
        });
    }

    public function userHasActiveBookingForTrip(int $userId, int $tripId): bool
    {
        return Booking::query()
            ->where('user_id', $userId)
            ->where('trip_id', $tripId)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->exists();
    }

    public function userHasActiveBookingForTripRequest(int $userId, int $tripRequestId): bool
    {
        return Booking::query()
            ->where('user_id', $userId)
            ->where('trip_request_id', $tripRequestId)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->exists();
    }

    public function tripIsBookable(Trip $trip): bool
    {
        return $trip->trip_date && $trip->trip_date->gte(today())
            && !in_array($trip->status, ['cancelled', 'completed'], true)
            && $trip->availableSeats() > 0;
    }

    private function timesOverlap(
        string $date,
        string $firstDeparture,
        ?string $firstEta,
        string $secondDeparture,
        ?string $secondEta,
    ): bool {
        [$firstStart, $firstEnd] = $this->timeWindow($date, $firstDeparture, $firstEta);
        [$secondStart, $secondEnd] = $this->timeWindow($date, $secondDeparture, $secondEta);

        return $firstStart->lt($secondEnd) && $secondStart->lt($firstEnd);
    }

    private function timeWindow(string $date, string $departureTime, ?string $eta): array
    {
        $start = Carbon::parse($date . ' ' . $departureTime);
        $end = $eta ? Carbon::parse($date . ' ' . $eta) : $start->copy()->addHours(2);

        // A same-day ETA earlier than departure is treated as an overnight ETA.
        if ($end->lte($start)) {
            $end->addDay();
        }

        return [$start, $end];
    }
}

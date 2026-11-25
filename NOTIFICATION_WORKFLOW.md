# Notification Workflow

This update adds or standardizes **in-system notifications** in the user dashboard notification bell and the administrator notification bell.

| Recipient | Event | Trigger |
|---|---|---|
| User | Trip request approved | An administrator approves the request and assigns a van. |
| User | Trip request declined | An administrator declines the request. |
| Administrator | New user registration | A user completes registration. |
| Administrator | New booking | A user submits a booking from an available trip or an approved trip request. |
| Administrator | Van takeoff | The confirmed driver presses **Mark Van Take Off** in the mobile app or **Take Off** in the driver dashboard. |
| Administrator | Van arrived | The foreground driver app verifies that the van entered the saved destination geofence. |
| Administrator | Van delayed | A departed, unarrived trip has passed its saved ETA and the foreground app sends a subsequent location update. |

## Delay Rule

The delayed alert is intentionally sent **once per trip**. It is created only after all of the following are true: the driver reported takeoff, the trip has an ETA, the trip has not arrived or been completed/cancelled, and a trusted foreground mobile location update reaches Laravel after the ETA. The alert reports the number of minutes overdue at the first qualifying update.

## Installation

Copy the Laravel update into the project while preserving your `.env` file, then run:

```bash
php artisan migrate
php artisan optimize:clear
```

The migration adds the departure and delay notification timestamps used to prevent duplicate alerts. For automatic arrival and delay notifications, drivers must keep the VanService Driver app open and location sharing active during the trip.

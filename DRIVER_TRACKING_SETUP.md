# VanService Driver Live Location Setup

## What This Update Adds

The `VanService Driver` native mobile application lets a signed-in driver share the exact current location of the van assigned to their account. The application subscribes only to **foreground** GPS updates. If the driver stops sharing, leaves the app, or closes it, new updates stop. The administrator sees each van's latest point on **Admin → Live Driver Locations**.

## Install the Laravel Update

Replace your Laravel project with this package or merge the changed files, then run the following command from the Laravel project directory:

```bash
php artisan migrate
```

The migration adds the optional `accuracy` field to existing `van_locations` rows. The application already includes Laravel Sanctum token support; the mobile sign-in route creates a driver-specific token and ties each update to the van assigned to that driver. Do not expose the retired legacy `/driver/location` routes.

## Driver Account and Van Assignment

The driver must use an existing account whose `role` is `driver`, and an administrator must have assigned that account to a van. The app will reject sign-in with a clear message if no van is assigned.

| Requirement | Where to Configure It |
|---|---|
| Driver account | Admin → Users / Drivers |
| Van assignment | Admin → Users / Drivers, assign a van to the driver |
| Admin map | Admin → Live Driver Locations |
| Mobile app server value | The public HTTPS Laravel URL, or the XAMPP computer's private-network address for local testing |

## Phone-to-XAMPP Local Testing

When testing on the same Wi-Fi network, start Laravel so another device can reach it:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Find the computer's private IP address with `ipconfig` on Windows, then enter the address in the mobile app as, for example:

```text
http://192.168.1.25:8000
```

Do not enter `http://localhost:8000` on the phone because `localhost` refers to the phone itself. For production deployment, configure HTTPS and enter the public `https://` URL in the app.

## Automatic App Server Discovery

The VanService Driver app no longer shows a server-address field. At startup it calls the small `/api/driver/mobile/health` endpoint and accepts a host only when it returns the expected VanService response.

| Environment | Automatic candidate |
|---|---|
| Phone or tablet running the Expo development build | The local-network address derived from the Expo development host, first with port `8000` and then with `/van_service_laravel/public` |
| Android Studio emulator | `http://10.0.2.2:8000`, followed by `http://10.0.2.2/van_service_laravel/public` |
| Installed production build | `EXPO_PUBLIC_LARAVEL_URL` must be set to your public HTTPS Laravel address at build time |

For Android Studio, start Laravel with `php artisan serve --host=0.0.0.0 --port=8000` before launching the emulator. The app displays **Try Again** rather than accepting an unknown server when discovery cannot verify the expected response.

## Privacy Behavior

The driver always starts sharing themselves by tapping **Start Sharing**. The app does not request background location permission. The administrator's map automatically shows the van offline when sharing is stopped or no update arrives for 45 seconds.

## Automatic Arrival Alert

The app checks the current GPS point against the active trip's saved destination while the driver app is open in the foreground. When the van enters the **150 metre** arrival radius, Laravel verifies that the trip belongs to the driver's assigned van, records the arrival once, and creates a **Van Arrived at Destination** alert in the administrator notification bell.

To enable this for an administrator-created trip, enter the pickup and destination, then use **Calculate Route & Distance** before saving the trip. This saves the exact destination coordinate used by the geofence. For a user trip request, the system attempts to save the destination coordinate during request submission. Existing trips created before this update must be opened, have their route calculated, and be saved again before they can trigger automatic arrival alerts.

The arrival rule operates only while the driver app remains open in the foreground. Closing or backgrounding the app stops GPS updates and therefore does not create an automatic arrival alert.

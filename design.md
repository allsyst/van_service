# VanService Driver Native Android Design

## Product intent

VanService Driver Native is a standalone Android application for drivers who operate vans assigned in the existing VanService Laravel system. It replaces the previous development-only mobile client with a conventional Kotlin Android application that can be built as an APK in Android Studio and does not rely on Metro or Expo at runtime.

The app sends precise location only after a driver explicitly enables sharing and only while the application is visible in the foreground. The Laravel server remains the source of truth for driver authentication, van assignment, active trips, arrival notifications, departure notifications, and administrator live tracking.

## Screen list

| Screen | Primary content and function |
|---|---|
| Server check and sign in | A compact connection state, driver email and password fields, and a Sign In action. The app checks approved local Laravel endpoints without displaying a server-address field. |
| Driver home | Driver and assigned-van identity, sharing status, current location accuracy, active-trip details, a Start/Stop Sharing control, and an explicit Sign Out action. |
| Active trip status | Route, destination, departure state, saved arrival radius, destination distance, Take Off control, and the arrival-notified state. It is included in the Driver home as a primary card for one-handed use. |
| Permission and error states | Clear explanations when location permission, a driver van assignment, an active trip, or a reachable Laravel server is unavailable. |

## Core user flows

| Flow | Steps |
|---|---|
| Sign in | Driver opens the app → app verifies a supported Laravel host → driver enters existing account credentials → Laravel issues a scoped token → the app securely saves the token and shows the assigned van. |
| Start live tracking | Driver taps Start Sharing → Android requests precise foreground location permission if needed → app subscribes to location updates while visible → app uploads each reading to the driver’s assigned van record. |
| Report departure | Driver taps Take Off on an eligible active trip → app sends the active trip ID → Laravel records departure and notifies administrators once. |
| Automatic arrival | An uploaded foreground reading is compared with the saved destination coordinate → when it is inside the trip radius, app asks Laravel to verify arrival → Laravel prevents duplicate notifications and records the administrator alert. |
| Stop tracking | Driver taps Stop Sharing, signs out, or backgrounds the application → local listener is removed → Laravel marks the van offline for deliberate stops. |

## Visual and interaction design

The layout assumes a 9:16 portrait phone or tablet and prioritizes large, bottom-reachable actions. The screen uses a calm white background, a dark navy title treatment, rounded cards, and concise status labels. The main actions are full-width buttons with immediate feedback and disabled states while a request is in progress.

| Token | Value | Use |
|---|---:|---|
| Primary navy | `#153E75` | Header, identity marker, primary text |
| Action blue | `#2563EB` | Start Sharing and Sign In buttons |
| Live green | `#15803D` | Active sharing and confirmed states |
| Warning amber | `#B45309` | GPS accuracy and pending action states |
| Alert red | `#B91C1C` | Errors, stop sharing, and permission failures |
| Page gray | `#F5F7FB` | Screen background and quiet status surfaces |

## Native implementation decisions

The project will use Kotlin, Android Views with Material Components, AndroidX Security encrypted preferences for the session token, Fused Location Provider for foreground readings, and `HttpURLConnection` for JSON calls. This avoids JavaScript bundling and native React dependency issues. The Android project targets API 24 or newer and builds universal `arm64-v8a`, `armeabi-v7a`, `x86`, and `x86_64` APK variants.

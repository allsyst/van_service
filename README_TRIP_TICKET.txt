VanService Native Driver App — NISU Driver's Trip Ticket update

This Android Studio project adds View Driver's Trip Ticket to the assigned-trip card.
The app loads the official trip record from Laravel, including route, driver, van,
booked passengers, distance, fuel recorded, takeoff, and arrival status.

Build with: ./gradlew :app:assembleDebug
The Laravel server must include GET /api/driver/mobile/trip-ticket/{trip}.

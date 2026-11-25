package com.vanservice.drivernative

data class DriverSession(
    val serverUrl: String,
    val token: String,
    val driverName: String,
    val driverEmail: String,
    val vanPlate: String,
    val vanModel: String
)

data class ActiveTrip(
    val id: Long,
    val routeName: String,
    val destinationName: String,
    val tripDate: String,
    val departureTime: String?,
    val destinationLatitude: Double?,
    val destinationLongitude: Double?,
    val radiusMeters: Int,
    var arrived: Boolean,
    var departed: Boolean,
    var canTakeOff: Boolean,
    var requiresConfirmation: Boolean
)

data class DriverBooking(
    val id: Long,
    val passengerName: String,
    val tripLabel: String,
    val bookingDate: String,
    val bookingTime: String?,
    val seatsRequested: Int,
    val purpose: String
)

data class TripTicketPassenger(
    val name: String,
    val seats: Int,
    val status: String
)

data class TripTicket(
    val tripNumber: String,
    val routeName: String,
    val pickupLocation: String,
    val dropoffLocation: String,
    val tripDate: String,
    val departureTime: String?,
    val driverName: String,
    val vanPlate: String,
    val vanModel: String,
    val departedAt: String?,
    val arrivedAt: String?,
    val distanceKm: Double,
    val fuelLiters: Double,
    val passengers: List<TripTicketPassenger>
)

data class LocationReading(
    val latitude: Double,
    val longitude: Double,
    val accuracyMeters: Float?,
    val speedKmh: Double?,
    val heading: Double?
)

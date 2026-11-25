package com.vanservice.drivernative

import android.content.Context
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import android.os.Handler
import android.os.Looper
import org.json.JSONObject
import java.io.BufferedReader
import java.io.OutputStreamWriter
import java.net.HttpURLConnection
import java.net.Inet4Address
import java.net.URL
import java.util.concurrent.Callable
import java.util.concurrent.ExecutorCompletionService
import java.util.concurrent.Executors

class ApiClient(private val context: Context) {
    private val executor = Executors.newSingleThreadExecutor()
    private val main = Handler(Looper.getMainLooper())

    /**
     * Finds only a Laravel server that proves its identity through the
     * dedicated health endpoint. The Wi-Fi scan is bounded to the device's
     * current /24 subnet and tests only the app's expected Laravel paths.
     */
    fun discover(defaultUrl: String, progress: (String) -> Unit, callback: (String?, String?) -> Unit) {
        val directCandidates = linkedSetOf(
            defaultUrl.trimEnd('/'),
            "http://10.0.2.2:8000",
            "http://10.0.2.2/van_service_laravel/public",
            "http://127.0.0.1:8000",
            "http://127.0.0.1/van_service_laravel/public"
        )
        executor.execute {
            post { progress("Checking direct development connections…") }
            directCandidates.firstOrNull(::isApprovedServer)?.let { server ->
                post { callback(server, null) }
                return@execute
            }

            val localAddress = currentWifiIpv4Address()
            if (localAddress == null) {
                post {
                    callback(
                        null,
                        "Connect this device to the same Wi-Fi as the Laravel computer, then tap Try Again."
                    )
                }
                return@execute
            }

            post { progress("Searching the connected Wi-Fi for VanService…") }
            val wifiAddress = localAddress.hostAddress ?: run {
                post { callback(null, "The connected Wi-Fi address could not be read. Tap Try Again.") }
                return@execute
            }
            val prefix = wifiAddress.substringBeforeLast('.')
            val ownHost = wifiAddress.substringAfterLast('.').toIntOrNull()
            val scanner = Executors.newFixedThreadPool(28)
            val completion = ExecutorCompletionService<String?>(scanner)
            val hosts = (1..254).filter { it != ownHost }
            hosts.forEach { host ->
                completion.submit(Callable {
                    val address = "$prefix.$host"
                    listOf(
                        "http://$address:8000",
                        "http://$address/van_service_laravel/public"
                    ).firstOrNull(::isApprovedServer)
                })
            }

            try {
                repeat(hosts.size) {
                    val found = completion.take().get()
                    if (found != null) {
                        scanner.shutdownNow()
                        post { callback(found, null) }
                        return@execute
                    }
                }
            } finally {
                scanner.shutdownNow()
            }

            post {
                callback(
                    null,
                    "No approved VanService server was found on this Wi-Fi. Start Laravel with php artisan serve --host=0.0.0.0 --port=8000 and allow port 8000 through Windows Firewall."
                )
            }
        }
    }

    private fun currentWifiIpv4Address(): Inet4Address? {
        val manager = context.getSystemService(Context.CONNECTIVITY_SERVICE) as ConnectivityManager
        val activeNetwork = manager.activeNetwork ?: return null
        val capabilities = manager.getNetworkCapabilities(activeNetwork) ?: return null
        if (!capabilities.hasTransport(NetworkCapabilities.TRANSPORT_WIFI)) return null
        return manager.getLinkProperties(activeNetwork)
            ?.linkAddresses
            ?.mapNotNull { it.address as? Inet4Address }
            ?.firstOrNull { !it.isLoopbackAddress && it.isSiteLocalAddress }
    }

    private fun isApprovedServer(baseUrl: String): Boolean {
        val result = request("GET", "$baseUrl/api/driver/mobile/health", null, null, 900, 900)
        if (result.code !in 200..299 || result.body == null) return false
        return runCatching { JSONObject(result.body) }.getOrNull()?.let { json ->
            json.optString("service") == "vanservice-driver-api" && json.optInt("version") == 1
        } ?: false
    }

    fun login(serverUrl: String, email: String, password: String, callback: (DriverSession?, String?) -> Unit) {
        val payload = JSONObject().put("email", email).put("password", password).toString()
        call("POST", "$serverUrl/api/driver/mobile/login", null, payload) { body, error ->
            if (body == null) return@call callback(null, error)
            try {
                val json = JSONObject(body)
                val driver = json.getJSONObject("driver")
                val van = json.getJSONObject("van")
                callback(
                    DriverSession(
                        serverUrl = serverUrl,
                        token = json.getString("token"),
                        driverName = driver.getString("name"),
                        driverEmail = driver.getString("email"),
                        vanPlate = van.optString("plate", "Assigned van"),
                        vanModel = van.optString("model", "")
                    ),
                    null
                )
            } catch (exception: Exception) {
                callback(null, "The server returned an unexpected sign-in response.")
            }
        }
    }

    fun activeTrip(session: DriverSession, callback: (ActiveTrip?, String?) -> Unit) {
        call("GET", "${session.serverUrl}/api/driver/mobile/active-trip", session.token, null) { body, error ->
            if (body == null) return@call callback(null, error)
            try {
                val trip = JSONObject(body).optJSONObject("trip") ?: return@call callback(null, null)
                callback(
                    ActiveTrip(
                        id = trip.getLong("id"),
                        routeName = trip.optString("route_name", "Assigned trip"),
                        destinationName = trip.optString("destination_name", "Destination"),
                        tripDate = trip.optString("trip_date", "Scheduled trip"),
                        departureTime = trip.optString("departure_time").takeIf { it.isNotBlank() },
                        destinationLatitude = trip.optionalDouble("destination_latitude"),
                        destinationLongitude = trip.optionalDouble("destination_longitude"),
                        radiusMeters = trip.optInt("arrival_geofence_radius_meters", 150),
                        arrived = !trip.isNull("arrived_at") && trip.optString("arrived_at").isNotBlank(),
                        departed = !trip.isNull("departed_at") && trip.optString("departed_at").isNotBlank(),
                        canTakeOff = trip.optBoolean("can_takeoff", false),
                        requiresConfirmation = trip.optBoolean("requires_confirmation", false)
                    ),
                    null
                )
            } catch (exception: Exception) {
                callback(null, "The active trip response could not be read.")
            }
        }
    }

    fun tripTicket(session: DriverSession, tripId: Long, callback: (TripTicket?, String?) -> Unit) {
        call("GET", "${session.serverUrl}/api/driver/mobile/trip-ticket/$tripId", session.token, null) { body, error ->
            if (body == null) return@call callback(null, error)
            try {
                val ticket = JSONObject(body).getJSONObject("ticket")
                val passengerArray = ticket.optJSONArray("passengers")
                val passengers = buildList {
                    if (passengerArray != null) {
                        for (index in 0 until passengerArray.length()) {
                            val passenger = passengerArray.getJSONObject(index)
                            add(TripTicketPassenger(
                                name = passenger.optString("name", "Passenger"),
                                seats = passenger.optInt("seats", 1),
                                status = passenger.optString("status", "Pending")
                            ))
                        }
                    }
                }
                callback(TripTicket(
                    tripNumber = ticket.optString("trip_number", "Trip ticket"),
                    routeName = ticket.optString("route_name", "Assigned trip"),
                    pickupLocation = ticket.optString("pickup_location", ""),
                    dropoffLocation = ticket.optString("dropoff_location", ""),
                    tripDate = ticket.optString("trip_date", ""),
                    departureTime = ticket.optString("departure_time").takeIf { it.isNotBlank() },
                    driverName = ticket.optString("driver_name", "Driver"),
                    vanPlate = ticket.optString("van_plate", ""),
                    vanModel = ticket.optString("van_model", ""),
                    departedAt = ticket.optString("departed_at").takeIf { it.isNotBlank() },
                    arrivedAt = ticket.optString("arrived_at").takeIf { it.isNotBlank() },
                    distanceKm = ticket.optDouble("distance_km", 0.0),
                    fuelLiters = ticket.optDouble("fuel_liters", 0.0),
                    passengers = passengers
                ), null)
            } catch (exception: Exception) {
                callback(null, "The trip ticket response could not be read.")
            }
        }
    }

    fun uploadLocation(session: DriverSession, reading: LocationReading, callback: (String?) -> Unit) {
        val payload = JSONObject()
            .put("latitude", reading.latitude)
            .put("longitude", reading.longitude)
            .putIfNotNull("accuracy", reading.accuracyMeters)
            .putIfNotNull("speed_kmh", reading.speedKmh)
            .putIfNotNull("heading", reading.heading)
            .toString()
        call("POST", "${session.serverUrl}/api/driver/mobile/location", session.token, payload) { _, error -> callback(error) }
    }

    fun takeOff(session: DriverSession, tripId: Long, callback: (Boolean, String?) -> Unit) {
        call("POST", "${session.serverUrl}/api/driver/mobile/takeoff", session.token, JSONObject().put("trip_id", tripId).toString()) { body, error ->
            callback(body != null, error)
        }
    }

    fun markArrived(session: DriverSession, tripId: Long, callback: (Boolean, String?) -> Unit) {
        val payload = JSONObject().put("trip_id", tripId).toString()
        call("POST", "${session.serverUrl}/api/driver/mobile/arrive", session.token, payload) { body, error ->
            callback(body != null, error)
        }
    }

    fun confirmTrip(session: DriverSession, tripId: Long, callback: (Boolean, String?) -> Unit) {
        val payload = JSONObject().put("trip_id", tripId).toString()
        call("POST", "${session.serverUrl}/api/driver/mobile/confirm-trip", session.token, payload) { body, error ->
            callback(body != null, error)
        }
    }

    fun pendingBookings(session: DriverSession, callback: (List<DriverBooking>, String?) -> Unit) {
        call("GET", "${session.serverUrl}/api/driver/mobile/bookings", session.token, null) { body, error ->
            if (body == null) return@call callback(emptyList(), error)
            try {
                val bookingArray = JSONObject(body).optJSONArray("bookings")
                val bookings = buildList {
                    if (bookingArray != null) {
                        for (index in 0 until bookingArray.length()) {
                            val booking = bookingArray.getJSONObject(index)
                            add(
                                DriverBooking(
                                    id = booking.getLong("id"),
                                    passengerName = booking.optString("passenger_name", "Passenger"),
                                    tripLabel = booking.optString("trip_label", "Assigned trip"),
                                    bookingDate = booking.optString("booking_date", ""),
                                    bookingTime = booking.optString("booking_time").takeIf { it.isNotBlank() },
                                    seatsRequested = booking.optInt("seats_requested", 1),
                                    purpose = booking.optString("purpose", "No purpose supplied")
                                )
                            )
                        }
                    }
                }
                callback(bookings, null)
            } catch (exception: Exception) {
                callback(emptyList(), "The booking response could not be read.")
            }
        }
    }

    fun bookingDecision(session: DriverSession, bookingId: Long, accept: Boolean, callback: (Boolean, String?) -> Unit) {
        val payload = JSONObject().put("action", if (accept) "accept" else "decline").toString()
        call("POST", "${session.serverUrl}/api/driver/mobile/bookings/$bookingId/decision", session.token, payload) { body, error ->
            callback(body != null, error)
        }
    }

    fun arrivalCheck(session: DriverSession, tripId: Long, reading: LocationReading, callback: (Boolean, Int?, String?) -> Unit) {
        val payload = JSONObject()
            .put("trip_id", tripId)
            .put("latitude", reading.latitude)
            .put("longitude", reading.longitude)
            .putIfNotNull("accuracy", reading.accuracyMeters)
            .toString()
        call("POST", "${session.serverUrl}/api/driver/mobile/arrival-check", session.token, payload) { body, error ->
            if (body == null) return@call callback(false, null, error)
            val json = runCatching { JSONObject(body) }.getOrNull()
            callback(json?.optBoolean("arrived", false) ?: false, json?.optInt("distance_meters"), null)
        }
    }

    fun stopSharing(session: DriverSession, callback: (() -> Unit)? = null) {
        call("POST", "${session.serverUrl}/api/driver/mobile/stop-sharing", session.token, "{}") { _, _ -> callback?.invoke() }
    }

    fun logout(session: DriverSession, callback: (() -> Unit)? = null) {
        call("POST", "${session.serverUrl}/api/driver/mobile/logout", session.token, "{}") { _, _ -> callback?.invoke() }
    }

    private fun call(method: String, url: String, token: String?, payload: String?, callback: (String?, String?) -> Unit) {
        executor.execute {
            val result = request(method, url, token, payload)
            post {
                if (result.code in 200..299 && result.body != null) callback(result.body, null)
                else callback(null, result.message ?: "Unable to reach the VanService server.")
            }
        }
    }

    private fun request(
        method: String,
        address: String,
        token: String?,
        payload: String?,
        connectionTimeout: Int = 5_000,
        responseTimeout: Int = 8_000
    ): HttpResult {
        return try {
            val connection = (URL(address).openConnection() as HttpURLConnection).apply {
                requestMethod = method
                connectTimeout = connectionTimeout
                readTimeout = responseTimeout
                setRequestProperty("Accept", "application/json")
                setRequestProperty("Content-Type", "application/json")
                if (token != null) setRequestProperty("Authorization", "Bearer $token")
                if (payload != null) {
                    doOutput = true
                    OutputStreamWriter(outputStream, Charsets.UTF_8).use { it.write(payload) }
                }
            }
            val code = connection.responseCode
            val input = if (code in 200..299) connection.inputStream else connection.errorStream
            val text = input?.bufferedReader()?.use(BufferedReader::readText)
            val message = text?.let { runCatching { JSONObject(it).optString("message") }.getOrNull() }?.takeIf { it.isNotBlank() }
            connection.disconnect()
            HttpResult(code, text, message)
        } catch (exception: Exception) {
            HttpResult(0, null, "Unable to connect to the VanService server.")
        }
    }

    private fun post(action: () -> Unit) = main.post(action)

    private data class HttpResult(val code: Int, val body: String?, val message: String?)
}

private fun JSONObject.optionalDouble(key: String): Double? {
    return if (isNull(key)) null else optDouble(key).takeUnless { it.isNaN() }
}

private fun JSONObject.putIfNotNull(key: String, value: Any?): JSONObject {
    if (value != null) put(key, value)
    return this
}

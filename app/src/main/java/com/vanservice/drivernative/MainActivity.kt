package com.vanservice.drivernative

import android.Manifest
import android.content.pm.PackageManager
import android.graphics.Color
import android.os.Bundle
import android.view.View
import android.widget.LinearLayout
import android.widget.TextView
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import androidx.core.content.ContextCompat
import com.google.android.material.button.MaterialButton
import com.google.android.material.textfield.TextInputEditText
import kotlin.math.atan2
import kotlin.math.cos
import kotlin.math.sin
import kotlin.math.sqrt

class MainActivity : AppCompatActivity() {
    private lateinit var api: ApiClient
    private lateinit var sessionStore: SessionStore
    private lateinit var locationTracker: LocationTracker

    private lateinit var signInCard: View
    private lateinit var dashboardGroup: View
    private lateinit var connectionTitle: TextView
    private lateinit var connectionDetail: TextView
    private lateinit var retryButton: MaterialButton
    private lateinit var emailInput: TextInputEditText
    private lateinit var passwordInput: TextInputEditText
    private lateinit var signInButton: MaterialButton
    private lateinit var driverNameText: TextView
    private lateinit var vanText: TextView
    private lateinit var sharingText: TextView
    private lateinit var locationText: TextView
    private lateinit var sharingButton: MaterialButton
    private lateinit var tripRouteText: TextView
    private lateinit var geofenceText: TextView
    private lateinit var confirmTripButton: MaterialButton
    private lateinit var takeoffButton: MaterialButton
    private lateinit var arriveButton: MaterialButton
    private lateinit var tripTicketButton: MaterialButton
    private lateinit var bookingEmptyText: TextView
    private lateinit var bookingList: LinearLayout
    private lateinit var signOutButton: MaterialButton

    private var discoveredServerUrl: String? = null
    private var session: DriverSession? = null
    private var activeTrip: ActiveTrip? = null
    private var isSharing = false
    private var serverVerified = false
    private var latestReading: LocationReading? = null

    private val permissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { result ->
        val granted = result[Manifest.permission.ACCESS_FINE_LOCATION] == true ||
            result[Manifest.permission.ACCESS_COARSE_LOCATION] == true
        if (granted) startSharing() else showMessage("Precise location permission is needed to share your live van location.")
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_main)
        api = ApiClient(applicationContext)
        sessionStore = SessionStore(this)
        locationTracker = LocationTracker(this)
        bindViews()
        bindActions()
        restoreOrDiscover()
    }

    override fun onPause() {
        super.onPause()
        // Privacy rule: no location sharing or listener survives when the app
        // is no longer visible in the foreground.
        if (isSharing) stopSharing(notifyServer = true)
    }

    override fun onDestroy() {
        locationTracker.stop()
        super.onDestroy()
    }

    private fun bindViews() {
        signInCard = findViewById(R.id.signInCard)
        dashboardGroup = findViewById(R.id.dashboardGroup)
        connectionTitle = findViewById(R.id.connectionTitle)
        connectionDetail = findViewById(R.id.connectionDetail)
        retryButton = findViewById(R.id.retryButton)
        emailInput = findViewById(R.id.emailInput)
        passwordInput = findViewById(R.id.passwordInput)
        signInButton = findViewById(R.id.signInButton)
        driverNameText = findViewById(R.id.driverNameText)
        vanText = findViewById(R.id.vanText)
        sharingText = findViewById(R.id.sharingText)
        locationText = findViewById(R.id.locationText)
        sharingButton = findViewById(R.id.sharingButton)
        tripRouteText = findViewById(R.id.tripRouteText)
        geofenceText = findViewById(R.id.geofenceText)
        confirmTripButton = findViewById(R.id.confirmTripButton)
        takeoffButton = findViewById(R.id.takeoffButton)
        arriveButton = findViewById(R.id.arriveButton)
        tripTicketButton = findViewById(R.id.tripTicketButton)
        bookingEmptyText = findViewById(R.id.bookingEmptyText)
        bookingList = findViewById(R.id.bookingList)
        signOutButton = findViewById(R.id.signOutButton)
    }

    private fun bindActions() {
        retryButton.setOnClickListener { discoverServer() }
        signInButton.setOnClickListener { signIn() }
        sharingButton.setOnClickListener {
            if (isSharing) stopSharing(notifyServer = true) else requestLocationThenStart()
        }
        confirmTripButton.setOnClickListener { confirmAssignedTrip() }
        takeoffButton.setOnClickListener { reportTakeOff() }
        arriveButton.setOnClickListener { confirmManualArrival() }
        tripTicketButton.setOnClickListener { showTripTicket() }
        signOutButton.setOnClickListener { signOut() }
    }

    private fun restoreOrDiscover() {
        session = sessionStore.load()
        if (session == null) {
            showLogin()
            discoverServer()
        } else {
            showDashboard(session!!)
            verifySavedSessionServer(session!!)
        }
    }

    private fun discoverServer() {
        setConnection("Checking VanService server", "Looking for the approved Laravel server.", false)
        signInButton.isEnabled = false
        api.discover(BuildConfig.DEFAULT_LARAVEL_URL, { progress ->
            setConnection("Checking VanService server", progress, false)
        }) { url, error ->
            discoveredServerUrl = url
            if (url == null) {
                serverVerified = false
                setConnection("VanService server not connected", error ?: "Start Laravel, then try again.", true)
            } else {
                serverVerified = true
                setConnection("VanService server connected", "Connected automatically to $url", false)
                signInButton.isEnabled = true
            }
        }
    }

    /**
     * A previously signed-in driver may have a session saved from a USB or
     * localhost test. Always replace it with the currently verified Wi-Fi
     * server before permitting new location uploads.
     */
    private fun verifySavedSessionServer(savedSession: DriverSession) {
        serverVerified = false
        sharingButton.isEnabled = false
        sharingText.text = "Finding VanService server on Wi-Fi…"
        sharingText.setTextColor(ContextCompat.getColor(this, R.color.warning_amber))
        locationText.text = "Location sharing waits for a verified server"

        api.discover(BuildConfig.DEFAULT_LARAVEL_URL, { progress ->
            sharingText.text = progress
        }) { url, error ->
            if (url == null) {
                sharingText.text = "VanService server not connected"
                locationText.text = error ?: "Connect to the system Wi-Fi and tap Sign Out to retry."
                showMessage("Location sharing is paused until the VanService server is found.")
                return@discover
            }

            val verifiedSession = savedSession.copy(serverUrl = url)
            session = verifiedSession
            sessionStore.save(verifiedSession)
            serverVerified = true
            sharingButton.isEnabled = true
            updateSharingUi()
            refreshTrip()
            refreshBookings()
        }
    }

    private fun signIn() {
        val server = discoveredServerUrl ?: return showMessage("Wait for a VanService server connection before signing in.")
        val email = emailInput.text?.toString()?.trim().orEmpty()
        val password = passwordInput.text?.toString().orEmpty()
        if (email.isBlank() || password.isBlank()) return showMessage("Enter your driver email and password.")

        signInButton.isEnabled = false
        signInButton.text = "Signing in…"
        api.login(server, email, password) { newSession, error ->
            signInButton.text = "Sign In"
            signInButton.isEnabled = discoveredServerUrl != null
            if (newSession == null) return@login showMessage(error ?: "Unable to sign in.")
            session = newSession
            sessionStore.save(newSession)
            passwordInput.setText("")
            showDashboard(newSession)
            refreshTrip()
            refreshBookings()
        }
    }

    private fun showLogin() {
        signInCard.visibility = View.VISIBLE
        dashboardGroup.visibility = View.GONE
    }

    private fun showDashboard(current: DriverSession) {
        signInCard.visibility = View.GONE
        dashboardGroup.visibility = View.VISIBLE
        driverNameText.text = current.driverName
        vanText.text = listOf(current.vanPlate, current.vanModel).filter { it.isNotBlank() }.joinToString(" • ")
        updateSharingUi()
    }

    private fun refreshTrip() {
        val current = session ?: return
        api.activeTrip(current) { trip, error ->
            if (error != null) {
                tripRouteText.text = "Unable to load today’s trip"
                geofenceText.text = error
                takeoffButton.isEnabled = false
                arriveButton.isEnabled = false
                return@activeTrip
            }
            activeTrip = trip
            renderTrip()
        }
    }

    private fun renderTrip() {
        val trip = activeTrip
        if (trip == null) {
            tripRouteText.text = "No current or upcoming trip assigned"
            geofenceText.text = "Your administrator must assign a trip to your van."
            confirmTripButton.visibility = View.GONE
            takeoffButton.isEnabled = false
            arriveButton.isEnabled = false
            tripTicketButton.isEnabled = false
            return
        }

        tripRouteText.text = "${trip.routeName}\n${trip.tripDate}${trip.departureTime?.let { " • $it" }.orEmpty()}\nDestination: ${trip.destinationName}"
        geofenceText.text = when {
            trip.arrived -> "Arrival confirmed. The administrator has been notified."
            trip.requiresConfirmation -> "Awaiting your confirmation before the van can take off."
            trip.destinationLatitude == null || trip.destinationLongitude == null -> "Destination coordinate is not saved yet."
            else -> "Arrival alert radius: ${trip.radiusMeters} m"
        }
        confirmTripButton.visibility = if (trip.requiresConfirmation) View.VISIBLE else View.GONE
        confirmTripButton.isEnabled = trip.requiresConfirmation
        takeoffButton.isEnabled = trip.canTakeOff && !trip.departed && !trip.requiresConfirmation
        takeoffButton.text = if (trip.departed) "Van Takeoff Reported" else "Mark Van Take Off"
        arriveButton.isEnabled = trip.departed && !trip.arrived && !trip.requiresConfirmation
        arriveButton.text = if (trip.arrived) "Van Arrival Reported" else "Mark Van Arrived"
        tripTicketButton.isEnabled = true
    }

    private fun confirmAssignedTrip() {
        val current = session ?: return
        val trip = activeTrip ?: return showMessage("There is no assigned trip to confirm.")
        if (!trip.requiresConfirmation) return
        confirmTripButton.isEnabled = false
        api.confirmTrip(current, trip.id) { success, error ->
            if (!success) {
                confirmTripButton.isEnabled = true
                return@confirmTrip showMessage(error ?: "Unable to confirm this trip.")
            }
            showMessage("Trip confirmed. You can now report van takeoff.")
            refreshTrip()
        }
    }

    private fun refreshBookings() {
        val current = session ?: return
        bookingEmptyText.visibility = View.VISIBLE
        bookingEmptyText.text = "Loading booking requests…"
        bookingList.removeAllViews()
        api.pendingBookings(current) { bookings, error ->
            if (error != null) {
                bookingEmptyText.text = "Unable to load booking requests: $error"
                return@pendingBookings
            }
            renderBookings(bookings)
        }
    }

    private fun renderBookings(bookings: List<DriverBooking>) {
        bookingList.removeAllViews()
        if (bookings.isEmpty()) {
            bookingEmptyText.visibility = View.VISIBLE
            bookingEmptyText.text = "No pending booking requests for your van."
            return
        }

        bookingEmptyText.visibility = View.GONE
        bookings.forEach { booking ->
            val container = LinearLayout(this).apply {
                orientation = LinearLayout.VERTICAL
                setPadding(0, 18, 0, 0)
            }
            val summary = TextView(this).apply {
                text = "${booking.passengerName} • ${booking.seatsRequested} seat(s)\n${booking.tripLabel}\n${booking.bookingDate}${booking.bookingTime?.let { " • $it" }.orEmpty()}\n${booking.purpose}"
                setTextColor(ContextCompat.getColor(this@MainActivity, R.color.ink))
                textSize = 14f
            }
            val actions = LinearLayout(this).apply {
                orientation = LinearLayout.HORIZONTAL
                setPadding(0, 10, 0, 0)
            }
            val accept = MaterialButton(this).apply {
                text = "Accept"
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f).apply { marginEnd = 8 }
                setOnClickListener { decideBooking(booking, true) }
            }
            val decline = MaterialButton(this).apply {
                text = "Decline"
                layoutParams = LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f)
                setTextColor(ContextCompat.getColor(this@MainActivity, R.color.error_red))
                setOnClickListener { decideBooking(booking, false) }
            }
            actions.addView(accept)
            actions.addView(decline)
            container.addView(summary)
            container.addView(actions)
            bookingList.addView(container)
        }
    }

    private fun decideBooking(booking: DriverBooking, accept: Boolean) {
        val current = session ?: return
        api.bookingDecision(current, booking.id, accept) { success, error ->
            if (!success) return@bookingDecision showMessage(error ?: "Unable to update the booking.")
            showMessage(if (accept) "Booking accepted." else "Booking declined.")
            refreshBookings()
        }
    }

    private fun requestLocationThenStart() {
        if (locationTracker.hasLocationPermission()) {
            startSharing()
        } else {
            permissionLauncher.launch(arrayOf(
                Manifest.permission.ACCESS_FINE_LOCATION,
                Manifest.permission.ACCESS_COARSE_LOCATION
            ))
        }
    }

    private fun startSharing() {
        if (session == null) return
        if (!serverVerified) {
            showMessage("Wait until the VanService server is connected before sharing location.")
            return
        }
        isSharing = true
        updateSharingUi()
        locationTracker.start { reading -> onLocationReading(reading) }
        showMessage("Live location sharing started. It will stop when this app leaves the foreground.")
    }

    private fun stopSharing(notifyServer: Boolean) {
        val current = session
        locationTracker.stop()
        isSharing = false
        updateSharingUi()
        if (notifyServer && current != null) api.stopSharing(current)
    }

    private fun onLocationReading(reading: LocationReading) {
        latestReading = reading
        val accuracy = reading.accuracyMeters?.let { " ±${it.toInt()} m" }.orEmpty()
        locationText.text = "Live location uploaded$accuracy"
        val current = session ?: return
        api.uploadLocation(current, reading) { error ->
            if (error != null) locationText.text = "Location not uploaded: $error"
        }

        val trip = activeTrip ?: return
        if (trip.arrived || trip.destinationLatitude == null || trip.destinationLongitude == null) return
        val distance = distanceInMeters(reading.latitude, reading.longitude, trip.destinationLatitude, trip.destinationLongitude)
        geofenceText.text = "${distance.toInt()} m from ${trip.destinationName} • alert at ${trip.radiusMeters} m"
        if (distance <= trip.radiusMeters) {
            api.arrivalCheck(current, trip.id, reading) { arrived, serverDistance, error ->
                if (error != null) {
                    geofenceText.text = "Arrival check unavailable: $error"
                } else if (arrived) {
                    trip.arrived = true
                    geofenceText.text = "Arrival confirmed${serverDistance?.let { " at ${it} m" }.orEmpty()}. Admin notified."
                    renderTrip()
                }
            }
        }
    }

    private fun reportTakeOff() {
        val current = session ?: return
        val trip = activeTrip ?: return showMessage("There is no active trip to report.")
        takeoffButton.isEnabled = false
        api.takeOff(current, trip.id) { success, error ->
            if (!success) {
                takeoffButton.isEnabled = trip.canTakeOff
                return@takeOff showMessage(error ?: "Unable to report takeoff.")
            }
            trip.departed = true
            trip.canTakeOff = false
            renderTrip()
            showMessage("Van takeoff reported. The administrator has been notified.")
        }
    }

    private fun confirmManualArrival() {
        val current = session ?: return
        val trip = activeTrip ?: return showMessage("There is no active trip to report.")
        if (!trip.departed) return showMessage("Record van takeoff before reporting arrival.")
        if (trip.arrived) return

        AlertDialog.Builder(this)
            .setTitle("Confirm van arrival")
            .setMessage("Confirm that the van has arrived at ${trip.destinationName}. The administrator will be notified.")
            .setNegativeButton("Cancel", null)
            .setPositiveButton("Mark Arrived") { _, _ ->
                arriveButton.isEnabled = false
                api.markArrived(current, trip.id) { success, error ->
                    if (!success) {
                        arriveButton.isEnabled = trip.departed && !trip.arrived
                        return@markArrived showMessage(error ?: "Unable to report van arrival.")
                    }
                    trip.arrived = true
                    renderTrip()
                    showMessage("Van arrival reported. The administrator has been notified.")
                }
            }
            .show()
    }

    private fun showTripTicket() {
        val current = session ?: return showMessage("Sign in to view the trip ticket.")
        val trip = activeTrip ?: return showMessage("There is no assigned trip ticket to display.")
        tripTicketButton.isEnabled = false
        api.tripTicket(current, trip.id) { ticket, error ->
            tripTicketButton.isEnabled = true
            if (ticket == null) return@tripTicket showMessage(error ?: "Unable to load the trip ticket.")

            val passengers = if (ticket.passengers.isEmpty()) {
                "No passenger bookings yet."
            } else {
                ticket.passengers.joinToString("\n") { passenger ->
                    "• ${passenger.name} — ${passenger.seats} seat(s) [${passenger.status}]"
                }
            }
            val message = buildString {
                append("${ticket.pickupLocation.ifBlank { "Lemery Campus" }} → ${ticket.dropoffLocation.ifBlank { ticket.routeName }}\n")
                append("Date: ${ticket.tripDate}\nDeparture: ${ticket.departureTime ?: "To be confirmed"}\n")
                append("Driver: ${ticket.driverName}\nVehicle: ${ticket.vanModel} ${ticket.vanPlate}\n")
                append("Distance: ${ticket.distanceKm} km\nFuel recorded: ${ticket.fuelLiters} L\n\n")
                append("AUTHORIZED PASSENGERS\n$passengers\n\n")
                append("Takeoff: ${ticket.departedAt ?: "Not recorded"}\nArrival: ${ticket.arrivedAt ?: "Not recorded"}")
            }
            AlertDialog.Builder(this)
                .setTitle("NISU Driver’s Trip Ticket\n${ticket.tripNumber}")
                .setMessage(message)
                .setPositiveButton("Close", null)
                .show()
        }
    }

    private fun signOut() {
        val current = session ?: return
        stopSharing(notifyServer = true)
        api.logout(current) {
            sessionStore.clear()
            session = null
            activeTrip = null
            showLogin()
            discoverServer()
        }
    }

    private fun updateSharingUi() {
        if (isSharing) {
            sharingText.text = "Live location sharing is active"
            sharingText.setTextColor(ContextCompat.getColor(this, R.color.live_green))
            sharingButton.text = "Stop Sharing"
        } else {
            sharingText.text = "Location sharing is off"
            sharingText.setTextColor(ContextCompat.getColor(this, R.color.warning_amber))
            sharingButton.text = "Start Sharing"
            locationText.text = "No current location uploaded"
        }
    }

    private fun setConnection(title: String, detail: String, retryVisible: Boolean) {
        connectionTitle.text = title
        connectionDetail.text = detail
        retryButton.visibility = if (retryVisible) View.VISIBLE else View.GONE
    }

    private fun showMessage(message: String) = Toast.makeText(this, message, Toast.LENGTH_LONG).show()

    private fun distanceInMeters(latA: Double, lonA: Double, latB: Double, lonB: Double): Double {
        val earthRadius = 6_371_000.0
        val latitudeDelta = Math.toRadians(latB - latA)
        val longitudeDelta = Math.toRadians(lonB - lonA)
        val a = sin(latitudeDelta / 2) * sin(latitudeDelta / 2) +
            cos(Math.toRadians(latA)) * cos(Math.toRadians(latB)) *
            sin(longitudeDelta / 2) * sin(longitudeDelta / 2)
        return earthRadius * 2 * atan2(sqrt(a), sqrt(1 - a))
    }
}

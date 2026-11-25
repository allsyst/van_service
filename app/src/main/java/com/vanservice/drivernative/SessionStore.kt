package com.vanservice.drivernative

import android.content.Context
import androidx.security.crypto.EncryptedSharedPreferences
import androidx.security.crypto.MasterKey

class SessionStore(context: Context) {
    private val key = MasterKey.Builder(context)
        .setKeyScheme(MasterKey.KeyScheme.AES256_GCM)
        .build()

    private val preferences = EncryptedSharedPreferences.create(
        context,
        "vanservice_driver_session",
        key,
        EncryptedSharedPreferences.PrefKeyEncryptionScheme.AES256_SIV,
        EncryptedSharedPreferences.PrefValueEncryptionScheme.AES256_GCM
    )

    fun save(session: DriverSession) {
        preferences.edit()
            .putString("server_url", session.serverUrl)
            .putString("token", session.token)
            .putString("driver_name", session.driverName)
            .putString("driver_email", session.driverEmail)
            .putString("van_plate", session.vanPlate)
            .putString("van_model", session.vanModel)
            .apply()
    }

    fun load(): DriverSession? {
        val token = preferences.getString("token", null) ?: return null
        val serverUrl = preferences.getString("server_url", null) ?: return null
        return DriverSession(
            serverUrl = serverUrl,
            token = token,
            driverName = preferences.getString("driver_name", "Driver") ?: "Driver",
            driverEmail = preferences.getString("driver_email", "") ?: "",
            vanPlate = preferences.getString("van_plate", "Unassigned van") ?: "Unassigned van",
            vanModel = preferences.getString("van_model", "") ?: ""
        )
    }

    fun clear() = preferences.edit().clear().apply()
}

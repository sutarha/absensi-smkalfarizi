package com.smkalfarizi.guru

import android.app.Application
import android.app.NotificationChannel
import android.app.NotificationManager
import com.onesignal.OneSignal
import com.onesignal.debug.LogLevel
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

/**
 * GuruApp — Application class untuk SMK Al-Farizi (WebView Edition)
 */
class GuruApp : Application() {

    companion object {
        const val CHANNEL_ID = "pengumuman_channel"
        const val CHANNEL_NAME = "Pengumuman & Notifikasi SMK Al-Farizi"
        
        // OneSignal App ID SMK Al-Farizi
        const val ONESIGNAL_APP_ID = "70c61e2a-caba-49bb-a9d4-97776600a225"
    }

    override fun onCreate() {
        super.onCreate()
        createNotificationChannel()
        initOneSignal()
    }

    private fun initOneSignal() {
        // Logging untuk debugging
        OneSignal.Debug.logLevel = LogLevel.VERBOSE

        // Inisialisasi OneSignal
        OneSignal.initWithContext(this, ONESIGNAL_APP_ID)

        // Set tag role sebagai guru agar filter notifikasi berjalan akurat
        OneSignal.User.addTag("role", "guru")

        // Minta izin notifikasi OneSignal di coroutine
        CoroutineScope(Dispatchers.IO).launch {
            OneSignal.Notifications.requestPermission(true)
        }
    }

    private fun createNotificationChannel() {
        val channel = NotificationChannel(
            CHANNEL_ID,
            CHANNEL_NAME,
            NotificationManager.IMPORTANCE_HIGH
        ).apply {
            description = "Notifikasi pengumuman dan absensi dari sekolah"
            enableVibration(true)
            enableLights(true)
        }

        val manager = getSystemService(NotificationManager::class.java)
        manager?.createNotificationChannel(channel)
    }
}

package com.smkalfarizi.guru

import android.Manifest
import android.annotation.SuppressLint
import android.content.Context
import android.content.pm.PackageManager
import android.webkit.JavascriptInterface
import android.widget.Toast
import androidx.core.content.ContextCompat
import com.google.android.gms.location.LocationServices
import com.google.android.gms.location.Priority
import com.google.android.gms.tasks.CancellationTokenSource

/**
 * GpsJsBridge — Jembatan JavaScript ↔ Android untuk mengambil koordinat GPS
 *
 * Cara pakai di PHP/JavaScript web:
 *
 *   // Cek apakah bridge tersedia (berjalan di dalam APK Android)
 *   if (window.AndroidGps) {
 *       window.AndroidGps.requestLocation();
 *   }
 *
 *   // Fungsi callback dipanggil oleh Android setelah dapat koordinat:
 *   function onGpsResult(lat, lng, accuracy) {
 *       console.log("Lat:", lat, "Lng:", lng);
 *   }
 *
 *   // Fungsi callback jika GPS gagal:
 *   function onGpsError(message) {
 *       console.error("GPS Error:", message);
 *   }
 */
class GpsJsBridge(
    private val context: Context,
    private val webView: android.webkit.WebView
) {

    private val fusedLocationClient = LocationServices.getFusedLocationProviderClient(context)

    /**
     * Dipanggil dari JavaScript: window.AndroidGps.requestLocation()
     * Akan memanggil kembali onGpsResult(lat, lng, accuracy) di JS
     */
    @JavascriptInterface
    fun requestLocation() {
        val hasPermission = ContextCompat.checkSelfPermission(
            context,
            Manifest.permission.ACCESS_FINE_LOCATION
        ) == PackageManager.PERMISSION_GRANTED

        if (!hasPermission) {
            invokeJsError("Izin GPS belum diberikan. Harap izinkan akses lokasi di pengaturan aplikasi.")
            return
        }

        val cts = CancellationTokenSource()
        fusedLocationClient
            .getCurrentLocation(Priority.PRIORITY_HIGH_ACCURACY, cts.token)
            .addOnSuccessListener { location ->
                if (location != null) {
                    val lat = location.latitude
                    val lng = location.longitude
                    val acc = location.accuracy
                    invokeJsCallback(lat, lng, acc)
                } else {
                    // Fallback: coba last known location
                    getLastKnownLocation()
                }
            }
            .addOnFailureListener { e ->
                invokeJsError("GPS gagal: ${e.localizedMessage}")
            }
    }

    /**
     * Dipanggil dari JavaScript: window.AndroidGps.isAvailable()
     * Returns "true" jika berjalan di dalam APK Android
     */
    @JavascriptInterface
    fun isAvailable(): String = "true"

    /**
     * Dipanggil dari JavaScript: window.AndroidGps.hasPermission()
     * Returns "true" jika izin GPS sudah diberikan
     */
    @JavascriptInterface
    fun hasPermission(): String {
        val granted = ContextCompat.checkSelfPermission(
            context,
            Manifest.permission.ACCESS_FINE_LOCATION
        ) == PackageManager.PERMISSION_GRANTED
        return if (granted) "true" else "false"
    }

    /**
     * Dipanggil dari JavaScript: window.AndroidGps.showToast("pesan")
     * Menampilkan Toast Android dari dalam web
     */
    @JavascriptInterface
    fun showToast(message: String) {
        (context as? android.app.Activity)?.runOnUiThread {
            Toast.makeText(context, message, Toast.LENGTH_SHORT).show()
        }
    }

    @SuppressLint("MissingPermission")
    private fun getLastKnownLocation() {
        fusedLocationClient.lastLocation
            .addOnSuccessListener { location ->
                if (location != null) {
                    invokeJsCallback(location.latitude, location.longitude, location.accuracy)
                } else {
                    invokeJsError("Koordinat GPS tidak tersedia. Pastikan GPS aktif dan coba lagi.")
                }
            }
            .addOnFailureListener { e ->
                invokeJsError("Gagal mendapatkan lokasi terakhir: ${e.localizedMessage}")
            }
    }

    private fun invokeJsCallback(lat: Double, lng: Double, accuracy: Float) {
        val js = "javascript:if(typeof onGpsResult==='function'){onGpsResult($lat,$lng,$accuracy);}"
        (context as? android.app.Activity)?.runOnUiThread {
            webView.loadUrl(js)
        }
    }

    private fun invokeJsError(message: String) {
        val safe = message.replace("'", "\\'")
        val js = "javascript:if(typeof onGpsError==='function'){onGpsError('$safe');}"
        (context as? android.app.Activity)?.runOnUiThread {
            webView.loadUrl(js)
        }
    }
}

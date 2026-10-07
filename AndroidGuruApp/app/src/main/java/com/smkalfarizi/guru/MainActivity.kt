package com.smkalfarizi.guru

import android.Manifest
import android.annotation.SuppressLint
import android.app.Activity
import android.content.Intent
import android.net.ConnectivityManager
import android.net.NetworkCapabilities
import android.net.Uri
import android.net.http.SslError
import android.os.Bundle
import android.view.KeyEvent
import android.webkit.*
import android.widget.Button
import android.widget.LinearLayout
import android.widget.TextView
import androidx.activity.result.contract.ActivityResultContracts
import androidx.appcompat.app.AppCompatActivity
import com.smkalfarizi.guru.databinding.ActivityMainBinding

/**
 * MainActivity — WebView utama aplikasi Guru SMK Al-Farizi
 *
 * Fitur:
 * - Load web PHP via WebView penuh layar
 * - GPS JavaScript Bridge (window.AndroidGps)
 * - Handling tombol Back (navigasi dalam WebView)
 * - Offline fallback page
 * - Permission GPS runtime request
 */
class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var webView: WebView
    private lateinit var gpsBridge: GpsJsBridge

    private val baseUrl: String by lazy { BuildConfig.WEB_BASE_URL }
    private val baseHost: String? by lazy { try { Uri.parse(baseUrl).host } catch (e: Exception) { null } }
    private val prefs by lazy { getSharedPreferences("app_prefs", MODE_PRIVATE) }

    // Permission launcher untuk GPS & Notifikasi
    private val locationPermissionLauncher = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { permissions ->
        val gpsGranted = permissions[Manifest.permission.ACCESS_FINE_LOCATION] == true
        val notifGranted = if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.TIRAMISU) {
            permissions[Manifest.permission.POST_NOTIFICATIONS] == true
        } else true

        if (gpsGranted) {
            webView.loadUrl("javascript:if(typeof onGpsPermissionGranted==='function'){onGpsPermissionGranted();}")
        }
        if (notifGranted) {
            webView.loadUrl("javascript:if(typeof onNotificationPermissionGranted==='function'){onNotificationPermissionGranted();}")
        }
    }

    @SuppressLint("SetJavaScriptEnabled", "JavascriptInterface")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        webView = binding.webView
        gpsBridge = GpsJsBridge(this, webView)

        setupWebView()
        requestAppPermissions()
        handleNotificationIntent(intent)
        checkConnectionAndLoad()

        // Tombol retry di halaman offline
        binding.btnRetry.setOnClickListener {
            checkConnectionAndLoad()
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    private fun setupWebView() {
        val settings = webView.settings

        // JavaScript wajib aktif
        settings.javaScriptEnabled = true

        // Mendukung localStorage / sessionStorage
        settings.domStorageEnabled = true

        // Mendukung database lokal
        settings.databaseEnabled = true

        // Zoom bisa di-pinch
        settings.builtInZoomControls = false
        settings.displayZoomControls = false
        settings.setSupportZoom(true)

        // Viewport responsif (penting agar tampilan web mobile-friendly)
        settings.useWideViewPort = true
        settings.loadWithOverviewMode = true

        // Cache — pakai cache jika tidak ada koneksi
        settings.cacheMode = WebSettings.LOAD_DEFAULT

        // Izinkan mixed content (HTTP dalam HTTPS) untuk development
        settings.mixedContentMode = WebSettings.MIXED_CONTENT_ALWAYS_ALLOW

        // User-Agent: tambahkan identifier app
        settings.userAgentString = "${settings.userAgentString} SMKAlFarizi-GuruApp/1.0"

        // CookieManager setup
        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        cookieManager.setAcceptThirdPartyCookies(webView, true)

        // Inject JavaScript Bridge untuk GPS
        webView.addJavascriptInterface(gpsBridge, "AndroidGps")

        // Inject JavaScript Bridge untuk permission request (GPS & Notifikasi)
        webView.addJavascriptInterface(object {
            @JavascriptInterface
            fun requestPermissions() {
                runOnUiThread {
                    requestAppPermissions()
                }
            }
            
            // Kompatibilitas mundur dengan script GPS lama
            @JavascriptInterface
            fun requestGpsPermission() {
                requestPermissions()
            }
        }, "AndroidPermission")

        // Inject JavaScript Bridge untuk Role Tagging OneSignal
        webView.addJavascriptInterface(object {
            @JavascriptInterface
            fun setRoleTag(role: String) {
                com.onesignal.OneSignal.User.addTag("role", role)
            }
        }, "AndroidOneSignal")

        webView.webViewClient = object : WebViewClient() {
            override fun onReceivedError(
                view: WebView?,
                request: WebResourceRequest?,
                error: WebResourceError?
            ) {
                if (request?.isForMainFrame == true) {
                    showOfflinePage()
                }
            }

            override fun onReceivedSslError(
                view: WebView?,
                handler: SslErrorHandler?,
                error: SslError?
            ) {
                // Di production, ganti dengan handler?.cancel()
                // Di debug/development, proceed untuk izinkan self-signed cert
                handler?.proceed()
            }

            override fun shouldOverrideUrlLoading(
                view: WebView?,
                request: WebResourceRequest?
            ): Boolean {
                val url = request?.url?.toString() ?: return false
                val targetHost = try { request.url?.host } catch (e: Exception) { null }

                // Jika domain sama dengan server kita (absensismkalfarizi.my.id / trycloudflare.com), buka tetap di dalam WebView
                return if (targetHost != null && (targetHost == baseHost || targetHost.endsWith("absensismkalfarizi.my.id") || targetHost.endsWith("trycloudflare.com"))) {
                    false // Buka di dalam WebView
                } else if (url.startsWith("http://") || url.startsWith("https://")) {
                    try {
                        startActivity(Intent(Intent.ACTION_VIEW, Uri.parse(url)))
                    } catch (e: Exception) { }
                    true // Buka di browser eksternal
                } else {
                    false
                }
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                binding.progressBar.visibility = android.view.View.GONE
                // Sembunyikan offline page, tampilkan WebView
                showWebView()

                // Simpan URL terakhir agar user (siswa/guru) kembali ke halaman role mereka
                if (!url.isNullOrEmpty() && (url.contains("absensismkalfarizi.my.id") || url.contains("trycloudflare.com"))) {
                    if (url.contains("/logout")) {
                        prefs.edit().remove("last_visited_url").apply()
                    } else if (!url.contains("/login")) {
                        prefs.edit().putString("last_visited_url", url).apply()
                    }
                }

                // Inject helper JS agar web tahu berjalan di Android
                view?.loadUrl(
                    "javascript:(function(){" +
                    "window.isAndroidApp=true;" +
                    "document.documentElement.setAttribute('data-android','true');" +
                    "})()"
                )
            }
        }

        webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                super.onProgressChanged(view, newProgress)
                if (newProgress < 100) {
                    binding.progressBar.visibility = android.view.View.VISIBLE
                } else {
                    binding.progressBar.visibility = android.view.View.GONE
                }
            }
            override fun onPermissionRequest(request: PermissionRequest?) {
                runOnUiThread {
                    request?.grant(request.resources)
                }
            }

            override fun onGeolocationPermissionsShowPrompt(
                origin: String?,
                callback: GeolocationPermissions.Callback?
            ) {
                // Otomatis izinkan geolocation dari web (sudah ditangani bridge)
                callback?.invoke(origin, true, false)
            }

            override fun onConsoleMessage(consoleMessage: ConsoleMessage?): Boolean {
                // Tampilkan console.log di Logcat untuk debugging
                android.util.Log.d(
                    "WebConsole",
                    "${consoleMessage?.message()} -- Line ${consoleMessage?.lineNumber()}"
                )
                return true
            }
        }
    }

    private fun requestAppPermissions() {
        val permissions = mutableListOf(
            Manifest.permission.ACCESS_FINE_LOCATION,
            Manifest.permission.ACCESS_COARSE_LOCATION
        )
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.TIRAMISU) {
            permissions.add(Manifest.permission.POST_NOTIFICATIONS)
        }
        locationPermissionLauncher.launch(permissions.toTypedArray())
    }

    private fun checkConnectionAndLoad() {
        if (isNetworkAvailable()) {
            showWebView()
            if (webView.url == null) {
                val savedUrl = prefs.getString("last_visited_url", null)
                if (!savedUrl.isNullOrEmpty()) {
                    webView.loadUrl(savedUrl)
                } else {
                    webView.loadUrl(baseUrl)
                }
            } else {
                webView.reload()
            }
        } else {
            showOfflinePage()
        }
    }

    override fun onNewIntent(intent: Intent) {
        super.onNewIntent(intent)
        setIntent(intent)
        handleNotificationIntent(intent)
    }

    private fun handleNotificationIntent(intent: Intent?) {
        val targetUrl = intent?.getStringExtra("target_url")
        if (!targetUrl.isNullOrEmpty()) {
            prefs.edit().putString("last_visited_url", targetUrl).apply()
        }
    }

    private fun isNetworkAvailable(): Boolean {
        val cm = getSystemService(CONNECTIVITY_SERVICE) as ConnectivityManager
        val network = cm.activeNetwork ?: return false
        val caps = cm.getNetworkCapabilities(network) ?: return false
        return caps.hasCapability(NetworkCapabilities.NET_CAPABILITY_INTERNET)
    }

    private fun showWebView() {
        binding.webView.visibility = android.view.View.VISIBLE
        binding.layoutOffline.visibility = android.view.View.GONE
    }

    private fun showOfflinePage() {
        binding.webView.visibility = android.view.View.GONE
        binding.layoutOffline.visibility = android.view.View.VISIBLE
    }

    // Tombol Back navigasi dalam WebView (bukan keluar app)
    override fun onKeyDown(keyCode: Int, event: KeyEvent?): Boolean {
        if (keyCode == KeyEvent.KEYCODE_BACK && webView.canGoBack()) {
            webView.goBack()
            return true
        }
        return super.onKeyDown(keyCode, event)
    }

    override fun onResume() {
        super.onResume()
        webView.onResume()
    }

    override fun onPause() {
        super.onPause()
        webView.onPause()
        // Simpan sesi cookie saat pause
        CookieManager.getInstance().flush()
    }

    override fun onDestroy() {
        webView.destroy()
        super.onDestroy()
    }
}

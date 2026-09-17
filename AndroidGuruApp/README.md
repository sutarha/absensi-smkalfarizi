# 📱 Android WebView App — Panduan Build & Deploy

## Struktur Project Android (WebView Edition)

```
AndroidGuruApp/
├── app/
│   ├── build.gradle.kts              ← Konfigurasi build (URL server di sini)
│   ├── src/main/
│   │   ├── AndroidManifest.xml       ← Permission GPS + Internet
│   │   ├── java/com/smkalfarizi/guru/
│   │   │   ├── GuruApp.kt            ← Application class
│   │   │   ├── SplashActivity.kt     ← Layar awal (2 detik)
│   │   │   ├── MainActivity.kt       ← WebView utama + bridge
│   │   │   └── GpsJsBridge.kt        ← JS↔Android GPS interface
│   │   └── res/
│   │       ├── layout/
│   │       │   ├── activity_splash.xml
│   │       │   └── activity_main.xml
│   │       ├── values/
│   │       │   ├── strings.xml
│   │       │   └── themes.xml
│   │       └── xml/
│   │           └── network_security_config.xml
```

---

## ⚙️ Langkah 1 — Ganti URL Server

Buka file [`build.gradle.kts`](app/build.gradle.kts) dan ganti URL server:

```kotlin
// Mode debug (development lokal)
buildConfigField("String", "WEB_BASE_URL", "\"http://192.168.1.4:8080/\"")

// Mode release (production/hosting)
buildConfigField("String", "WEB_BASE_URL", "\"https://domain-anda.com/\"")
```

> **⚠️ Penting:** Ganti `192.168.1.4` dengan IP PC/laptop Anda saat development.
> Emulator Android pakai `10.0.2.2` sebagai alias untuk `localhost`.

---

## ⚙️ Langkah 2 — Build APK (Debug)

```bash
# Di folder AndroidGuruApp:
./gradlew assembleDebug
```

APK tersedia di:
```
app/build/outputs/apk/debug/app-debug.apk
```

---

## ⚙️ Langkah 3 — Build APK (Release/Production)

```bash
./gradlew assembleRelease
```

> Untuk signing, konfigurasi `signingConfigs` di `build.gradle.kts` sebelum release ke Play Store.

---

## 🌐 JavaScript Bridge — Cara Pakai di PHP/Web

### Mendeteksi apakah berjalan di Android App:

```javascript
if (window.AndroidGps && window.AndroidGps.isAvailable() === 'true') {
    console.log('Berjalan di dalam Android App');
} else {
    console.log('Berjalan di Browser biasa');
}

// Atau gunakan:
if (document.documentElement.getAttribute('data-android') === 'true') {
    // di Android
}
```

### Meminta koordinat GPS dari Android:

```javascript
// Fungsi callback — dipanggil Android setelah dapat koordinat
function onGpsResult(lat, lng, accuracy) {
    console.log('Lat:', lat, 'Lng:', lng, 'Akurasi:', accuracy + 'm');
    // kirim ke server via form/fetch
}

// Fungsi callback — dipanggil jika GPS gagal
function onGpsError(message) {
    alert('GPS gagal: ' + message);
}

// Trigger minta GPS
window.AndroidGps.requestLocation();
```

### Menampilkan Toast Android dari web:

```javascript
window.AndroidGps.showToast("Check-in berhasil! ✅");
```

---

## 🔑 Permission GPS

Saat pertama kali dibuka, app akan **otomatis meminta izin GPS** kepada pengguna.  
Jika pengguna menolak, fungsi `onGpsPermissionDenied()` dipanggil di JS.

---

## 📶 Network Security

- **Development:** HTTP (`http://192.168.1.4:8080`) → diizinkan di [`network_security_config.xml`](app/src/main/res/xml/network_security_config.xml)
- **Production:** Wajib HTTPS — saat sudah ada hosting, langsung ganti URL dan HTTP sudah diblokir otomatis

---

## 🚀 Tahap Pengembangan Selanjutnya

| Tahap | Deskripsi | Status |
|-------|-----------|--------|
| ✅ WebView App | App wrapper untuk web PHP | Selesai |
| ✅ GPS Bridge | Ambil GPS dari Android native | Selesai |
| ✅ Offline Page | Halaman jika tidak ada internet | Selesai |
| 🔄 Hosting Deploy | Upload PHP ke hosting/VPS | Pending |
| 📱 Aplikasi Siswa | Android WebView untuk siswa | Fase 2 |
| 🔔 Push Notifikasi | FCM untuk absensi reminder | Opsional |

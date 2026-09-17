<?php
use App\Config\App;

$activeNav = 'geofence';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfigurasi Geofence & Tarif - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 380px;
            border-radius: 1rem;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Modern Admin Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div>
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Konfigurasi GPS Geofencing & Tarif Honor</h1>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan titik tengah sekolah pada peta dan kelola tarif honor serta denda keterlambatan KBM per menit</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" id="btn-get-location" onclick="useCurrentLocation()" 
                        class="inline-flex items-center gap-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs px-4 py-2 rounded-xl border border-indigo-200 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span id="btn-location-text">Ambil Lokasi GPS Perangkat</span>
                </button>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <form action="<?= App::baseUrl('admin/geofence') ?>" method="POST" enctype="multipart/form-data" id="geofence-form">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    
                    <!-- Left Column: Interactive Map & Coordinates (7 cols) -->
                    <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                                <h2 class="font-display font-bold text-base text-slate-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                    <span>Peta Geofence & Titik Pusat Sekolah</span>
                                </h2>
                                <span class="text-[11px] font-semibold text-brand-700 bg-brand-50 px-2.5 py-1 rounded-full border border-brand-100 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Haversine Formula Guard
                                </span>
                            </div>

                            <!-- Peta Leaflet -->
                            <div id="map" class="border border-slate-200 shadow-inner mb-3"></div>

                            <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                                💡 <strong>Cara Mengubah Lokasi GPS:</strong> Klik langsung di peta, atau geser penanda biru ke lokasi sekolah, atau tempel koordinat dari Google Maps di kotak bawah.
                            </p>

                            <!-- Tool Cepat: Tempel Format Google Maps (lat, lng) -->
                            <div class="bg-indigo-50/70 border border-indigo-200/80 rounded-xl p-3 mb-4">
                                <label class="block text-[11px] font-bold text-indigo-900 mb-1 flex items-center justify-between">
                                    <span>📋 Tempel Koordinat dari Google Maps:</span>
                                    <span class="text-[10px] font-normal text-indigo-600">Format: lat, lng</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="text" id="paste_coords" placeholder="Contoh: -6.954117, 108.220889" 
                                           class="flex-1 bg-white border border-indigo-200 rounded-lg px-3 py-2 text-xs font-mono text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:outline-none shadow-sm">
                                    <button type="button" onclick="applyPastedCoords()" 
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-3.5 py-2 rounded-lg shadow-sm transition flex-shrink-0 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Terapkan Titik</span>
                                    </button>
                                </div>
                                <div id="paste-msg" class="text-[10px] text-emerald-700 font-semibold mt-1.5 hidden"></div>
                            </div>
                        </div>

                        <!-- Form Input Koordinat Riil -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-4 border-t border-slate-100">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Latitude Pusat
                                </label>
                                <input type="text" inputmode="decimal" name="latitude_pusat" id="latitude_pusat" 
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" 
                                       value="<?= htmlspecialchars((string)$config['latitude_pusat']) ?>" required>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Contoh: -6.95411742</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Longitude Pusat
                                </label>
                                <input type="text" inputmode="decimal" name="longitude_pusat" id="longitude_pusat" 
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" 
                                       value="<?= htmlspecialchars((string)$config['longitude_pusat']) ?>" required>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Contoh: 108.22088962</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                    Radius Toleransi (Meter)
                                </label>
                                <input type="number" name="radius_meter" id="radius_meter" 
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-brand-700 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" 
                                       value="<?= (int)$config['radius_meter'] ?>" min="10" max="5000" required>
                                <span class="text-[10px] text-slate-400 mt-0.5 block">Default: 50 - 150 meter</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Honor Rates & Identity (5 cols) -->
                    <div class="lg:col-span-5 space-y-6">
                        
                        <!-- Tarif Honor Card -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm space-y-4">
                            <h2 class="font-display font-bold text-base text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Tarif Honor KBM & Denda Telat</span>
                            </h2>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Honor per JP (Rp)</label>
                                    <input type="number" step="100" name="honor_per_jp" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= (float)$config['honor_per_jp'] ?>" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Durasi 1 JP (Menit)</label>
                                    <input type="number" name="durasi_jp_menit" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= (int)$config['durasi_jp_menit'] ?>" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Denda Telat / Menit (Rp)</label>
                                    <input type="number" step="1" name="denda_per_menit" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-rose-600 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= (float)$config['denda_per_menit'] ?>" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Toleransi H- (Menit)</label>
                                    <input type="number" name="toleransi_h_minus" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= (int)$config['toleransi_h_minus'] ?>" required>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        ☀️ Batas Masuk Guru (WIB)
                                    </label>
                                    <input type="time" name="jam_guru_masuk_selesai" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-brand-700 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= htmlspecialchars($config['jam_guru_masuk_selesai'] ?? '06:30') ?>" required>
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Lewat jam ini dihitung terlambat</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                        🏠 Mulai Buka Pulang (WIB)
                                    </label>
                                    <input type="time" name="jam_guru_pulang_mulai" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-emerald-700 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= htmlspecialchars($config['jam_guru_pulang_mulai'] ?? '13:00') ?>" required>
                                    <span class="text-[10px] text-slate-400 mt-0.5 block">Tombol pulang aktif sesudah jam ini</span>
                                </div>
                            </div>

                            <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-3 text-[11px] text-amber-900 leading-relaxed">
                                ⚖️ <strong>Aturan Disiplin:</strong> Masuk sekolah $\le$ <strong><?= substr($config['jam_guru_masuk_selesai'] ?? '06:30', 0, 5) ?> WIB</strong> (tepat waktu). Jam kepulangan guru baru dapat di-tap sesudah pukul <strong><?= substr($config['jam_guru_pulang_mulai'] ?? '13:00', 0, 5) ?> WIB</strong>.
                            </div>
                        </div>

                        <!-- Identitas Sekolah Card -->
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm space-y-4">
                            <h2 class="font-display font-bold text-base text-slate-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>Identitas & Pejabat Sekolah</span>
                            </h2>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Lembaga Pendidikan</label>
                                <input type="text" name="nama_sekolah" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= htmlspecialchars($config['nama_sekolah']) ?>" required>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Alamat Lengkap Sekolah</label>
                                <input type="text" name="alamat_sekolah" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= htmlspecialchars($config['alamat_sekolah']) ?>">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kepala Sekolah</label>
                                    <input type="text" name="kepala_sekolah" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= htmlspecialchars($config['kepala_sekolah']) ?>" required>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Bendahara TU</label>
                                    <input type="text" name="bendahara_tu" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition shadow-sm" value="<?= htmlspecialchars($config['bendahara_tu']) ?>" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Logo Sekolah (Opsional, max 2MB, png/jpg/jpeg)</label>
                                <div class="flex items-center gap-4">
                                    <?php if (!empty($config['logo_kop'])): ?>
                                        <img src="<?= App::baseUrl('uploads/' . htmlspecialchars($config['logo_kop'])) ?>" alt="Logo Lama" class="w-12 h-12 object-contain bg-slate-50 rounded-lg border border-slate-200 p-1">
                                    <?php endif; ?>
                                    <input type="file" name="logo_kop" accept="image/png, image/jpeg, image/jpg" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 transition cursor-pointer">
                                </div>
                                <span class="text-[10px] text-slate-400 mt-1 block">Biarkan kosong jika tidak ingin mengubah logo saat ini.</span>
                            </div>

                            <button type="submit" class="w-full mt-2 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs py-3 rounded-xl shadow-soft-md hover:shadow-glow-brand transition flex items-center justify-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                <span>Simpan Seluruh Konfigurasi</span>
                            </button>
                        </div>

                    </div>

                </div>
            </form>

        </div>
    </main>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const initialLat = <?= (float)$config['latitude_pusat'] ?>;
        const initialLng = <?= (float)$config['longitude_pusat'] ?>;
        let initialRadius = <?= (int)$config['radius_meter'] ?>;

        const map = L.map('map').setView([initialLat, initialLng], 17);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
        const circle = L.circle([initialLat, initialLng], {
            color: '#0284c7',
            fillColor: '#38bdf8',
            fillOpacity: 0.25,
            radius: initialRadius
        }).addTo(map);

        // Update koordinat dari interaksi peta (drag / click)
        function setCoordsFromMap(lat, lng) {
            document.getElementById('latitude_pusat').value = Number(lat).toFixed(8);
            document.getElementById('longitude_pusat').value = Number(lng).toFixed(8);
            marker.setLatLng([lat, lng]);
            circle.setLatLng([lat, lng]);
        }

        // Event drag marker pada peta
        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            setCoordsFromMap(pos.lat, pos.lng);
        });

        // Event klik di area peta
        map.on('click', function(e) {
            setCoordsFromMap(e.latlng.lat, e.latlng.lng);
        });

        // Handler perubahan Radius
        document.getElementById('radius_meter').addEventListener('input', function(e) {
            const rad = parseInt(e.target.value) || 50;
            circle.setRadius(rad);
        });

        // Helper untuk parse string float dengan dukungan koma atau titik
        function parseCleanFloat(str) {
            if (!str) return null;
            const cleaned = str.toString().trim().replace(',', '.');
            const num = parseFloat(cleaned);
            return (!isNaN(num) && isFinite(num)) ? num : null;
        }

        // Sinkronisasi real-time saat user mengetik Latitude
        document.getElementById('latitude_pusat').addEventListener('input', function(e) {
            let val = e.target.value.trim();

            // Jika user mem-paste koordinat gabungan "lat, lng"
            if (val.includes(',') || val.includes(' ')) {
                const parts = val.split(/[,\s]+/).filter(Boolean);
                if (parts.length >= 2) {
                    const pLat = parseCleanFloat(parts[0]);
                    const pLng = parseCleanFloat(parts[1]);
                    if (pLat !== null && pLng !== null) {
                        document.getElementById('latitude_pusat').value = pLat.toFixed(8);
                        document.getElementById('longitude_pusat').value = pLng.toFixed(8);
                        marker.setLatLng([pLat, pLng]);
                        circle.setLatLng([pLat, pLng]);
                        map.panTo([pLat, pLng]);
                        return;
                    }
                }
            }

            const lat = parseCleanFloat(val);
            const lng = parseCleanFloat(document.getElementById('longitude_pusat').value);
            if (lat !== null && lng !== null && lat >= -90 && lat <= 90) {
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
            }
        });

        // Sinkronisasi real-time saat user mengetik Longitude
        document.getElementById('longitude_pusat').addEventListener('input', function(e) {
            const lat = parseCleanFloat(document.getElementById('latitude_pusat').value);
            const lng = parseCleanFloat(e.target.value);
            if (lat !== null && lng !== null && lng >= -180 && lng <= 180) {
                marker.setLatLng([lat, lng]);
                circle.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
            }
        });

        // Format saat keluar dari input (blur)
        document.getElementById('latitude_pusat').addEventListener('blur', function(e) {
            const num = parseCleanFloat(e.target.value);
            if (num !== null) {
                e.target.value = num.toFixed(8);
            }
        });

        document.getElementById('longitude_pusat').addEventListener('blur', function(e) {
            const num = parseCleanFloat(e.target.value);
            if (num !== null) {
                e.target.value = num.toFixed(8);
            }
        });

        // Fitur Tempel Koordinat dari Google Maps
        function applyPastedCoords() {
            const input = document.getElementById('paste_coords');
            const msg = document.getElementById('paste-msg');
            const text = input.value.trim();

            if (!text) {
                alert('Silakan masukkan atau tempel koordinat terlebih dahulu (contoh: -6.954117, 108.220889)');
                return;
            }

            const parts = text.split(/[,\s]+/).filter(Boolean);
            if (parts.length < 2) {
                alert('Format koordinat tidak dikenali. Pastikan ada Latitude dan Longitude (contoh: -6.954117, 108.220889)');
                return;
            }

            const lat = parseCleanFloat(parts[0]);
            const lng = parseCleanFloat(parts[1]);

            if (lat === null || lng === null || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                alert('Nilai koordinat tidak valid. Harap periksa kembali angka Latitude dan Longitude.');
                return;
            }

            setCoordsFromMap(lat, lng);
            map.setView([lat, lng], 18);

            msg.textContent = `✓ Koordinat berhasil diterapkan: ${lat.toFixed(8)}, ${lng.toFixed(8)}`;
            msg.classList.remove('hidden');
            setTimeout(() => { msg.classList.add('hidden'); }, 4000);
            input.value = '';
        }

        // Fitur Ambil Lokasi Perangkat Sekarang
        function useCurrentLocation() {
            const btn = document.getElementById('btn-get-location');
            const btnText = document.getElementById('btn-location-text');

            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung deteksi lokasi Geolocation.');
                return;
            }

            btn.disabled = true;
            btnText.textContent = 'Mencari Lokasi GPS...';

            navigator.geolocation.getCurrentPosition(
                pos => {
                    btn.disabled = false;
                    btnText.textContent = 'Ambil Lokasi GPS Perangkat';
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    setCoordsFromMap(lat, lng);
                    map.setView([lat, lng], 18);
                    alert(`✓ Lokasi GPS saat ini berhasil diambil:\nLatitude: ${lat.toFixed(8)}\nLongitude: ${lng.toFixed(8)}\n\nJangan lupa klik tombol "Simpan Seluruh Konfigurasi" di bawah!`);
                },
                err => {
                    btn.disabled = false;
                    btnText.textContent = 'Ambil Lokasi GPS Perangkat';
                    let pesan = err.message;
                    if (err.code === 1) pesan = 'Izin akses lokasi ditolak oleh browser. Harap izinkan akses lokasi pada browser Anda.';
                    else if (err.code === 2) pesan = 'Posisi GPS tidak dapat ditentukan oleh perangkat Anda.';
                    else if (err.code === 3) pesan = 'Waktu permintaan lokasi GPS habis (timeout).';
                    alert('Gagal mendeteksi lokasi: ' + pesan);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0
                }
            );
        }
    </script>
</body>
</html>

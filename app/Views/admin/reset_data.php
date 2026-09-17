<?php
use App\Config\App;

$activeNav = 'reset_data';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembersihan & Reset Data - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Modern Admin Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight flex items-center gap-2">
                    <span>🧹</span>
                    <span>Pembersihan & Reset Data Dummy</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola dan bersihkan data contoh (dummy) untuk memulai operasional dengan data riil sekolah</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Sistem Siap Operasional</span>
                </span>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-5xl w-full mx-auto">
            
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold animate-fade-in">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold animate-fade-in">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Ringkasan Statistik Data Saat Ini -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm p-6">
                <h2 class="text-sm font-outfit font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span>📊</span>
                    <span>Status Data Tersimpan di Sistem Saat Ini</span>
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <div class="text-xl font-black text-slate-900"><?= (int)$counts['siswa'] ?></div>
                        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">Siswa Aktif</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <div class="text-xl font-black text-slate-900"><?= (int)$counts['buku_induk'] ?></div>
                        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">Buku Induk</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <div class="text-xl font-black text-slate-900"><?= (int)$counts['guru'] ?></div>
                        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">Guru (<?= (int)$counts['guru_non_admin'] ?> Non-Admin)</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <div class="text-xl font-black text-slate-900"><?= (int)$counts['presensi_gerbang'] ?></div>
                        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">Log Gerbang</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-center">
                        <div class="text-xl font-black text-slate-900"><?= (int)$counts['presensi_mapel'] ?></div>
                        <div class="text-[11px] text-slate-500 font-semibold mt-0.5">Presensi Mapel</div>
                    </div>
                </div>
            </div>

            <!-- OPSI PEMBERSIHAN MODULAR / PER KATEGORI -->
            <div>
                <h2 class="text-sm font-outfit font-bold text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span>⚙️</span>
                    <span>Pilihan Pembersihan Spesifik (Aman Sesuai Kebutuhan)</span>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- 1. Reset Presensi & Absensi Saja -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                                    🚪
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">1. Reset Presensi & Absensi KBM Saja</h3>
                                    <p class="text-[11px] text-slate-500">Log scan gerbang, sesi mengajar guru, dan absensi mapel</p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed mt-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                💡 <strong>Rekomendasi:</strong> Jika data guru dan siswa Anda sudah benar, namun Anda ingin mengosongkan riwayat absensi demo agar mulai dari nol di hari pertama tahun ajaran baru. Master data siswa & guru <strong>tetap utuh</strong>.
                            </p>
                        </div>
                        <form action="<?= App::baseUrl('admin/reset-data/execute') ?>" method="POST" class="mt-4"
                              onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan SELURUH riwayat presensi gerbang, sesi guru, dan absensi mapel?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                            <input type="hidden" name="action" value="presensi">
                            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-soft-sm transition">
                                🧹 Kosongkan Data Presensi (<?= (int)$counts['presensi_gerbang'] + (int)$counts['presensi_mapel'] ?> Record)
                            </button>
                        </form>
                    </div>

                    <!-- 2. Reset Nilai Raport -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                    📊
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">2. Reset Nilai Raport & Asesmen</h3>
                                    <p class="text-[11px] text-slate-500">Nilai formatif, sumatif, SAS semester 1 s/d 6</p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed mt-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                💡 Mengosongkan seluruh nilai raport dummy agar buku induk dan transkrip raport siap diinput dengan nilai rapor asli siswa.
                            </p>
                        </div>
                        <form action="<?= App::baseUrl('admin/reset-data/execute') ?>" method="POST" class="mt-4"
                              onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan SELURUH nilai raport semester siswa?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                            <input type="hidden" name="action" value="nilai">
                            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-soft-sm transition">
                                📊 Kosongkan Data Nilai Raport (<?= (int)$counts['nilai_siswa'] ?> Nilai)
                            </button>
                        </form>
                    </div>

                    <!-- 3. Hapus Seluruh Siswa & Buku Induk -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                                    👥
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">3. Hapus Siswa & Buku Induk Dummy</h3>
                                    <p class="text-[11px] text-slate-500">Seluruh data siswa contoh, buku induk, dan barcode</p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed mt-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                ⚠️ Menghapus seluruh <?= (int)$counts['siswa'] ?> siswa dummy beserta buku induk dan presensi terkait. Setelah dikosongkan, Anda dapat menambahkan data siswa asli lewat form tambah siswa atau impor.
                            </p>
                        </div>
                        <form action="<?= App::baseUrl('admin/reset-data/execute') ?>" method="POST" class="mt-4"
                              onsubmit="return confirm('PERINGATAN: Semua data siswa dummy akan dihapus permanen. Lanjutkan?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                            <input type="hidden" name="action" value="siswa">
                            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-soft-sm transition">
                                🗑️ Hapus Semua Data Siswa (<?= (int)$counts['siswa'] ?> Siswa)
                            </button>
                        </form>
                    </div>

                    <!-- 4. Hapus Guru Dummy Non-Admin -->
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                                    👨‍🏫
                                </div>
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">4. Hapus Guru Dummy (Non-Admin)</h3>
                                    <p class="text-[11px] text-slate-500">Guru mata pelajaran dan piket contoh</p>
                                </div>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed mt-2 bg-slate-50 p-3 rounded-xl border border-slate-100">
                                🛡️ Akun <strong>Super Admin ('admin')</strong> diproteksi secara permanen dan <strong>TIDAK AKAN terhapus</strong>, sehingga Anda tetap bisa login. Menghapus <?= (int)$counts['guru_non_admin'] ?> guru contoh agar Anda bisa mendaftarkan guru-guru asli.
                            </p>
                        </div>
                        <form action="<?= App::baseUrl('admin/reset-data/execute') ?>" method="POST" class="mt-4"
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua akun guru dummy non-admin?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                            <input type="hidden" name="action" value="guru">
                            <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-soft-sm transition">
                                👨‍🏫 Hapus Guru Non-Admin (<?= (int)$counts['guru_non_admin'] ?> Guru)
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            <!-- RESET BERSIH TOTAL (FACTORY RESET DUMMY) -->
            <div class="bg-gradient-to-br from-rose-50 to-white border-2 border-rose-200 rounded-3xl p-6 shadow-soft-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-2xl shadow-glow-rose flex-shrink-0">
                        💥
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-outfit font-extrabold text-slate-900">Reset Total / Bersihkan Seluruh Data Dummy Sekaligus</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Mengosongkan seluruh data dummy sekaligus: <strong>Siswa, Buku Induk, Nilai Raport, Sesi Mengajar, Log Gerbang, Jadwal KBM, dan Guru Non-Admin</strong>.
                        </p>
                        <div class="mt-3 p-3 rounded-xl bg-white border border-rose-200 text-xs text-slate-700 space-y-1">
                            <div class="font-bold text-slate-900">Data yang AMAN & TETAP DIPERTAHANKAN:</div>
                            <ul class="list-disc list-inside text-[11px] text-slate-600 space-y-0.5">
                                <li>Akun Super Admin (<code>admin</code>) tetap utuh untuk login</li>
                                <li>Master Rombel/Kelas (<?= (int)$counts['kelas'] ?> kelas) & Master Mata Pelajaran (<?= (int)$counts['mapel'] ?> mapel)</li>
                                <li>Konfigurasi GPS Geofence & Tarif Honor Sekolah</li>
                            </ul>
                        </div>

                        <form action="<?= App::baseUrl('admin/reset-data/execute') ?>" method="POST" class="mt-4 flex items-center gap-3 flex-wrap"
                              onsubmit="return confirmResetTotal()">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                            <input type="hidden" name="action" value="all">
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Ketik kata <span class="text-rose-600 font-mono bg-rose-100 px-1.5 py-0.5 rounded">BERSIHKAN</span>:</label>
                                <input type="text" id="confirmText" name="confirm_text" required placeholder="Ketik BERSIHKAN di sini" 
                                       class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-mono font-bold focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 uppercase">
                            </div>
                            <button type="submit" class="py-2.5 px-5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-soft-sm hover:shadow-glow-rose transition flex items-center gap-1.5">
                                <span>💥</span>
                                <span>Eksekusi Bersihkan Semua Data Dummy</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        function confirmResetTotal() {
            const input = document.getElementById('confirmText');
            if (!input || input.value.trim().toUpperCase() !== 'BERSIHKAN') {
                alert('Konfirmasi tidak valid! Harap ketik kata BERSIHKAN dengan benar sebelum melakukan reset total.');
                if (input) input.focus();
                return false;
            }
            return confirm('PERINGATAN TERAKHIR: Seluruh data siswa, buku induk, nilai, absensi, dan guru dummy akan dihapus permanen. Lanjutkan?');
        }
    </script>
</body>
</html>

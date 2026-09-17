<?php
use App\Config\App;
use App\Helpers\TimeHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>KBM Guru - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200/90 shadow-soft-lg flex flex-col pb-28">
        
        <!-- Sticky Header (Contains both Banner and Profile Info) -->
        <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-xs flex flex-col">
            <!-- Bright Smartphone Top Header -->
            <?php if (in_array($user['role'], ['admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara'])): ?>
                <div class="bg-blue-600 text-white text-xs px-4 py-2 flex items-center justify-between shadow-sm">
                    <span class="font-medium flex items-center gap-1.5">
                        <span>👑</span> Mode Guru (Rangkap)
                    </span>
                    <a href="<?= App::baseUrl('admin/dashboard') ?>" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full font-bold transition-colors">
                        ⚙️ Panel Manajemen &rarr;
                    </a>
                </div>
            <?php endif; ?>

            <div class="px-5 py-3.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <?php if (!empty($config['logo_kop'])): ?>
                            <img src="<?= App::baseUrl('uploads/' . htmlspecialchars($config['logo_kop'])) ?>" alt="Logo" class="w-11 h-11 rounded-2xl object-contain bg-white shadow-md shadow-blue-500/20 border-2 border-slate-200 p-1">
                        <?php else: ?>
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-sky-500 text-white font-extrabold text-base flex items-center justify-center shadow-md shadow-blue-500/20 border-2 border-white">
                                <?= strtoupper(substr($user['nama_lengkap'] ?? 'G', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200/80 uppercase tracking-wider">
                                    <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK AL-FARIZI') ?>
                                </span>
                            </div>
                            <h1 class="text-sm font-bold text-slate-900 leading-tight mt-0.5 line-clamp-1">
                                <?= htmlspecialchars($user['nama_lengkap']) ?>
                            </h1>
                            <p class="text-[10px] text-slate-500 font-mono">NIP: <?= htmlspecialchars($user['nik_nip']) ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <!-- Notification Bell -->
                        <button type="button" onclick="openNotifModal()" 
                                class="relative w-9 h-9 rounded-xl bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 border border-slate-200 hover:border-blue-200 flex items-center justify-center text-sm transition-all cursor-pointer" 
                                title="Notifikasi & Pengumuman">
                            🔔
                            <?php if (($unreadNotifCount ?? 0) > 0): ?>
                            <span id="guruNotifBadge" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-black flex items-center justify-center border-2 border-white animate-pulse">
                                <?= $unreadNotifCount ?>
                            </span>
                            <?php else: ?>
                            <span id="guruNotifBadge" class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-black items-center justify-center border-2 border-white animate-pulse">
                                0
                            </span>
                            <?php endif; ?>
                        </button>

                        <a href="<?= App::baseUrl('logout') ?>" 
                           onclick="return confirm('Apakah Anda yakin ingin keluar?')"
                           class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-200 hover:border-rose-200 flex items-center justify-center text-sm transition-all" title="Keluar">
                            🚪
                        </a>
                    </div>
                </div>
                <!-- Sub Header Info: Tanggal & Jam Live -->
                <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-600 font-medium">
                    <div class="flex items-center gap-1.5">
                        <span class="text-blue-500">📅</span>
                        <span><?= TimeHelper::formatDateIndonesian(date('Y-m-d')) ?></span>
                    </div>
                    <div class="flex items-center gap-1 bg-slate-50 border border-slate-200/80 px-2 py-0.5 rounded-lg text-slate-700 font-mono font-bold text-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="liveClock">--:--:--</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 flex-1 space-y-4">
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2 shadow-soft-sm animate-fade-in">
                <span class="text-base">✅</span>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2 shadow-soft-sm animate-fade-in">
                <span class="text-base">⚠️</span>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Announcement Banner from Admin (if unread notifications exist) -->
            <?php if (!empty($notifikasiList) && ($unreadNotifCount ?? 0) > 0): ?>
            <?php $latestNotif = $notifikasiList[0]; ?>
            <div onclick="openNotifModal()" class="p-3.5 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 text-amber-900 shadow-soft-sm cursor-pointer hover:shadow-md transition">
                <div class="flex items-start gap-2.5">
                    <span class="text-xl flex-shrink-0 animate-bounce">📢</span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-200/60 px-1.5 py-0.2 rounded">
                                <?= htmlspecialchars($latestNotif['tipe']) ?>
                            </span>
                            <span class="text-[10px] text-amber-700 font-semibold">Ketuk untuk membaca ➔</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-900 leading-snug line-clamp-1">
                            <?= htmlspecialchars($latestNotif['judul']) ?>
                        </h4>
                        <p class="text-[11px] text-slate-600 line-clamp-1 mt-0.5">
                            <?= htmlspecialchars($latestNotif['pesan']) ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Dompet Honor Card (Vibrant Light Accent) -->
            <div class="p-5 rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-600 to-sky-600 text-white shadow-lg shadow-blue-500/20 relative overflow-hidden">
                <!-- Background decorative shape -->
                <div class="absolute -right-6 -bottom-6 w-28 h-28 bg-white/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="absolute right-4 top-3 text-white/10 text-6xl font-extrabold select-none pointer-events-none">💰</div>

                <div class="flex justify-between items-center mb-1 relative z-10">
                    <span class="text-xs font-semibold text-blue-100">Estimasi Honor Bulan Ini</span>
                    <a href="<?= App::baseUrl('guru/dompet') ?>" 
                       class="text-[11px] font-bold text-white bg-white/20 hover:bg-white/30 px-2.5 py-1 rounded-xl backdrop-blur-sm transition-all flex items-center gap-1">
                        <span>Buku Honor</span>
                        <span>➜</span>
                    </a>
                </div>
                <div class="text-3xl font-outfit font-extrabold tracking-tight mt-1 relative z-10 text-white drop-shadow-sm">
                    <?= TimeHelper::formatRupiah($totalHonorBulanIni + (float)$user['tunjangan_tugas']) ?>
                </div>
                <div class="text-[11px] text-blue-100 mt-3 pt-2.5 border-t border-white/20 flex flex-wrap gap-x-4 gap-y-1 relative z-10">
                    <span>Honor KBM: <strong><?= TimeHelper::formatRupiah($totalHonorBulanIni) ?></strong></span>
                    <?php if ((float)$user['tunjangan_tugas'] > 0): ?>
                    <span>Tunjangan: <strong><?= TimeHelper::formatRupiah($user['tunjangan_tugas']) ?></strong></span>
                    <?php endif; ?>
                </div>
                <?php if ($totalDendaBulanIni > 0): ?>
                <div class="mt-2 text-[11px] text-rose-100 bg-rose-500/30 p-2 rounded-xl border border-rose-300/30 flex items-center gap-1.5 relative z-10">
                    <span>⚠️</span>
                    <span>Potongan Telat: -<?= TimeHelper::formatRupiah($totalDendaBulanIni) ?> (<?= $totalTelatBulanIni ?> mnt)</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Handphone App Icon Grid (Menu Handphone) -->
            <div class="bg-white rounded-3xl p-4 border border-slate-200/80 shadow-soft-sm">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Menu Utama
                    </h2>
                    <span class="text-[10px] text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded-full">
                        Aplikasi Guru
                    </span>
                </div>
                <div class="grid grid-cols-4 gap-2 text-center">
                    <!-- 1. Jadwal KBM -->
                    <a href="#section-jadwal" class="flex flex-col items-center gap-1 p-2 rounded-2xl hover:bg-slate-50 transition active:scale-95 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-500 to-sky-400 text-white flex items-center justify-center text-xl shadow-sm shadow-blue-500/20 group-hover:scale-105 transition-transform">
                            📅
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 leading-tight">Jadwal KBM</span>
                    </a>

                    <!-- 2. Presensi GPS -->
                    <a href="#section-gerbang" class="flex flex-col items-center gap-1 p-2 rounded-2xl hover:bg-slate-50 transition active:scale-95 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white flex items-center justify-center text-xl shadow-sm shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                            📍
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 leading-tight">Presensi GPS</span>
                    </a>

                    <!-- 3. Honor Guru -->
                    <a href="<?= App::baseUrl('guru/dompet') ?>" class="flex flex-col items-center gap-1 p-2 rounded-2xl hover:bg-slate-50 transition active:scale-95 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-400 text-white flex items-center justify-center text-xl shadow-sm shadow-amber-500/20 group-hover:scale-105 transition-transform">
                            💰
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 leading-tight">Buku Honor</span>
                    </a>

                    <!-- 4. Tabungan Siswa -->
                    <a href="<?= App::baseUrl('guru/tabungan') ?>" class="flex flex-col items-center gap-1 p-2 rounded-2xl hover:bg-slate-50 transition active:scale-95 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-400 text-white flex items-center justify-center text-xl shadow-sm shadow-indigo-500/20 group-hover:scale-105 transition-transform">
                            💳
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 leading-tight">Tabungan</span>
                    </a>

                    <!-- 5. LMS Guru -->
                    <a href="<?= App::baseUrl('guru/lms') ?>" class="flex flex-col items-center gap-1 p-2 rounded-2xl hover:bg-slate-50 transition active:scale-95 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-500 to-pink-400 text-white flex items-center justify-center text-xl shadow-sm shadow-purple-500/20 group-hover:scale-105 transition-transform">
                            📚
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 leading-tight">LMS Guru</span>
                    </a>

                    <!-- 6. Slip Gaji PDF -->
                    <a href="<?= App::baseUrl("payroll/slip/{$user['id']}/" . date('n') . "/" . date('Y')) ?>" target="_blank" class="flex flex-col items-center gap-1 p-2 rounded-2xl hover:bg-slate-50 transition active:scale-95 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-500 to-cyan-400 text-white flex items-center justify-center text-xl shadow-sm shadow-teal-500/20 group-hover:scale-105 transition-transform">
                            📄
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 leading-tight">Slip Gaji</span>
                    </a>

                    <!-- 7. Riwayat KBM -->
                    <a href="<?= App::baseUrl('guru/dompet') ?>" class="flex flex-col items-center gap-1 p-2 rounded-2xl hover:bg-slate-50 transition active:scale-95 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-500 to-pink-500 text-white flex items-center justify-center text-xl shadow-sm shadow-rose-500/20 group-hover:scale-105 transition-transform">
                            📊
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 leading-tight">Riwayat KBM</span>
                    </a>

                    <!-- 8. Keluar -->
                    <a href="<?= App::baseUrl('logout') ?>" onclick="return confirm('Keluar dari akun guru?')" class="flex flex-col items-center gap-1 p-2 rounded-2xl hover:bg-slate-50 transition active:scale-95 group">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-slate-400 to-slate-500 text-white flex items-center justify-center text-xl shadow-sm group-hover:scale-105 transition-transform">
                            🚪
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 leading-tight">Keluar</span>
                    </a>
                </div>
            </div>

            <!-- GPS Status Banner -->
            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-soft-sm">
                        📍
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-800">Geofencing Presensi GPS</div>
                        <div id="gpsStatusText" class="text-[11px] text-slate-500">Mendeteksi lokasi...</div>
                    </div>
                </div>
                <button type="button" onclick="acquireGps(true)"
                        class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-700 text-xs font-semibold border border-slate-200 transition-all duration-150">
                    Perbarui GPS
                </button>
            </div>

            <!-- Widget Presensi Gerbang Sekolah (Dua Arah & Non-Hadir) -->
            <div id="section-gerbang" class="p-5 rounded-3xl bg-white border border-slate-200/90 shadow-soft-md space-y-4">
                <div class="flex justify-between items-start gap-2">
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200/80">
                            Presensi Kehadiran Sekolah
                        </span>
                        <h2 class="text-base font-outfit font-extrabold text-slate-900 mt-1">
                            Presensi Gerbang Mandiri
                        </h2>
                        <p class="text-[11px] text-slate-500">Mencatat jam tiba & pulang di sekolah (2 Arah) via GPS HP</p>
                    </div>

                    <!-- Badge Status Hari Ini -->
                    <div id="badgeStatusGerbang">
                        <?php if (!$presensiGerbangHariIni): ?>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 border border-slate-200">
                                ⚪ Belum Datang
                            </span>
                        <?php elseif ($presensiGerbangHariIni['status_kehadiran'] === 'HADIR'): ?>
                            <?php if (!empty($presensiGerbangHariIni['waktu_pulang'])): ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    ✅ Hadir Lengkap
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-sky-100 text-sky-800 border border-sky-300 animate-pulse">
                                    ☀️ Di Sekolah
                                </span>
                            <?php endif; ?>
                        <?php elseif ($presensiGerbangHariIni['status_kehadiran'] === 'TERLAMBAT'): ?>
                            <?php if (!empty($presensiGerbangHariIni['waktu_pulang'])): ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-amber-100 text-amber-800 border border-amber-300">
                                    ⏱️ Hadir (Telat <?= $presensiGerbangHariIni['menit_terlambat_datang'] ?>m)
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">
                                    ⏱️ Telat (Menunggu Pulang)
                                </span>
                            <?php endif; ?>
                        <?php elseif ($presensiGerbangHariIni['status_kehadiran'] === 'TUGAS_LUAR'): ?>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-purple-100 text-purple-800 border border-purple-300">
                                💼 Tugas Luar / Dinas
                            </span>
                        <?php elseif ($presensiGerbangHariIni['status_kehadiran'] === 'SAKIT'): ?>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-rose-100 text-rose-800 border border-rose-300">
                                🏥 Sakit
                            </span>
                        <?php elseif ($presensiGerbangHariIni['status_kehadiran'] === 'IZIN'): ?>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-blue-100 text-blue-800 border border-blue-300">
                                ✉️ Izin
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-xl bg-rose-100 text-rose-800 border border-rose-300">
                                ❌ <?= htmlspecialchars($presensiGerbangHariIni['status_kehadiran']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Grid Waktu Datang & Pulang -->
                <div class="grid grid-cols-2 gap-2.5">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                            <span>☀️</span> Jam Datang
                        </div>
                        <div class="text-base font-extrabold font-mono text-slate-800 mt-0.5" id="valJamDatang">
                            <?= !empty($presensiGerbangHariIni['waktu_datang']) ? date('H:i', strtotime($presensiGerbangHariIni['waktu_datang'])) . ' WIB' : '--:--' ?>
                        </div>
                        <div class="text-[10px] text-slate-500 truncate mt-0.5" id="noteJamDatang">
                            <?php if (!empty($presensiGerbangHariIni['jarak_datang_meter']) && $presensiGerbangHariIni['jarak_datang_meter'] > 0): ?>
                                Jarak: <?= round($presensiGerbangHariIni['jarak_datang_meter']) ?>m
                            <?php elseif (!empty($presensiGerbangHariIni['keterangan'])): ?>
                                <?= htmlspecialchars($presensiGerbangHariIni['keterangan']) ?>
                            <?php else: ?>
                                Batas: <?= substr($config['jam_guru_masuk_selesai'] ?? '06:30:00', 0, 5) ?> WIB
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                            <span>🏠</span> Jam Pulang
                        </div>
                        <div class="text-base font-extrabold font-mono text-slate-800 mt-0.5" id="valJamPulang">
                            <?php if (!empty($presensiGerbangHariIni['waktu_pulang'])): ?>
                                <?= date('H:i', strtotime($presensiGerbangHariIni['waktu_pulang'])) . ' WIB' ?>
                            <?php elseif ($presensiGerbangHariIni && in_array($presensiGerbangHariIni['status_kehadiran'], ['TUGAS_LUAR', 'IZIN', 'SAKIT'])): ?>
                                <span class="text-xs font-sans text-purple-600 font-bold">Bebas Pulang</span>
                            <?php else: ?>
                                --:--
                            <?php endif; ?>
                        </div>
                        <div class="text-[10px] text-slate-500 truncate mt-0.5" id="noteJamPulang">
                            <?php if (!empty($presensiGerbangHariIni['waktu_pulang'])): ?>
                                Jarak: <?= round($presensiGerbangHariIni['jarak_pulang_meter']) ?>m
                            <?php elseif ($presensiGerbangHariIni && in_array($presensiGerbangHariIni['status_kehadiran'], ['TUGAS_LUAR', 'IZIN', 'SAKIT'])): ?>
                                Bebas Tap Pulang
                            <?php else: ?>
                                Aktif: <?= substr($config['jam_guru_pulang_mulai'] ?? '13:00:00', 0, 5) ?> WIB
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi Gerbang -->
                <div class="space-y-2 pt-1" id="boxTapGerbangAction">
                    <?php if (!$presensiGerbangHariIni || empty($presensiGerbangHariIni['waktu_datang'])): ?>
                        <!-- Tombol Buka Modal Presensi Datang -->
                        <button type="button" onclick="openModalDatang()"
                                class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs tracking-wide shadow-soft-sm flex items-center justify-center gap-2 transition-all duration-150">
                            <span class="text-base">☀️</span>
                            <span>Presensi Datang / Ajukan Tugas, Izin, Sakit</span>
                        </button>
                    <?php elseif (in_array($presensiGerbangHariIni['status_kehadiran'], ['TUGAS_LUAR', 'IZIN', 'SAKIT'])): ?>
                        <!-- Info Non-Hadir -->
                        <div class="p-3 rounded-2xl bg-purple-50 border border-purple-200/80 text-purple-900 text-xs font-medium flex items-center gap-2">
                            <span class="text-base">💼</span>
                            <div>
                                <strong>Status: <?= htmlspecialchars($presensiGerbangHariIni['status_kehadiran']) ?></strong>
                                <p class="text-[11px] text-purple-700 mt-0.5">
                                    "<?= htmlspecialchars($presensiGerbangHariIni['keterangan'] ?? '') ?>" (Tidak memerlukan Tap Pulang sore ini).
                                </p>
                            </div>
                        </div>
                    <?php elseif (empty($presensiGerbangHariIni['waktu_pulang'])): ?>
                        <?php
                        $nowClock = date('H:i:s');
                        $jamBukaPulang = $config['jam_guru_pulang_mulai'] ?? '13:00:00';
                        $isPulangActive = ($nowClock >= $jamBukaPulang);
                        ?>
                        <?php if ($isPulangActive): ?>
                            <!-- Tombol Tap Pulang Aktif -->
                            <button type="button" onclick="doTapPulangGerbang()"
                                    class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 text-white font-bold text-xs tracking-wide shadow-glow-brand flex items-center justify-center gap-2 transition-all duration-150">
                                <span class="text-base">🏠</span>
                                <span>Tap Pulang Sekarang (Selesai Hari Ini)</span>
                            </button>
                            <p class="text-[10px] text-center text-slate-400">
                                ⚠️ Wajib Tap Pulang sebelum jam 17:00 WIB agar kehadiran harian tidak dinyatakan Alpha.
                            </p>
                        <?php else: ?>
                            <!-- Jam Pulang Belum Aktif -->
                            <div class="p-3.5 rounded-2xl bg-slate-100/90 border border-slate-200 text-slate-600 text-xs font-medium text-center space-y-1.5">
                                <div class="font-bold flex items-center justify-center gap-1.5 text-slate-800">
                                    <span>⏳</span> Tap Pulang Belum Aktif
                                </div>
                                <div id="tickerCountdownPulang" class="inline-block text-xs font-mono font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-xl border border-amber-200">
                                    Menghitung waktu buka...
                                </div>
                                <p class="text-[11px] text-slate-500">
                                    Tombol kepulangan guru aktif sesudah pukul <strong><?= substr($jamBukaPulang, 0, 5) ?> WIB</strong>.
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- Sudah Selesai Dua Arah -->
                        <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                            <span class="text-base">🎉</span>
                            <span>Presensi kehadiran sekolah hari ini sudah lengkap (Datang: <?= date('H:i', strtotime($presensiGerbangHariIni['waktu_datang'])) ?> WIB, Pulang: <?= date('H:i', strtotime($presensiGerbangHariIni['waktu_pulang'])) ?> WIB). Terima kasih atas dedikasi Anda!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Jadwal Hari Ini Cards -->
            <div id="section-jadwal" class="flex justify-between items-center px-1 pt-2">
                <h2 class="text-sm font-outfit font-bold text-slate-900 uppercase tracking-wider">
                    Jadwal Mengajar Hari Ini
                </h2>
                <span class="text-xs font-bold text-brand-600 bg-brand-50 px-2.5 py-0.5 rounded-full border border-brand-200">
                    <?= count($jadwalList) ?> Sesi
                </span>
            </div>

            <?php if (empty($jadwalList)): ?>
            <div class="p-8 text-center rounded-3xl bg-white border border-slate-200 shadow-soft-sm text-slate-400">
                <div class="text-4xl mb-2">🎉</div>
                <h3 class="text-sm font-bold text-slate-700">Tidak Ada Jadwal KBM Hari Ini</h3>
                <p class="text-xs text-slate-500 mt-1">Anda bebas dari tugas mengajar hari ini. Selamat beristirahat atau menyelesaikan administrasi kelas!</p>
            </div>
            <?php else: ?>
                <div class="space-y-3.5">
                <?php foreach ($jadwalList as $j): ?>
                <div class="p-4 rounded-2xl bg-white border transition-all duration-200 shadow-soft-sm <?= $j['status_badge'] === 'SEDANG_MENGAJAR' ? 'border-emerald-400 bg-emerald-50/40 shadow-glow-emerald' : ($j['status_badge'] === 'SIAP_CHECKIN' ? 'border-brand-400 bg-brand-50/40 shadow-glow-brand' : ($j['status_badge'] === 'TIDAK_HADIR' ? 'border-rose-300 bg-rose-50/30' : 'border-slate-200/80')) ?>" id="card-jadwal-<?= $j['id'] ?>">
                    <div class="flex justify-between items-start gap-2 mb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 font-mono text-xs font-bold">
                                ⏰ <?= substr($j['jam_mulai'], 0, 5) ?> - <?= substr($j['jam_selesai'], 0, 5) ?>
                            </span>
                            <span class="text-[11px] text-slate-500 font-medium">
                                (<?= (int)$j['jumlah_jp'] ?> JP = <?= (int)$j['jumlah_jp'] * 40 ?> mnt)
                            </span>
                        </div>
                        <div id="badge-jadwal-<?= $j['id'] ?>">
                            <?php if ($j['status_badge'] === 'SELESAI'): ?>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider border border-slate-200">
                                    ✅ Selesai
                                </span>
                            <?php elseif ($j['status_badge'] === 'SEDANG_MENGAJAR'): ?>
                                <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider border border-emerald-300 animate-pulse">
                                    Sedang Berlangsung
                                </span>
                            <?php elseif ($j['status_badge'] === 'SIAP_CHECKIN'): ?>
                                <span class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-700 text-[10px] font-bold uppercase tracking-wider border border-sky-300">
                                    Siap Check-in (H-5)
                                </span>
                            <?php elseif ($j['status_badge'] === 'TIDAK_HADIR'): ?>
                                <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider border border-rose-300">
                                    ❌ Tidak Hadir (Sesi Berakhir)
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold uppercase tracking-wider border border-amber-200">
                                    Akan Datang
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h3 class="text-base font-outfit font-bold text-slate-900">
                        <?= htmlspecialchars($j['nama_mapel']) ?>
                    </h3>
                    <div class="text-xs font-semibold text-brand-600 mb-3">
                        🏫 <?= htmlspecialchars($j['nama_kelas']) ?> (<?= htmlspecialchars($j['jurusan']) ?>)
                    </div>

                    <!-- Action State Buttons -->
                    <div id="action-jadwal-<?= $j['id'] ?>">
                    <?php if ($j['status_badge'] === 'SEDANG_MENGAJAR'): ?>
                        <?php 
                        $currentTimeStr = date('H:i:s');
                        $isFinishTimeReached = ($currentTimeStr >= $j['jam_selesai']);
                        ?>
                        <div class="p-3 rounded-xl bg-emerald-100/60 border border-emerald-200 text-xs text-emerald-900 mb-3 space-y-0.5">
                            <div>Check-in: <strong class="font-mono"><?= date('H:i:s', strtotime($j['waktu_checkin'])) ?></strong></div>
                            <?php if ((int)$j['menit_terlambat'] > 0): ?>
                                <div class="text-rose-600 font-medium">Keterlambatan: <?= (int)$j['menit_terlambat'] ?> menit (- <?= TimeHelper::formatRupiah($j['menit_terlambat'] * $config['denda_per_menit']) ?>)</div>
                            <?php else: ?>
                                <div class="text-emerald-700 font-medium">Tepat Waktu (Honor Maksimal: <?= TimeHelper::formatRupiah($j['honor_didapat']) ?>)</div>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-2">
                            <div class="grid grid-cols-2 gap-2">
                                <a href="<?= App::baseUrl("guru/presensi/{$j['sesi_id']}") ?>" 
                                   class="py-2.5 px-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold text-center shadow-soft-sm transition-all duration-150 flex items-center justify-center gap-1.5">
                                    <span>📋</span>
                                    <span>Presensi</span>
                                </a>
                                <a href="<?= App::baseUrl("guru/lms/materi/{$j['kelas_id']}/" . ($j['mapel_id'] ?? 0)) ?>" 
                                   class="py-2.5 px-3 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold text-center shadow-soft-sm transition-all duration-150 flex items-center justify-center gap-1.5">
                                    <span>📚</span>
                                    <span>Buka LMS</span>
                                </a>
                            </div>
                                <form action="<?= App::baseUrl("guru/selesai/{$j['sesi_id']}") ?>" method="POST" class="flex-1" 
                                      onsubmit="return confirm('Akhiri sesi mengajar ini? Honor akan dihitung final.')">
                                    <button type="submit" 
                                            class="w-full py-2.5 px-3 rounded-xl font-semibold text-xs shadow-soft-sm transition-all duration-150 flex items-center justify-center gap-1 <?= $isFinishTimeReached ? 'bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer' : 'bg-slate-200 text-slate-400 border border-slate-300 cursor-not-allowed' ?>"
                                            <?= !$isFinishTimeReached ? 'disabled' : '' ?>
                                            title="<?= $isFinishTimeReached ? 'Klik untuk menyelesaikan KBM' : 'Tombol aktif setelah pukul ' . substr($j['jam_selesai'], 0, 5) . ' WIB' ?>">
                                        <span><?= $isFinishTimeReached ? '🏁' : '🔒' ?></span>
                                        <span><?= $isFinishTimeReached ? 'Selesai KBM' : 'Selesai ' . substr($j['jam_selesai'], 0, 5) ?></span>
                                    </button>
                                </form>
                            </div>
                            <?php if (!$isFinishTimeReached): ?>
                            <div class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg text-center font-medium">
                                ⏳ Tombol Selesai KBM aktif otomatis saat jam pembelajaran usai (pukul <?= substr($j['jam_selesai'], 0, 5) ?> WIB).
                            </div>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($j['status_badge'] === 'SELESAI'): ?>
                        <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                            <div>
                                <span class="text-slate-500">Honor Diterima:</span>
                                <strong class="text-emerald-600 font-bold ml-1 text-sm"><?= TimeHelper::formatRupiah($j['honor_didapat']) ?></strong>
                            </div>
                            <a href="<?= App::baseUrl("guru/presensi/{$j['sesi_id']}") ?>" 
                               class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[11px] font-semibold transition-colors">
                                Lihat Presensi
                            </a>
                        </div>

                    <?php elseif ($j['status_badge'] === 'SIAP_CHECKIN'): ?>
                        <div class="text-xs text-brand-600 font-medium mb-2">
                            ✨ Jendela check-in H-5 menit aktif! Pastikan GPS aktif dan berada di sekolah.
                        </div>
                        <button type="button" 
                                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white font-bold text-xs shadow-glow-brand hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2" 
                                onclick="doCheckin(<?= $j['id'] ?>)">
                            <span>📍 Check-In Mulai Mengajar</span>
                        </button>

                    <?php elseif ($j['status_badge'] === 'TIDAK_HADIR'): ?>
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-1">
                            <div class="font-bold flex items-center gap-1.5 text-rose-800">
                                <span>🔒</span> Presensi Ditutup (Melewati Jam Mengajar)
                            </div>
                            <p class="text-[11px] text-rose-700 leading-relaxed">
                                Jam mengajar sesi ini telah berakhir pada pukul <strong><?= substr($j['jam_selesai'], 0, 5) ?> WIB</strong>. Anda tidak melakukan presensi selama jam mengajar berlangsung, sehingga otomatis dinyatakan <strong>Tidak Hadir (Alpha)</strong> untuk jadwal ini.
                            </p>
                        </div>

                    <?php else: ?>
                        <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs mb-2">
                            🔒 Tombol check-in terkunci. Terbuka tepat 5 menit sebelum <?= substr($j['jam_mulai'], 0, 5) ?>.
                        </div>
                        <button type="button" 
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-100 border border-slate-200 text-slate-400 text-xs font-semibold cursor-not-allowed" 
                                disabled>
                            ⏳ Menunggu Jendela H-5
                        </button>
                    <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Penilaian KBM Semester Berjalan (Guru Mode) -->
            <div class="mt-6 pt-4 border-t border-slate-200">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between px-1 mb-3 gap-2">
                    <div>
                        <h2 class="text-sm font-outfit font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="shrink-0">📊</span>
                            <span class="leading-tight">Penilaian KBM Semester Berjalan</span>
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Input Tugas/PR, UH, UTS & UAS dengan kalkulasi otomatis</p>
                    </div>
                    <?php if (!empty($activeTapel)): ?>
                    <span class="text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200 px-2.5 py-1 rounded-full whitespace-nowrap self-start sm:self-auto shrink-0">
                        <?= htmlspecialchars($activeTapel['tahun_ajaran']) ?> (<?= htmlspecialchars($activeTapel['semester']) ?>)
                    </span>
                    <?php endif; ?>
                </div>

                <?php if (empty($kelasMapelGuru)): ?>
                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-soft-sm text-center">
                    <div class="text-3xl mb-2">📚</div>
                    <p class="text-xs font-bold text-slate-800">Belum Ada Jadwal Mengajar KBM</p>
                    <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                        Anda belum memiliki jadwal mengajar mata pelajaran pada rombel aktif. Penilaian KBM hanya dibuka untuk mata pelajaran yang Anda ampu sesuai jadwal.
                    </p>
                </div>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($kelasMapelGuru as $km): ?>
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200 hover:border-brand-300 shadow-soft-sm transition-all duration-150 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-slate-900 truncate"><?= htmlspecialchars($km['nama_mapel']) ?></span>
                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[10px] font-bold shrink-0">Sem <?= $km['semester_rekomendasi'] ?></span>
                            </div>
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5 line-clamp-1">
                                🏫 Kelas <?= htmlspecialchars($km['nama_kelas']) ?> (<?= htmlspecialchars($km['jurusan']) ?>)
                            </div>
                        </div>
                        <a href="<?= App::baseUrl("guru/nilai/input?kelas_id={$km['kelas_id']}&mapel_id={$km['mapel_id']}&semester_ke={$km['semester_rekomendasi']}") ?>" 
                           class="shrink-0 px-3.5 py-2 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white text-xs font-bold shadow-soft-sm flex items-center gap-1.5 transition-all">
                            <span>Input</span>
                            <span>➜</span>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 text-center">
                    <a href="<?= App::baseUrl('guru/nilai') ?>" class="text-xs font-bold text-brand-600 hover:text-brand-700 hover:underline">
                        Lihat Rekap Penilaian Kelas yang Anda Ampu ➜
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tabungan Siswa -->
            <div class="mt-6 pt-4 border-t border-slate-200 mb-8">
                <div class="flex justify-between items-center px-1 mb-3">
                    <div>
                        <h2 class="text-sm font-outfit font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="shrink-0">💰</span>
                            <span class="leading-tight">Manajemen Tabungan</span>
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">Akses program tabungan siswa</p>
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 shadow-soft-sm">
                    <div class="flex gap-4 items-center mb-3">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-2xl shadow-sm text-emerald-600 shrink-0">
                            🏦
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-emerald-900 truncate">Tabungan Siswa</h3>
                            <p class="text-[11px] text-emerald-700 leading-tight mt-0.5">Lihat data tabungan dan kelola program.</p>
                        </div>
                    </div>
                    <a href="<?= App::baseUrl('guru/tabungan') ?>" 
                       class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition-colors">
                        <span>Buka Panel Tabungan</span>
                        <span>➜</span>
                    </a>
                </div>
            </div>

        </main>

        <!-- Bottom Navigation Mobile -->
        <?php require __DIR__ . '/partials/bottom_nav.php'; ?>
    </div>

    <script>
        window.BASE_URL = '<?= App::baseUrl() ?>/';
        const schoolLat = <?= (float)$config['latitude_pusat'] ?>;
        const schoolLng = <?= (float)$config['longitude_pusat'] ?>;
        const radiusMeters = <?= (int)$config['radius_meter'] ?>;

        let userLat = null;
        let userLng = null;

        let serverOffsetMs = 0;
        let jamPulangMulai = '<?= $config['jam_guru_pulang_mulai'] ?? '13:00:00' ?>';
        let isPulangOpenNow = <?= ($isPulangActive ?? false) ? 'true' : 'false' ?>;
        let presensiStatus = '<?= $presensiGerbangHariIni['status_kehadiran'] ?? '' ?>';
        let hasPulang = <?= (!empty($presensiGerbangHariIni['waktu_pulang'])) ? 'true' : 'false' ?>;

        // Jam Digital Real-time Sinkron Jam Server
        function updateClock() {
            const now = new Date(Date.now() + serverOffsetMs);
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const clockEl = document.getElementById('liveClock');
            if (clockEl) {
                clockEl.textContent = `${h}:${m}:${s} WIB`;
            }

            // Update ticker countdown pulang jika tombol pulang belum aktif
            updatePulangCountdown(now);
        }
        setInterval(updateClock, 1000);
        updateClock();

        function formatHms(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }

        function updatePulangCountdown(serverDate) {
            const tickerEl = document.getElementById('tickerCountdownPulang');
            if (!tickerEl || hasPulang) return;

            const parts = jamPulangMulai.split(':');
            const target = new Date(serverDate);
            target.setHours(parseInt(parts[0], 10), parseInt(parts[1], 10), parseInt(parts[2] || '0', 10), 0);

            const diffSec = Math.floor((target.getTime() - serverDate.getTime()) / 1000);
            if (diffSec > 0) {
                tickerEl.innerHTML = `⏳ Buka Pulang: <strong>${formatHms(diffSec)}</strong> (${jamPulangMulai.substring(0, 5)} WIB)`;
            } else {
                tickerEl.innerHTML = `✅ Jam pulang telah dibuka!`;
                unlockPulangButton();
            }
        }

        function unlockPulangButton() {
            const box = document.getElementById('boxTapGerbangAction');
            if (!box || hasPulang || ['TUGAS_LUAR', 'IZIN', 'SAKIT'].includes(presensiStatus)) return;
            // Jika belum ada tombol tap pulang aktif, ganti secara mulus tanpa reload
            if (!document.getElementById('btnTapPulangLive')) {
                box.innerHTML = `
                    <button type="button" id="btnTapPulangLive" onclick="doTapPulangGerbang()"
                            class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-700 hover:to-indigo-700 text-white font-bold text-xs tracking-wide shadow-glow-brand flex items-center justify-center gap-2 transition-all duration-150 animate-bounce-short">
                        <span class="text-base">🏠</span>
                        <span>Tap Pulang Sekarang (Selesai Hari Ini)</span>
                    </button>
                    <p class="text-[10px] text-center text-slate-400">
                        ⚠️ Wajib Tap Pulang sebelum jam 17:00 WIB agar kehadiran harian tidak dinyatakan Alpha.
                    </p>
                `;
            }
        }

        // Live Background Polling Sync Guru
        let guruPollTimer = null;
        let lastUnreadNotifGuru = <?= (int)($unreadNotifCount ?? 0) ?>;
        async function pollGuruLive() {
            if (document.hidden) return;
            try {
                let rawBase = (window.BASE_URL || '/').trim().replace(/\/+$/, '');
                if (!rawBase.startsWith('/')) rawBase = '/' + rawBase;
                if (rawBase === '/') rawBase = '';
                const endpoint = rawBase + '/guru/live-status';

                const res = await fetch(endpoint, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success) return;

                // Sync server time offset
                if (data.server_datetime) {
                    const srvDate = new Date(data.server_datetime.replace(' ', 'T'));
                    serverOffsetMs = srvDate.getTime() - Date.now();
                }

                // Sync realtime notifikasi guru
                if (typeof data.unread_notif !== 'undefined') {
                    if (data.unread_notif > lastUnreadNotifGuru) {
                        playNotifChime();
                        showLiveNotificationToast(data.latest_notif);
                        const modal = document.getElementById('notifGuruModal');
                        if (modal && !modal.classList.contains('hidden')) {
                            loadGuruNotifList();
                        }
                    }
                    lastUnreadNotifGuru = data.unread_notif;
                    updateNotifBadge(data.unread_notif);
                }

                if (data.jam_pulang_mulai) {
                    jamPulangMulai = data.jam_pulang_mulai;
                }

                // Cek status kepulangan gerbang
                if (data.is_pulang_open && !hasPulang) {
                    unlockPulangButton();
                }

                // Periksa data presensi gerbang jika ada pembaruan dari perangkat lain
                if (data.presensi_gerbang) {
                    const p = data.presensi_gerbang;
                    presensiStatus = p.status_kehadiran;
                    if (p.waktu_pulang) {
                        hasPulang = true;
                        const valPulang = document.getElementById('valJamPulang');
                        if (valPulang && valPulang.textContent.trim() === '--:--') {
                            valPulang.textContent = p.waktu_pulang.substring(11, 16) + ' WIB';
                        }
                    }
                }

                // Cek pembaruan jadwal KBM (H-5 check-in window & penutupan absen lewat jam mengajar)
                if (Array.isArray(data.jadwal_list)) {
                    data.jadwal_list.forEach(j => {
                        const card = document.getElementById('card-jadwal-' + j.id);
                        const badge = document.getElementById('badge-jadwal-' + j.id);
                        const action = document.getElementById('action-jadwal-' + j.id);

                        if (card && badge && action) {
                            if (j.status_badge === 'TIDAK_HADIR' && !card.classList.contains('border-rose-300')) {
                                card.className = 'p-4 rounded-2xl bg-white border border-rose-300 bg-rose-50/30 transition-all duration-200 shadow-soft-sm';
                                badge.innerHTML = `
                                    <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider border border-rose-300">
                                        ❌ Tidak Hadir (Sesi Berakhir)
                                    </span>
                                `;
                                action.innerHTML = `
                                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 space-y-1">
                                        <div class="font-bold flex items-center gap-1.5 text-rose-800">
                                            <span>🔒</span> Presensi Ditutup (Melewati Jam Mengajar)
                                        </div>
                                        <p class="text-[11px] text-rose-700 leading-relaxed">
                                            Jam mengajar sesi ini telah berakhir pada pukul <strong>${j.jam_selesai ? j.jam_selesai.substring(0, 5) : ''} WIB</strong>. Anda tidak melakukan presensi selama jam mengajar berlangsung, sehingga otomatis dinyatakan <strong>Tidak Hadir (Alpha)</strong> untuk jadwal ini.
                                        </p>
                                    </div>
                                `;
                            } else if (j.status_badge === 'SIAP_CHECKIN' && !card.classList.contains('border-brand-400')) {
                                card.className = 'p-4 rounded-2xl bg-white border border-brand-400 bg-brand-50/40 shadow-glow-brand transition-all duration-200';
                                badge.innerHTML = `
                                    <span class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-700 text-[10px] font-bold uppercase tracking-wider border border-sky-300 animate-pulse">
                                        Siap Check-in (H-5)
                                    </span>
                                `;
                                action.innerHTML = `
                                    <div class="text-xs text-brand-600 font-medium mb-2">
                                        ✨ Jendela check-in H-5 menit aktif! Pastikan GPS aktif dan berada di sekolah.
                                    </div>
                                    <button type="button" 
                                            class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white font-bold text-xs shadow-glow-brand hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 flex items-center justify-center gap-2" 
                                            onclick="doCheckin(${j.id})">
                                        <span>📍 Check-In Mulai Mengajar</span>
                                    </button>
                                `;
                            }
                        }
                    });
                }
            } catch (err) {
                console.warn('Guru live sync error:', err);
            }
        }

        // Mulai interval polling setiap 3.5 detik
        guruPollTimer = setInterval(pollGuruLive, 3500);

        // Langsung refresh polling saat tab aktif kembali
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                pollGuruLive();
            }
        });

        // ─────────────────────────────────────────────────────
        // Callback GPS dari Android Bridge (dipanggil oleh GpsJsBridge.kt)
        // ─────────────────────────────────────────────────────
        function onGpsResult(lat, lng, accuracy) {
            userLat = lat;
            userLng = lng;
            const dist = calculateDistance(userLat, userLng, schoolLat, schoolLng);
            const isInside = dist <= radiusMeters;
            const statusEl = document.getElementById('gpsStatusText');
            if (statusEl) {
                statusEl.innerHTML = `📡 Android GPS — Jarak: <strong>${Math.round(dist)}m</strong> (${isInside ? '<span style="color:#10b981">Dalam Radius ✅</span>' : '<span style="color:#ef4444">Di Luar Radius ❌</span>'})`;
            }
        }

        function onGpsError(message) {
            const statusEl = document.getElementById('gpsStatusText');
            if (statusEl) statusEl.textContent = '⚠️ Android GPS: ' + message;
        }

        function onGpsPermissionGranted() {
            // Setelah izin diberikan, minta lokasi lagi
            if (window.AndroidGps) window.AndroidGps.requestLocation();
        }

        function onGpsPermissionDenied() {
            const statusEl = document.getElementById('gpsStatusText');
            if (statusEl) statusEl.textContent = '⚠️ Izin GPS ditolak. Harap aktifkan di pengaturan app.';
        }

        // Ambil GPS Perangkat (mendukung Android Bridge & Browser Geolocation)
        function acquireGps(showNotice = false) {
            const statusEl = document.getElementById('gpsStatusText');

            // PRIORITAS 1: Android WebView GPS Bridge
            if (window.AndroidGps && window.AndroidGps.isAvailable && window.AndroidGps.isAvailable() === 'true') {
                statusEl.textContent = '📡 Meminta GPS dari Android...';

                // Cek apakah sudah ada permission
                if (window.AndroidGps.hasPermission && window.AndroidGps.hasPermission() === 'false') {
                    statusEl.textContent = '🔐 Meminta izin GPS Android...';
                    if (window.AndroidPermission) {
                        window.AndroidPermission.requestGpsPermission();
                    }
                    return;
                }

                window.AndroidGps.requestLocation();
                return;
            }

            // PRIORITAS 2: Browser Geolocation API (web biasa / PWA)
            if (navigator.geolocation) {
                statusEl.textContent = 'Mencari sinyal GPS akurat...';
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        userLat = pos.coords.latitude;
                        userLng = pos.coords.longitude;
                        const dist = calculateDistance(userLat, userLng, schoolLat, schoolLng);
                        const isInside = dist <= radiusMeters;
                        statusEl.innerHTML = `Jarak: <strong>${Math.round(dist)}m</strong> (${isInside ? '<span style="color:#10b981">Dalam Radius</span>' : '<span style="color:#ef4444">Di Luar Radius</span>'})`;
                        if (showNotice) {
                            alert(`Koordinat GPS terdeteksi!\nJarak ke sekolah: ${Math.round(dist)} meter\nBatas toleransi: ${radiusMeters} meter`);
                        }
                    },
                    err => {
                        statusEl.textContent = '⚠️ GPS belum diizinkan atau dinonaktifkan';
                        if (showNotice) {
                            alert('Gagal mendapatkan lokasi GPS: ' + err.message);
                        }
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            } else {
                statusEl.textContent = 'Browser tidak mendukung GPS';
            }
        }
        acquireGps(false);


        // Hitung jarak Haversine di clientside
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return R * c;
        }

        // Eksekusi Check-in KBM
        async function doCheckin(jadwalId) {
            if (!confirm('Lakukan check-in mengajar untuk jadwal ini sekarang?')) return;

            const formData = new FormData();
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) formData.append('csrf_token', csrfMeta.content);
            formData.append('jadwal_id', jadwalId);
            if (userLat && userLng) {
                formData.append('latitude', userLat);
                formData.append('longitude', userLng);
            } else {
                // Beri pilihan bypass jika testing di browser tanpa GPS
                const bypass = confirm("GPS perangkat belum terdeteksi. Apakah ingin menggunakan bypass GPS untuk mode simulasi?");
                if (bypass) {
                    formData.append('bypass_gps', '1');
                } else {
                    return;
                }
            }

            try {
                let rawBase = (window.BASE_URL || '/').trim().replace(/\/+$/, '');
                if (!rawBase.startsWith('/')) rawBase = '/' + rawBase;
                if (rawBase === '/') rawBase = '';
                const endpoint = rawBase + '/guru/checkin';

                const res = await fetch(endpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    // Otomatis arahkan ke halaman input presensi siswa mapel
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                    } else if (data.sesi_id) {
                        let target = (window.BASE_URL || '/').trim().replace(/\/+$/, '');
                        window.location.href = target + '/guru/presensi/' + data.sesi_id;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert('❌ Gagal Check-in: ' + data.message);
                }
            } catch (e) {
                console.error('Checkin error:', e);
                alert('Terjadi kesalahan jaringan atau server.');
            }
        }

        // --- PRESENSI GERBANG MANDIRI (DATANG & PULANG) ---
        function openModalDatang() {
            document.getElementById('modalDatang').classList.remove('hidden');
            document.getElementById('modalDatang').classList.add('flex');
            toggleKeteranganField();
        }

        function closeModalDatang() {
            document.getElementById('modalDatang').classList.add('hidden');
            document.getElementById('modalDatang').classList.remove('flex');
        }

        function toggleKeteranganField() {
            const form = document.getElementById('formTapDatang');
            const selected = form.querySelector('input[name="status_pilihan"]:checked')?.value || 'HADIR';
            const wrapper = document.getElementById('keteranganWrapper');
            const label = document.getElementById('keteranganLabel');
            const input = document.getElementById('inputKeterangan');

            if (selected === 'HADIR') {
                wrapper.classList.add('hidden');
                input.required = false;
            } else {
                wrapper.classList.remove('hidden');
                input.required = true;
                if (selected === 'TUGAS_LUAR') {
                    label.innerHTML = 'Keterangan Tugas Luar / No. Surat / Lokasi <span class="text-rose-500">*</span>';
                    input.placeholder = 'Contoh: Surat Tugas No. 005/SMK-AF/2026 ke Balai Guru Penggerak';
                } else if (selected === 'SAKIT') {
                    label.innerHTML = 'Keterangan Sakit / Gejala / Istirahat <span class="text-rose-500">*</span>';
                    input.placeholder = 'Contoh: Demam dan flu, istirahat atas petunjuk dokter';
                } else if (selected === 'IZIN') {
                    label.innerHTML = 'Alasan / Keperluan Izin <span class="text-rose-500">*</span>';
                    input.placeholder = 'Contoh: Keperluan keluarga mendesak di luar kota';
                }
                setTimeout(() => input.focus(), 150);
            }
        }

        async function submitTapDatang(e) {
            e.preventDefault();
            const form = document.getElementById('formTapDatang');
            const selected = form.querySelector('input[name="status_pilihan"]:checked')?.value || 'HADIR';
            const keterangan = document.getElementById('inputKeterangan').value.trim();
            const btnSubmit = document.getElementById('btnSubmitDatang');

            const formData = new FormData();
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) formData.append('csrf_token', csrfMeta.content);
            formData.append('status_pilihan', selected);
            formData.append('keterangan', keterangan);

            // Jika HADIR, butuh GPS
            if (selected === 'HADIR') {
                if (userLat && userLng) {
                    formData.append('latitude', userLat);
                    formData.append('longitude', userLng);
                } else {
                    const bypass = confirm("GPS perangkat belum terdeteksi. Apakah ingin menggunakan bypass GPS untuk mode simulasi?");
                    if (bypass) {
                        formData.append('bypass_gps', '1');
                    } else {
                        return;
                    }
                }
            }

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = 'Mengirim...';

            try {
                let rawBase = (window.BASE_URL || '/').trim().replace(/\/+$/, '');
                if (!rawBase.startsWith('/')) rawBase = '/' + rawBase;
                if (rawBase === '/') rawBase = '';
                const endpoint = rawBase + '/guru/gerbang/datang';

                const res = await fetch(endpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert('✅ ' + data.message);
                    closeModalDatang();
                    window.location.reload();
                } else {
                    alert('❌ Gagal Presensi: ' + data.message);
                }
            } catch (err) {
                console.error('Tap datang error:', err);
                alert('Terjadi kesalahan jaringan atau server.');
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Kirim Presensi ➜';
            }
        }

        async function doTapPulangGerbang() {
            const jamBukaPulang = '<?= substr($config['jam_guru_pulang_mulai'] ?? '13:00:00', 0, 5) ?>';
            const now = new Date();
            const curH = String(now.getHours()).padStart(2, '0');
            const curM = String(now.getMinutes()).padStart(2, '0');
            const curTime = `${curH}:${curM}`;

            if (curTime < jamBukaPulang) {
                alert(`⏳ Presensi pulang belum dibuka!\nJam kepulangan guru baru dapat dilakukan sesudah pukul ${jamBukaPulang} WIB.`);
                return;
            }

            if (!confirm('Lakukan Tap Pulang untuk mengakhiri jam kerja sekolah hari ini?')) return;

            const formData = new FormData();
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta) formData.append('csrf_token', csrfMeta.content);
            if (userLat && userLng) {
                formData.append('latitude', userLat);
                formData.append('longitude', userLng);
            } else {
                const bypass = confirm("GPS perangkat belum terdeteksi. Apakah ingin menggunakan bypass GPS untuk mode simulasi?");
                if (bypass) {
                    formData.append('bypass_gps', '1');
                } else {
                    return;
                }
            }

            try {
                let rawBase = (window.BASE_URL || '/').trim().replace(/\/+$/, '');
                if (!rawBase.startsWith('/')) rawBase = '/' + rawBase;
                if (rawBase === '/') rawBase = '';
                const endpoint = rawBase + '/guru/gerbang/pulang';

                const res = await fetch(endpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    alert('✅ ' + data.message);
                    window.location.reload();
                } else {
                    alert('❌ Gagal Tap Pulang: ' + data.message);
                }
            } catch (err) {
                console.error('Tap pulang error:', err);
                alert('Terjadi kesalahan jaringan atau server.');
            }
        }
    </script>

    <!-- Modal Dialog Presensi Datang -->
    <div id="modalDatang" class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-scale-up">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-base font-outfit font-extrabold text-slate-900">Presensi Kedatangan Guru</h3>
                    <p class="text-xs text-slate-500">Pilih status kehadiran Anda hari ini</p>
                </div>
                <button type="button" onclick="closeModalDatang()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 flex items-center justify-center text-sm font-bold">
                    ✕
                </button>
            </div>

            <form id="formTapDatang" onsubmit="submitTapDatang(event)" class="space-y-3">
                <div class="space-y-2">
                    <!-- Opsi HADIR -->
                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/40">
                        <input type="radio" name="status_pilihan" value="HADIR" checked onchange="toggleKeteranganField()" class="mt-1 text-brand-600 focus:ring-brand-500">
                        <div>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                🏫 Hadir di Sekolah
                                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded">Wajib GPS</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">Tiba di gerbang/area sekolah & wajib Tap Pulang di sore hari.</p>
                        </div>
                    </label>

                    <!-- Opsi TUGAS_LUAR -->
                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-purple-500 has-[:checked]:bg-purple-50/40">
                        <input type="radio" name="status_pilihan" value="TUGAS_LUAR" onchange="toggleKeteranganField()" class="mt-1 text-purple-600 focus:ring-purple-500">
                        <div>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                💼 Tugas Luar / Dinas
                                <span class="text-[10px] font-semibold text-purple-700 bg-purple-100 px-1.5 py-0.2 rounded">Bebas GPS</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">Dinas luar sekolah/MGMP/pelatihan. Tidak perlu Tap Pulang.</p>
                        </div>
                    </label>

                    <!-- Opsi SAKIT -->
                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50/40">
                        <input type="radio" name="status_pilihan" value="SAKIT" onchange="toggleKeteranganField()" class="mt-1 text-rose-600 focus:ring-rose-500">
                        <div>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                🏥 Sakit
                                <span class="text-[10px] font-semibold text-rose-700 bg-rose-100 px-1.5 py-0.2 rounded">Bebas GPS</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">Berhalangan karena kondisi kesehatan. Tidak perlu Tap Pulang.</p>
                        </div>
                    </label>

                    <!-- Opsi IZIN -->
                    <label class="flex items-start gap-3 p-3 rounded-2xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50/40">
                        <input type="radio" name="status_pilihan" value="IZIN" onchange="toggleKeteranganField()" class="mt-1 text-blue-600 focus:ring-blue-500">
                        <div>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                ✉️ Izin
                                <span class="text-[10px] font-semibold text-blue-700 bg-blue-100 px-1.5 py-0.2 rounded">Bebas GPS</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">Keperluan keluarga/pribadi mendesak. Tidak perlu Tap Pulang.</p>
                        </div>
                    </label>
                </div>

                <!-- Input Keterangan Alasan (Wajib jika Non-Hadir) -->
                <div id="keteranganWrapper" class="space-y-1 hidden">
                    <label class="block text-xs font-bold text-slate-700" id="keteranganLabel">
                        Keterangan / Alasan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="keterangan" id="inputKeterangan" rows="2" 
                              class="w-full text-xs p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-brand-500 focus:outline-none"
                              placeholder="Contoh: Mengikuti Bimtek Kurikulum di Balai Diklat / Demam / Izin keperluan keluarga"></textarea>
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="button" onclick="closeModalDatang()" class="flex-1 py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitDatang" class="flex-1 py-2.5 px-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-soft-sm transition">
                        Kirim Presensi ➜
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL NOTIFIKASI & PENGUMUMAN GURU -->
    <div id="notifGuruModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh] animate-scale-up">
            <!-- Header Modal -->
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg shadow-xs">
                        🔔
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Pemberitahuan & Pengumuman</h3>
                        <p class="text-[11px] text-slate-500">Pesan dari Admin TU / Sekolah</p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <?php if (!empty($notifikasiList)): ?>
                    <button type="button" onclick="markAllNotifRead()" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-2.5 py-1 rounded-xl transition cursor-pointer">
                        Baca Semua
                    </button>
                    <?php endif; ?>
                    <button type="button" onclick="closeNotifModal()" class="w-8 h-8 rounded-full bg-slate-200/70 hover:bg-slate-300 text-slate-600 flex items-center justify-center text-sm font-bold transition cursor-pointer">
                        ✕
                    </button>
                </div>
            </div>

            <!-- List Notifikasi -->
            <div id="guruNotifList" class="p-4 overflow-y-auto space-y-3 flex-1">
                <?php if (empty($notifikasiList)): ?>
                <div class="text-center py-10">
                    <div class="text-3xl mb-2 text-slate-300">📭</div>
                    <div class="text-xs font-bold text-slate-600">Belum Ada Notifikasi</div>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pengumuman penting akan muncul di sini.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($notifikasiList as $n): ?>
                    <?php
                        $isUnread = ((int)$n['is_read'] === 0);
                        $tColors = [
                            'penting'    => 'bg-rose-50 text-rose-700 border-rose-200',
                            'akademik'   => 'bg-blue-50 text-blue-700 border-blue-200',
                            'kegiatan'   => 'bg-purple-50 text-purple-700 border-purple-200',
                            'info'       => 'bg-amber-50 text-amber-700 border-amber-200',
                            'pengumuman' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        ];
                        $badgeStyle = $tColors[$n['tipe']] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                    ?>
                    <div id="notif-item-<?= $n['id'] ?>" class="p-3.5 rounded-2xl border transition relative <?= $isUnread ? 'bg-rose-50/40 border-rose-200' : 'bg-white border-slate-200/80' ?>">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded-md border uppercase tracking-wider <?= $badgeStyle ?>">
                                <?= htmlspecialchars($n['tipe']) ?>
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">
                                <?= date('d M, H:i', strtotime($n['created_at'])) ?>
                            </span>
                        </div>
                        <h4 class="font-bold text-slate-900 text-xs leading-snug">
                            <?= htmlspecialchars($n['judul']) ?>
                        </h4>
                        <p class="text-[11px] text-slate-600 mt-1 leading-relaxed whitespace-pre-line">
                            <?= htmlspecialchars($n['pesan']) ?>
                        </p>
                        
                        <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-slate-100 text-[10px]">
                            <span class="text-slate-400">Dari: <strong class="text-slate-600"><?= htmlspecialchars($n['pengirim'] ?? 'Admin') ?></strong></span>
                            <div class="flex items-center gap-2">
                                <?php if (!empty($n['link_url'])): ?>
                                <a href="<?= htmlspecialchars($n['link_url']) ?>" target="_blank" class="text-blue-600 font-bold hover:underline">
                                    🔗 Buka Tautan
                                </a>
                                <?php endif; ?>
                                <?php if ($isUnread): ?>
                                <button type="button" onclick="markNotifRead(<?= $n['id'] ?>)" class="text-emerald-700 bg-emerald-100/70 hover:bg-emerald-200 font-bold px-2 py-0.5 rounded-lg transition cursor-pointer">
                                    ✓ Tandai Dibaca
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function openNotifModal() {
            document.getElementById('notifGuruModal').classList.remove('hidden');
        }

        function closeNotifModal() {
            document.getElementById('notifGuruModal').classList.add('hidden');
        }

        function markNotifRead(id) {
            fetch('<?= App::baseUrl('guru/notifikasi/read/') ?>' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const item = document.getElementById('notif-item-' + id);
                    if (item) {
                        item.classList.remove('bg-rose-50/40', 'border-rose-200');
                        item.classList.add('bg-white', 'border-slate-200/80');
                        // Hide button "Tandai Dibaca"
                        const btn = item.querySelector('button[onclick*="markNotifRead"]');
                        if (btn) btn.remove();
                    }
                    updateNotifBadge(data.unread_count);
                }
            })
            .catch(err => console.error(err));
        }

        function markAllNotifRead() {
            fetch('<?= App::baseUrl('guru/notifikasi/read-all') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const items = document.querySelectorAll('[id^="notif-item-"]');
                    items.forEach(el => {
                        el.classList.remove('bg-rose-50/40', 'border-rose-200');
                        el.classList.add('bg-white', 'border-slate-200/80');
                        const btn = el.querySelector('button[onclick*="markNotifRead"]');
                        if (btn) btn.remove();
                    });
                    updateNotifBadge(0);
                }
            })
            .catch(err => console.error(err));
        }

        function updateNotifBadge(count) {
            const badge = document.getElementById('guruNotifBadge');
            if (badge) {
                if (count > 0) {
                    badge.innerText = count;
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');
                } else {
                    badge.innerText = '0';
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }
            }
        }

        // Web Audio Synthesizer: Friendly two-tone chime for new notifications
        function playNotifChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                const now = ctx.currentTime;
                
                // Tone 1: E5 (659Hz)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(659.25, now);
                gain1.gain.setValueAtTime(0.12, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.3);

                // Tone 2: G#5 (830Hz)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(830.61, now + 0.1);
                gain2.gain.setValueAtTime(0.15, now + 0.1);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.1);
                osc2.stop(now + 0.5);
            } catch(e) {}
        }

        // Floating Realtime Toast Popup
        function showLiveNotificationToast(notif) {
            if (!notif) return;
            let toast = document.getElementById('liveGuruNotifToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'liveGuruNotifToast';
                toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] w-[92%] max-w-md transition-all duration-300 transform -translate-y-20 opacity-0 pointer-events-auto';
                document.body.appendChild(toast);
            }

            const tipeIcons = {
                'penting': '🚨',
                'akademik': '📚',
                'kegiatan': '🗓️',
                'info': 'ℹ️',
                'pengumuman': '📢'
            };
            const icon = tipeIcons[notif.tipe] || '🔔';

            toast.innerHTML = `
                <div onclick="openNotifModal(); hideLiveNotificationToast();" class="p-3.5 rounded-2xl bg-slate-900/95 backdrop-blur-md text-white shadow-2xl border border-slate-700 flex items-start gap-3 cursor-pointer hover:bg-slate-900 transition">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center text-xl flex-shrink-0 animate-pulse">
                        ${icon}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-0.5">
                            <span class="text-[9px] font-black uppercase tracking-wider text-rose-400 bg-rose-500/20 px-1.5 py-0.2 rounded">
                                ${notif.tipe || 'PEMBERITAHUAN'} BARU
                            </span>
                            <span class="text-[10px] text-slate-400">Baru saja</span>
                        </div>
                        <h4 class="font-bold text-white text-xs leading-snug truncate">${notif.judul || 'Pengumuman Baru'}</h4>
                        <p class="text-[11px] text-slate-300 line-clamp-1 mt-0.5">${notif.pesan || ''}</p>
                    </div>
                    <button type="button" onclick="event.stopPropagation(); hideLiveNotificationToast();" class="text-slate-400 hover:text-white text-sm font-bold p-1">
                        ✕
                    </button>
                </div>
            `;

            toast.classList.remove('-translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            if (window._guruToastTimeout) clearTimeout(window._guruToastTimeout);
            window._guruToastTimeout = setTimeout(hideLiveNotificationToast, 6000);
        }

        function hideLiveNotificationToast() {
            const toast = document.getElementById('liveGuruNotifToast');
            if (toast) {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-20', 'opacity-0');
            }
        }

        // Muat ulang daftar notifikasi guru secara dinamis
        function loadGuruNotifList() {
            fetch('<?= App::baseUrl('guru/notifikasi/list') ?>', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && Array.isArray(data.data)) {
                    const listEl = document.getElementById('guruNotifList');
                    if (!listEl) return;
                    if (data.data.length === 0) {
                        listEl.innerHTML = `
                            <div class="text-center py-10">
                                <div class="text-3xl mb-2 text-slate-300">📭</div>
                                <div class="text-xs font-bold text-slate-600">Belum Ada Notifikasi</div>
                                <p class="text-[11px] text-slate-400 mt-0.5">Pengumuman penting akan muncul di sini.</p>
                            </div>
                        `;
                    } else {
                        const tipeBadges = {
                            'penting':    'bg-rose-50 text-rose-700 border-rose-200',
                            'akademik':   'bg-blue-50 text-blue-700 border-blue-200',
                            'kegiatan':   'bg-purple-50 text-purple-700 border-purple-200',
                            'info':       'bg-amber-50 text-amber-700 border-amber-200',
                            'pengumuman': 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        };
                        listEl.innerHTML = data.data.map(n => {
                            const isUnread = parseInt(n.is_read) === 0;
                            const badgeCls = tipeBadges[n.tipe] || 'bg-slate-50 text-slate-700 border-slate-200';
                            const tgl = new Date(n.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', hour:'2-digit', minute:'2-digit'});
                            return `
                                <div id="notif-item-${n.id}" class="p-3.5 rounded-2xl border transition relative ${isUnread ? 'bg-rose-50/40 border-rose-200' : 'bg-white border-slate-200/80'}">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-md border uppercase tracking-wider ${badgeCls}">
                                            ${n.tipe}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono">${tgl}</span>
                                    </div>
                                    <h4 class="font-bold text-slate-900 text-xs leading-snug">${n.judul}</h4>
                                    <p class="text-[11px] text-slate-600 mt-1 leading-relaxed whitespace-pre-line">${n.pesan}</p>
                                    <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-slate-100 text-[10px]">
                                        <span class="text-slate-400">Dari: <strong class="text-slate-600">${n.pengirim || 'Admin'}</strong></span>
                                        <div class="flex items-center gap-2">
                                            ${n.link_url ? `<a href="${n.link_url}" target="_blank" class="text-blue-600 font-bold hover:underline">🔗 Buka Tautan</a>` : ''}
                                            ${isUnread ? `<button type="button" onclick="markNotifRead(${n.id})" class="text-emerald-700 bg-emerald-100/70 hover:bg-emerald-200 font-bold px-2 py-0.5 rounded-lg transition cursor-pointer">✓ Tandai Dibaca</button>` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    }
                    updateNotifBadge(data.unread_count || 0);
                }
            })
            .catch(err => console.error(err));
        }
    </script>
</body>
</html>

<?php
use App\Config\App;
use App\Helpers\TimeHelper;

$activeNav = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin & TU - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Modern Admin Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div>
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Dashboard Administrator & TU</h1>
                <p class="text-xs text-slate-500 mt-0.5">Sistem Informasi Presensi & Penggajian Terintegrasi <?= htmlspecialchars($config['nama_sekolah']) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <?php if (!empty($activeTapel)): ?>
                <a href="<?= App::baseUrl('admin/tahun-pelajaran') ?>" class="inline-flex items-center gap-2 bg-brand-50 border border-brand-200 text-brand-700 text-xs font-semibold px-3 py-1.5 rounded-xl hover:bg-brand-100 transition" title="Kelola Tahun Pelajaran">
                    <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>TP: <?= htmlspecialchars($activeTapel['tahun_ajaran']) ?> (<?= htmlspecialchars($activeTapel['semester']) ?>)</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </a>
                <?php endif; ?>
                <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold px-3 py-1.5 rounded-xl">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span><?= TimeHelper::formatDateIndonesian(date('Y-m-d')) ?></span>
                </div>
                <a href="<?= App::baseUrl('piket/scanner') ?>" target="_blank" class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-3.5 py-1.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Scanner Gerbang</span>
                </a>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <!-- Quick Stat KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Card 1: Guru -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Guru</span>
                        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-display font-extrabold text-slate-900"><?= count($allGuru) ?></div>
                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                            <span class="text-emerald-600 font-semibold">Aktif KBM</span> • Guru & Pegawai
                        </div>
                    </div>
                </div>

                <!-- Card 2: Siswa -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Siswa</span>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-display font-extrabold text-slate-900"><?= count($allSiswa) ?></div>
                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                            <span class="text-brand-600 font-semibold">QR Terpasang</span> • Terdaftar
                        </div>
                    </div>
                </div>

                <!-- Card 3: Rombel / Kelas -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Rombongan Belajar</span>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-display font-extrabold text-slate-900"><?= count($allKelas) ?></div>
                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                            <span class="text-amber-600 font-semibold">Kelas</span> • X, XI & XII
                        </div>
                    </div>
                </div>

                <!-- Card 4: Radius GPS -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Radius Geofence GPS</span>
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-display font-extrabold text-slate-900"><?= (int)$config['radius_meter'] ?> <span class="text-sm font-semibold text-slate-500">meter</span></div>
                        <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                            <span class="text-emerald-600 font-semibold">Aktif</span> • Haversine Guard
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monitoring Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Real-time Guru Today Panel -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h2 class="font-display font-bold text-base text-slate-900">Realisasi KBM Guru Hari Ini</h2>
                            </div>
                            <a href="<?= App::baseUrl('admin/monitoring/guru') ?>" class="text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-lg transition">
                                Lihat Rincian →
                            </a>
                        </div>

                        <div class="grid grid-cols-2 gap-3 my-4">
                            <div class="bg-emerald-50/70 border border-emerald-200/70 rounded-xl p-4 text-center">
                                <div class="text-3xl font-display font-extrabold text-emerald-700"><?= $guruCheckinCount ?></div>
                                <div class="text-xs font-semibold text-emerald-800 mt-1">Check-in Sesi Mengajar</div>
                            </div>
                            <div class="bg-rose-50/70 border border-rose-200/70 rounded-xl p-4 text-center">
                                <div class="text-3xl font-display font-extrabold text-rose-700"><?= $guruTelatCount ?></div>
                                <div class="text-xs font-semibold text-rose-800 mt-1">Terlambat Mengajar</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-3 text-xs text-slate-500 flex items-start gap-2">
                        <svg class="w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Potongan denda keterlambatan <strong>Rp 125/menit</strong> dihitung otomatis per menit dari selisih jadwal dengan check-in riil guru.</span>
                    </div>
                </div>

                <!-- Real-time Siswa Gerbang Panel -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                </div>
                                <h2 class="font-display font-bold text-base text-slate-900">Presensi Gerbang Dua Arah</h2>
                            </div>
                            <a href="<?= App::baseUrl('admin/monitoring/siswa') ?>" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                                Lihat Rincian →
                            </a>
                        </div>

                        <div class="grid grid-cols-3 gap-3 my-4">
                            <div class="bg-blue-50/70 border border-blue-200/70 rounded-xl p-3 text-center">
                                <div class="text-2xl font-display font-extrabold text-blue-700"><?= $siswaDatangCount ?></div>
                                <div class="text-[11px] font-semibold text-blue-800 mt-1">Tap-In Datang</div>
                            </div>
                            <div class="bg-amber-50/70 border border-amber-200/70 rounded-xl p-3 text-center">
                                <div class="text-2xl font-display font-extrabold text-amber-700"><?= $siswaPulangCount ?></div>
                                <div class="text-[11px] font-semibold text-amber-800 mt-1">Tap-Out Pulang</div>
                            </div>
                            <div class="bg-emerald-50/70 border border-emerald-200/70 rounded-xl p-3 text-center">
                                <div class="text-2xl font-display font-extrabold text-emerald-700"><?= $siswaHadirLengkap ?></div>
                                <div class="text-[11px] font-semibold text-emerald-800 mt-1">Hadir Dua Arah</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200/60 rounded-xl p-3 text-xs text-slate-500 flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Siswa yang hadir pagi namun <strong>tidak tap-out</strong> saat pulang sekolah akan otomatis gugur menjadi status <strong>ALPHA</strong>.</span>
                    </div>
                </div>

            </div>

            <!-- Quick Action Shortcuts -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Aksi Cepat Tata Usaha</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="<?= App::baseUrl('admin/siswa/cetak-kartu') ?>" target="_blank" 
                       class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-brand-500 hover:bg-brand-50/50 hover:shadow-soft-sm transition group">
                        <div class="w-10 h-10 rounded-lg bg-brand-50 group-hover:bg-brand-600 text-brand-600 group-hover:text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 group-hover:text-brand-900">Cetak Kartu Siswa</div>
                            <div class="text-[11px] text-slate-400">Layout A4 Siap Cetak</div>
                        </div>
                    </a>

                    <a href="<?= App::baseUrl('payroll') ?>" 
                       class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 hover:shadow-soft-sm transition group">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 group-hover:bg-emerald-600 text-emerald-600 group-hover:text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 group-hover:text-emerald-900">Rekapitulasi Gaji</div>
                            <div class="text-[11px] text-slate-400">Slip PDF Otomatis</div>
                        </div>
                    </a>

                    <a href="<?= App::baseUrl('admin/geofence') ?>" 
                       class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/50 hover:shadow-soft-sm transition group">
                        <div class="w-10 h-10 rounded-lg bg-indigo-50 group-hover:bg-indigo-600 text-indigo-600 group-hover:text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 group-hover:text-indigo-900">Koordinat GPS</div>
                            <div class="text-[11px] text-slate-400">Titik Geofence Sekolah</div>
                        </div>
                    </a>

                    <a href="<?= App::baseUrl('admin/buku-induk') ?>" 
                       class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-violet-500 hover:bg-violet-50/50 hover:shadow-soft-sm transition group">
                        <div class="w-10 h-10 rounded-lg bg-violet-50 group-hover:bg-violet-600 text-violet-600 group-hover:text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 group-hover:text-violet-900">Buku Induk Siswa</div>
                            <div class="text-[11px] text-slate-400">Import Dapodik & Transkrip</div>
                        </div>
                    </a>

                    <a href="<?= App::baseUrl('admin/mapel') ?>" 
                       class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-teal-500 hover:bg-teal-50/50 hover:shadow-soft-sm transition group">
                        <div class="w-10 h-10 rounded-lg bg-teal-50 group-hover:bg-teal-600 text-teal-600 group-hover:text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 group-hover:text-teal-900">Mata Pelajaran</div>
                            <div class="text-[11px] text-slate-400">Kurikulum & Kelompok</div>
                        </div>
                    </a>

                    <a href="<?= App::baseUrl('admin/kelas') ?>" 
                       class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-amber-500 hover:bg-amber-50/50 hover:shadow-soft-sm transition group">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 group-hover:bg-amber-600 text-amber-600 group-hover:text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 group-hover:text-amber-900">Rombel & Wali Kelas</div>
                            <div class="text-[11px] text-slate-400">Plotting & Penugasan</div>
                        </div>
                    </a>

                    <a href="<?= App::baseUrl('admin/surat') ?>" 
                       class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 hover:border-sky-500 hover:bg-sky-50/50 hover:shadow-soft-sm transition group">
                        <div class="w-10 h-10 rounded-lg bg-sky-50 group-hover:bg-sky-600 text-sky-600 group-hover:text-white flex items-center justify-center transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-slate-800 group-hover:text-sky-900">Format Surat & SPD</div>
                            <div class="text-[11px] text-slate-400">Kop & TTD Permendiknas</div>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </main>

</body>
</html>

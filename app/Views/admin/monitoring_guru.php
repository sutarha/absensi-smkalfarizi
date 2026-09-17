<?php
use App\Config\App;
use App\Helpers\TimeHelper;

$activeNav = 'monitoring_guru';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Guru Real-Time - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
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
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Monitoring KBM Guru Real-Time</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pantau realisasi check-in guru vs jadwal, jarak GPS toleransi, dan pemotongan denda telat otomatis</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold shadow-soft-sm">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span>LIVE SYNC</span>
                    <span class="text-slate-300">|</span>
                    <span id="syncIndicatorText" class="text-slate-600 font-medium">Aktif (<span id="syncCountdown">3</span>s)</span>
                    <button type="button" id="btnToggleSync" onclick="toggleSync()" class="ml-1 px-2 py-0.5 rounded-md bg-emerald-200/60 hover:bg-emerald-200 text-emerald-900 text-[10px] font-bold transition">
                        Jeda
                    </button>
                </div>

                <form method="GET" action="<?= App::baseUrl('admin/monitoring/guru') ?>" class="flex items-center gap-2">
                    <div class="relative">
                        <input type="date" name="tanggal" id="filterTanggal" value="<?= htmlspecialchars($tanggal) ?>" 
                               class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" 
                               onchange="this.form.submit()">
                    </div>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-soft-sm transition">
                        Tampilkan
                    </button>
                    <a href="<?= App::baseUrl('admin/monitoring/guru/export?tanggal=' . urlencode($tanggal)) ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-soft-sm transition flex items-center gap-1.5">
                        <span>📊</span>
                        <span>Export Excel</span>
                    </a>
                </form>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between shadow-soft-sm">
                <div class="flex items-center gap-2">
                    <span class="text-base">✅</span>
                    <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <!-- STATISTIK GERBANG GURU HARI INI -->
            <?php
            $cntHadirLengkap = 0;
            $cntDiSekolah = 0;
            $cntTugasLuar = 0;
            $cntIzinSakit = 0;
            $cntBelum = 0;
            if (!empty($presensiGerbangGuruList)) {
                foreach ($presensiGerbangGuruList as $pg) {
                    if ($pg['status_kehadiran'] === 'HADIR' || $pg['status_kehadiran'] === 'TERLAMBAT') {
                        if (!empty($pg['waktu_pulang'])) {
                            $cntHadirLengkap++;
                        } else {
                            $cntDiSekolah++;
                        }
                    } elseif ($pg['status_kehadiran'] === 'TUGAS_LUAR') {
                        $cntTugasLuar++;
                    } elseif (in_array($pg['status_kehadiran'], ['IZIN', 'SAKIT'])) {
                        $cntIzinSakit++;
                    } else {
                        $cntBelum++;
                    }
                }
            }
            ?>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statHadirLengkap" class="text-2xl font-extrabold font-display text-emerald-600"><?= $cntHadirLengkap ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>✅</span> Hadir Lengkap (2 Arah)
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statDiSekolah" class="text-2xl font-extrabold font-display text-sky-600"><?= $cntDiSekolah ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>☀️</span> Di Sekolah (Menunggu Pulang)
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statTugasLuar" class="text-2xl font-extrabold font-display text-purple-600"><?= $cntTugasLuar ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>💼</span> Tugas Luar / Dinas
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statIzinSakit" class="text-2xl font-extrabold font-display text-blue-600"><?= $cntIzinSakit ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>✉️</span> Izin / Sakit
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statBelum" class="text-2xl font-extrabold font-display text-slate-400"><?= $cntBelum ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>⚪</span> Belum Presensi
                    </div>
                </div>
            </div>

            <!-- TABEL 1: PRESENSI GERBANG KEHADIRAN SEKOLAH (MANDIRI GPS & NON-HADIR) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-slate-50 to-white">
                    <div>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="text-base">📍</span>
                            Presensi Gerbang Sekolah Guru: <strong class="text-slate-800 font-bold"><?= TimeHelper::formatDateIndonesian($tanggal) ?></strong>
                        </span>
                        <p class="text-[11px] text-slate-400 mt-0.5">Memantau catatan jam datang, jam pulang mandiri GPS, serta izin/tugas luar guru</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="<?= App::baseUrl('admin/monitoring/evaluasi-gerbang') ?>" onsubmit="return confirm('Jalankan audit evaluasi gerbang harian sekarang? Guru yang hanya tap datang tanpa tap pulang akan dikunci statusnya menjadi ALPHA.')">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                            <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-soft-sm transition flex items-center gap-1.5">
                                <span>⚖️</span>
                                <span>Audit Evaluasi Sore (17:00)</span>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">Nama Guru & NIP</th>
                                <th class="py-3.5 px-4">Jam Datang (Tap-In)</th>
                                <th class="py-3.5 px-4">Jam Pulang (Tap-Out)</th>
                                <th class="py-3.5 px-4">Status Kehadiran</th>
                                <th class="py-3.5 px-4">Keterangan / Tugas / Lokasi</th>
                                <th class="py-3.5 px-4 w-20 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyGerbangGuru" class="divide-y divide-slate-100 text-xs">
                            <?php if (empty($presensiGerbangGuruList)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-10 text-slate-400">
                                    Tidak ada data guru yang terdaftar.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($presensiGerbangGuruList as $g): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($g['nama_lengkap']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">NIP: <?= htmlspecialchars($g['nik_nip']) ?></div>
                                        <?php if (!empty($g['tugas_tambahan'])): ?>
                                            <span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                                <?= htmlspecialchars($g['tugas_tambahan']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($g['waktu_datang'])): ?>
                                            <div class="font-mono font-bold text-slate-900">
                                                <?= date('H:i:s', strtotime($g['waktu_datang'])) ?> WIB
                                            </div>
                                            <?php if (!empty($g['jarak_datang_meter']) && (float)$g['jarak_datang_meter'] > 0): ?>
                                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                                    📍 <?= round($g['jarak_datang_meter']) ?>m dari sekolah
                                                </div>
                                            <?php endif; ?>
                                            <?php if ((int)$g['menit_terlambat_datang'] > 0): ?>
                                                <span class="inline-block text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200 mt-0.5">
                                                    Telat <?= (int)$g['menit_terlambat_datang'] ?> mnt
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($g['waktu_pulang'])): ?>
                                            <div class="font-mono font-bold text-slate-900">
                                                <?= date('H:i:s', strtotime($g['waktu_pulang'])) ?> WIB
                                            </div>
                                            <?php if (!empty($g['jarak_pulang_meter']) && (float)$g['jarak_pulang_meter'] > 0): ?>
                                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                                    📍 <?= round($g['jarak_pulang_meter']) ?>m dari sekolah
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif (in_array($g['status_kehadiran'], ['TUGAS_LUAR', 'IZIN', 'SAKIT'])): ?>
                                            <span class="text-[11px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                                                Bebas Tap Pulang
                                            </span>
                                        <?php elseif (!empty($g['waktu_datang'])): ?>
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Belum Pulang
                                            </span>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($g['status_kehadiran'] === 'HADIR'): ?>
                                            <?php if (!empty($g['waktu_pulang'])): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    ✅ Hadir Lengkap
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200 animate-pulse">
                                                    ☀️ Di Sekolah
                                                </span>
                                            <?php endif; ?>
                                        <?php elseif ($g['status_kehadiran'] === 'TERLAMBAT'): ?>
                                            <?php if (!empty($g['waktu_pulang'])): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    ⏱️ Hadir (Terlambat)
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                    ⏱️ Terlambat (Di Sekolah)
                                                </span>
                                            <?php endif; ?>
                                        <?php elseif ($g['status_kehadiran'] === 'TUGAS_LUAR'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                💼 Tugas Luar / Dinas
                                            </span>
                                        <?php elseif ($g['status_kehadiran'] === 'SAKIT'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                🏥 Sakit
                                            </span>
                                        <?php elseif ($g['status_kehadiran'] === 'IZIN'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                ✉️ Izin
                                            </span>
                                        <?php elseif ($g['status_kehadiran'] === 'ALPHA'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
                                                ❌ Alpha
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                                ⚪ Belum Presensi
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <?= !empty($g['keterangan']) ? htmlspecialchars($g['keterangan']) : '<span class="text-slate-300">-</span>' ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <button onclick="openModalEditGerbang(<?= htmlspecialchars(json_encode([
                                                'guru_id' => $g['guru_id'],
                                                'waktu_datang' => $g['waktu_datang'] ? date('H:i', strtotime($g['waktu_datang'])) : '',
                                                'waktu_pulang' => $g['waktu_pulang'] ? date('H:i', strtotime($g['waktu_pulang'])) : '',
                                                'status_kehadiran' => $g['status_kehadiran'],
                                                'menit_terlambat_datang' => $g['menit_terlambat_datang'] ?? 0,
                                                'keterangan' => $g['keterangan'] ?? '',
                                                'nama_guru' => $g['nama_lengkap']
                                            ])) ?>)" class="px-2 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded text-[10px] font-bold transition">
                                                Edit
                                            </button>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABEL 2: REALISASI SESI MENGAJAR KBM DI KELAS -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="text-base">📖</span>
                        Realisasi Sesi Mengajar KBM: <strong class="text-slate-800 font-bold"><?= TimeHelper::formatDateIndonesian($tanggal) ?></strong>
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Total Jadwal KBM: <strong id="statTotalSesi" class="text-slate-800 font-bold"><?= count($monitoringList) ?></strong> sesi</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">Jam Jadwal</th>
                                <th class="py-3.5 px-4">Guru Pengampu</th>
                                <th class="py-3.5 px-4">Mapel & Rombel</th>
                                <th class="py-3.5 px-4">Waktu Check-in</th>
                                <th class="py-3.5 px-4">Keterlambatan</th>
                                <th class="py-3.5 px-4">Potongan Denda</th>
                                <th class="py-3.5 px-4">Validasi GPS</th>
                                <th class="py-3.5 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyKbmSessions" class="divide-y divide-slate-100 text-xs">
                            <?php if (empty($monitoringList)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400">
                                    <div class="text-2xl mb-1">⏱️</div>
                                    <p class="font-medium">Tidak ada jadwal KBM pada hari ini (<?= TimeHelper::formatDateIndonesian($tanggal) ?>).</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($monitoringList as $m): ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-mono font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-100 text-[11px] inline-block">
                                            <?= substr($m['jam_mulai'], 0, 5) ?> - <?= substr($m['jam_selesai'], 0, 5) ?>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-1"><?= (int)$m['jumlah_jp'] ?> JP (<?= (int)$m['jumlah_jp'] * 40 ?> mnt)</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($m['nama_guru']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">NIP: <?= htmlspecialchars($m['nik_nip']) ?></div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($m['nama_mapel']) ?></div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 mt-0.5">
                                            <?= htmlspecialchars($m['nama_kelas']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($m['waktu_checkin'])): ?>
                                            <div class="font-mono font-bold text-slate-900">
                                                <?= date('H:i:s', strtotime($m['waktu_checkin'])) ?>
                                            </div>
                                            <?php if (!empty($m['waktu_checkout'])): ?>
                                                <div class="text-[11px] font-semibold text-emerald-600 mt-0.5">Selesai: <?= date('H:i', strtotime($m['waktu_checkout'])) ?></div>
                                            <?php else: ?>
                                                <div class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 mt-0.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    Sedang KBM
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php
                                            $isPastClass = ($tanggal < date('Y-m-d')) || ($tanggal === date('Y-m-d') && date('H:i:s') >= $m['jam_selesai']);
                                            ?>
                                            <?php if ($isPastClass): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    ❌ Tidak Hadir (Sesi Berakhir)
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Belum Check-in
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($m['waktu_checkin'])): ?>
                                            <?php if ((int)$m['menit_terlambat'] > 0): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Telat <?= (int)$m['menit_terlambat'] ?> mnt
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Tepat Waktu
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($m['waktu_checkin'])): ?>
                                            <?php if ((int)$m['menit_terlambat'] > 0): ?>
                                                <span class="font-display font-bold text-rose-600">
                                                    - <?= TimeHelper::formatRupiah($m['menit_terlambat'] * $config['denda_per_menit']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="font-semibold text-emerald-600">Rp 0</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($m['waktu_checkin'])): ?>
                                            <span class="inline-flex items-center gap-1 font-mono text-[11px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-100" title="Koordinat: <?= $m['lat_checkin'] ?>, <?= $m['long_checkin'] ?>">
                                                📍 <?= (float)$m['jarak_meter'] ?> m
                                            </span>
                                            <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">✓ <?= htmlspecialchars($m['status_verifikasi']) ?></div>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <button onclick="openModalEditSesi(<?= htmlspecialchars(json_encode([
                                                'jadwal_id' => $m['jadwal_id'],
                                                'guru_id' => $m['guru_id'],
                                                'waktu_checkin' => $m['waktu_checkin'] ? date('H:i', strtotime($m['waktu_checkin'])) : '',
                                                'waktu_checkout' => $m['waktu_checkout'] ? date('H:i', strtotime($m['waktu_checkout'])) : '',
                                                'menit_terlambat' => $m['menit_terlambat'],
                                                'nama_guru' => $m['nama_guru'],
                                                'nama_mapel' => $m['nama_mapel']
                                            ])) ?>)" class="px-2 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 rounded text-[10px] font-bold transition">
                                                Edit
                                            </button>
                                        <?php else: ?>
                                            <span class="text-slate-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- REAL-TIME LIVE SYNC SCRIPT -->
    <script>
    let isSyncActive = true;
    let countdownSec = 3;
    const POLLING_INTERVAL_SEC = 3;
    const filterTanggal = document.getElementById('filterTanggal')?.value || '<?= date('Y-m-d') ?>';

    function toggleSync() {
        isSyncActive = !isSyncActive;
        const btn = document.getElementById('btnToggleSync');
        const text = document.getElementById('syncIndicatorText');
        if (isSyncActive) {
            btn.textContent = 'Jeda';
            btn.className = 'ml-1 px-2 py-0.5 rounded-md bg-emerald-200/60 hover:bg-emerald-200 text-emerald-900 text-[10px] font-bold transition';
            text.innerHTML = `Aktif (<span id="syncCountdown">${countdownSec}</span>s)`;
        } else {
            btn.textContent = 'Lanjutkan';
            btn.className = 'ml-1 px-2 py-0.5 rounded-md bg-amber-200 text-amber-900 text-[10px] font-bold transition';
            text.innerHTML = '<span class="text-amber-600 font-bold">Dijeda</span>';
        }
    }

    // Countdown ticker
    setInterval(() => {
        if (!isSyncActive) return;
        countdownSec--;
        const cntEl = document.getElementById('syncCountdown');
        if (cntEl) cntEl.textContent = countdownSec;

        if (countdownSec <= 0) {
            countdownSec = POLLING_INTERVAL_SEC;
            fetchLiveMonitoring();
        }
    }, 1000);

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    async function fetchLiveMonitoring() {
        if (document.hidden) return; // Hemat resource saat tab tidak aktif

        try {
            const res = await fetch(`<?= App::baseUrl('admin/monitoring/guru-live') ?>?tanggal=${encodeURIComponent(filterTanggal)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success) return;

            // 1. Update Counters
            if (data.counters) {
                const c = data.counters;
                const updateEl = (id, val) => {
                    const el = document.getElementById(id);
                    if (el && el.textContent != val) {
                        el.textContent = val;
                        el.classList.add('scale-110', 'transition-transform');
                        setTimeout(() => el.classList.remove('scale-110'), 400);
                    }
                };
                updateEl('statHadirLengkap', c.hadir_lengkap);
                updateEl('statDiSekolah', c.di_sekolah);
                updateEl('statTugasLuar', c.tugas_luar);
                updateEl('statIzinSakit', c.izin_sakit);
                updateEl('statBelum', c.belum_hadir);
                updateEl('statTotalSesi', c.total_sesi);
            }

            // 2. Update Tabel 1: Gerbang Guru
            if (data.gerbang_list) {
                const tbody = document.getElementById('tbodyGerbangGuru');
                if (tbody && data.gerbang_list.length > 0) {
                    let rowsHtml = '';
                    data.gerbang_list.forEach((g, idx) => {
                        let waktuDatang = g.waktu_datang ? `<div class="font-mono font-bold text-slate-900">${g.waktu_datang.substring(11, 19)} WIB</div>` : '<span class="text-slate-400">-</span>';
                        if (g.waktu_datang && g.jarak_datang_meter > 0) {
                            waktuDatang += `<div class="text-[10px] text-slate-500 font-mono mt-0.5">📍 ${Math.round(g.jarak_datang_meter)}m dari sekolah</div>`;
                        }
                        if (g.menit_terlambat_datang > 0) {
                            waktuDatang += `<span class="inline-block text-[10px] font-bold text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200 mt-0.5">Telat ${g.menit_terlambat_datang} mnt</span>`;
                        }

                        let waktuPulang = '<span class="text-slate-400">-</span>';
                        if (g.waktu_pulang) {
                            waktuPulang = `<div class="font-mono font-bold text-slate-900">${g.waktu_pulang.substring(11, 19)} WIB</div>`;
                            if (g.jarak_pulang_meter > 0) {
                                waktuPulang += `<div class="text-[10px] text-slate-500 font-mono mt-0.5">📍 ${Math.round(g.jarak_pulang_meter)}m dari sekolah</div>`;
                            }
                        } else if (['TUGAS_LUAR', 'IZIN', 'SAKIT'].includes(g.status_kehadiran)) {
                            waktuPulang = '<span class="text-[11px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">Bebas Tap Pulang</span>';
                        } else if (g.waktu_datang) {
                            waktuPulang = '<span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Belum Pulang</span>';
                        }

                        let badge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">⚪ Belum Presensi</span>';
                        if (g.status_kehadiran === 'HADIR') {
                            badge = g.waktu_pulang ? '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✅ Hadir Lengkap</span>' : '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200 animate-pulse">☀️ Di Sekolah</span>';
                        } else if (g.status_kehadiran === 'TERLAMBAT') {
                            badge = g.waktu_pulang ? '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">⏱️ Hadir (Terlambat)</span>' : '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">⏱️ Telat (Di Sekolah)</span>';
                        } else if (g.status_kehadiran === 'TUGAS_LUAR') {
                            badge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">💼 Tugas Luar</span>';
                        } else if (g.status_kehadiran === 'SAKIT') {
                            badge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">🏥 Sakit</span>';
                        } else if (g.status_kehadiran === 'IZIN') {
                            badge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">✉️ Izin</span>';
                        } else if (g.status_kehadiran === 'ALPHA') {
                            badge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">❌ Alpha</span>';
                        }

                        let tugasBadges = g.tugas_tambahan ? `<span class="inline-block mt-0.5 px-1.5 py-0.2 rounded text-[10px] font-semibold bg-sky-50 text-sky-700 border border-sky-200">${escapeHtml(g.tugas_tambahan)}</span>` : '';
                        let ket = g.keterangan ? escapeHtml(g.keterangan) : '<span class="text-slate-300">-</span>';

                        let aksiCol = '<span class="text-slate-400">-</span>';
                        <?php if ($user['role'] === 'admin'): ?>
                            const gerbangData = {
                                guru_id: g.guru_id,
                                waktu_datang: g.waktu_datang ? g.waktu_datang.substring(11, 16) : '',
                                waktu_pulang: g.waktu_pulang ? g.waktu_pulang.substring(11, 16) : '',
                                status_kehadiran: g.status_kehadiran,
                                menit_terlambat_datang: g.menit_terlambat_datang || 0,
                                keterangan: g.keterangan || '',
                                nama_guru: g.nama_lengkap
                            };
                            const gerbangJson = JSON.stringify(gerbangData).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
                            aksiCol = `<button onclick="openModalEditGerbang(${gerbangJson})" class="px-2 py-1 bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 rounded text-[10px] font-bold transition">Edit</button>`;
                        <?php endif; ?>

                        rowsHtml += `
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-medium">${idx + 1}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">${escapeHtml(g.nama_lengkap)}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIP: ${escapeHtml(g.nik_nip)}</div>
                                    ${tugasBadges}
                                </td>
                                <td class="py-3 px-4">${waktuDatang}</td>
                                <td class="py-3 px-4">${waktuPulang}</td>
                                <td class="py-3 px-4">${badge}</td>
                                <td class="py-3 px-4 text-slate-600">${ket}</td>
                                <td class="py-3 px-4 text-center">${aksiCol}</td>
                            </tr>
                        `;
                    });
                    tbody.innerHTML = rowsHtml;
                }
            }

            // 3. Update Tabel 2: Realisasi KBM
            if (data.sessions) {
                const tbodyKbm = document.getElementById('tbodyKbmSessions');
                if (tbodyKbm && data.sessions.length > 0) {
                    let kbmHtml = '';
                    data.sessions.forEach((m, idx) => {
                        let checkinCol = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Belum Check-in</span>';
                        if (m.waktu_checkin) {
                            let checkoutInfo = m.waktu_checkout ? `<div class="text-[11px] font-semibold text-emerald-600 mt-0.5">Selesai: ${m.waktu_checkout.substring(11, 16)}</div>` : `<div class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 mt-0.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>Sedang KBM</div>`;
                            checkinCol = `<div class="font-mono font-bold text-slate-900">${m.waktu_checkin.substring(11, 19)}</div>${checkoutInfo}`;
                        } else {
                            let nowTimeStr = (data.server_time || '').substring(0, 8);
                            let isPast = (data.server_date && data.server_date < '<?= date('Y-m-d') ?>') || (m.jam_selesai && nowTimeStr >= m.jam_selesai);
                            if (isPast) {
                                checkinCol = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">❌ Tidak Hadir (Sesi Berakhir)</span>';
                            }
                        }

                        let telatBadge = '<span class="text-slate-400">-</span>';
                        let dendaCol = '<span class="text-slate-400">-</span>';
                        let gpsCol = '<span class="text-slate-400">-</span>';

                        if (m.waktu_checkin) {
                            let telatMenit = parseInt(m.menit_terlambat || 0);
                            if (telatMenit > 0) {
                                telatBadge = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Telat ${telatMenit} mnt</span>`;
                                let nominalDenda = telatMenit * 125;
                                dendaCol = `<span class="font-display font-bold text-rose-600">- ${formatRupiah(nominalDenda)}</span>`;
                            } else {
                                telatBadge = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Tepat Waktu</span>`;
                                dendaCol = `<span class="font-semibold text-emerald-600">Rp 0</span>`;
                            }
                            gpsCol = `<span class="inline-flex items-center gap-1 font-mono text-[11px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-100">📍 ${m.jarak_meter} m</span>
                                      <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">✓ ${escapeHtml(m.status_verifikasi)}</div>`;
                        }

                        kbmHtml += `
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 text-center text-slate-400 font-medium">${idx + 1}</td>
                                <td class="py-3 px-4 font-mono font-semibold text-slate-700">${m.jam_mulai.substring(0, 5)} - ${m.jam_selesai.substring(0, 5)}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">${escapeHtml(m.nama_guru)}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">NIP: ${escapeHtml(m.nik_nip)}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800">${escapeHtml(m.nama_mapel)}</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 mt-0.5">${escapeHtml(m.nama_kelas)}</span>
                                </td>
                                <td class="py-3 px-4">${checkinCol}</td>
                                <td class="py-3 px-4">${telatBadge}</td>
                                <td class="py-3 px-4">${dendaCol}</td>
                                <td class="py-3 px-4">${gpsCol}</td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($user['role'] === 'admin'): ?>
                                        ${(() => {
                                            const sessionData = {
                                                jadwal_id: m.jadwal_id,
                                                guru_id: m.guru_id,
                                                waktu_checkin: m.waktu_checkin ? m.waktu_checkin.substring(11, 16) : '',
                                                waktu_checkout: m.waktu_checkout ? m.waktu_checkout.substring(11, 16) : '',
                                                menit_terlambat: m.menit_terlambat || 0,
                                                nama_guru: m.nama_guru,
                                                nama_mapel: m.nama_mapel
                                            };
                                            const sessionJson = JSON.stringify(sessionData).replace(/'/g, "&#39;").replace(/"/g, "&quot;");
                                            return `<button onclick="openModalEditSesi(${sessionJson})" class="px-2 py-1 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 rounded text-[10px] font-bold transition">Edit</button>`;
                                        })()}
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        `;
                    });
                    tbodyKbm.innerHTML = kbmHtml;
                }
            }

        } catch (err) {
            console.error('Live Sync Error:', err);
        }
    }
    
    // Modal Edit Sesi Manual
    function openModalEditSesi(data) {
        document.getElementById('modalEditSesi').classList.remove('hidden');
        document.getElementById('edit_jadwal_id').value = data.jadwal_id;
        document.getElementById('edit_guru_id').value = data.guru_id;
        document.getElementById('edit_nama_guru').value = data.nama_guru;
        document.getElementById('edit_nama_mapel').value = data.nama_mapel;
        document.getElementById('edit_waktu_checkin').value = data.waktu_checkin || '';
        document.getElementById('edit_waktu_checkout').value = data.waktu_checkout || '';
        document.getElementById('edit_menit_terlambat').value = data.menit_terlambat || 0;
    }

    function closeModalEditSesi() {
        document.getElementById('modalEditSesi').classList.add('hidden');
    }
    </script>

    <!-- Modal Edit Sesi -->
    <div id="modalEditSesi" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800">Edit / Manual Absen KBM</h3>
                <button type="button" onclick="closeModalEditSesi()" class="text-slate-400 hover:text-slate-600">✖</button>
            </div>
            <form action="<?= App::baseUrl('admin/monitoring/guru/update-manual') ?>" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="jadwal_id" id="edit_jadwal_id">
                <input type="hidden" name="guru_id" id="edit_guru_id">
                <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Guru</label>
                    <input type="text" id="edit_nama_guru" class="w-full bg-slate-100 border border-slate-200 text-slate-700 text-sm rounded-xl px-3 py-2" readonly>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Mata Pelajaran</label>
                    <input type="text" id="edit_nama_mapel" class="w-full bg-slate-100 border border-slate-200 text-slate-700 text-sm rounded-xl px-3 py-2" readonly>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Waktu Check-in</label>
                        <input type="time" name="waktu_checkin" id="edit_waktu_checkin" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500">
                        <p class="text-[10px] text-slate-500 mt-1">Kosongkan jika absen/alpha</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Waktu Check-out</label>
                        <input type="time" name="waktu_checkout" id="edit_waktu_checkout" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Menit Terlambat & Denda</label>
                    <div class="flex items-center gap-2">
                        <input type="number" min="0" name="menit_terlambat" id="edit_menit_terlambat" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-bold rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500">
                        <span class="text-xs font-medium text-slate-500 whitespace-nowrap">Menit</span>
                    </div>
                    <p class="text-[10px] text-slate-500 mt-1">Isi 0 untuk menghapus denda keterlambatan (Diampuni).</p>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeModalEditSesi()" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-soft-sm transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
    </div>

    <!-- Modal Edit Gerbang -->
    <div id="modalEditGerbang" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-slate-800">Edit / Manual Presensi Gerbang</h3>
                <button type="button" onclick="closeModalEditGerbang()" class="text-slate-400 hover:text-slate-600">✖</button>
            </div>
            <form action="<?= App::baseUrl('admin/monitoring/guru/gerbang-update-manual') ?>" method="POST">
                <div class="p-6 space-y-4">
                    <input type="hidden" name="guru_id" id="edit_gerbang_guru_id">
                    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">

                    <div class="bg-sky-50 rounded-xl p-3 border border-sky-100">
                        <div class="text-xs font-semibold text-sky-800 mb-1">Nama Guru</div>
                        <div class="text-sm font-bold text-slate-900" id="edit_gerbang_nama_guru"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jam Datang (Tap-In)</label>
                            <input type="time" name="waktu_datang" id="edit_waktu_datang" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                            <div class="text-[10px] text-slate-400 mt-1">Kosongkan jika absen</div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jam Pulang (Tap-Out)</label>
                            <input type="time" name="waktu_pulang" id="edit_waktu_pulang" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Kehadiran</label>
                        <select name="status_kehadiran" id="edit_status_kehadiran" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                            <option value="HADIR">HADIR</option>
                            <option value="TERLAMBAT">TERLAMBAT</option>
                            <option value="TUGAS_LUAR">TUGAS LUAR / DINAS</option>
                            <option value="IZIN">IZIN</option>
                            <option value="SAKIT">SAKIT</option>
                            <option value="ALPHA">ALPHA</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Menit Terlambat</label>
                        <input type="number" min="0" name="menit_terlambat_datang" id="edit_gerbang_menit_terlambat" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" id="edit_keterangan" placeholder="Keterangan tambahan..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" onclick="closeModalEditGerbang()" class="px-4 py-2 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-soft-sm transition">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Mematikan JS Sync saat pengguna mengetik agar form tidak kedip/hilang
    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('focus', () => { isSyncActive = false; });
        el.addEventListener('blur', () => { isSyncActive = true; countdownSec = POLLING_INTERVAL_SEC; });
    });

    function openModalEditGerbang(data) {
        document.getElementById('modalEditGerbang').classList.remove('hidden');
        document.getElementById('edit_gerbang_guru_id').value = data.guru_id;
        document.getElementById('edit_waktu_datang').value = data.waktu_datang || '';
        document.getElementById('edit_waktu_pulang').value = data.waktu_pulang || '';
        document.getElementById('edit_status_kehadiran').value = data.status_kehadiran || 'HADIR';
        document.getElementById('edit_gerbang_menit_terlambat').value = data.menit_terlambat_datang || 0;
        document.getElementById('edit_keterangan').value = data.keterangan || '';
        document.getElementById('edit_gerbang_nama_guru').textContent = data.nama_guru;
        isSyncActive = false;
    }

    function closeModalEditGerbang() {
        document.getElementById('modalEditGerbang').classList.add('hidden');
        isSyncActive = true;
        countdownSec = POLLING_INTERVAL_SEC;
    }
    </script>
</body>
</html>

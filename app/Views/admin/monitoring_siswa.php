<?php
use App\Config\App;
use App\Helpers\TimeHelper;

$activeNav = 'monitoring_siswa';

// Buat lookup mapel summary per siswa untuk tab gerbang
$mapelSummaryBySiswa = [];
if (!empty($siswaRincianMapel)) {
    foreach ($siswaRincianMapel as $sr) {
        $mapelSummaryBySiswa[$sr['siswa_id']] = $sr;
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Siswa Real-Time - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
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
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Monitoring Kehadiran Siswa</h1>
                <p class="text-xs text-slate-500 mt-0.5">Validasi integritas presensi gerbang dua arah dan rincian kehadiran setiap mata pelajaran hari ini</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-2 bg-emerald-50 text-emerald-800 border border-emerald-200 px-3 py-1.5 rounded-xl text-xs font-bold shadow-soft-sm">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span>LIVE SYNC</span>
                    <span class="text-slate-300">|</span>
                    <span id="syncIndicatorSiswaText" class="text-slate-600 font-medium">Aktif (<span id="syncCountdownSiswa">3</span>s)</span>
                    <button type="button" id="btnToggleSyncSiswa" onclick="toggleSyncSiswa()" class="ml-1 px-2 py-0.5 rounded-md bg-emerald-200/60 hover:bg-emerald-200 text-emerald-900 text-[10px] font-bold transition">
                        Jeda
                    </button>
                </div>

                <!-- Tombol Eksekusi Audit Evaluasi Gerbang Sore -->
                <form action="<?= App::baseUrl('admin/monitoring/evaluasi-gerbang') ?>" method="POST"
                      onsubmit="return confirm('Kunci status siswa yang tidak melakukan Tap-Out pulang hari ini menjadi ALPHA secara permanen?')">
                    <input type="hidden" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>">
                    <button type="submit" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-rose transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Kunci Status Gugur Alpha</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold animate-fade-in">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <!-- Filter Controls Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft-sm">
                <form method="GET" action="<?= App::baseUrl('admin/monitoring/siswa') ?>" class="flex items-center gap-4 flex-wrap">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Pilih Tanggal:</label>
                        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" 
                                class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" 
                                onchange="this.form.submit()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 mb-1">Pilih Rombel / Kelas:</label>
                        <div class="relative min-w-[200px]">
                            <select name="kelas_id" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition appearance-none" onchange="this.form.submit()">
                                <option value="">Semua Kelas</option>
                                <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= $kelasId == $k['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <svg class="w-4 h-4 text-slate-400 absolute right-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <div class="pt-5 flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="belum_pulang" value="1" <?= $filterBelumPulang ? 'checked' : '' ?> 
                                   class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500" 
                                   onchange="this.form.submit()">
                            <span class="text-xs font-bold text-amber-800 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-lg">
                                ⚠️ Filter: Belum Tap-Out Pulang Saja
                            </span>
                        </label>
                        <a href="<?= App::baseUrl('admin/monitoring/siswa/export?tanggal=' . urlencode($tanggal) . '&kelas_id=' . urlencode($kelasId) . '&belum_pulang=' . ($filterBelumPulang ? '1' : '0')) ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-soft-sm transition flex items-center gap-1.5">
                            <span>📊</span>
                            <span>Export Excel</span>
                        </a>
                    </div>
                </form>
            </div>

            <!-- STATISTIK GERBANG SISWA -->
            <?php
            $cntTotalSiswa = count($gerbangList);
            $cntDatangSiswa = 0;
            $cntPulangSiswa = 0;
            $cntHadirLengkapSiswa = 0;
            $cntBelumPulangSiswa = 0;
            foreach ($gerbangList as $r) {
                if (!empty($r['waktu_datang'])) $cntDatangSiswa++;
                if (!empty($r['waktu_pulang'])) $cntPulangSiswa++;
                if ($r['status_kehadiran'] === 'HADIR') $cntHadirLengkapSiswa++;
                elseif (!empty($r['waktu_datang']) && empty($r['waktu_pulang'])) $cntBelumPulangSiswa++;
            }
            ?>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statSiswaDatang" class="text-2xl font-extrabold font-display text-sky-600"><?= $cntDatangSiswa ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>☀️</span> Tap-In Masuk
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statSiswaPulang" class="text-2xl font-extrabold font-display text-amber-600"><?= $cntPulangSiswa ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>🌙</span> Tap-Out Pulang
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statSiswaLengkap" class="text-2xl font-extrabold font-display text-emerald-600"><?= $cntHadirLengkapSiswa ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>✅</span> Hadir Lengkap (2 Arah)
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft-sm">
                    <div id="statSiswaBelumPulang" class="text-2xl font-extrabold font-display text-rose-600"><?= $cntBelumPulangSiswa ?></div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mt-1 flex items-center gap-1">
                        <span>⏱️</span> Belum Tap-Out
                    </div>
                </div>
            </div>

            <!-- Tab Switcher Navigation -->
            <div class="flex items-center gap-2">
                <button onclick="switchTab('tabGerbang')" id="btnTabGerbang"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-soft-sm bg-brand-600 text-white flex items-center gap-1.5">
                    <span>🚪</span>
                    <span>Presensi Gerbang (Tap-in & Tap-out)</span>
                </button>
                <button onclick="switchTab('tabMapel')" id="btnTabMapel"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5">
                    <span>📚</span>
                    <span>Rincian Mapel Hari Ini & Status Kehadiran</span>
                    <?php if (!empty($jadwalHariIni)): ?>
                    <span class="ml-1 bg-brand-100 text-brand-700 text-[10px] px-2 py-0.5 rounded-full font-extrabold">
                        <?= count($jadwalHariIni) ?> Sesi
                    </span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- TAB 1: GERBANG DUA ARAH -->
            <div id="tabGerbang" class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Log Presensi Gerbang: <strong class="text-slate-800 font-bold"><?= TimeHelper::formatDateIndonesian($tanggal) ?></strong>
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Total: <strong class="text-slate-800 font-bold"><?= count($gerbangList) ?></strong> siswa</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">NISN</th>
                                <th class="py-3.5 px-4">Nama Siswa</th>
                                <th class="py-3.5 px-4">Kelas</th>
                                <th class="py-3.5 px-4">Waktu Datang (In)</th>
                                <th class="py-3.5 px-4">Waktu Pulang (Out)</th>
                                <th class="py-3.5 px-4">Status Gerbang</th>
                                <th class="py-3.5 px-4">Status Pelajaran Hari Ini</th>
                                <th class="py-3.5 px-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyGerbangSiswa" class="divide-y divide-slate-100 text-xs">
                            <?php if (empty($gerbangList)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400">
                                    <div class="text-2xl mb-1">🚪</div>
                                    <p class="font-medium">Belum ada data presensi gerbang pada tanggal ini.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($gerbangList as $g): 
                                    $mapelInfo = $mapelSummaryBySiswa[$g['siswa_id']] ?? null;
                                ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800"><?= htmlspecialchars($g['nisn']) ?></td>
                                    <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($g['nama_siswa']) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <?= htmlspecialchars($g['nama_kelas']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($g['waktu_datang'])): ?>
                                            <span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
                                                <?= date('H:i:s', strtotime($g['waktu_datang'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-rose-600 font-semibold text-[11px]">Belum Tap-In</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if (!empty($g['waktu_pulang'])): ?>
                                            <span class="font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                                <?= date('H:i:s', strtotime($g['waktu_pulang'])) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-amber-600 font-semibold text-[11px]">Belum Tap-Out</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($g['status_kehadiran'] === 'HADIR'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                ✓ HADIR (VALID)
                                            </span>
                                        <?php elseif ($g['status_kehadiran'] === 'ALPHA'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                ✕ ALPHA
                                            </span>
                                        <?php elseif ($g['status_kehadiran'] === 'TERLAMBAT'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-yellow-50 text-yellow-700 border border-yellow-200">
                                                ⏱️ TERLAMBAT
                                            </span>
                                        <?php elseif ($g['status_kehadiran'] === 'SAKIT'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                🤒 SAKIT
                                            </span>
                                        <?php elseif ($g['status_kehadiran'] === 'IZIN'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
                                                📋 IZIN
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                BELUM SCAN
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($mapelInfo): ?>
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <?php if ($mapelInfo['summary']['status_ketuntasan'] === 'TUNTAS / HADIR LENGKAP'): ?>
                                                    <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                                        ✅ Masuk Lengkap (<?= $mapelInfo['summary']['count_hadir'] ?>/<?= $mapelInfo['summary']['total_jadwal'] ?>)
                                                    </span>
                                                <?php elseif ($mapelInfo['summary']['status_ketuntasan'] === 'TIDAK TUNTAS / TIDAK HADIR'): ?>
                                                    <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">
                                                        ⚠️ Ada Tidak Masuk (H:<?= $mapelInfo['summary']['count_hadir'] ?> S:<?= $mapelInfo['summary']['count_sakit'] ?> I:<?= $mapelInfo['summary']['count_izin'] ?> A:<?= $mapelInfo['summary']['count_alpha'] ?>)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-medium">
                                                        ⏳ Belum Ada Sesi
                                                    </span>
                                                <?php endif; ?>
                                                <button type="button" onclick="switchTab('tabMapel')" class="text-brand-600 hover:text-brand-700 font-bold text-[10px] underline">
                                                    Lihat Rincian ➜
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-[11px]">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 text-[11px]">
                                        <?= htmlspecialchars($g['keterangan'] ?: '-') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: RINCIAN MAPEL HARI INI & STATUS KEHADIRAN SISWA -->
            <div id="tabMapel" class="space-y-6 hidden">
                
                <!-- Card 1: Daftar Mata Pelajaran Terjadwal Hari Ini -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm p-5">
                    <div class="flex justify-between items-center mb-3">
                        <div>
                            <h2 class="text-sm font-outfit font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                <span>📅</span>
                                <span>Mata Pelajaran Hari Ini (<?= htmlspecialchars($hariIndo) ?>, <?= TimeHelper::formatDateIndonesian($tanggal) ?>)</span>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh jadwal KBM yang dijadwalkan pada hari ini dan status sesi mengajar guru</p>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-sky-50 text-sky-700 border border-sky-200 text-xs font-bold">
                            <?= count($jadwalHariIni) ?> Mata Pelajaran
                        </span>
                    </div>

                    <?php if (empty($jadwalHariIni)): ?>
                    <div class="p-6 text-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-xs">
                        Tidak ada mata pelajaran yang terjadwal pada hari <?= htmlspecialchars($hariIndo) ?>.
                    </div>
                    <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <?php foreach ($jadwalHariIni as $jh): ?>
                        <div class="p-3.5 rounded-xl border <?= $jh['status_sesi'] === 'SEDANG_BERLANGSUNG' ? 'border-emerald-300 bg-emerald-50/40' : ($jh['status_sesi'] === 'SELESAI' ? 'border-slate-200 bg-slate-50/60' : 'border-amber-200 bg-amber-50/30') ?> shadow-soft-xs">
                            <div class="flex justify-between items-start gap-2 mb-1.5">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-mono text-[10px] font-bold">
                                    ⏰ <?= substr($jh['jam_mulai'], 0, 5) ?> - <?= substr($jh['jam_selesai'], 0, 5) ?> (<?= (int)$jh['jumlah_jp'] ?> JP)
                                </span>
                                <?php if ($jh['status_sesi'] === 'SEDANG_BERLANGSUNG'): ?>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold border border-emerald-300 animate-pulse">
                                        🟢 Mengajar
                                    </span>
                                <?php elseif ($jh['status_sesi'] === 'SELESAI'): ?>
                                    <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 text-[10px] font-bold border border-slate-300">
                                        ✅ Selesai
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold border border-amber-300">
                                        ⏳ Menunggu Guru
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="font-bold text-xs text-slate-900 truncate"><?= htmlspecialchars($jh['nama_mapel']) ?></div>
                            <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                🏫 Kelas: <strong class="text-slate-700"><?= htmlspecialchars($jh['nama_kelas']) ?></strong>
                            </div>
                            <div class="text-[11px] text-brand-600 font-medium truncate mt-0.5">
                                👨‍🏫 <?= htmlspecialchars($jh['nama_guru']) ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Card 2: Tabel Rincian Kehadiran Siswa Per Mata Pelajaran -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider block">
                                Rincian Kehadiran Siswa Per Mata Pelajaran Hari Ini
                            </span>
                            <span class="text-[11px] text-slate-500 font-medium">
                                Memvalidasi apakah siswa masuk (hadir) atau tidak masuk (sakit, izin, alpa) pada setiap mata pelajaran terjadwal
                            </span>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">
                            Total: <strong class="text-slate-800 font-bold"><?= count($siswaRincianMapel) ?></strong> siswa
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    <th class="py-3.5 px-4 w-12 text-center">No</th>
                                    <th class="py-3.5 px-4">Nama Siswa & NISN</th>
                                    <th class="py-3.5 px-4">Kelas</th>
                                    <th class="py-3.5 px-4 min-w-[340px]">Rincian Pelajaran Hari Ini & Status Kehadiran Siswa</th>
                                    <th class="py-3.5 px-4 text-center">Rekap (H/S/I/A)</th>
                                    <th class="py-3.5 px-4 text-center">Status Harian</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php if (empty($siswaRincianMapel)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-12 text-slate-400">
                                        <div class="text-2xl mb-1">📚</div>
                                        <p class="font-medium">Tidak ada data siswa ditemukan.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php $i = 1; foreach ($siswaRincianMapel as $idx => $sr): ?>
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900"><?= htmlspecialchars($sr['nama_siswa']) ?></div>
                                            <div class="text-[11px] font-mono text-slate-500">NISN: <?= htmlspecialchars($sr['nisn']) ?></div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                <?= htmlspecialchars($sr['nama_kelas']) ?>
                                            </span>
                                        </td>
                                        
                                        <!-- RINCIAN SETIAP PELAJARAN HARI INI -->
                                        <td class="py-3 px-4">
                                            <?php if (empty($sr['mapel_list'])): ?>
                                                <span class="text-slate-400 text-xs italic">Tidak ada jadwal KBM kelas ini hari ini.</span>
                                            <?php else: ?>
                                                <div class="space-y-1.5">
                                                    <?php foreach ($sr['mapel_list'] as $m): ?>
                                                    <div class="flex items-center justify-between gap-2 p-1.5 rounded-lg border bg-white shadow-soft-xs text-[11px]
                                                        <?= $m['status_kehadiran'] === 'HADIR' ? 'border-emerald-200' : ($m['status_kehadiran'] === 'IZIN' ? 'border-sky-200 bg-sky-50/30' : ($m['status_kehadiran'] === 'SAKIT' ? 'border-amber-200 bg-amber-50/30' : ($m['status_kehadiran'] === 'ALPHA' ? 'border-rose-200 bg-rose-50/30' : 'border-slate-200 bg-slate-50/50'))) ?>">
                                                        
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-bold text-slate-800 truncate flex items-center gap-1.5">
                                                                <span class="text-[10px] font-mono bg-slate-100 px-1 py-0.5 rounded text-slate-600">
                                                                    <?= substr($m['jam_mulai'], 0, 5) ?>
                                                                </span>
                                                                <span class="truncate"><?= htmlspecialchars($m['nama_mapel']) ?></span>
                                                            </div>
                                                            <div class="text-[10px] text-slate-500 truncate mt-0.5">
                                                                Guru: <?= htmlspecialchars($m['nama_guru']) ?>
                                                                <?php if (!empty($m['catatan'])): ?>
                                                                • <span class="text-amber-600 italic">"<?= htmlspecialchars($m['catatan']) ?>"</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <!-- STATUS MASUK ATAU TIDAK -->
                                                        <div class="shrink-0">
                                                            <?php if ($m['status_kehadiran'] === 'HADIR'): ?>
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                                    <span>✓</span>
                                                                    <span>Masuk (Hadir)</span>
                                                                </span>
                                                            <?php elseif ($m['status_kehadiran'] === 'SAKIT'): ?>
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                                    <span>🤒</span>
                                                                    <span>Tidak Masuk (Sakit)</span>
                                                                </span>
                                                            <?php elseif ($m['status_kehadiran'] === 'IZIN'): ?>
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800 border border-sky-300">
                                                                    <span>📋</span>
                                                                    <span>Tidak Masuk (Izin)</span>
                                                                </span>
                                                            <?php elseif ($m['status_kehadiran'] === 'ALPHA'): ?>
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                                                    <span>✕</span>
                                                                    <span>Tidak Masuk (Alpa)</span>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                                                                    <span>⏳</span>
                                                                    <span>Sesi Belum Mulai</span>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- REKAPITULASI SESI H/S/I/A -->
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex flex-col gap-1 items-center font-bold text-[10px]">
                                                <div class="flex items-center gap-1">
                                                    <span class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">H: <?= $sr['summary']['count_hadir'] ?></span>
                                                    <span class="text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">S: <?= $sr['summary']['count_sakit'] ?></span>
                                                </div>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">I: <?= $sr['summary']['count_izin'] ?></span>
                                                    <span class="text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">A: <?= $sr['summary']['count_alpha'] ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- STATUS KETUNTASAN HARIAN -->
                                        <td class="py-3 px-4 text-center">
                                            <?php if ($sr['summary']['status_ketuntasan'] === 'TUNTAS / HADIR LENGKAP'): ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                                    ✅ TUNTAS HADIR
                                                </span>
                                            <?php elseif ($sr['summary']['status_ketuntasan'] === 'TIDAK TUNTAS / TIDAK HADIR'): ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 whitespace-nowrap">
                                                    ❌ TIDAK TUNTAS
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap">
                                                    ⏳ Belum Ada Sesi
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- AKSI DETAIL MODAL -->
                                        <td class="py-3 px-4 text-center">
                                            <button type="button" 
                                                    onclick='bukaModalDetail(<?= json_encode($sr, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                                    class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 border border-slate-200 text-[11px] font-bold transition shadow-soft-xs inline-flex items-center gap-1">
                                                <span>🔍</span>
                                                <span>Rincian</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- MODAL POPUP: DETAIL MATA PELAJARAN SISWA -->
    <div id="modalDetailSiswa" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-soft-2xl border border-slate-200 max-h-[90vh] flex flex-col animate-fade-in">
            <div class="flex justify-between items-start pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-outfit font-bold text-slate-900" id="modalNamaSiswa">Nama Siswa</h3>
                    <p class="text-xs text-slate-500 font-mono mt-0.5" id="modalNisnKelas">NISN: - • Kelas: -</p>
                </div>
                <button type="button" onclick="tutupModalDetail()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold flex items-center justify-center text-sm transition">
                    ✕
                </button>
            </div>

            <div class="py-4 overflow-y-auto flex-1 space-y-3" id="modalTimelineMapel">
                <!-- Diisi secara dinamis oleh JavaScript -->
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="tutupModalDetail()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-soft-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabId) {
            const tabGerbang = document.getElementById('tabGerbang');
            const tabMapel = document.getElementById('tabMapel');
            const btnGerbang = document.getElementById('btnTabGerbang');
            const btnMapel = document.getElementById('btnTabMapel');

            if (tabId === 'tabGerbang') {
                tabGerbang.classList.remove('hidden');
                tabMapel.classList.add('hidden');
                btnGerbang.className = 'px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-soft-sm bg-brand-600 text-white flex items-center gap-1.5';
                btnMapel.className = 'px-4 py-2.5 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5';
            } else {
                tabMapel.classList.remove('hidden');
                tabGerbang.classList.add('hidden');
                btnMapel.className = 'px-4 py-2.5 rounded-xl text-xs font-bold transition-all shadow-soft-sm bg-brand-600 text-white flex items-center gap-1.5';
                btnGerbang.className = 'px-4 py-2.5 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 flex items-center gap-1.5';
            }
        }

        function bukaModalDetail(data) {
            document.getElementById('modalNamaSiswa').textContent = data.nama_siswa;
            document.getElementById('modalNisnKelas').textContent = `NISN: ${data.nisn} • Kelas: ${data.nama_kelas}`;

            const container = document.getElementById('modalTimelineMapel');
            container.innerHTML = '';

            if (!data.mapel_list || data.mapel_list.length === 0) {
                container.innerHTML = '<div class="p-6 text-center text-slate-400 text-xs">Tidak ada jadwal KBM pada hari ini.</div>';
            } else {
                data.mapel_list.forEach(m => {
                    let badgeClass = 'bg-slate-100 text-slate-600 border-slate-200';
                    let statusLabel = 'Belum Dimulai';

                    if (m.status_kehadiran === 'HADIR') {
                        badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
                        statusLabel = '✓ Masuk (Hadir)';
                    } else if (m.status_kehadiran === 'SAKIT') {
                        badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                        statusLabel = '🤒 Tidak Masuk (Sakit)';
                    } else if (m.status_kehadiran === 'IZIN') {
                        badgeClass = 'bg-sky-100 text-sky-800 border-sky-300';
                        statusLabel = '📋 Tidak Masuk (Izin)';
                    } else if (m.status_kehadiran === 'ALPHA') {
                        badgeClass = 'bg-rose-100 text-rose-800 border-rose-300';
                        statusLabel = '✕ Tidak Masuk (Alpa)';
                    }

                    const card = document.createElement('div');
                    card.className = 'p-3 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-1';
                    card.innerHTML = `
                        <div class="flex justify-between items-start gap-2">
                            <span class="font-bold text-xs text-slate-900">${m.nama_mapel}</span>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border ${badgeClass}">${statusLabel}</span>
                        </div>
                        <div class="text-[11px] text-slate-500 font-mono">
                            ⏰ Jam KBM: ${m.jam_mulai.substring(0,5)} - ${m.jam_selesai.substring(0,5)} (${m.jumlah_jp} JP)
                        </div>
                        <div class="text-[11px] text-brand-600 font-medium">
                            👨‍🏫 Guru: ${m.nama_guru}
                        </div>
                        ${m.catatan ? `<div class="text-[11px] text-amber-700 bg-amber-50 p-2 rounded-lg border border-amber-200 mt-1">💬 Catatan: ${m.catatan}</div>` : ''}
                    `;
                    container.appendChild(card);
                });
            }

            const modal = document.getElementById('modalDetailSiswa');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function tutupModalDetail() {
            const modal = document.getElementById('modalDetailSiswa');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Tutup modal jika klik di luar area modal
        document.getElementById('modalDetailSiswa').addEventListener('click', function(e) {
            if (e.target === this) {
                tutupModalDetail();
            }
        });

        // ===============================================
        // REAL-TIME AUTO SYNC SISWA
        // ===============================================
        let isSyncActiveSiswa = true;
        let countdownSecSiswa = 3;
        const POLLING_INTERVAL_SISWA = 3;

        const currentTanggal = '<?= $tanggal ?>';
        const currentKelasId = '<?= $kelasId ?? '' ?>';
        const currentBelumPulang = '<?= $filterBelumPulang ? '1' : '' ?>';

        function toggleSyncSiswa() {
            isSyncActiveSiswa = !isSyncActiveSiswa;
            const btn = document.getElementById('btnToggleSyncSiswa');
            const text = document.getElementById('syncIndicatorSiswaText');
            if (isSyncActiveSiswa) {
                btn.textContent = 'Jeda';
                btn.className = 'ml-1 px-2 py-0.5 rounded-md bg-emerald-200/60 hover:bg-emerald-200 text-emerald-900 text-[10px] font-bold transition';
                text.innerHTML = `Aktif (<span id="syncCountdownSiswa">${countdownSecSiswa}</span>s)`;
            } else {
                btn.textContent = 'Lanjutkan';
                btn.className = 'ml-1 px-2 py-0.5 rounded-md bg-amber-200 text-amber-900 text-[10px] font-bold transition';
                text.innerHTML = '<span class="text-amber-600 font-bold">Dijeda</span>';
            }
        }

        setInterval(() => {
            if (!isSyncActiveSiswa) return;
            countdownSecSiswa--;
            const cntEl = document.getElementById('syncCountdownSiswa');
            if (cntEl) cntEl.textContent = countdownSecSiswa;

            if (countdownSecSiswa <= 0) {
                countdownSecSiswa = POLLING_INTERVAL_SISWA;
                fetchLiveSiswa();
            }
        }, 1000);

        function escapeStr(s) {
            if (!s) return '';
            return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        async function fetchLiveSiswa() {
            if (document.hidden) return;

            try {
                const params = new URLSearchParams({
                    tanggal: currentTanggal,
                    kelas_id: currentKelasId,
                    belum_pulang: currentBelumPulang
                });
                const res = await fetch(`<?= App::baseUrl('admin/monitoring/siswa-live') ?>?${params.toString()}`, {
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
                    updateEl('statSiswaDatang', c.total_datang);
                    updateEl('statSiswaPulang', c.total_pulang);
                    updateEl('statSiswaLengkap', c.total_hadir_lengkap);
                    updateEl('statSiswaBelumPulang', c.total_belum_pulang);
                }

                // 2. Update Gerbang Table
                if (data.gerbang_list && data.gerbang_list.length > 0) {
                    const tbody = document.getElementById('tbodyGerbangSiswa');
                    if (tbody) {
                        let html = '';
                        data.gerbang_list.forEach((g, idx) => {
                            let waktuDatang = g.waktu_datang ? `<span class="font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">${g.waktu_datang.substring(11, 19)}</span>` : '<span class="text-rose-600 font-semibold text-[11px]">Belum Tap-In</span>';
                            let waktuPulang = g.waktu_pulang ? `<span class="font-mono font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">${g.waktu_pulang.substring(11, 19)}</span>` : '<span class="text-amber-600 font-semibold text-[11px]">Belum Tap-Out</span>';

                            let statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">⚪ Belum Presensi</span>';
                            if (g.status_kehadiran === 'HADIR') {
                                statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">✅ Hadir Lengkap</span>';
                            } else if (g.status_kehadiran === 'ALPHA') {
                                statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">❌ Gugur Alpha</span>';
                            } else if (g.status_kehadiran === 'TERLAMBAT') {
                                statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-yellow-50 text-yellow-700 border border-yellow-200">⏱️ Terlambat</span>';
                            } else if (g.status_kehadiran === 'SAKIT') {
                                statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">🤒 Sakit</span>';
                            } else if (g.status_kehadiran === 'IZIN') {
                                statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">📋 Izin</span>';
                            }

                            let mapelInfo = null;
                            if (data.mapel_summary && data.mapel_summary[g.siswa_id]) {
                                mapelInfo = data.mapel_summary[g.siswa_id];
                            }

                            let mapelStatusBadge = '';
                            if (mapelInfo) {
                                if (mapelInfo.status_ketuntasan === 'TUNTAS / HADIR LENGKAP') {
                                    mapelStatusBadge = `<span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">✅ Masuk Lengkap (${mapelInfo.count_hadir}/${mapelInfo.total_jadwal})</span>`;
                                } else if (mapelInfo.status_ketuntasan === 'TIDAK TUNTAS / TIDAK HADIR') {
                                    mapelStatusBadge = `<span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold">⚠️ Ada Tidak Masuk (H:${mapelInfo.count_hadir} S:${mapelInfo.count_sakit} I:${mapelInfo.count_izin} A:${mapelInfo.count_alpha})</span>`;
                                } else {
                                    mapelStatusBadge = '<span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-medium">⏳ Belum Ada Sesi</span>';
                                }
                                mapelStatusBadge = `<div class="flex items-center gap-1.5 flex-wrap">${mapelStatusBadge}<button type="button" onclick="switchTab('tabMapel')" class="text-brand-600 hover:text-brand-700 font-bold text-[10px] underline">Lihat Rincian ➜</button></div>`;
                            } else {
                                mapelStatusBadge = '<span class="text-slate-400 text-[11px]">-</span>';
                            }

                            let ket = g.keterangan ? escapeStr(g.keterangan) : '<span class="text-slate-300">-</span>';

                            html += `
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium">${idx + 1}</td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800">${escapeStr(g.nisn)}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">${escapeStr(g.nama_siswa)}</td>
                                    <td class="py-3 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">${escapeStr(g.nama_kelas)}</span></td>
                                    <td class="py-3 px-4">${waktuDatang}</td>
                                    <td class="py-3 px-4">${waktuPulang}</td>
                                    <td class="py-3 px-4">${statusBadge}</td>
                                    <td class="py-3 px-4">${mapelStatusBadge}</td>
                                    <td class="py-3 px-4 text-slate-600">${ket}</td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    }
                }

            } catch (err) {
                console.error('Live Siswa Sync Error:', err);
            }
        }
    </script>
</body>
</html>

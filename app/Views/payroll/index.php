<?php
use App\Config\App;
use App\Helpers\TimeHelper;

$activeNav = 'payroll';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Penggajian Guru - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
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
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Rekapitulasi Penggajian & Cetak Slip Gaji</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kalkulasi: (Total JP Valid × Honor Riil Menit) - Denda Keterlambatan + Tunjangan Tugas Tambahan</p>
            </div>
            
            <form method="GET" action="<?= App::baseUrl('payroll') ?>" class="flex items-center gap-2">
                <div class="relative">
                    <select name="bulan" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2 pr-8 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition appearance-none" onchange="this.form.submit()">
                        <?php foreach (TimeHelper::MONTHS_ID as $num => $name): ?>
                        <option value="<?= $num ?>" <?= $bulan == $num ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
                <div class="relative">
                    <select name="tahun" class="bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2 pr-8 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition appearance-none" onchange="this.form.submit()">
                        <option value="2026" <?= $tahun == 2026 ? 'selected' : '' ?>>2026</option>
                        <option value="2025" <?= $tahun == 2025 ? 'selected' : '' ?>>2025</option>
                    </select>
                    <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </form>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <!-- Grand Totals KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- KPI 1: Total Gaji -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Take Home Pay</span>
                        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-display font-black text-brand-600 tracking-tight">
                            <?= TimeHelper::formatRupiah($grandTotalGaji) ?>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Total Alokasi Honor & Gaji</div>
                    </div>
                </div>

                <!-- KPI 2: Total JP -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Jam Mengajar</span>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-display font-extrabold text-slate-900">
                            <?= $grandTotalJp ?> <span class="text-sm font-semibold text-slate-500">JP</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Terealisasi Presensi KBM</div>
                    </div>
                </div>

                <!-- KPI 3: Keterlambatan -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Keterlambatan</span>
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-3xl font-display font-extrabold text-rose-600">
                            <?= $grandTotalMenitTelat ?> <span class="text-sm font-semibold text-slate-500">menit</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Akumulasi Seluruh Guru</div>
                    </div>
                </div>

                <!-- KPI 4: Potongan Denda -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Potongan Denda</span>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-display font-black text-slate-900">
                            <?= TimeHelper::formatRupiah($grandTotalDenda) ?>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">Efisiensi Kas Anggaran KBM</div>
                    </div>
                </div>
            </div>

            <!-- Table Rekapitulasi Gaji -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Rincian Penggajian Bulan: <strong class="text-slate-800 font-bold"><?= TimeHelper::MONTHS_ID[(int)$bulan] ?? 'Bulan' ?> <?= $tahun ?></strong>
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Total: <strong class="text-slate-800 font-bold"><?= count($rekapData) ?></strong> guru</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">Nama Guru & NIP</th>
                                <th class="py-3.5 px-4">Tugas Tambahan</th>
                                <th class="py-3.5 px-4 text-center">Total JP</th>
                                <th class="py-3.5 px-4">Plafon Terjadwal</th>
                                <th class="py-3.5 px-4">Denda Telat</th>
                                <th class="py-3.5 px-4">Honor Riil KBM</th>
                                <th class="py-3.5 px-4">Tunjangan</th>
                                <th class="py-3.5 px-4">Take Home Pay</th>
                                <th class="py-3.5 px-4 text-center">Slip Gaji</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php if (empty($rekapData)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-12 text-slate-400">
                                    <div class="text-2xl mb-1">💰</div>
                                    <p class="font-medium">Belum ada data guru terdaftar.</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $i = 1; foreach ($rekapData as $r): $g = $r['guru']; ?>
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-4 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900"><?= htmlspecialchars($g['nama_lengkap']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono">NIP: <?= htmlspecialchars($g['nik_nip']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 font-medium">
                                        <?= htmlspecialchars($g['tugas_tambahan'] ?: '-') ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="font-bold text-slate-900"><?= (int)$r['total_jp'] ?> JP</div>
                                        <div class="text-[10px] text-slate-400"><?= $r['total_sesi'] ?> sesi</div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <?= TimeHelper::formatRupiah($r['total_plafon']) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <?php if ($r['total_menit_telat'] > 0): ?>
                                            <span class="font-display font-bold text-rose-600">
                                                - <?= TimeHelper::formatRupiah($r['total_denda']) ?>
                                            </span>
                                            <div class="text-[10px] text-rose-600"><?= $r['total_menit_telat'] ?> mnt telat</div>
                                        <?php else: ?>
                                            <span class="text-emerald-600 font-medium">Rp 0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">
                                        <?= TimeHelper::formatRupiah($r['total_honor_kbm']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">
                                        <?= TimeHelper::formatRupiah($r['tunjangan']) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <strong class="font-display font-extrabold text-brand-600 text-sm">
                                            <?= TimeHelper::formatRupiah($r['take_home_pay']) ?>
                                        </strong>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <a href="<?= App::baseUrl("payroll/slip/{$g['id']}/{$bulan}/{$tahun}") ?>" target="_blank" 
                                           class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-brand-50 text-slate-700 hover:text-brand-700 font-semibold text-[11px] px-3 py-1.5 rounded-xl border border-slate-200 hover:border-brand-200 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span>Slip PDF</span>
                                        </a>
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

</body>
</html>

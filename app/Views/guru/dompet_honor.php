<?php
use App\Config\App;
use App\Helpers\TimeHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Buku Saku Honor Guru - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <!-- Centered Mobile Container -->
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200/90 shadow-soft-lg flex flex-col pb-28">
        
        <!-- Bright Smartphone Top Header -->
        <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-xs px-5 py-3.5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-extrabold text-base flex items-center justify-center shadow-md shadow-emerald-500/20 border-2 border-white">
                        💰
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/80 uppercase tracking-wider">
                                Buku Saku Honor
                            </span>
                        </div>
                        <h1 class="text-sm font-bold text-slate-900 leading-tight mt-0.5 line-clamp-1">
                            <?= htmlspecialchars($user['nama_lengkap']) ?>
                        </h1>
                        <p class="text-[10px] text-slate-500 font-mono">NIP: <?= htmlspecialchars($user['nik_nip']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
                    <a href="<?= App::baseUrl("payroll/slip/{$user['id']}/{$bulan}/{$tahun}") ?>" target="_blank" 
                       class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition-all flex items-center gap-1" title="Unduh Slip PDF">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="hidden sm:inline">Slip</span>
                    </a>
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
                    <span class="text-emerald-600">📅</span>
                    <span><?= TimeHelper::formatDateIndonesian(date('Y-m-d')) ?></span>
                </div>
                <div class="flex items-center gap-1 bg-slate-50 border border-slate-200/80 px-2 py-0.5 rounded-lg text-slate-700 font-mono font-bold text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="liveClock">--:--:--</span>
                </div>
            </div>
        </header>

        <main class="p-5 flex-1 space-y-4">
            
            <!-- Back to Dashboard Quick Action -->
            <div class="flex items-center justify-between">
                <a href="<?= App::baseUrl('guru/dashboard') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 px-3 py-1.5 rounded-xl transition border border-brand-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Jadwal KBM</span>
                </a>
                <span class="text-xs text-slate-400 font-medium">Buku Saku Guru</span>
            </div>

            <!-- Filter Bulan & Tahun -->
            <div class="bg-white p-3 rounded-2xl border border-slate-200/80 shadow-soft-sm">
                <form method="GET" action="<?= App::baseUrl('guru/dompet') ?>" class="flex gap-2">
                    <div class="relative flex-1">
                        <select name="bulan" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition appearance-none cursor-pointer" onchange="this.form.submit()">
                            <?php foreach (TimeHelper::MONTHS_ID as $num => $name): ?>
                            <option value="<?= $num ?>" <?= $bulan == $num ? 'selected' : '' ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <div class="relative w-28">
                        <select name="tahun" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition appearance-none cursor-pointer" onchange="this.form.submit()">
                            <option value="2026" <?= $tahun == 2026 ? 'selected' : '' ?>>2026</option>
                            <option value="2025" <?= $tahun == 2025 ? 'selected' : '' ?>>2025</option>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-2.5 top-3 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </form>
            </div>

            <!-- Summary Take Home Pay Card (Buku Saku Honor) -->
            <div class="p-5 rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 text-white shadow-glow-brand relative overflow-hidden">
                <div class="flex items-center justify-between text-xs text-sky-100 mb-1">
                    <span class="font-medium">Estimasi Take Home Pay</span>
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-sky-100 bg-white/15 backdrop-blur-sm border border-white/20 px-2.5 py-0.5 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <?= TimeHelper::MONTHS_ID[(int)$bulan] ?? 'Bulan Ini' ?> <?= $tahun ?>
                    </span>
                </div>

                <div class="text-3xl font-outfit font-extrabold tracking-tight my-2">
                    <?= TimeHelper::formatRupiah($totalTakeHome) ?>
                </div>

                <div class="border-t border-white/20 pt-3 mt-3 space-y-2 text-xs">
                    <div class="flex justify-between items-center text-sky-100">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-300"></span>
                            Honor Sesi KBM (<?= $totalJp ?> JP)
                        </span>
                        <span class="font-bold text-white"><?= TimeHelper::formatRupiah($totalHonorBersih) ?></span>
                    </div>

                    <?php if ($totalDenda > 0): ?>
                    <div class="flex justify-between items-center text-rose-100 bg-rose-950/40 px-2.5 py-1.5 rounded-xl border border-rose-300/30">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Potongan Terlambat (<?= $totalTelat ?> menit)
                        </span>
                        <span class="font-bold">- <?= TimeHelper::formatRupiah($totalDenda) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($tunjanganTugas > 0): ?>
                    <div class="flex justify-between items-center text-emerald-100 bg-emerald-950/40 px-2.5 py-1.5 rounded-xl border border-emerald-300/30">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Tunjangan Tugas Tambahan
                        </span>
                        <span class="font-bold">+ <?= TimeHelper::formatRupiah($tunjanganTugas) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- List Sesi Mengajar Header -->
            <div class="flex items-center justify-between pt-1">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Sesi KBM Terverifikasi (<?= count($history) ?>)
                </h2>
                <span class="text-[11px] text-slate-400 font-medium">Auto-Validasi GPS</span>
            </div>

            <!-- History Sesi Cards -->
            <?php if (empty($history)): ?>
            <div class="bg-white rounded-2xl p-8 text-center border border-dashed border-slate-300 shadow-soft-sm">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                    📂
                </div>
                <div class="text-sm font-bold text-slate-700">Belum Ada Sesi Mengajar</div>
                <div class="text-xs text-slate-500 mt-1">Data mengajar bulan ini belum tercatat atau belum selesai presensi.</div>
            </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($history as $h): ?>
                    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md transition-all">
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <div>
                                <h3 class="font-bold text-sm text-slate-900 tracking-tight">
                                    <?= htmlspecialchars($h['nama_mapel']) ?>
                                </h3>
                                <div class="flex items-center gap-2 mt-1 text-xs">
                                    <span class="font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-md border border-brand-100">
                                        <?= htmlspecialchars($h['nama_kelas']) ?>
                                    </span>
                                    <span class="text-slate-400">•</span>
                                    <span class="text-slate-500 font-medium"><?= (int)$h['jumlah_jp'] ?> JP</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-display font-extrabold text-emerald-600 text-sm">
                                    <?= TimeHelper::formatRupiah($h['honor_didapat']) ?>
                                </div>
                                <div class="text-[11px] text-slate-400 font-medium mt-0.5">
                                    <?= date('d M Y', strtotime($h['tanggal'])) ?>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-2.5 mt-2 text-xs">
                            <div class="text-slate-500 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Check-in: <strong class="text-slate-700 font-semibold"><?= date('H:i', strtotime($h['waktu_checkin'])) ?></strong></span>
                                <span class="text-[11px] text-slate-400">(Jadwal <?= substr($h['jam_mulai'], 0, 5) ?>)</span>
                            </div>
                            <div>
                                <?php if ((int)$h['menit_terlambat'] > 0): ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Telat <?= (int)$h['menit_terlambat'] ?>m (-<?= TimeHelper::formatRupiah($h['menit_terlambat'] * $config['denda_per_menit']) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Tepat Waktu (100%)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <!-- Bottom Navigation Mobile -->
        <?php require __DIR__ . '/partials/bottom_nav.php'; ?>
    </div>

    <script>
        // Jam Digital Real-time
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const el = document.getElementById('liveClock');
            if (el) el.textContent = `${h}:${m}:${s} WIB`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>

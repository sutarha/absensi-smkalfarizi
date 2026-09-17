<?php
use App\Config\App;
use App\Helpers\TimeHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tabungan Siswa - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK AL-FARIZI') ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200/90 shadow-soft-lg flex flex-col pb-28">
        
        <!-- Bright Smartphone Top Header -->
        <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-xs px-5 py-3.5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a href="<?= App::baseUrl('guru/dashboard') ?>" class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 hover:bg-indigo-100 flex items-center justify-center text-base font-bold transition border border-indigo-200/80 shadow-xs" title="Kembali">
                        ◀
                    </a>
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200/80 uppercase tracking-wider">
                                Tabungan Siswa
                            </span>
                        </div>
                        <h1 class="text-sm font-bold text-slate-900 leading-tight mt-0.5 line-clamp-1">
                            <?= htmlspecialchars($user['nama_lengkap']) ?>
                        </h1>
                        <p class="text-[10px] text-slate-500 font-mono">NIP: <?= htmlspecialchars($user['nik_nip']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-1.5">
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
                    <span class="text-indigo-600">📅</span>
                    <span><?= TimeHelper::formatDateIndonesian(date('Y-m-d')) ?></span>
                </div>
                <div class="flex items-center gap-1 bg-slate-50 border border-slate-200/80 px-2 py-0.5 rounded-lg text-slate-700 font-mono font-bold text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="liveClock">--:--:--</span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 p-4 space-y-4">
            <!-- Back to Dashboard Quick Action -->
            <div class="flex items-center justify-between">
                <a href="<?= App::baseUrl('guru/dashboard') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-xl transition border border-indigo-100">
                    <span>◀</span>
                    <span>Kembali ke Jadwal KBM</span>
                </a>
                <span class="text-xs text-slate-400 font-medium">Portal Tabungan</span>
            </div>

            <?php if(isset($_SESSION['flash_success'])): ?>
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2 shadow-soft-sm animate-fade-in">
                    <span class="text-base">✅</span>
                    <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    <?php unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['flash_error'])): ?>
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2 shadow-soft-sm animate-fade-in">
                    <span class="text-base">⚠️</span>
                    <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    <?php unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <?php if(empty($programList)): ?>
                <div class="bg-white rounded-3xl p-8 text-center border border-slate-200/80 shadow-soft-sm">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl">
                        💳
                    </div>
                    <h3 class="font-bold text-slate-800 mb-1">Tidak Ada Program Tabungan</h3>
                    <p class="text-xs text-slate-500">Belum ada program tabungan aktif yang tersedia saat ini.</p>
                </div>
            <?php else: ?>
                <div class="flex justify-between items-center px-1">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Program Tabungan Aktif
                    </h2>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-200">
                        <?= count($programList) ?> Program
                    </span>
                </div>

                <div class="grid gap-3.5">
                    <?php foreach($programList as $p): ?>
                    <a href="<?= App::baseUrl('guru/tabungan/program/' . $p['id']) ?>" class="block bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-soft-sm hover:shadow-md hover:border-indigo-300 transition-all active:scale-[0.99] group">
                        <?php if($p['foto']): ?>
                            <div class="h-32 w-full bg-slate-100 overflow-hidden">
                                <img src="<?= App::baseUrl($p['foto']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        <?php endif; ?>
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2.5 gap-2">
                                <h3 class="font-bold text-slate-900 text-base leading-snug group-hover:text-indigo-600 transition-colors">
                                    <?= htmlspecialchars($p['nama_program']) ?>
                                </h3>
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shrink-0">
                                    Aktif
                                </span>
                            </div>
                            
                            <div class="space-y-1.5 mb-3.5 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                <div class="flex items-center text-xs justify-between">
                                    <span class="text-slate-500">Target Saldo:</span>
                                    <span class="font-bold font-mono text-slate-800">Rp <?= number_format($p['target_nominal'], 0, ',', '.') ?></span>
                                </div>
                                <div class="flex items-center text-xs justify-between">
                                    <span class="text-slate-500">Batas Waktu:</span>
                                    <span class="text-slate-700 font-medium"><?= $p['target_tanggal'] ? date('d M Y', strtotime($p['target_tanggal'])) : 'Tanpa batas waktu' ?></span>
                                </div>
                                <div class="flex items-center text-xs justify-between">
                                    <span class="text-slate-500">Sasaran Rombel:</span>
                                    <span class="text-slate-700 font-medium"><?= $p['kelas_id'] ? $p['nama_kelas'] : 'Semua Kelas' ?></span>
                                </div>
                            </div>

                            <?php $isPengelolaProgram = ($p['pengelola_id'] == $user['id'] || $p['asisten_pengelola_id'] == $user['id'] || $user['role'] == 'admin'); ?>
                            <?php if($isPengelolaProgram): ?>
                            <div class="bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white font-bold py-2.5 rounded-xl text-center text-xs w-full border border-indigo-200/80 transition-all flex items-center justify-center gap-1.5">
                                <span>Kelola Setoran Siswa</span>
                                <span>➜</span>
                            </div>
                            <?php else: ?>
                            <div class="bg-slate-50 hover:bg-slate-100 text-slate-600 font-bold py-2.5 rounded-xl text-center text-xs w-full border border-slate-200/80 transition-all flex items-center justify-center gap-1.5">
                                <span>Lihat Data Tabungan</span>
                                <span>➜</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <!-- Bottom Navigation Mobile -->
        <?php require __DIR__ . '/partials/bottom_nav.php'; ?>
    </div>

    <script>
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

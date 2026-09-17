<?php
use App\Config\App;
use App\Helpers\TimeHelper;
$activeNav = 'lms';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>LMS Guru - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK AL-FARIZI') ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200/90 shadow-soft-lg flex flex-col pb-28">
        
        <!-- Bright Smartphone Top Header -->
        <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-xs px-5 py-3.5">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <a href="<?= App::baseUrl('guru/dashboard') ?>" class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 hover:bg-purple-100 flex items-center justify-center text-base font-bold transition border border-purple-200/80 shadow-xs" title="Kembali">
                        ◀
                    </a>
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200/80 uppercase tracking-wider">
                                LMS E-Learning
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
                    <span class="text-purple-600">📅</span>
                    <span><?= TimeHelper::formatDateIndonesian(date('Y-m-d')) ?></span>
                </div>
                <div class="flex items-center gap-1 bg-slate-50 border border-slate-200/80 px-2 py-0.5 rounded-lg text-slate-700 font-mono font-bold text-xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span id="liveClock">--:--:--</span>
                </div>
            </div>
        </header>

        <main class="p-4 flex-1 space-y-4">
            <!-- Back to Dashboard Quick Action -->
            <div class="flex items-center justify-between">
                <a href="<?= App::baseUrl('guru/dashboard') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-purple-600 hover:text-purple-700 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl transition border border-purple-100">
                    <span>◀</span>
                    <span>Kembali ke Jadwal KBM</span>
                </a>
                <span class="text-xs text-slate-400 font-medium">Portal Materi LMS</span>
            </div>

            <?php if(empty($kelasMapel)): ?>
                <div class="p-8 rounded-3xl bg-white border border-slate-200/80 shadow-soft-sm text-center">
                    <div class="text-4xl mb-2">📚</div>
                    <p class="text-sm font-bold text-slate-800">Belum Ada Jadwal Mengajar KBM</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                        Anda belum memiliki jadwal mengajar mata pelajaran pada rombel aktif saat ini.
                    </p>
                </div>
            <?php else: ?>
                <div class="flex justify-between items-center px-1">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Kelas & Mata Pelajaran Diampu
                    </h2>
                    <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2.5 py-0.5 rounded-full border border-purple-200">
                        <?= count($kelasMapel) ?> Rombel
                    </span>
                </div>

                <div class="space-y-3">
                    <?php foreach($kelasMapel as $km): ?>
                    <a href="<?= App::baseUrl("guru/lms/materi/{$km['kelas_id']}/{$km['mapel_id']}") ?>" class="block bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-soft-sm hover:shadow-md hover:border-purple-300 transition-all active:scale-[0.99] group">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-bold text-slate-900 text-base leading-tight group-hover:text-purple-600 transition-colors">
                                    <?= htmlspecialchars($km['nama_mapel']) ?>
                                </h3>
                                <span class="bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider shrink-0">
                                    Aktif
                                </span>
                            </div>
                            
                            <div class="space-y-1.5 mb-3 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                                <div class="flex items-center text-xs text-slate-700">
                                    <span class="font-semibold">🏫 Kelas: <?= htmlspecialchars($km['nama_kelas']) ?> (<?= htmlspecialchars($km['jurusan']) ?>)</span>
                                </div>
                            </div>

                            <div class="bg-purple-50 hover:bg-purple-600 text-purple-700 hover:text-white font-bold py-2.5 rounded-xl text-center text-xs w-full border border-purple-200/80 transition-all flex items-center justify-center gap-1.5">
                                <span>Kelola Materi & Tugas</span>
                                <span>➜</span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <!-- Bottom Navigation Mobile -->
        <?php require __DIR__ . '/../partials/bottom_nav.php'; ?>
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

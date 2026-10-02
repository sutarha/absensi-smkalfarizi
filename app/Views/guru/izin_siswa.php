<?php
use App\Config\App;
use App\Helpers\TimeHelper;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Persetujuan Izin Siswa - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <!-- Centered Mobile Container -->
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200/90 shadow-soft-lg flex flex-col pb-28">
        
        <!-- Header -->
        <header class="bg-white border-b border-slate-200/90 sticky top-0 z-30 shadow-xs px-5 py-3.5 flex items-center gap-3">
            <a href="<?= App::baseUrl('guru/dashboard') ?>" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-600 transition-colors">
                <span class="text-xl">⬅️</span>
            </a>
            <div>
                <h1 class="text-base font-bold text-slate-900 leading-tight">Persetujuan Izin</h1>
                <p class="text-[11px] text-slate-500 font-medium">Khusus Wali Kelas</p>
            </div>
        </header>

        <main class="p-4 flex-1 space-y-4">
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2 shadow-soft-sm">
                <span class="text-base">✅</span>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2 shadow-soft-sm">
                <span class="text-base">⚠️</span>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Filter Status -->
            <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-sm flex overflow-x-auto gap-2 scrollbar-hide">
                <a href="<?= App::baseUrl('guru/izin-siswa') ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap <?= empty($status) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600' ?>">Semua</a>
                <a href="<?= App::baseUrl('guru/izin-siswa?status=PENDING') ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap <?= $status === 'PENDING' ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600' ?>">Pending</a>
                <a href="<?= App::baseUrl('guru/izin-siswa?status=DISETUJUI') ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap <?= $status === 'DISETUJUI' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-600' ?>">Disetujui</a>
                <a href="<?= App::baseUrl('guru/izin-siswa?status=DITOLAK') ?>" class="px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap <?= $status === 'DITOLAK' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-600' ?>">Ditolak</a>
            </div>

            <div class="space-y-3">
                <?php if (empty($pengajuanList)): ?>
                <div class="text-center py-10 px-4 bg-white rounded-3xl border border-slate-200 border-dashed">
                    <span class="text-4xl mb-3 block">📭</span>
                    <p class="text-sm font-bold text-slate-700">Tidak Ada Pengajuan</p>
                    <p class="text-xs text-slate-500 mt-1">Belum ada data pengajuan izin untuk saat ini.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($pengajuanList as $p): ?>
                        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                            <div class="p-4 border-b border-slate-100">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-bold text-slate-800"><?= htmlspecialchars($p['nama_siswa']) ?></h3>
                                        <div class="flex gap-2 items-center mt-1">
                                            <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full border border-slate-200"><?= htmlspecialchars($p['nama_kelas']) ?></span>
                                            <span class="text-[10px] bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full border border-indigo-200 font-medium"><?= htmlspecialchars($p['jenis_izin']) ?></span>
                                        </div>
                                    </div>
                                    <?php if ($p['status'] === 'MENUNGGU'): ?>
                                        <span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-1 rounded-lg font-bold border border-amber-200 uppercase">Menunggu</span>
                                    <?php elseif ($p['status'] === 'DISETUJUI'): ?>
                                        <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-1 rounded-lg font-bold border border-emerald-200 uppercase">Disetujui</span>
                                    <?php else: ?>
                                        <span class="text-[10px] bg-rose-100 text-rose-700 px-2 py-1 rounded-lg font-bold border border-rose-200 uppercase">Ditolak</span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-3 bg-slate-50 rounded-xl p-3 border border-slate-100 text-xs text-slate-600">
                                    <div class="grid grid-cols-2 gap-2 mb-2">
                                        <div>
                                            <span class="block text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">Tanggal Mulai</span>
                                            <span class="font-medium text-slate-700"><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></span>
                                        </div>
                                        <div>
                                            <span class="block text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">Tanggal Selesai</span>
                                            <span class="font-medium text-slate-700"><?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="block text-[9px] text-slate-400 uppercase tracking-wider mb-0.5">Alasan</span>
                                        <p class="font-medium text-slate-700 line-clamp-2"><?= htmlspecialchars($p['alasan']) ?></p>
                                    </div>
                                    <?php if (!empty($p['file_lampiran'])): ?>
                                    <div class="mt-3">
                                        <a href="<?= App::baseUrl($p['file_lampiran']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-[10px] bg-blue-50 text-blue-600 px-2.5 py-1.5 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors font-medium">
                                            📎 Lihat Bukti
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <?php if ($p['status'] === 'MENUNGGU'): ?>
                            <div class="p-3 bg-slate-50/50">
                                <form action="<?= App::baseUrl('guru/izin-siswa/proses') ?>" method="POST" class="space-y-3">
                                    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                    <textarea name="catatan_guru" rows="2" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-xs" placeholder="Catatan opsional (misal: Cepat sembuh ya)"></textarea>
                                    <div class="flex gap-2">
                                        <button type="submit" name="status" value="DISETUJUI" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-xl text-xs font-bold transition-colors">
                                            ✅ Setujui
                                        </button>
                                        <button type="submit" name="status" value="DITOLAK" class="flex-1 bg-rose-600 hover:bg-rose-700 text-white py-2 rounded-xl text-xs font-bold transition-colors" onclick="return confirm('Tolak pengajuan izin ini?')">
                                            ❌ Tolak
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <?php elseif (!empty($p['catatan_guru'])): ?>
                            <div class="p-3 bg-slate-50/50 text-[11px]">
                                <span class="font-bold text-slate-700">Catatan Wali Kelas:</span>
                                <p class="text-slate-600 mt-1"><?= htmlspecialchars($p['catatan_guru']) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
        
        <?php $activeNav = 'beranda'; require __DIR__ . '/partials/bottom_nav.php'; ?>
    </div>
</body>
</html>

<?php
use App\Config\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tes & Kuis CBT - <?= htmlspecialchars($config['nama_sekolah'] ?? 'Sekolah') ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-24">
        
        <!-- Mobile Header -->
        <header class="bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white rounded-b-3xl shadow-soft-lg p-6 relative">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <a href="<?= App::baseUrl('guru/lms') ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-outfit font-extrabold tracking-tight text-white"><?= htmlspecialchars($mapelInfo['nama_mapel']) ?></h1>
                        <p class="text-xs text-slate-300">Kelas <?= htmlspecialchars($kelasInfo['nama_kelas']) ?></p>
                    </div>
                </div>
            </div>
            
            <button type="button" onclick="openUjianModal()" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold text-sm tracking-wide shadow-md flex items-center justify-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Buat Tes / Kuis Baru</span>
            </button>
        </header>

        <!-- 3 Modern Navigation Tabs -->
        <div class="flex border-b border-slate-200 bg-white shadow-sm sticky top-0 z-10">
            <a href="<?= App::baseUrl("guru/lms/materi/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1">
                <span>📁</span> Materi & Tugas
            </a>
            <a href="<?= App::baseUrl("guru/lms/ujian/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold border-b-2 border-brand-600 text-brand-600 flex items-center justify-center gap-1">
                <span>📝</span> Tes / CBT
            </a>
            <a href="<?= App::baseUrl("guru/lms/diskusi/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1">
                <span>💬</span> Diskusi
            </a>
        </div>

        <main class="p-4 flex-1 space-y-4">
            <?php if(isset($_SESSION['flash_success'])): ?>
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-2xl text-xs font-medium flex items-center gap-2">
                    <span>✅</span>
                    <span><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['flash_error'])): ?>
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-2xl text-xs font-medium flex items-center gap-2">
                    <span>⚠️</span>
                    <span><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
                </div>
            <?php endif; ?>

            <?php if(empty($ujianList)): ?>
                <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-sm">
                    <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 mb-1">Belum Ada Tes / Ujian</h3>
                    <p class="text-xs text-slate-500 mb-4">Buat tes pilihan ganda dan essay dengan batasan durasi pengerjaan otomatis.</p>
                    <button type="button" onclick="openUjianModal()" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition">
                        + Buat Ujian Sekarang
                    </button>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($ujianList as $u): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:border-slate-300 transition">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 mr-2">
                                    <h3 class="font-bold text-slate-800 text-base leading-snug mb-1"><?= htmlspecialchars($u['judul']) ?></h3>
                                    <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                        <span class="px-2 py-0.5 rounded-full font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            ⏱️ <?= (int)$u['durasi_menit'] ?> Menit
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                            🎯 KKM: <?= number_format($u['kkm'], 0) ?>
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full font-bold <?= (int)$u['is_aktif'] === 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' ?>">
                                            <?= (int)$u['is_aktif'] === 1 ? '🟢 Aktif' : '⚪ Nonaktif' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <!-- Toggle Aktif -->
                                    <form action="<?= App::baseUrl("guru/lms/ujian/toggle/{$u['id']}/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" method="POST">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                        <button type="submit" class="w-8 h-8 rounded-lg <?= (int)$u['is_aktif'] === 1 ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-slate-100 text-slate-400 hover:bg-slate-200' ?> flex items-center justify-center transition" title="Ubah Status Aktif">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        </button>
                                    </form>
                                    <!-- Hapus -->
                                    <form action="<?= App::baseUrl("guru/lms/ujian/delete/{$u['id']}/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" method="POST" onsubmit="return confirm('Hapus tes/kuis ini beserta seluruh soal dan data pengerjaan?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center transition" title="Hapus Ujian">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <?php if(!empty($u['deskripsi'])): ?>
                                <p class="text-xs text-slate-500 mb-3 whitespace-pre-wrap leading-relaxed"><?= nl2br(htmlspecialchars($u['deskripsi'])) ?></p>
                            <?php endif; ?>

                            <!-- Stat Soal & Peserta -->
                            <div class="grid grid-cols-3 gap-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100 mb-3 text-center">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Pilihan Ganda</div>
                                    <div class="text-sm font-extrabold text-blue-600"><?= (int)$u['total_pg'] ?> Soal</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Soal Essay</div>
                                    <div class="text-sm font-extrabold text-purple-600"><?= (int)$u['total_essay'] ?> Soal</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase">Peserta Mengerjakan</div>
                                    <div class="text-sm font-extrabold text-emerald-600"><?= (int)$u['total_peserta'] ?> Siswa</div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="grid grid-cols-2 gap-2">
                                <a href="<?= App::baseUrl("guru/lms/ujian/soal/{$u['id']}") ?>" class="py-2.5 px-3 rounded-xl bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold text-xs flex items-center justify-center gap-1.5 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Kelola Butir Soal</span>
                                </a>
                                <a href="<?= App::baseUrl("guru/lms/ujian/hasil/{$u['id']}") ?>" class="py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    <span>Lihat Hasil & Nilai</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Tambah Ujian -->
    <div id="ujianModal" class="hidden fixed inset-0 z-50 flex justify-center items-end bg-slate-900/50 backdrop-blur-sm sm:items-center">
        <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Buat Tes / CBT Baru</h3>
                <button type="button" onclick="closeUjianModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="overflow-y-auto p-5">
                <form action="<?= App::baseUrl("guru/lms/ujian/store/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" method="POST" class="space-y-4">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Judul Tes / Ujian</label>
                        <input type="text" name="judul" required placeholder="Contoh: Kuis Harian 1 / UTS Semester Ganjil" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Petunjuk Pengerjaan</label>
                        <textarea name="deskripsi" rows="3" placeholder="Contoh: Kerjakan secara mandiri, waktu otomatis berjalan saat menekan tombol mulai." class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-700">Durasi Pengerjaan</label>
                            <div class="relative">
                                <input type="number" name="durasi_menit" value="60" min="5" max="300" required class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5 pr-12">
                                <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-bold">Menit</span>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Timer realtime otomatis</p>
                        </div>
                        <div>
                            <label class="block mb-1 text-xs font-bold text-slate-700">Nilai KKM</label>
                            <input type="number" step="0.5" name="kkm" value="75" min="0" max="100" required class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5">
                            <p class="text-[10px] text-slate-400 mt-1">Standar kelulusan</p>
                        </div>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full text-white bg-brand-600 hover:bg-brand-700 font-bold rounded-xl text-sm px-5 py-3 text-center transition shadow-soft-sm">
                            Lanjut: Tambahkan Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Nav -->
    <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white/95 backdrop-blur-xl border-t border-slate-200/80 shadow-soft-xl flex justify-around py-2.5 px-2 z-30">
        <a href="<?= App::baseUrl('guru/dashboard') ?>" class="flex flex-col items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-brand-600 transition-colors">
            <span class="text-lg">📅</span><span>KBM</span>
        </a>
        <a href="<?= App::baseUrl('guru/nilai') ?>" class="flex flex-col items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-brand-600 transition-colors">
            <span class="text-lg">📝</span><span>Nilai</span>
        </a>
        <a href="<?= App::baseUrl('guru/lms') ?>" class="flex flex-col items-center gap-1 text-[10px] font-bold text-brand-600 transition-colors">
            <span class="text-lg">📚</span><span>LMS</span>
        </a>
    </nav>

    <script>
        function openUjianModal() {
            document.getElementById('ujianModal').classList.remove('hidden');
        }
        function closeUjianModal() {
            document.getElementById('ujianModal').classList.add('hidden');
        }
    </script>
</body>
</html>

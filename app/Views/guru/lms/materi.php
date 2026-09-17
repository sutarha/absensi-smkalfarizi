<?php
use App\Config\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Materi & Tugas LMS - <?= htmlspecialchars($config['nama_sekolah'] ?? 'Sekolah') ?></title>
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
            
            <button type="button" onclick="openTambahModal()" class="w-full py-2.5 px-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm tracking-wide shadow-soft-sm flex items-center justify-center gap-2 transition-all duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Materi / Tugas</span>
            </button>
        </header>

        <!-- 3 Modern Navigation Tabs -->
        <div class="flex border-b border-slate-200 bg-white shadow-sm sticky top-0 z-10">
            <a href="<?= App::baseUrl("guru/lms/materi/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold border-b-2 border-brand-600 text-brand-600 flex items-center justify-center gap-1">
                <span>📁</span> Materi & Tugas
            </a>
            <a href="<?= App::baseUrl("guru/lms/ujian/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1">
                <span>📝</span> Tes / CBT
            </a>
            <a href="<?= App::baseUrl("guru/lms/diskusi/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" class="flex-1 py-3 text-center text-xs font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1">
                <span>💬</span> Diskusi
            </a>
        </div>

        <main class="p-4 flex-1 space-y-4">
            <?php if(isset($_SESSION['flash_success'])): ?>
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
                    <span>✅</span>
                    <span><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['flash_error'])): ?>
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-2xl text-sm font-medium flex items-center gap-2">
                    <span>⚠️</span>
                    <span><?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?></span>
                </div>
            <?php endif; ?>

            <?php if(empty($materiList)): ?>
                <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-sm">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 mb-1">Belum Ada Konten</h3>
                    <p class="text-xs text-slate-500">Klik tombol di atas untuk menambah modul materi atau instruksi tugas.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($materiList as $m): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:border-slate-300 transition">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 mr-2">
                                    <h3 class="font-bold text-slate-800 text-base leading-snug mb-1"><?= htmlspecialchars($m['judul']) ?></h3>
                                    <div class="flex items-center gap-2">
                                        <?php if(strtoupper($m['tipe']) === 'MATERI'): ?>
                                            <span class="bg-sky-100 text-sky-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Materi</span>
                                        <?php else: ?>
                                            <span class="bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Tugas</span>
                                        <?php endif; ?>
                                        <span class="text-[11px] text-slate-400"><?= date('d M Y H:i', strtotime($m['created_at'])) ?></span>
                                    </div>
                                </div>
                                <form action="<?= App::baseUrl("guru/lms/materi/delete/{$m['id']}/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" method="POST" onsubmit="return confirm('Hapus konten ini?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center transition" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            
                            <p class="text-xs text-slate-600 mb-3 whitespace-pre-wrap leading-relaxed">
                                <?= nl2br(htmlspecialchars($m['deskripsi'])) ?>
                            </p>

                            <?php if($m['file_path']): ?>
                                <a href="<?= App::baseUrl($m['file_path']) ?>" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-brand-600 font-bold bg-brand-50 px-3 py-1.5 rounded-xl mb-3 hover:bg-brand-100 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                    Unduh Lampiran File
                                </a>
                            <?php endif; ?>

                            <?php if(strtoupper($m['tipe']) === 'TUGAS'): ?>
                                <div class="bg-amber-50 rounded-xl p-3 mb-3 border border-amber-100">
                                    <div class="text-[10px] font-bold text-amber-800 uppercase tracking-wider mb-0.5">Tenggat Waktu</div>
                                    <div class="text-xs font-bold text-amber-900">
                                        <?= $m['deadline'] ? date('d M Y, H:i', strtotime($m['deadline'])) : 'Tanpa batas waktu' ?>
                                    </div>
                                </div>
                                <a href="<?= App::baseUrl("guru/lms/submission/{$m['id']}") ?>" class="block w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-center text-xs transition shadow-soft-sm">
                                    Review & Nilai Jawaban Siswa (<?= $m['jumlah_submission'] ?>)
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Tambah Materi / Tugas -->
    <div id="tambahModal" class="hidden fixed inset-0 z-50 flex justify-center items-end bg-slate-900/50 backdrop-blur-sm sm:items-center">
        <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Konten Baru</h3>
                <button type="button" onclick="closeTambahModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="overflow-y-auto p-5">
                <form action="<?= App::baseUrl("guru/lms/materi/store/{$kelasInfo['id']}/{$mapelInfo['id']}") ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Tipe Konten</label>
                        <select name="tipe" id="tipeSelect" onchange="toggleDeadline()" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5">
                            <option value="materi">Modul Materi Belajar</option>
                            <option value="tugas">Penugasan Siswa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Judul</label>
                        <input type="text" name="judul" required placeholder="Contoh: Bab 1: Pengenalan Basis Data" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Deskripsi / Petunjuk</label>
                        <textarea name="deskripsi" rows="4" required placeholder="Tulis ringkasan materi atau instruksi tugas..." class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5"></textarea>
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">File Lampiran (Opsional)</label>
                        <input type="file" name="file_lampiran" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 border border-slate-200 rounded-xl bg-slate-50">
                        <p class="mt-1 text-[10px] text-slate-400">PDF, DOCX, PPTX, JPG, PNG (Maks 10MB)</p>
                    </div>
                    <div id="deadlineContainer" class="hidden">
                        <label class="block mb-1 text-xs font-bold text-slate-700">Tenggat Waktu Pengumpulan</label>
                        <input type="datetime-local" name="deadline" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5">
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full text-white bg-brand-600 hover:bg-brand-700 font-bold rounded-xl text-sm px-5 py-3 text-center transition shadow-soft-sm">
                            Simpan & Publikasikan
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
        function openTambahModal() {
            document.getElementById('tambahModal').classList.remove('hidden');
        }
        function closeTambahModal() {
            document.getElementById('tambahModal').classList.add('hidden');
        }
        function toggleDeadline() {
            const tipe = document.getElementById('tipeSelect').value;
            const container = document.getElementById('deadlineContainer');
            if(tipe === 'tugas') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    </script>
</body>
</html>

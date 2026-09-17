<?php
use App\Config\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Review Tugas - <?= htmlspecialchars($config['nama_sekolah'] ?? 'Sekolah') ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-24">
        
        <!-- Mobile Header -->
        <header class="bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white rounded-b-3xl shadow-soft-lg p-6 relative">
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= App::baseUrl("guru/lms/materi/{$materi['kelas_id']}/{$materi['mapel_id']}") ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h1 class="text-lg font-outfit font-extrabold tracking-tight text-white line-clamp-1"><?= htmlspecialchars($materi['judul']) ?></h1>
            </div>
            <p class="text-[11px] text-slate-300">Tenggat: <?= $materi['deadline'] ? date('d M Y H:i', strtotime($materi['deadline'])) : 'Tidak ada' ?></p>
        </header>

        <main class="p-5 flex-1 space-y-4">
            <?php if(isset($_SESSION['flash_success'])): ?>
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-2xl text-sm font-medium">
                    <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if(empty($submissions)): ?>
                <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-sm">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 mb-1">Belum Ada Pengumpulan</h3>
                    <p class="text-sm text-slate-500">Belum ada siswa yang mengumpulkan tugas ini.</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($submissions as $s): 
                        $is_terlambat = $materi['deadline'] && (strtotime($s['submitted_at']) > strtotime($materi['deadline']));
                    ?>
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-bold text-slate-800 text-base leading-tight"><?= htmlspecialchars($s['nama_siswa']) ?></h3>
                                    <div class="text-xs text-slate-500 mt-0.5">NISN: <?= htmlspecialchars($s['nisn']) ?></div>
                                </div>
                                <?php if($s['nilai'] !== null): ?>
                                    <div class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-lg font-bold text-lg">
                                        <?= $s['nilai'] ?>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded-lg font-bold text-xs uppercase">
                                        Belum Dinilai
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center gap-2 mb-3">
                                <?php if($is_terlambat): ?>
                                    <span class="bg-rose-50 text-rose-600 text-[10px] font-bold px-2 py-0.5 rounded border border-rose-200">Terlambat</span>
                                <?php else: ?>
                                    <span class="bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2 py-0.5 rounded border border-emerald-200">Tepat Waktu</span>
                                <?php endif; ?>
                                <span class="text-[11px] text-slate-500 font-mono"><?= date('d/m/y H:i', strtotime($s['submitted_at'])) ?></span>
                            </div>

                            <?php if($s['jawaban']): ?>
                                <div class="bg-slate-50 rounded-xl p-3 text-sm text-slate-700 mb-3 border border-slate-100">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase mb-1">Teks Jawaban:</div>
                                    <?= nl2br(htmlspecialchars($s['jawaban'])) ?>
                                </div>
                            <?php endif; ?>

                            <?php if($s['file_path']): ?>
                                <a href="<?= App::baseUrl($s['file_path']) ?>" target="_blank" class="inline-flex items-center justify-center w-full gap-2 text-sm text-brand-700 font-medium bg-brand-50 border border-brand-100 px-3 py-2 rounded-xl mb-3 hover:bg-brand-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh File Jawaban
                                </a>
                            <?php endif; ?>

                            <?php if($s['catatan_guru']): ?>
                                <div class="bg-indigo-50 rounded-xl p-3 text-sm text-indigo-700 mb-3 border border-indigo-100">
                                    <div class="text-[10px] font-bold text-indigo-400 uppercase mb-1">Catatan Anda:</div>
                                    <?= nl2br(htmlspecialchars($s['catatan_guru'])) ?>
                                </div>
                            <?php endif; ?>

                            <button type="button" onclick="openNilaiModal(<?= $s['id'] ?>, <?= $s['nilai'] ?? 0 ?>, '<?= htmlspecialchars(addslashes($s['catatan_guru'] ?? '')) ?>')" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-xl text-center text-sm transition shadow-sm">
                                <?= $s['nilai'] !== null ? 'Ubah Nilai' : 'Beri Nilai' ?>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Nilai -->
    <div id="nilaiModal" class="hidden fixed inset-0 z-50 flex justify-center items-end bg-slate-900/50 backdrop-blur-sm sm:items-center">
        <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-5 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Beri Nilai</h3>
                <button type="button" onclick="closeNilaiModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-5">
                <form id="formNilai" action="" method="POST" class="space-y-4">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-slate-700">Nilai (0-100)</label>
                        <input type="number" name="nilai" id="inputNilai" min="0" max="100" step="0.01" class="bg-slate-50 border border-slate-200 text-slate-900 font-bold text-lg rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-3 text-center" required>
                    </div>
                    <div>
                        <label class="block mb-1.5 text-sm font-bold text-slate-700">Catatan untuk Siswa (Opsional)</label>
                        <textarea name="catatan" id="inputCatatan" rows="3" class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5"></textarea>
                    </div>
                    <div class="pt-2">
                        <button type="submit" class="w-full text-white bg-brand-600 hover:bg-brand-700 font-bold rounded-xl text-sm px-5 py-3 text-center transition-colors">
                            Simpan Penilaian
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openNilaiModal(submissionId, nilai, catatan) {
            document.getElementById('nilaiModal').classList.remove('hidden');
            document.getElementById('inputNilai').value = nilai;
            document.getElementById('inputCatatan').value = catatan;
            document.getElementById('formNilai').action = '<?= App::baseUrl("guru/lms/submission/nilai/") ?>' + submissionId + '/<?= $materi['id'] ?>';
        }
        function closeNilaiModal() {
            document.getElementById('nilaiModal').classList.add('hidden');
        }
    </script>
</body>
</html>

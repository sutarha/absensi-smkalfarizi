<?php
use App\Config\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kelola Butir Soal - <?= htmlspecialchars($ujian['judul']) ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-24">
        
        <!-- Mobile Header -->
        <header class="bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white rounded-b-3xl shadow-soft-lg p-6 relative">
            <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <a href="<?= App::baseUrl("guru/lms/ujian/{$ujian['kelas_id']}/{$ujian['mapel_id']}") ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    <div>
                        <h1 class="text-base font-outfit font-extrabold tracking-tight text-white"><?= htmlspecialchars($ujian['judul']) ?></h1>
                        <p class="text-xs text-slate-300"><?= htmlspecialchars($ujian['nama_mapel']) ?> • Kelas <?= htmlspecialchars($ujian['nama_kelas']) ?></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs bg-white/10 backdrop-blur-md rounded-2xl p-3 border border-white/20 mb-4">
                <div>
                    <span class="text-slate-300">Durasi:</span>
                    <span class="font-bold text-white ml-1"><?= (int)$ujian['durasi_menit'] ?> Menit</span>
                </div>
                <div>
                    <span class="text-slate-300">KKM:</span>
                    <span class="font-bold text-white ml-1"><?= number_format($ujian['kkm'], 0) ?></span>
                </div>
                <div>
                    <span class="text-slate-300">Total:</span>
                    <span class="font-bold text-amber-300 ml-1"><?= count($soalList) ?> Soal</span>
                </div>
            </div>
            
            <button type="button" onclick="openTambahSoalModal()" class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-sm tracking-wide shadow-md flex items-center justify-center gap-2 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Butir Soal (PG / Essay)</span>
            </button>
        </header>

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

            <?php if(empty($soalList)): ?>
                <div class="bg-white rounded-2xl p-6 text-center border border-slate-200 shadow-sm">
                    <div class="w-14 h-14 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm mb-1">Belum Ada Soal</h3>
                    <p class="text-xs text-slate-500 mb-4">Klik tombol di atas untuk menambahkan soal pilihan ganda atau essay.</p>
                    <button type="button" onclick="openTambahSoalModal()" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition">
                        + Tambah Soal Pertama
                    </button>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach($soalList as $idx => $s): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:border-slate-300 transition">
                        <div class="p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-slate-900 text-white font-black text-xs flex items-center justify-center">
                                        <?= $idx + 1 ?>
                                    </span>
                                    <?php if($s['tipe_soal'] === 'essay'): ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 uppercase tracking-wider">
                                            ✍️ Essay
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">
                                            🔘 Pilihan Ganda
                                        </span>
                                    <?php endif; ?>
                                    <span class="text-[11px] text-slate-500 font-semibold">Bobot: <?= number_format($s['bobot_nilai'], 1) ?></span>
                                </div>
                                <form action="<?= App::baseUrl("guru/lms/ujian/soal/delete/{$s['id']}/{$ujian['id']}") ?>" method="POST" onsubmit="return confirm('Hapus butir soal no <?= $idx + 1 ?>?')">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center transition" title="Hapus Soal">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>

                            <p class="text-xs text-slate-800 font-medium whitespace-pre-wrap leading-relaxed mb-3">
                                <?= nl2br(htmlspecialchars($s['pertanyaan'])) ?>
                            </p>

                            <?php if(!empty($s['gambar'])): ?>
                                <img src="<?= App::baseUrl($s['gambar']) ?>" alt="Gambar Soal" class="rounded-xl max-h-48 w-auto object-cover mb-3 border border-slate-200">
                            <?php endif; ?>

                            <?php if($s['tipe_soal'] === 'pilihan_ganda'): ?>
                                <div class="space-y-1.5 text-xs">
                                    <?php foreach(['A', 'B', 'C', 'D', 'E'] as $opt): ?>
                                        <?php 
                                            $key = 'pilihan_' . strtolower($opt);
                                            if(!empty($s[$key])):
                                                $isKunci = (strtoupper($s['kunci_jawaban']) === $opt);
                                        ?>
                                            <div class="flex items-start gap-2 p-2 rounded-xl border <?= $isKunci ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-bold' : 'bg-slate-50 border-slate-100 text-slate-600' ?>">
                                                <span class="w-5 h-5 rounded-lg <?= $isKunci ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' ?> flex items-center justify-center font-bold text-[10px] shrink-0">
                                                    <?= $opt ?>
                                                </span>
                                                <span class="leading-tight pt-0.5"><?= htmlspecialchars($s[$key]) ?></span>
                                                <?php if($isKunci): ?>
                                                    <span class="ml-auto text-[10px] uppercase text-emerald-700 bg-emerald-200/60 px-1.5 py-0.5 rounded">Kunci</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="p-3 bg-purple-50/50 rounded-xl border border-purple-100 text-purple-900 text-xs flex items-center gap-2">
                                    <span>💡</span>
                                    <span>Soal essay akan dikoreksi dan dinilai secara manual oleh guru pada menu Hasil Ujian.</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Tambah Butir Soal (Supports Pilihan Ganda & Essay) -->
    <div id="tambahSoalModal" class="hidden fixed inset-0 z-50 flex justify-center items-end bg-slate-900/50 backdrop-blur-sm sm:items-center">
        <div class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-base font-bold text-slate-900">Tambah Butir Soal</h3>
                <button type="button" onclick="closeTambahSoalModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-slate-500 hover:bg-slate-200 border border-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="overflow-y-auto p-5">
                <form action="<?= App::baseUrl("guru/lms/ujian/soal/store/{$ujian['id']}") ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                                                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                    
                    <!-- Pilihan Tipe Soal -->
                    <div>
                        <label class="block mb-1.5 text-xs font-bold text-slate-700">Tipe Butir Soal</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center justify-center gap-2 p-3 rounded-2xl border-2 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 border-slate-200 bg-white">
                                <input type="radio" name="tipe_soal" value="pilihan_ganda" checked onchange="toggleTipeSoal('pilihan_ganda')" class="text-blue-600 focus:ring-blue-500">
                                <span class="text-xs font-bold text-slate-800">🔘 Pilihan Ganda</span>
                            </label>
                            <label class="flex items-center justify-center gap-2 p-3 rounded-2xl border-2 cursor-pointer transition has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50 border-slate-200 bg-white">
                                <input type="radio" name="tipe_soal" value="essay" onchange="toggleTipeSoal('essay')" class="text-purple-600 focus:ring-purple-500">
                                <span class="text-xs font-bold text-slate-800">✍️ Soal Essay</span>
                            </label>
                        </div>
                    </div>

                    <!-- Pertanyaan -->
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Pertanyaan / Soal</label>
                        <textarea name="pertanyaan" rows="3" required placeholder="Tuliskan butir soal di sini..." class="bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5"></textarea>
                    </div>

                    <!-- Gambar Opsional -->
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Gambar Soal (Opsional)</label>
                        <input type="file" name="gambar" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl bg-slate-50">
                    </div>

                    <!-- Bobot Nilai -->
                    <div>
                        <label class="block mb-1 text-xs font-bold text-slate-700">Bobot Nilai / Poin</label>
                        <input type="number" step="0.5" name="bobot_nilai" id="bobotNilaiInput" value="1.0" min="0.5" max="100" class="bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2.5">
                    </div>

                    <!-- Container Opsi Pilihan Ganda -->
                    <div id="pgContainer" class="space-y-3 pt-2 border-t border-slate-100">
                        <div class="text-xs font-bold text-slate-700 flex items-center justify-between">
                            <span>Pilihan Jawaban (A - E)</span>
                            <span class="text-[10px] text-slate-400">Minimal opsi A - D</span>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">A</span>
                                <input type="text" name="pilihan_a" id="pilihanA" placeholder="Teks opsi A..." class="bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">B</span>
                                <input type="text" name="pilihan_b" id="pilihanB" placeholder="Teks opsi B..." class="bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">C</span>
                                <input type="text" name="pilihan_c" id="pilihanC" placeholder="Teks opsi C..." class="bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">D</span>
                                <input type="text" name="pilihan_d" id="pilihanD" placeholder="Teks opsi D..." class="bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2">
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">E</span>
                                <input type="text" name="pilihan_e" placeholder="Teks opsi E (opsional)..." class="bg-slate-50 border border-slate-200 text-slate-900 text-xs rounded-xl focus:ring-brand-500 focus:border-brand-500 block w-full p-2">
                            </div>
                        </div>

                        <!-- Kunci Jawaban -->
                        <div class="pt-2">
                            <label class="block mb-1 text-xs font-bold text-slate-700">Kunci Jawaban Benar</label>
                            <select name="kunci_jawaban" id="kunciJawabanSelect" class="bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-bold rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full p-2.5">
                                <option value="A">Pilihan A</option>
                                <option value="B">Pilihan B</option>
                                <option value="C">Pilihan C</option>
                                <option value="D">Pilihan D</option>
                                <option value="E">Pilihan E</option>
                            </select>
                        </div>
                    </div>

                    <!-- Container Info Essay -->
                    <div id="essayContainer" class="hidden p-3 bg-purple-50 rounded-2xl border border-purple-200 text-purple-900 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <span>✍️ Mode Soal Essay</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Siswa akan diberikan kolom textarea untuk mengetikkan uraian jawaban. Jawaban essay dikoreksi langsung oleh guru.
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full text-white bg-brand-600 hover:bg-brand-700 font-bold rounded-xl text-sm px-5 py-3 text-center transition shadow-soft-sm">
                            Simpan Butir Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openTambahSoalModal() {
            document.getElementById('tambahSoalModal').classList.remove('hidden');
        }
        function closeTambahSoalModal() {
            document.getElementById('tambahSoalModal').classList.add('hidden');
        }

        function toggleTipeSoal(tipe) {
            const pgContainer = document.getElementById('pgContainer');
            const essayContainer = document.getElementById('essayContainer');
            const bobotInput = document.getElementById('bobotNilaiInput');
            const pilA = document.getElementById('pilihanA');
            const pilB = document.getElementById('pilihanB');
            const pilC = document.getElementById('pilihanC');
            const pilD = document.getElementById('pilihanD');

            if (tipe === 'essay') {
                pgContainer.classList.add('hidden');
                essayContainer.classList.remove('hidden');
                bobotInput.value = '5.0';
                pilA.required = false;
                pilB.required = false;
                pilC.required = false;
                pilD.required = false;
            } else {
                pgContainer.classList.remove('hidden');
                essayContainer.classList.add('hidden');
                bobotInput.value = '1.0';
                pilA.required = true;
                pilB.required = true;
                pilC.required = true;
                pilD.required = true;
            }
        }
    </script>
</body>
</html>

<?php
use App\Config\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lembar Jawaban - <?= htmlspecialchars($detail['nama_siswa']) ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-24">
        
        <!-- Mobile Header -->
        <header class="bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white rounded-b-3xl shadow-soft-lg p-6 relative">
            <div class="flex items-center gap-3 mb-4">
                <a href="<?= App::baseUrl("guru/lms/ujian/hasil/{$detail['ujian_id']}") ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h1 class="text-base font-outfit font-extrabold tracking-tight text-white"><?= htmlspecialchars($detail['nama_siswa']) ?></h1>
                    <p class="text-xs text-slate-300"><?= htmlspecialchars($detail['judul_ujian']) ?></p>
                </div>
            </div>

            <!-- Score Summary Card -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/20">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-slate-300 uppercase font-semibold">Total Nilai Akhir</div>
                        <div class="text-3xl font-black text-white"><?= number_format($detail['nilai_akhir'], 1) ?></div>
                        <div class="text-[11px] text-amber-300 font-bold mt-0.5">KKM: <?= number_format($detail['kkm'], 0) ?></div>
                    </div>
                    <div class="space-y-1 text-right text-xs">
                        <div class="bg-white/20 px-2.5 py-1 rounded-lg">
                            <span class="text-slate-300">Skor PG:</span>
                            <span class="font-bold text-white ml-1"><?= number_format($detail['nilai_pg'], 1) ?></span>
                        </div>
                        <div class="bg-white/20 px-2.5 py-1 rounded-lg">
                            <span class="text-slate-300">Skor Essay:</span>
                            <span class="font-bold text-purple-300 ml-1"><?= number_format($detail['nilai_essay'], 1) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-4 flex-1 space-y-4">
            <?php if(isset($_SESSION['flash_success'])): ?>
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-2xl text-xs font-medium flex items-center gap-2">
                    <span>✅</span>
                    <span><?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?></span>
                </div>
            <?php endif; ?>

            <div class="text-xs font-bold text-slate-500 uppercase tracking-wider px-1">Lembar Butir Jawaban Siswa</div>

            <div class="space-y-4">
                <?php foreach($soalJawaban as $idx => $item): ?>
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-900 text-white font-black text-xs flex items-center justify-center">
                                    <?= $idx + 1 ?>
                                </span>
                                <?php if($item['tipe_soal'] === 'essay'): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800">✍️ Essay</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">🔘 Pilihan Ganda</span>
                                <?php endif; ?>
                            </div>
                            <span class="text-[11px] text-slate-400 font-semibold">Bobot: <?= number_format($item['bobot_nilai'], 1) ?></span>
                        </div>

                        <p class="text-xs text-slate-800 font-semibold whitespace-pre-wrap leading-relaxed mb-3">
                            <?= nl2br(htmlspecialchars($item['pertanyaan'])) ?>
                        </p>

                        <?php if($item['tipe_soal'] === 'pilihan_ganda'): ?>
                            <?php 
                                $jawabanSiswa = strtoupper($item['jawaban_pg'] ?? '');
                                $kunci = strtoupper($item['kunci_jawaban'] ?? '');
                                $isBenar = (!empty($jawabanSiswa) && $jawabanSiswa === $kunci);
                            ?>
                            <div class="p-3 rounded-xl border <?= $isBenar ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' ?> text-xs space-y-1.5 mb-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold <?= $isBenar ? 'text-emerald-800' : 'text-rose-800' ?>">
                                        <?= $isBenar ? '✅ Jawaban Benar (+'.$item['bobot_nilai'].' Poin)' : '❌ Jawaban Salah (0 Poin)' ?>
                                    </span>
                                </div>
                                <div class="text-slate-600">
                                    Jawaban Siswa: <strong class="<?= $isBenar ? 'text-emerald-700' : 'text-rose-700' ?>"><?= $jawabanSiswa ?: 'Tidak dijawab' ?></strong>
                                    <?php if(!$isBenar): ?>
                                        <span class="text-slate-400 ml-2">(Kunci Benar: <strong><?= $kunci ?></strong>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- ESSAY REVIEW & GRADING FORM -->
                            <div class="mb-3">
                                <div class="text-[11px] font-bold text-slate-600 mb-1">Jawaban Siswa:</div>
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-800 whitespace-pre-wrap leading-relaxed min-h-[60px]">
                                    <?= !empty($item['jawaban_essay']) ? htmlspecialchars($item['jawaban_essay']) : '<span class="text-slate-400 italic">Siswa tidak mengisi jawaban essay ini.</span>' ?>
                                </div>
                            </div>

                            <!-- Form Penilaian Essay -->
                            <form action="<?= App::baseUrl("guru/lms/ujian/nilai-essay/{$detail['id']}") ?>" method="POST" class="bg-purple-50/70 p-3 rounded-xl border border-purple-100 space-y-2.5">
                                <input type="hidden" name="soal_id" value="<?= $item['id'] ?>">
                                
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-bold text-purple-900">Beri Nilai Butir Ini:</label>
                                    <div class="flex items-center gap-1">
                                        <input type="number" step="0.5" min="0" max="<?= (float)$item['bobot_nilai'] ?>" name="nilai_butir" value="<?= (float)($item['nilai_butir'] ?? 0) ?>" required class="w-20 bg-white border border-purple-300 text-purple-900 font-black text-sm rounded-lg p-1.5 text-center focus:ring-purple-500 focus:outline-none">
                                        <span class="text-xs text-purple-600 font-bold">/ <?= number_format($item['bobot_nilai'], 1) ?></span>
                                    </div>
                                </div>

                                <div>
                                    <input type="text" name="catatan_guru" value="<?= htmlspecialchars($detail['catatan_guru'] ?? '') ?>" placeholder="Catatan evaluasi guru (opsional)..." class="w-full bg-white border border-purple-200 text-xs rounded-lg p-2 text-slate-800 focus:ring-purple-500 focus:outline-none">
                                </div>

                                <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg text-xs transition shadow-soft-sm cursor-pointer">
                                    Simpan Nilai Essay
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>

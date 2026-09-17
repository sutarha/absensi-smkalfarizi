<?php
use App\Config\App;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hasil Ujian Siswa - <?= htmlspecialchars($ujian['judul']) ?></title>
    <link rel="manifest" href="<?= App::baseUrl('manifest.json') ?>">
    <?php require __DIR__ . '/../../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen selection:bg-brand-500 selection:text-white">
    <div class="max-w-md mx-auto min-h-screen bg-slate-50 border-x border-slate-200 shadow-soft-lg flex flex-col pb-24">
        
        <!-- Mobile Header -->
        <header class="bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 text-white rounded-b-3xl shadow-soft-lg p-6 relative">
            <div class="flex items-center gap-3 mb-4">
                <a href="<?= App::baseUrl("guru/lms/ujian/{$ujian['kelas_id']}/{$ujian['mapel_id']}") ?>" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h1 class="text-base font-outfit font-extrabold tracking-tight text-white"><?= htmlspecialchars($ujian['judul']) ?></h1>
                    <p class="text-xs text-slate-300">Hasil & Penilaian Siswa</p>
                </div>
            </div>

            <!-- Ringkasan Info -->
            <div class="grid grid-cols-3 gap-2 bg-white/10 backdrop-blur-md rounded-2xl p-3 border border-white/20 text-center text-xs">
                <div>
                    <div class="text-slate-300 text-[10px]">KKM</div>
                    <div class="font-extrabold text-white text-sm"><?= number_format($ujian['kkm'], 0) ?></div>
                </div>
                <div>
                    <div class="text-slate-300 text-[10px]">Durasi</div>
                    <div class="font-extrabold text-white text-sm"><?= (int)$ujian['durasi_menit'] ?>m</div>
                </div>
                <div>
                    <div class="text-slate-300 text-[10px]">Peserta</div>
                    <div class="font-extrabold text-amber-300 text-sm"><?= count($hasilSiswa) ?> Siswa</div>
                </div>
            </div>
        </header>

        <main class="p-4 flex-1 space-y-3">
            <?php if(empty($hasilSiswa)): ?>
                <div class="bg-white rounded-2xl p-8 text-center border border-slate-200 shadow-sm">
                    <div class="w-14 h-14 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm mb-1">Belum Ada Siswa Mengerjakan</h3>
                    <p class="text-xs text-slate-500">Hasil pengerjaan siswa akan otomatis muncul di sini begitu siswa mulai atau menyelesaikan ujian.</p>
                </div>
            <?php else: ?>
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider px-1">Daftar Nilai Peserta</div>

                <div class="space-y-3">
                    <?php foreach($hasilSiswa as $h): ?>
                        <?php 
                            $isLulus = ((float)$h['nilai_akhir'] >= (float)$ujian['kkm']);
                            $isSelesai = ($h['status'] === 'selesai' || $h['status'] === 'waktu_habis');
                        ?>
                        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm hover:border-slate-300 transition">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-soft-sm">
                                        <?= strtoupper(substr($h['nama_siswa'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-sm leading-tight"><?= htmlspecialchars($h['nama_siswa']) ?></h4>
                                        <span class="text-[11px] text-slate-400 font-mono">NISN: <?= htmlspecialchars($h['nisn']) ?></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-black <?= $isLulus ? 'text-emerald-600' : 'text-rose-600' ?>">
                                        <?= number_format($h['nilai_akhir'], 1) ?>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full <?= $isLulus ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                        <?= $isLulus ? 'Lulus' : 'Belum Lulus' ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Detail Breakdown -->
                            <div class="grid grid-cols-3 gap-2 bg-slate-50 rounded-xl p-2.5 text-center text-xs mb-3 border border-slate-100">
                                <div>
                                    <div class="text-[10px] text-slate-400 font-medium">Status</div>
                                    <div class="font-bold <?= $isSelesai ? 'text-emerald-700' : 'text-amber-600 animate-pulse' ?>">
                                        <?= $h['status'] === 'selesai' ? 'Selesai' : ($h['status'] === 'waktu_habis' ? 'Waktu Habis' : 'Sedang Ujian') ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-medium">Nilai PG</div>
                                    <div class="font-bold text-blue-600"><?= number_format($h['nilai_pg'], 1) ?></div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-400 font-medium">Nilai Essay</div>
                                    <div class="font-bold text-purple-600"><?= number_format($h['nilai_essay'], 1) ?></div>
                                </div>
                            </div>

                            <a href="<?= App::baseUrl("guru/lms/ujian/detail-siswa/{$h['id']}") ?>" class="block w-full py-2 bg-brand-50 hover:bg-brand-100 text-brand-700 font-bold rounded-xl text-center text-xs transition">
                                Koreksi Essay & Detail Lembar Jawaban ➔
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

<?php
use App\Config\App;

$activeNav = 'nilai';
$isGuru = (($user['role'] ?? '') === 'guru');
$filterUrl = $isGuru ? App::baseUrl('guru/nilai') : App::baseUrl('admin/nilai');
$inputUrl = $isGuru 
    ? App::baseUrl("guru/nilai/input?kelas_id={$kelasId}&mapel_id={$mapelId}&semester_ke={$semesterKe}") 
    : App::baseUrl("admin/nilai/input?kelas_id={$kelasId}&mapel_id={$mapelId}&semester_ke={$semesterKe}");
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penilaian Semester (1-6) - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Sidebar (Admin only) -->
    <?php if (!$isGuru): ?>
        <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>
    <?php endif; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-6 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div class="flex items-center gap-3">
                <?php if ($isGuru): ?>
                    <a href="<?= App::baseUrl('guru/dashboard') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Kembali ke Dashboard Guru">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    </a>
                <?php endif; ?>
                <div>
                    <h1 class="text-lg lg:text-xl font-display font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Penilaian KBM Semester (1 - 6)
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">Kalkulasi terotomatisasi Tugas, UH, UTS, UAS, Predikat (A-D), Transkrip Kumulatif & Ijazah</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <?php if (!empty($hasSchedule) && !empty($kelasList) && !empty($mapelList)): ?>
                <a href="<?= $inputUrl ?>" class="inline-flex items-center gap-1.5 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Input / Perbarui Nilai KBM</span>
                </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">

            <!-- Flash Alert -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-soft-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-soft-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Formula Info Banner -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-soft-sm flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center justify-center font-bold flex-shrink-0">∑</span>
                    <div>
                        <div class="font-bold text-slate-800">Formulasi Nilai Akhir (NA) Sesuai Panduan Asesmen Kemendikbud:</div>
                        <div class="text-slate-500 font-mono text-[11px] mt-0.5">
                            NA = (60% × Rata-rata Sumatif Lingkup Materi) + (40% × Sumatif Akhir Semester / SAS)
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap text-[11px]">
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">A (86 - 100) : Sangat Baik</span>
                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold border border-blue-200">B (71 - 85) : Baik</span>
                    <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-bold border border-amber-200">C (56 - 70) : Cukup</span>
                    <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 font-bold border border-rose-200">D (&lt; 56) : Kurang</span>
                </div>
            </div>

            <?php if (empty($hasSchedule)): ?>
                <div class="bg-white rounded-2xl border border-amber-200/80 p-8 shadow-soft-sm text-center">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 text-2xl border border-amber-200">
                        📚
                    </div>
                    <h2 class="text-base font-bold text-slate-900">Belum Ada Jadwal Mengajar KBM Aktif</h2>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto leading-relaxed">
                        Anda saat ini belum memiliki jadwal mengajar mata pelajaran pada kelas aktif. Sesuai kebijakan kurikulum, guru hanya dapat melihat dan menginput nilai rapor untuk mata pelajaran dan rombel yang diampu.
                    </p>
                    <div class="mt-5">
                        <a href="<?= App::baseUrl('guru/dashboard') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-soft-sm transition">
                            <span>◀ Kembali ke Dashboard Guru</span>
                        </a>
                    </div>
                </div>
            <?php else: ?>

            <!-- Filter Controls -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-soft-sm">
                <?php if ($isGuru): ?>
                <div class="flex items-center gap-2 mb-4 text-xs font-semibold text-brand-700 bg-brand-50 border border-brand-200/80 px-3 py-2 rounded-xl">
                    <svg class="w-4 h-4 text-brand-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Mode Guru: Filter ini dibatasi khusus pada kelas & mata pelajaran yang Anda ampu sesuai Jadwal KBM.</span>
                </div>
                <?php endif; ?>
                <form method="GET" action="<?= $filterUrl ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Kelas / Rombel</label>
                        <select name="kelas_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:border-brand-500" onchange="this.form.submit()">
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($kelasId === (int)$k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['jurusan']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mata Pelajaran</label>
                        <select name="mapel_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:border-brand-500" onchange="this.form.submit()">
                            <?php foreach ($mapelList as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= ($mapelId === (int)$m['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mapel']) ?> (<?= htmlspecialchars($m['kelompok']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Semester Ke (1 - 6)</label>
                        <select name="semester_ke" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono">
                            <?php for ($s = 1; $s <= 6; $s++): ?>
                                <option value="<?= $s ?>" <?= ($semesterKe === $s) ? 'selected' : '' ?>>
                                    Semester <?= $s ?> (Kelas <?= ($s <= 2) ? 'X' : (($s <= 4) ? 'XI' : 'XII') ?>)
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs py-2 px-4 rounded-xl transition">
                            Tampilkan Nilai
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Nilai Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display font-bold text-slate-900 text-sm">
                            Rekap Nilai: <?= htmlspecialchars($selectedMapel['nama_mapel'] ?? '-') ?>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Kelas: <strong class="text-slate-800"><?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '-') ?></strong> • 
                            Semester Ke: <strong class="text-slate-800"><?= $semesterKe ?></strong> • 
                            Tahun Ajaran Aktif: <strong class="text-brand-600"><?= htmlspecialchars($activeTapel['tahun_ajaran'] ?? '-') ?></strong>
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="block xl:table w-full text-left text-xs text-slate-600">
                        <thead class="hidden xl:table-header-group bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">No</th>
                                <th class="py-3 px-4 min-w-[170px]">Nama Siswa & NISN</th>
                                <th class="py-3 px-2 text-center text-sky-800">Tugas/PR</th>
                                <th class="py-3 px-2 text-center text-sky-800">UH</th>
                                <th class="py-3 px-2 text-center text-indigo-800">UTS</th>
                                <th class="py-3 px-2 text-center text-indigo-800">UAS</th>
                                <th class="py-3 px-2 text-center font-bold text-slate-900 bg-slate-100/60">NA Rapor</th>
                                <th class="py-3 px-2 text-center">Predikat</th>
                                <th class="py-3 px-2 text-center">Asal Nilai</th>
                                <th class="py-3 px-4 min-w-[180px]">Capaian Kompetensi</th>
                                <th class="py-3 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="block xl:table-row-group divide-y divide-slate-200 xl:divide-slate-100 font-medium">
                            <?php if (empty($daftarNilai)): ?>
                                <tr class="block xl:table-row">
                                    <td colspan="11" class="block xl:table-cell py-8 text-center text-slate-400">
                                        Tidak ada data siswa ditemukan di kelas ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($daftarNilai as $dn): 
                                    $tugasShow = (isset($dn['nilai_tugas']) && $dn['nilai_tugas'] > 0) ? number_format((float)$dn['nilai_tugas'], 1) : ((isset($dn['nilai_formatif']) && $dn['nilai_formatif'] > 0) ? number_format((float)$dn['nilai_formatif'], 1) : '-');
                                    $uhShow = (isset($dn['nilai_uh']) && $dn['nilai_uh'] > 0) ? number_format((float)$dn['nilai_uh'], 1) : '-';
                                    $utsShow = (isset($dn['nilai_uts']) && $dn['nilai_uts'] > 0) ? number_format((float)$dn['nilai_uts'], 1) : ((isset($dn['nilai_sumatif_materi']) && $dn['nilai_sumatif_materi'] > 0) ? number_format((float)$dn['nilai_sumatif_materi'], 1) : '-');
                                    $uasShow = (isset($dn['nilai_uas']) && $dn['nilai_uas'] > 0) ? number_format((float)$dn['nilai_uas'], 1) : ((isset($dn['nilai_sas']) && $dn['nilai_sas'] > 0) ? number_format((float)$dn['nilai_sas'], 1) : '-');
                                    $naShow = ($dn['nilai_akhir'] > 0) ? number_format((float)$dn['nilai_akhir'], 1) : '-';
                                    $isManual = !empty($dn['is_manual']);
                                ?>
                                    <tr class="block xl:table-row hover:bg-slate-50/70 transition p-4 xl:p-0 mb-4 xl:mb-0 bg-white xl:bg-transparent rounded-xl xl:rounded-none shadow-sm xl:shadow-none border border-slate-200 xl:border-none">
                                        <td class="hidden xl:table-cell py-3 px-3 text-center text-slate-400 font-mono"><?= $no++ ?></td>
                                        
                                        <td class="block xl:table-cell py-3 px-4 xl:px-4 border-b border-slate-100 xl:border-none">
                                            <div class="flex items-center xl:block gap-3">
                                                <div class="xl:hidden w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm">
                                                    <?= $no - 1 ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900 text-sm xl:text-xs"><?= htmlspecialchars($dn['nama_siswa']) ?></div>
                                                    <div class="text-[11px] text-slate-400 font-mono">NISN: <?= htmlspecialchars($dn['nisn']) ?></div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center font-mono text-slate-700 border-b xl:border-none border-slate-50">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">Tugas/PR</span>
                                            <span><?= $tugasShow ?></span>
                                        </td>
                                        <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center font-mono text-slate-700 border-b xl:border-none border-slate-50">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">UH</span>
                                            <span><?= $uhShow ?></span>
                                        </td>
                                        <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center font-mono text-slate-700 border-b xl:border-none border-slate-50">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">UTS</span>
                                            <span><?= $utsShow ?></span>
                                        </td>
                                        <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center font-mono text-slate-700 border-b xl:border-none border-slate-50">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">UAS</span>
                                            <span><?= $uasShow ?></span>
                                        </td>
                                        <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center font-mono font-black text-sm xl:bg-slate-50/70 border-b xl:border-none border-slate-100 <?= ($dn['nilai_akhir'] >= 75) ? 'text-emerald-700' : 'text-slate-800' ?>">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">NA Rapor</span>
                                            <span><?= $naShow ?></span>
                                        </td>
                                        <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center border-b xl:border-none border-slate-50">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">Predikat</span>
                                            <?php
                                            $pred = $dn['predikat'] ?? '-';
                                            $pClass = match($pred) {
                                                'A' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'B' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'C' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'D' => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default => 'bg-slate-100 text-slate-400 border-slate-200'
                                            };
                                            ?>
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black border <?= $pClass ?>">
                                                <?= htmlspecialchars($pred) ?>
                                            </span>
                                        </td>
                                        <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center border-b xl:border-none border-slate-50">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">Asal Nilai</span>
                                            <?php if ($isManual): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-bold border border-amber-200" title="Diinput manual oleh Admin TU">
                                                    Manual TU
                                                </span>
                                            <?php elseif ($dn['nilai_akhir'] > 0): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 text-[10px] font-bold border border-sky-200" title="Kalkulasi KBM Semester Berjalan">
                                                    KBM Guru
                                                </span>
                                            <?php else: ?>
                                                <span class="text-slate-300 text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="flex xl:table-cell flex-col xl:flex-row justify-between items-start xl:items-center py-3 xl:py-3 px-4 xl:px-4 text-slate-500 text-[11px] border-b xl:border-none border-slate-50">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 mb-1.5">Capaian Kompetensi</span>
                                            <div class="xl:max-w-xs xl:truncate">
                                                <?= htmlspecialchars($dn['capaian_kompetensi'] ?? 'Menunjukkan penguasaan yang baik dalam tujuan pembelajaran.') ?>
                                            </div>
                                        </td>
                                        <td class="flex xl:table-cell justify-between items-center py-4 xl:py-3 px-4 xl:px-3 text-center whitespace-nowrap bg-slate-50 xl:bg-transparent rounded-b-xl xl:rounded-none mt-2 xl:mt-0">
                                            <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">Aksi Cetak</span>
                                            <div class="inline-flex items-center gap-2 xl:gap-1 w-full xl:w-auto justify-end">
                                                <a href="<?= App::baseUrl("admin/nilai/transkrip/{$dn['siswa_id']}") ?>" target="_blank" title="Cetak Transkrip Kumulatif Sem 1-6" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 xl:px-2 xl:py-1 rounded-lg transition border border-indigo-200 flex-1 xl:flex-auto justify-center">
                                                    Kumulatif
                                                </a>
                                                <a href="<?= App::baseUrl("admin/nilai/transkrip-ijazah/{$dn['siswa_id']}") ?>" target="_blank" title="Cetak Transkrip Ijazah Resmi" class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 px-3 py-1.5 xl:px-2 xl:py-1 rounded-lg transition border border-emerald-200 flex-1 xl:flex-auto justify-center">
                                                    Ijazah 🎓
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </main>

</body>
</html>

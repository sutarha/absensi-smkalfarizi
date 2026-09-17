<?php
use App\Config\App;

$activeNav = 'nilai';
$isGuru = (($user['role'] ?? '') === 'guru');
$saveUrl = $isGuru ? App::baseUrl('guru/nilai/save') : App::baseUrl('admin/nilai/save');
$backUrl = $isGuru 
    ? App::baseUrl("guru/nilai?kelas_id={$kelasId}&mapel_id={$mapelId}&semester_ke={$semesterKe}")
    : App::baseUrl("admin/nilai?kelas_id={$kelasId}&mapel_id={$mapelId}&semester_ke={$semesterKe}");
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai KBM Semester <?= $semesterKe ?> - <?= htmlspecialchars($selectedMapel['nama_mapel'] ?? '') ?></title>
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
                <a href="<?= $backUrl ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Kembali">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg lg:text-xl font-display font-black text-slate-900 tracking-tight">
                            Penilaian KBM Semester <?= $semesterKe ?>
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $isGuru ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-brand-100 text-brand-800 border border-brand-200' ?>">
                            <?= $isGuru ? 'Mode Guru KBM' : 'Mode Admin TU' ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        <?= htmlspecialchars($selectedMapel['nama_mapel'] ?? '') ?> • Kelas <?= htmlspecialchars($selectedKelas['nama_kelas'] ?? '') ?> (<?= htmlspecialchars($selectedKelas['jurusan'] ?? '') ?>)
                        <?php if ($isGuru): ?>
                        • Guru Pengampu: <strong class="text-slate-800"><?= htmlspecialchars($user['nama_lengkap']) ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= $backUrl ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" form="form-input-nilai" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white font-semibold text-xs px-5 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Nilai KBM</span>
                </button>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">

            <!-- Flash Alert -->
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-soft-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-soft-sm animate-fade-in">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <!-- Real-time calculation helper box Permendikbud -->
            <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 text-white p-5 rounded-2xl shadow-soft-md flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs border border-white/10">
                <div class="space-y-1">
                    <div class="font-bold text-sm text-brand-300 flex items-center gap-2">
                        <span>⚡</span>
                        <span>Kalkulasi Otomatis Nilai Rapor KBM Permendikbudristek</span>
                    </div>
                    <p class="text-slate-300 leading-relaxed text-[11px]">
                        Masukkan nilai komponen <strong>Tugas / PR</strong>, <strong>Ulangan Harian (UH)</strong>, <strong>UTS / STS</strong>, dan <strong>UAS / SAS</strong>. Sistem menghitung Rata-rata Harian ($R_H$), Nilai Rapor Jadi ($NA$), dan Predikat secara real-time saat diketik!
                    </p>
                </div>
                <div class="bg-white/10 p-2.5 rounded-xl border border-white/10 flex-shrink-0 font-mono text-[11px] space-y-1">
                    <div class="text-sky-300">R_H = (Tugas + UH) ÷ 2</div>
                    <div class="text-emerald-300 font-bold">NA = [(2 × R_H) + UTS + UAS] ÷ 4</div>
                </div>
            </div>

            <form id="form-input-nilai" method="POST" action="<?= $saveUrl ?>">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <input type="hidden" name="kelas_id" value="<?= $kelasId ?>">
                <input type="hidden" name="mapel_id" value="<?= $mapelId ?>">
                <input type="hidden" name="semester_ke" value="<?= $semesterKe ?>">
                <input type="hidden" name="tahun_pelajaran_id" value="<?= $activeTapel['id'] ?? '' ?>">

                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="block xl:table w-full text-left text-xs text-slate-700">
                            <thead class="hidden xl:table-header-group bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3 w-10 text-center">No</th>
                                    <th class="py-3 px-4 min-w-[180px]">Nama Siswa & NISN</th>
                                    <th class="py-3 px-2 w-24 text-center text-sky-800 bg-sky-50/50">Tugas / PR</th>
                                    <th class="py-3 px-2 w-24 text-center text-sky-800 bg-sky-50/50">Ulangan Harian</th>
                                    <th class="py-3 px-2 w-20 text-center bg-slate-100/70 text-slate-600 font-bold">R. Harian</th>
                                    <th class="py-3 px-2 w-24 text-center text-indigo-800 bg-indigo-50/50">UTS / STS</th>
                                    <th class="py-3 px-2 w-24 text-center text-indigo-800 bg-indigo-50/50">UAS / SAS</th>
                                    <th class="py-3 px-2 w-20 text-center bg-emerald-50 text-emerald-900 font-black">NA Rapor</th>
                                    <th class="py-3 px-2 w-16 text-center bg-emerald-50 text-emerald-900">Pred</th>
                                    <th class="py-3 px-4 min-w-[200px]">Capaian Kompetensi / Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="block xl:table-row-group divide-y divide-slate-200 xl:divide-slate-100 font-medium">
                                <?php if (empty($daftarNilai)): ?>
                                    <tr class="block xl:table-row">
                                        <td colspan="10" class="block xl:table-cell py-8 text-center text-slate-400">
                                            Tidak ada data siswa ditemukan di kelas ini.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($daftarNilai as $dn): 
                                        $sid = $dn['siswa_id'];
                                        $tugasVal = (isset($dn['nilai_tugas']) && $dn['nilai_tugas'] > 0) ? (float)$dn['nilai_tugas'] : ((isset($dn['nilai_formatif']) && $dn['nilai_formatif'] > 0) ? (float)$dn['nilai_formatif'] : '');
                                        $uhVal = (isset($dn['nilai_uh']) && $dn['nilai_uh'] > 0) ? (float)$dn['nilai_uh'] : '';
                                        $utsVal = (isset($dn['nilai_uts']) && $dn['nilai_uts'] > 0) ? (float)$dn['nilai_uts'] : ((isset($dn['nilai_sumatif_materi']) && $dn['nilai_sumatif_materi'] > 0) ? (float)$dn['nilai_sumatif_materi'] : '');
                                        $uasVal = (isset($dn['nilai_uas']) && $dn['nilai_uas'] > 0) ? (float)$dn['nilai_uas'] : ((isset($dn['nilai_sas']) && $dn['nilai_sas'] > 0) ? (float)$dn['nilai_sas'] : '');
                                        $naVal = ($dn['nilai_akhir'] > 0) ? number_format((float)$dn['nilai_akhir'], 1) : '-';
                                        $predVal = $dn['predikat'] ?? '-';
                                    ?>
                                        <tr class="block xl:table-row hover:bg-slate-50/70 transition p-4 xl:p-0 mb-4 xl:mb-0 bg-white xl:bg-transparent rounded-xl xl:rounded-none shadow-sm xl:shadow-none border border-slate-200 xl:border-none" data-siswa-id="<?= $sid ?>">
                                            <td class="hidden xl:table-cell py-2.5 px-3 text-center text-slate-400 font-mono"><?= $no++ ?></td>
                                            
                                            <td class="block xl:table-cell py-2.5 px-4 xl:px-4 border-b border-slate-100 xl:border-none">
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

                                            <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center xl:bg-sky-50/20 border-b xl:border-none border-slate-50">
                                                <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">Tugas / PR</span>
                                                <input type="number" step="0.1" min="0" max="100" name="nilai[<?= $sid ?>][tugas]" value="<?= $tugasVal ?>" placeholder="0" oninput="hitungRowNilai(<?= $sid ?>)" id="tugas-<?= $sid ?>" class="w-20 text-center font-mono py-1.5 px-1 bg-white border border-sky-300 rounded-lg text-xs text-sky-900 font-semibold focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                            </td>
                                            <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center xl:bg-sky-50/20 border-b xl:border-none border-slate-50">
                                                <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">Ulangan Harian</span>
                                                <input type="number" step="0.1" min="0" max="100" name="nilai[<?= $sid ?>][uh]" value="<?= $uhVal ?>" placeholder="0" oninput="hitungRowNilai(<?= $sid ?>)" id="uh-<?= $sid ?>" class="w-20 text-center font-mono py-1.5 px-1 bg-white border border-sky-300 rounded-lg text-xs text-sky-900 font-semibold focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                            </td>
                                            <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center bg-slate-50 xl:bg-slate-100/50 font-mono font-bold text-xs text-slate-700 border-b xl:border-none border-slate-100">
                                                <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">R. Harian</span>
                                                <span id="rh-<?= $sid ?>">-</span>
                                            </td>

                                            <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center xl:bg-indigo-50/20 border-b xl:border-none border-slate-50">
                                                <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">UTS / STS</span>
                                                <input type="number" step="0.1" min="0" max="100" name="nilai[<?= $sid ?>][uts]" value="<?= $utsVal ?>" placeholder="0" oninput="hitungRowNilai(<?= $sid ?>)" id="uts-<?= $sid ?>" class="w-20 text-center font-mono py-1.5 px-1 bg-white border border-indigo-300 rounded-lg text-xs text-indigo-900 font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            </td>
                                            <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center xl:bg-indigo-50/20 border-b xl:border-none border-slate-50">
                                                <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">UAS / SAS</span>
                                                <input type="number" step="0.1" min="0" max="100" name="nilai[<?= $sid ?>][uas]" value="<?= $uasVal ?>" placeholder="0" oninput="hitungRowNilai(<?= $sid ?>)" id="uas-<?= $sid ?>" class="w-20 text-center font-mono py-1.5 px-1 bg-white border border-indigo-300 rounded-lg text-xs text-indigo-900 font-semibold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            </td>

                                            <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center bg-emerald-50/30 xl:bg-emerald-50/60 font-mono font-black text-sm text-emerald-900 border-b xl:border-none border-emerald-100">
                                                <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">NA Rapor</span>
                                                <span id="na-<?= $sid ?>"><?= $naVal ?></span>
                                            </td>
                                            <td class="flex xl:table-cell justify-between items-center py-2 px-4 xl:px-2 text-center bg-emerald-50/30 xl:bg-emerald-50/60 border-b xl:border-none border-emerald-100">
                                                <span class="xl:hidden font-semibold text-[11px] text-slate-500 w-1/3 text-left">Predikat</span>
                                                <span id="pred-<?= $sid ?>" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black border bg-white text-slate-700 border-slate-200">
                                                    <?= $predVal ?>
                                                </span>
                                            </td>
                                            <td class="flex xl:table-cell flex-col xl:flex-row justify-between items-start xl:items-center py-3 xl:py-2 px-4 xl:px-3">
                                                <span class="xl:hidden font-semibold text-[11px] text-slate-500 mb-1.5">Capaian Kompetensi / Catatan</span>
                                                <input type="text" name="nilai[<?= $sid ?>][capaian]" value="<?= htmlspecialchars($dn['capaian_kompetensi'] ?? 'Menunjukkan penguasaan yang baik dalam capaian pembelajaran.') ?>" class="w-full py-1.5 px-2.5 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-5 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="text-xs text-slate-500 text-center md:text-left">
                            💡 Data tersimpan otomatis ke <strong>Buku Induk Siswa</strong> dan <strong>Transkrip Ijazah</strong>.
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <a href="<?= $backUrl ?>" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Seluruh Nilai KBM
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </main>

    <script>
    function hitungRowNilai(sid) {
        const tugasInput = document.getElementById('tugas-' + sid);
        const uhInput = document.getElementById('uh-' + sid);
        const utsInput = document.getElementById('uts-' + sid);
        const uasInput = document.getElementById('uas-' + sid);

        const rhCell = document.getElementById('rh-' + sid);
        const naCell = document.getElementById('na-' + sid);
        const predBadge = document.getElementById('pred-' + sid);

        if (!tugasInput || !uhInput || !utsInput || !uasInput) return;

        const t = parseFloat(tugasInput.value) || 0;
        const uh = parseFloat(uhInput.value) || 0;
        const uts = parseFloat(utsInput.value) || 0;
        const uas = parseFloat(uasInput.value) || 0;

        const hasInput = tugasInput.value !== '' || uhInput.value !== '' || utsInput.value !== '' || uasInput.value !== '';
        if (!hasInput) {
            rhCell.innerText = '-';
            naCell.innerText = '-';
            predBadge.innerText = '-';
            predBadge.className = 'inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black border bg-white text-slate-700 border-slate-200';
            return;
        }

        // Permendikbud Formula:
        // Rata Harian = (Tugas + UH) / 2
        // NA = (2 * RH + UTS + UAS) / 4
        const rh = (t + uh) / 2.0;
        const na = ((2.0 * rh) + uts + uas) / 4.0;

        rhCell.innerText = rh.toFixed(1);
        naCell.innerText = na.toFixed(1);

        let pred = 'D';
        let badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';

        if (na >= 86) {
            pred = 'A';
            badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        } else if (na >= 71) {
            pred = 'B';
            badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
        } else if (na >= 56) {
            pred = 'C';
            badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
        }

        predBadge.innerText = pred;
        predBadge.className = 'inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black border ' + badgeClass;
    }

    // Hitung seluruh baris saat halaman selesai dimuat
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('tr[data-siswa-id]').forEach(tr => {
            const sid = tr.getAttribute('data-siswa-id');
            hitungRowNilai(sid);
        });
    });
    </script>

</body>
</html>

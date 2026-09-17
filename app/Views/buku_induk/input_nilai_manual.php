<?php
use App\Config\App;

$activeNav = 'buku_induk';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Raport Manual - <?= htmlspecialchars($siswa['nama_lengkap']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-6 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div class="flex items-center gap-3">
                <a href="<?= App::baseUrl("admin/buku-induk/detail/{$siswa['id']}") ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Kembali ke Buku Induk">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg lg:text-xl font-display font-black text-slate-900 tracking-tight">
                            Input Nilai Raport Manual (Buku Induk)
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-200">
                            Mode TU / Raport Lalu
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        Siswa: <strong class="text-slate-800"><?= htmlspecialchars($siswa['nama_lengkap']) ?></strong> • 
                        NISN: <span class="font-mono text-slate-700"><?= htmlspecialchars($siswa['nisn']) ?></span> • 
                        Kelas: <?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= App::baseUrl("admin/buku-induk/detail/{$siswa['id']}") ?>" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" form="form-input-manual" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-semibold text-xs px-5 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-emerald transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Raport Semester <?= $semesterKe ?></span>
                </button>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-6 lg:p-8 max-w-6xl w-full mx-auto space-y-6">

            <!-- Semester Picker Tabs -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-soft-sm">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Pilih Semester Raport:
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <?php for ($s = 1; $s <= 6; $s++): 
                            $tingkatLabel = ($s <= 2) ? 'Kelas X' : (($s <= 4) ? 'Kelas XI' : 'Kelas XII');
                            $isActive = ($semesterKe === $s);
                        ?>
                            <a href="<?= App::baseUrl("admin/buku-induk/input-nilai-manual/{$siswa['id']}?semester_ke={$s}") ?>" 
                               class="px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 <?= $isActive ? 'bg-emerald-600 text-white shadow-soft-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' ?>">
                                <span>Semester <?= $s ?></span>
                                <span class="text-[10px] opacity-80 font-normal">(<?= $tingkatLabel ?>)</span>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Notice Box -->
            <div class="bg-gradient-to-r from-amber-950 via-amber-900 to-slate-900 text-white p-5 rounded-2xl shadow-soft-md flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs border border-amber-500/20">
                <div class="space-y-1">
                    <div class="font-bold text-sm text-amber-300 flex items-center gap-2">
                        <span>📝</span>
                        <span>Mode Input Nilai Raport Jadi (Masa Lalu / Pindahan)</span>
                    </div>
                    <p class="text-amber-100/90 leading-relaxed text-[11px]">
                        Form ini untuk menginput <strong>Nilai Rapor Jadi</strong> per mata pelajaran secara langsung untuk Semester <?= $semesterKe ?>. Nilai yang diinput di sini akan ditandai sebagai <code>Manual TU</code> pada Buku Induk.
                        Jika mata pelajaran belum ada (khususnya untuk mapel pindahan/masa lalu), silakan tambahkan dulu di <a href="<?= App::baseUrl('admin/mapel') ?>" target="_blank" class="text-amber-400 hover:text-amber-300 font-bold underline">Master Data Mapel</a>.
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap text-[11px] bg-white/10 p-2.5 rounded-xl border border-white/10 flex-shrink-0">
                    <span class="text-emerald-300 font-bold">A: &ge; 86</span>
                    <span class="text-blue-300 font-bold">B: 71 - 85</span>
                    <span class="text-amber-300 font-bold">C: 56 - 70</span>
                    <span class="text-rose-300 font-bold">D: &lt; 56</span>
                </div>
            </div>

            <!-- Form Table -->
            <form id="form-input-manual" method="POST" action="<?= App::baseUrl("admin/buku-induk/input-nilai-manual/{$siswa['id']}") ?>
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>">
                <input type="hidden" name="semester_ke" value="<?= $semesterKe ?>">

                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3 w-10 text-center">No</th>
                                    <th class="py-3 px-4 min-w-[220px]">Mata Pelajaran</th>
                                    <th class="py-3 px-3 w-28 text-center">Kelompok</th>
                                    <th class="py-3 px-3 w-32 text-center text-emerald-800 bg-emerald-50/50">Nilai Akhir Raport</th>
                                    <th class="py-3 px-3 w-20 text-center bg-slate-100">Predikat</th>
                                    <th class="py-3 px-4 min-w-[220px]">Capaian Kompetensi / Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php if (empty($mapelList)): ?>
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400">
                                            Tidak ada mata pelajaran terdaftar.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($mapelList as $m): 
                                        $mid = (int)$m['id'];
                                        $exist = $nilaiMap[$mid] ?? null;
                                        $val = ($exist && $exist['nilai_akhir'] > 0) ? (float)$exist['nilai_akhir'] : '';
                                        $capaianVal = $exist['capaian_kompetensi'] ?? 'Mencapai ketuntasan kompetensi mata pelajaran dengan baik.';
                                    ?>
                                        <tr class="hover:bg-slate-50/70 transition" data-mapel-id="<?= $mid ?>">
                                            <td class="py-3 px-3 text-center text-slate-400 font-mono"><?= $no++ ?></td>
                                            <td class="py-3 px-4">
                                                <div class="font-bold text-slate-900"><?= htmlspecialchars($m['nama_mapel']) ?></div>
                                                <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($m['kode_mapel']) ?></div>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                    <?= htmlspecialchars($m['kelompok']) ?>
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-3 text-center bg-emerald-50/20">
                                                <input type="number" step="0.1" min="0" max="100" 
                                                       name="nilai_mapel[<?= $mid ?>][nilai_akhir]" 
                                                       value="<?= $val ?>" 
                                                       placeholder="0" 
                                                       oninput="updatePredikat(<?= $mid ?>)" 
                                                       id="nilai-<?= $mid ?>" 
                                                       class="w-24 text-center font-mono font-black text-sm py-1.5 px-2 bg-white border border-emerald-300 rounded-lg text-emerald-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-soft-sm">
                                            </td>
                                            <td class="py-2.5 px-3 text-center bg-slate-50">
                                                <span id="pred-<?= $mid ?>" class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black border bg-white text-slate-700 border-slate-200">
                                                    -
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4">
                                                <input type="text" 
                                                       name="nilai_mapel[<?= $mid ?>][capaian]" 
                                                       value="<?= htmlspecialchars($capaianVal) ?>" 
                                                       class="w-full py-1.5 px-3 bg-slate-50 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-5 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            💡 Kosongkan nilai untuk mapel yang belum diajarkan pada semester ini.
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="<?= App::baseUrl("admin/buku-induk/detail/{$siswa['id']}") ?>" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-500 hover:to-emerald-600 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-emerald transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Nilai Raport Semester <?= $semesterKe ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </main>

    <script>
    function updatePredikat(mid) {
        const input = document.getElementById('nilai-' + mid);
        const badge = document.getElementById('pred-' + mid);
        if (!input || !badge) return;

        const val = parseFloat(input.value);
        if (isNaN(val) || input.value === '') {
            badge.innerText = '-';
            badge.className = 'inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black border bg-white text-slate-700 border-slate-200';
            return;
        }

        let pred = 'D';
        let badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';

        if (val >= 86) {
            pred = 'A';
            badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
        } else if (val >= 71) {
            pred = 'B';
            badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
        } else if (val >= 56) {
            pred = 'C';
            badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';
        }

        badge.innerText = pred;
        badge.className = 'inline-flex items-center justify-center w-7 h-7 rounded-lg text-xs font-black border ' + badgeClass;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('tr[data-mapel-id]').forEach(tr => {
            const mid = tr.getAttribute('data-mapel-id');
            updatePredikat(mid);
        });
    });
    </script>

</body>
</html>

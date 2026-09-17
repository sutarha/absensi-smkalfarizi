<?php
use App\Config\App;

$activeNav = 'buku_induk';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Nilai Kolektif - SMK Al-Farizi</title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
    <style>
        /* Sticky first column for student names */
        .table-sticky th:first-child, .table-sticky td:first-child {
            position: sticky;
            left: 0;
            z-index: 20;
            background-color: white;
            box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1);
        }
        .table-sticky th:first-child {
            z-index: 30; /* Above the sticky header */
        }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto pb-24">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-40 shadow-soft-sm">
            <div class="flex items-center gap-4">
                <a href="<?= App::baseUrl('admin/buku-induk') ?>" class="w-10 h-10 flex items-center justify-center bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-xl transition border border-slate-200/60 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-display font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                        <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Input Nilai Kolektif (Satu Kelas)</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">Fasilitas input massal untuk Buku Induk / backfill data nilai semester lalu</p>
                </div>
            </div>
        </header>

        <div class="p-8 space-y-6 max-w-[1600px] w-full mx-auto">
            
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-soft-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3 shadow-soft-sm">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Form Pencarian / Plotting Mapel -->
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 overflow-hidden relative">
                <form method="GET" action="<?= App::baseUrl('admin/buku-induk/input-kolektif') ?>" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Kelas</label>
                            <select name="kelas_id" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= $kelasId == $k['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih Semester (1-6)</label>
                            <select name="semester_ke" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?= $i ?>" <?= $semesterKe == $i ? 'selected' : '' ?>>Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Mata Pelajaran (Otomatis)</label>
                        <p class="text-xs text-slate-500 mb-4">
                            Mata pelajaran akan dimuat secara otomatis berdasarkan data yang telah di-plot di menu <a href="<?= App::baseUrl('admin/kelas') ?>" class="text-brand-600 font-bold hover:underline">Atur Mapel Kelas</a>. 
                            <?php if ($isPastSemester): ?>
                            Jika Anda sedang mengisi nilai <strong>Semester Lampau</strong> yang mata pelajarannya sudah tidak di-plot di kelas ini, Anda dapat menambahkan kolomnya secara manual di bawah ini.
                            <?php else: ?>
                            Mata pelajaran akan sama persis dengan yang dipelajari pada semester berjalan.
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($isPastSemester): ?>
                        <div class="mt-4 bg-orange-50/50 p-4 border border-orange-100 rounded-xl">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Tambah Mapel Semester Lampau (Opsional)</label>
                            <select name="mapel_tambahan[]" multiple class="w-full bg-white border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition" style="min-height: 120px;">
                                <?php 
                                $mapelTambahanIds = !empty($_GET['mapel_tambahan']) ? (array)$_GET['mapel_tambahan'] : [];
                                foreach ($semuaMapel as $m): 
                                ?>
                                    <option value="<?= $m['id'] ?>" <?= in_array($m['id'], $mapelTambahanIds) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m['nama_mapel']) ?> (<?= htmlspecialchars($m['kelompok']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1">Tahan tombol Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu.</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Jika mata pelajaran yang Anda cari belum ada di sistem sama sekali, Anda bisa <strong><a href="#" onclick="document.getElementById('modalBuatMapel').classList.remove('hidden'); return false;" class="text-orange-600 font-bold hover:underline">Buat Mapel Baru</a></strong> di sini.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm px-6 py-3 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition flex items-center gap-2">
                            <span>Buat Tabel Matrix Input</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bagian Grid / Spreadsheet Matrix Input -->
            <?php if ($kelasId && !empty($selectedMapel)): ?>
                
                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3 shadow-soft-sm">
                    <div class="text-amber-500 mt-0.5">⚠️</div>
                    <div>
                        <h4 class="text-sm font-bold text-amber-800">Catatan Penginputan</h4>
                        <p class="text-xs text-amber-700 mt-1">Anda diizinkan meng-override/mengubah semua nilai, termasuk nilai untuk semester yang sedang berjalan sebagai tindakan koreksi. Namun untuk kegiatan KBM normal, penginputan sebaiknya dilakukan oleh Guru Mata Pelajaran secara langsung.</p>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    <form method="POST" action="<?= App::baseUrl('admin/buku-induk/input-kolektif') ?>">
                        <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                        <input type="hidden" name="kelas_id" value="<?= $kelasId ?>">
                        <input type="hidden" name="semester_ke" value="<?= $semesterKe ?>">
                        <?php foreach ($mapelIds as $mid): ?>
                            <input type="hidden" name="mapel_ids[]" value="<?= $mid ?>">
                        <?php endforeach; ?>

                        <div class="overflow-x-auto w-full border-b border-slate-200 max-h-[600px] overflow-y-auto">
                            <table class="w-full text-left text-sm text-slate-600 whitespace-nowrap table-sticky">
                                <thead class="bg-slate-50 text-xs font-bold text-slate-800 uppercase tracking-wider sticky top-0 z-10 border-b border-slate-200">
                                    <tr>
                                        <th class="px-6 py-4 border-r border-slate-200 min-w-[250px]">Nama Siswa</th>
                                        <?php foreach ($selectedMapel as $m): ?>
                                            <th class="px-4 py-4 border-r border-slate-200 min-w-[120px] text-center" title="<?= htmlspecialchars($m['nama_mapel']) ?>">
                                                <div class="truncate max-w-[120px] mx-auto"><?= htmlspecialchars($m['nama_mapel']) ?></div>
                                            </th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if(empty($siswaList)): ?>
                                        <tr>
                                            <td colspan="<?= count($selectedMapel) + 1 ?>" class="px-6 py-12 text-center text-slate-500">
                                                Tidak ada siswa di kelas ini.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($siswaList as $s): ?>
                                            <tr class="hover:bg-slate-50/50 transition">
                                                <td class="px-6 py-3 border-r border-slate-200 font-semibold text-slate-800 bg-white group-hover:bg-slate-50/50 transition">
                                                    <?= htmlspecialchars($s['nama_siswa']) ?>
                                                    <div class="text-[10px] text-slate-400 font-normal"><?= htmlspecialchars($s['nisn']) ?></div>
                                                </td>
                                                <?php foreach ($selectedMapel as $m): ?>
                                                    <?php 
                                                        $val = isset($nilaiMatrix[$s['id']][$m['id']]) ? (float)$nilaiMatrix[$s['id']][$m['id']]['nilai_akhir'] : ''; 
                                                        $val = $val > 0 ? $val : '';
                                                    ?>
                                                    <td class="px-2 py-3 border-r border-slate-200 text-center relative group p-0 m-0">
                                                        <input type="number" step="0.01" min="0" max="100" 
                                                               name="nilai[<?= $s['id'] ?>][<?= $m['id'] ?>]" 
                                                               value="<?= $val ?>" 
                                                               placeholder="-"
                                                               class="w-full h-full text-center bg-transparent border-0 focus:ring-0 focus:bg-brand-50/30 text-sm font-semibold transition px-1 py-3 outline-none placeholder:text-slate-300">
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="p-6 bg-slate-50 border-t border-slate-200 flex justify-end gap-3 sticky bottom-0 z-20">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-8 py-3.5 rounded-xl shadow-soft-sm hover:shadow-glow-emerald transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                <span>Simpan Semua Nilai (<?= count($siswaList) ?> Siswa)</span>
                            </button>
                        </div>
                    </form>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <!-- Modal Buat Mapel Baru -->
    <div id="modalBuatMapel" class="fixed inset-0 z-[100] hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
            <button onclick="document.getElementById('modalBuatMapel').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-800">Buat Mata Pelajaran Baru</h3>
                <p class="text-sm text-slate-500">Mata pelajaran yang ditambahkan akan tersedia untuk dipilih pada input kolektif semester lampau.</p>
            </div>
            <form method="POST" action="<?= App::baseUrl('admin/mapel/store') ?>" class="p-6 space-y-4">
                <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kode Mapel</label>
                    <input type="text" name="kode_mapel" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition" placeholder="Misal: PAI, MTK, dst">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Nama Mata Pelajaran</label>
                    <input type="text" name="nama_mapel" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition" placeholder="Misal: Pendidikan Agama Islam">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kelompok (Kategori)</label>
                    <select name="kelompok" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-sm font-semibold rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition">
                        <option value="Muatan Nasional">Muatan Nasional</option>
                        <option value="Muatan Kewilayahan">Muatan Kewilayahan</option>
                        <option value="Muatan Peminatan Kejuruan">Muatan Peminatan Kejuruan</option>
                        <option value="Dasar Bidang Keahlian">Dasar Bidang Keahlian</option>
                        <option value="Dasar Program Keahlian">Dasar Program Keahlian</option>
                        <option value="Kompetensi Keahlian">Kompetensi Keahlian</option>
                        <option value="Umum">Umum</option>
                        <option value="Lokal">Muatan Lokal</option>
                    </select>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modalBuatMapel').classList.add('hidden')" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl shadow-soft-sm transition">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>

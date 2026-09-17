<?php
use App\Config\App;

$activeNav = 'buku_induk';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Induk Siswa (Dapodik) - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto pb-24">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div>
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Buku Induk Siswa Resmi (Standar Dapodik)</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Pengelolaan profil lengkap siswa, riwayat akademik, lembar buku induk resmi, dan penempatan kelas</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="<?= App::baseUrl('admin/buku-induk/input-kolektif') ?>" class="inline-flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-amber transition border border-amber-200">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Input Kolektif</span>
                </a>
                <a href="<?= App::baseUrl('admin/buku-induk/download-template') ?>" class="inline-flex items-center gap-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-emerald-200">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Template Excel (.xlsx)</span>
                </a>
                <a href="<?= App::baseUrl('admin/buku-induk/import') ?>" class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Import Dari Dapodik</span>
                </a>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">

            <!-- Flash Alert -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-soft-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-soft-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Filter & Search Toolbar -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-soft-sm">
                <form method="GET" action="<?= App::baseUrl('admin/buku-induk') ?>" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <div class="w-full sm:w-56">
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Rombel / Kelas</label>
                            <select name="kelas_id" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach ($kelasList as $k): ?>
                                    <option value="<?= $k['id'] ?>" <?= ($kelasId === (int)$k['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['jurusan']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full md:w-80">
                        <div class="relative w-full">
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari Nama, NISN, atau NIK..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition">
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                            Cari
                        </button>
                        <?php if (!empty($search) || !empty($kelasId)): ?>
                            <a href="<?= App::baseUrl('admin/buku-induk') ?>" class="text-xs text-slate-500 hover:text-rose-600 px-2 font-medium">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Form Bulk Assign Kelas (Ceklis Massal) -->
                <form id="bulkAssignForm" method="POST" action="<?= App::baseUrl('admin/siswa/bulk-assign-kelas') ?>">
                    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                    <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? App::baseUrl('admin/buku-induk')) ?>">

                <!-- Table Card -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h2 class="font-display font-bold text-slate-900 text-sm">Daftar Siswa Terdaftar di Buku Induk</h2>
                            <p class="text-xs text-slate-500">Total ditemukan: <?= number_format($totalRows) ?> siswa • Ceklis siswa untuk memasukkan ke kelas secara cepat</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-3 w-10 text-center">
                                        <input type="checkbox" id="checkAll" onchange="toggleCheckAll(this)" 
                                               class="w-4 h-4 text-brand-600 bg-slate-100 border-slate-300 rounded focus:ring-brand-500 cursor-pointer">
                                    </th>
                                    <th class="py-3 px-3 w-12 text-center">No</th>
                                    <th class="py-3 px-4">Nama Lengkap & NISN</th>
                                    <th class="py-3 px-4">Kelas Saat Ini</th>
                                    <th class="py-3 px-4">TTL & JK</th>
                                    <th class="py-3 px-4">Orang Tua / Wali</th>
                                    <th class="py-3 px-4">Status Dapodik</th>
                                    <th class="py-3 px-4 text-center w-36">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php if (empty($bukuList)): ?>
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-slate-400">
                                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            Belum ada data siswa di Buku Induk. Silakan lakukan Import Dapodik terlebih dahulu.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = $offset + 1; foreach ($bukuList as $s): ?>
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="py-3.5 px-3 text-center">
                                                <input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>" onchange="handleRowCheck()"
                                                       class="buku-checkbox w-4 h-4 text-brand-600 bg-slate-100 border-slate-300 rounded focus:ring-brand-500 cursor-pointer">
                                            </td>
                                            <td class="py-3.5 px-3 text-center text-slate-400"><?= $no++ ?></td>
                                            <td class="py-3.5 px-4">
                                                <a href="<?= App::baseUrl("admin/buku-induk/detail/{$s['id']}") ?>" class="font-bold text-slate-900 hover:text-brand-600 transition">
                                                    <?= htmlspecialchars($s['nama_lengkap']) ?>
                                                </a>
                                                <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                                    <span>NISN: <span class="font-mono text-slate-700"><?= htmlspecialchars($s['nisn']) ?></span></span>
                                                    <?php if (!empty($s['nis'])): ?>
                                                        <span>• NIS: <span class="font-mono text-slate-700"><?= htmlspecialchars($s['nis']) ?></span></span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                    <?= htmlspecialchars($s['nama_kelas'] ?? 'Belum Ada Kelas') ?>
                                                </span>
                                                <div class="text-[11px] text-slate-400 mt-0.5"><?= htmlspecialchars($s['jurusan'] ?? '') ?></div>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <div class="text-slate-800"><?= htmlspecialchars($s['tempat_lahir'] ?? '-') ?>, <?= !empty($s['tanggal_lahir']) ? date('d/m/Y', strtotime($s['tanggal_lahir'])) : '-' ?></div>
                                                <div class="text-[11px] text-slate-500 mt-0.5">
                                                    Jenis Kelamin: <span class="font-semibold"><?= ($s['jenis_kelamin'] === 'L') ? 'Laki-laki' : 'Perempuan' ?></span>
                                                </div>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <div class="text-slate-800">Ayah: <?= htmlspecialchars($s['nama_ayah'] ?? '-') ?></div>
                                                <div class="text-[11px] text-slate-500">Ibu: <?= htmlspecialchars($s['nama_ibu'] ?? '-') ?></div>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <?php
                                                $st = $s['status_siswa'] ?? 'AKTIF';
                                                $badgeClass = match($st) {
                                                    'AKTIF' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                    'LULUS' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                    'MUTASI_KELUAR' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                    default => 'bg-rose-50 text-rose-700 border-rose-200'
                                                };
                                                ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border <?= $badgeClass ?>">
                                                    <?= htmlspecialchars($st) ?>
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <div class="inline-flex items-center gap-1.5">
                                                    <a href="<?= App::baseUrl("admin/buku-induk/detail/{$s['id']}") ?>" title="Lihat Profil Lengkap" class="p-1.5 rounded-lg text-slate-600 hover:text-brand-600 hover:bg-brand-50 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    </a>
                                                    <a href="<?= App::baseUrl("admin/buku-induk/edit/{$s['id']}") ?>" title="Edit Buku Induk" class="p-1.5 rounded-lg text-slate-600 hover:text-amber-600 hover:bg-amber-50 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    </a>
                                                    <a href="<?= App::baseUrl("admin/buku-induk/cetak/{$s['id']}") ?>" target="_blank" title="Cetak Buku Induk Komplit (Biodata + Nilai Sem 1-6)" class="p-1.5 rounded-lg text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                            <div>Halaman <?= $page ?> dari <?= $totalPages ?></div>
                            <div class="flex items-center gap-1.5">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <a href="<?= App::baseUrl("admin/buku-induk?page={$i}&kelas_id={$kelasId}&search=" . urlencode($search)) ?>" class="px-3 py-1.5 rounded-lg font-semibold transition <?= ($i === $page) ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Floating Bulk Assignment Action Bar (Muncul Otomatis Saat Siswa Diceklis) -->
                <div id="bulkBar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-slate-900 text-white px-6 py-3.5 rounded-2xl shadow-2xl border border-slate-800 flex items-center gap-4 z-40 hidden transition-all">
                    <div class="flex items-center gap-2 text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span><strong id="selectedCount" class="text-white font-black text-sm">0</strong> siswa dipilih</span>
                    </div>

                    <div class="h-4 w-px bg-slate-700"></div>

                    <div class="flex items-center gap-2">
                        <label class="text-xs text-slate-300 font-medium">Masukkan ke Kelas:</label>
                        <select name="target_kelas_id" id="target_kelas_id" class="bg-slate-800 border border-slate-700 text-white text-xs font-semibold rounded-xl px-3 py-1.5 focus:ring-2 focus:ring-brand-500 focus:outline-none transition" required>
                            <option value="">-- Pilih Kelas Tujuan --</option>
                            <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" onclick="return confirmBulkAssign()"
                            class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs px-4 py-2 rounded-xl shadow-soft-sm transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Terapkan Kelas</span>
                    </button>

                    <button type="button" onclick="clearAllChecks()" class="text-slate-400 hover:text-white text-xs px-2 py-1 transition">
                        Batal
                    </button>
                </div>
            </form>

        </div>
    </main>

    <script>
        const bulkBar = document.getElementById('bulkBar');
        const countDisplay = document.getElementById('selectedCount');

        function toggleCheckAll(master) {
            const checks = document.querySelectorAll('.buku-checkbox');
            checks.forEach(c => c.checked = master.checked);
            handleRowCheck();
        }

        function handleRowCheck() {
            const checks = document.querySelectorAll('.buku-checkbox:checked');
            const total = checks.length;
            countDisplay.textContent = total;
            
            if (total > 0) {
                bulkBar.classList.remove('hidden');
            } else {
                bulkBar.classList.add('hidden');
                document.getElementById('checkAll').checked = false;
            }
        }

        function clearAllChecks() {
            document.querySelectorAll('.buku-checkbox').forEach(c => c.checked = false);
            document.getElementById('checkAll').checked = false;
            handleRowCheck();
        }

        function confirmBulkAssign() {
            const sel = document.getElementById('target_kelas_id');
            if (!sel.value) {
                alert('Pilih kelas tujuan terlebih dahulu!');
                sel.focus();
                return false;
            }
            const count = document.querySelectorAll('.buku-checkbox:checked').length;
            const namaK = sel.options[sel.selectedIndex].text;
            return confirm(`Apakah Anda yakin ingin memasukkan/memindahkan ${count} siswa terpilih ke ${namaK}?`);
        }
    </script>
</body>
</html>

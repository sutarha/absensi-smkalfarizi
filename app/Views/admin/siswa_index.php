<?php
use App\Config\App;
use App\Helpers\BarcodeHelper;

$activeNav = 'siswa';
$kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Siswa & Barcode - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Modern Admin Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto pb-24">
        
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div>
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                    <span>Master Siswa & Barcode Gerbang</span>
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Integrasi data siswa dengan Buku Induk Dapodik, barcode kotak presensi, dan penempatan rombel</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="<?= App::baseUrl('admin/buku-induk/import') ?>" 
                   class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-slate-300">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <span>Import dari Dapodik</span>
                </a>
                <a href="<?= App::baseUrl('admin/siswa/cetak-kartu' . ($kelasId ? '?kelas_id=' . $kelasId : '')) ?>" target="_blank"
                   class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-soft-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Kartu Barcode (A4)</span>
                </a>
                <button onclick="openCreateModal()" 
                        class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Siswa Manual</span>
                </button>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-soft-sm text-xs font-semibold">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
            <?php endif; ?>

            <!-- Filter Kelas Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft-sm flex items-center justify-between flex-wrap gap-4">
                <form method="GET" action="<?= App::baseUrl('admin/siswa') ?>" class="flex items-center gap-3 flex-wrap">
                    <label class="text-xs font-bold text-slate-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Filter Rombel:</span>
                    </label>
                    <div class="relative min-w-[240px]">
                        <select name="kelas_id" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl px-3.5 py-2 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition appearance-none" onchange="this.form.submit()">
                            <option value="">-- Tampilkan Seluruh Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= $kelasId == $k['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['nama_kelas']) ?> (<?= htmlspecialchars($k['jurusan']) ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="w-4 h-4 text-slate-400 absolute right-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    <?php if ($kelasId): ?>
                    <a href="<?= App::baseUrl('admin/siswa') ?>" class="text-xs font-semibold text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-3 py-2 rounded-xl transition">
                        Reset Filter
                    </a>
                    <?php endif; ?>
                </form>

                <div class="text-xs text-slate-500 font-medium">
                    Total Siswa: <strong class="text-slate-800 font-bold"><?= count($siswaList) ?></strong> orang
                </div>
            </div>

            <!-- Form Bulk Assign Kelas (Ceklis Massal) -->
            <form id="bulkAssignForm" method="POST" action="<?= App::baseUrl('admin/siswa/bulk-assign-kelas') ?>">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? App::baseUrl('admin/siswa')) ?>">

                <!-- Table Siswa -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    <th class="py-3.5 px-3 w-10 text-center">
                                        <input type="checkbox" id="checkAll" onchange="toggleCheckAll(this)" 
                                               class="w-4 h-4 text-brand-600 bg-slate-100 border-slate-300 rounded focus:ring-brand-500 cursor-pointer">
                                    </th>
                                    <th class="py-3.5 px-3 w-12 text-center">No</th>
                                    <th class="py-3.5 px-4">NISN</th>
                                    <th class="py-3.5 px-4">Nama Siswa</th>
                                    <th class="py-3.5 px-4">Kelas &amp; Wali Kelas</th>
                                    <th class="py-3.5 px-4 text-center">L/P</th>
                                    <th class="py-3.5 px-4 text-center">Login PWA</th>
                                    <th class="py-3.5 px-4">Barcode Kotak (QR)</th>
                                    <th class="py-3.5 px-4">Kode Fisik</th>
                                    <th class="py-3.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php if (empty($siswaList)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-12 text-slate-400">
                                        <div class="text-2xl mb-1">👨‍🎓</div>
                                        <p class="font-medium">Belum ada data siswa pada filter ini. Silakan tambahkan manual atau impor Dapodik.</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php $i = 1; foreach ($siswaList as $s): ?>
                                    <tr class="hover:bg-slate-50/70 transition" id="row-<?= $s['id'] ?>">
                                        <td class="py-3 px-3 text-center">
                                            <input type="checkbox" name="siswa_ids[]" value="<?= $s['id'] ?>" onchange="handleRowCheck()"
                                                   class="siswa-checkbox w-4 h-4 text-brand-600 bg-slate-100 border-slate-300 rounded focus:ring-brand-500 cursor-pointer">
                                        </td>
                                        <td class="py-3 px-3 text-center text-slate-400 font-medium"><?= $i++ ?></td>
                                        <td class="py-3 px-4 font-mono font-bold text-slate-800"><?= htmlspecialchars($s['nisn']) ?></td>
                                        <td class="py-3 px-4 font-bold text-slate-900">
                                            <a href="<?= App::baseUrl("admin/buku-induk/detail/{$s['id']}") ?>" class="hover:text-brand-600 transition">
                                                <?= htmlspecialchars($s['nama_siswa']) ?>
                                            </a>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-brand-50 text-brand-700 border border-brand-100">
                                                <?= htmlspecialchars($s['nama_kelas']) ?>
                                            </span>
                                            <?php if (!empty($s['nama_wali_kelas'])): ?>
                                                <div class="text-[10px] text-slate-400 mt-0.5">Wali: <?= htmlspecialchars($s['nama_wali_kelas']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold <?= $s['jenis_kelamin'] === 'P' ? 'bg-pink-50 text-pink-700 border border-pink-200' : 'bg-blue-50 text-blue-700 border border-blue-200' ?>">
                                                <?= $s['jenis_kelamin'] === 'P' ? 'P' : 'L' ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <?php if (!empty($s['tanggal_lahir'])): ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Tanggal Lahir: <?= htmlspecialchars($s['tanggal_lahir']) ?>">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Siap
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Tanggal lahir belum diisi — siswa belum bisa login PWA">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Belum
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="inline-block p-1 bg-white border border-slate-200 rounded-xl shadow-soft-sm">
                                                <img src="<?= BarcodeHelper::getQrCodeDataUri($s['barcode_code']) ?>" alt="QR Code" class="w-10 h-10 block rounded-md">
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                            <span class="bg-slate-100 px-2 py-1 rounded-md border border-slate-200">
                                                <?= htmlspecialchars($s['barcode_code']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <div class="inline-flex items-center gap-1.5 justify-center">
                                                <a href="<?= App::baseUrl("admin/buku-induk/detail/{$s['id']}") ?>" 
                                                   title="Buka Buku Induk"
                                                   class="p-1.5 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                </a>
                                                <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)" 
                                                        title="Edit Siswa"
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-brand-600 hover:bg-brand-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </button>
                                                <button type="button" onclick="confirmDelete(<?= $s['id'] ?>)" 
                                                        title="Hapus Siswa"
                                                        class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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

    <!-- Modal Form Tambah/Edit Siswa -->
    <div id="siswaModal" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 transition-all">
            <div class="flex items-center justify-between mb-5 border-b border-slate-100 pb-3">
                <h3 id="modalTitle" class="font-display font-bold text-base text-slate-900">Tambah Siswa Baru</h3>
                <button type="button" onclick="closeModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                    &times;
                </button>
            </div>

            <form id="siswaForm" method="POST" action="<?= App::baseUrl('admin/siswa/store') ?>" class="space-y-4">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">NISN (Nomor Induk Siswa Nasional)</label>
                    <input type="text" name="nisn" id="m_nisn" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="10 digit nomor NISN" required oninput="autoGenBarcode(this.value)">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode Barcode</label>
                    <input type="text" name="barcode_code" id="m_barcode_code" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-mono font-bold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Otomatis: ALF-[NISN]">
                    <p class="text-[10px] text-slate-400 mt-1">Dapat dikosongkan untuk menghasilkan barcode otomatis ALF-[NISN]</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="nama_siswa" id="m_nama_siswa" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" placeholder="Nama lengkap sesuai akta / ijazah" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kelas Rombel</label>
                        <select name="kelas_id" id="m_kelas_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                            <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="m_jenis_kelamin" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>

                <!-- ====== BARU: Data untuk Login PWA Siswa ====== -->
                <div class="border-t border-slate-100 pt-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-brand-600 mb-3 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21l4-4 4 4M3 9l9-9 9 9"/></svg>
                        Data Login Portal PWA Siswa
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Tanggal Lahir
                                <span class="text-rose-500 ml-0.5">*</span>
                            </label>
                            <input type="date" name="tanggal_lahir" id="m_tanggal_lahir"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition"
                                   max="<?= date('Y-m-d') ?>">
                            <p class="text-[10px] text-slate-400 mt-1">Digunakan sebagai password login PWA</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No HP Orang Tua</label>
                            <input type="tel" name="no_hp_ortu" id="m_no_hp_ortu"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:bg-white focus:outline-none transition"
                                   placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>
                <!-- ====== END BARU ====== -->

                <div class="flex justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-700 text-white shadow-soft-sm transition">
                        Simpan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteForm" method="POST" action="" class="hidden">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?></form>

    <script>
        const modal = document.getElementById('siswaModal');
        const form = document.getElementById('siswaForm');
        const bulkBar = document.getElementById('bulkBar');
        const countDisplay = document.getElementById('selectedCount');

        // Ceklis Massal Logic
        function toggleCheckAll(master) {
            const checks = document.querySelectorAll('.siswa-checkbox');
            checks.forEach(c => c.checked = master.checked);
            handleRowCheck();
        }

        function handleRowCheck() {
            const checks = document.querySelectorAll('.siswa-checkbox:checked');
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
            document.querySelectorAll('.siswa-checkbox').forEach(c => c.checked = false);
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
            const count = document.querySelectorAll('.siswa-checkbox:checked').length;
            const namaK = sel.options[sel.selectedIndex].text;
            return confirm(`Apakah Anda yakin ingin memasukkan/memindahkan ${count} siswa terpilih ke ${namaK}?`);
        }

        function autoGenBarcode(nisn) {
            const bcInput = document.getElementById('m_barcode_code');
            if (form.action.includes('store')) {
                bcInput.value = nisn ? 'ALF-' + nisn.trim() : '';
            }
        }

        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Siswa Baru';
            form.action = '<?= App::baseUrl('admin/siswa/store') ?>';
            form.reset();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function openEditModal(s) {
            document.getElementById('modalTitle').textContent = 'Edit Siswa: ' + s.nama_siswa;
            form.action = '<?= App::baseUrl('admin/siswa/update/') ?>' + s.id;
            document.getElementById('m_nisn').value = s.nisn || '';
            document.getElementById('m_barcode_code').value = s.barcode_code || '';
            document.getElementById('m_nama_siswa').value = s.nama_siswa || '';
            document.getElementById('m_kelas_id').value = s.kelas_id || '';
            document.getElementById('m_jenis_kelamin').value = s.jenis_kelamin || 'L';
            document.getElementById('m_tanggal_lahir').value = s.tanggal_lahir || '';
            document.getElementById('m_no_hp_ortu').value = s.no_hp_ortu || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function confirmDelete(id) {
            if (confirm('Yakin ingin menghapus siswa ini? Seluruh data presensi dan riwayat kelas terkait akan ikut terhapus.')) {
                const df = document.getElementById('deleteForm');
                df.action = '<?= App::baseUrl('admin/siswa/delete/') ?>' + id;
                df.submit();
            }
        }

        window.onclick = function(e) {
            if (e.target === modal) closeModal();
        }
    </script>
</body>
</html>

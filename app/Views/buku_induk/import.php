<?php
use App\Config\App;

$activeNav = 'buku_induk';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Buku Induk Siswa Dapodik - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div class="flex items-center gap-3">
                <a href="<?= App::baseUrl('admin/buku-induk') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Import Data Pokok Siswa (Dapodik)</h1>
                    <p class="text-xs text-slate-500">Unggah berkas hasil unduhan Dapodik langsung ke database Buku Induk</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= App::baseUrl('admin/buku-induk/download-template') ?>" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl transition shadow-soft-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Template Excel (.xlsx)
                </a>
                <a href="<?= App::baseUrl('admin/buku-induk/download-template?format=csv') ?>" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-700 text-xs px-2.5 py-2 rounded-xl hover:bg-slate-200 transition">
                    (CSV)
                </a>
            </div>
        </header>

        <!-- Body Content -->
        <div class="p-8 max-w-4xl w-full mx-auto space-y-6">

            <!-- Flash Error -->
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-soft-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Petunjuk Alur Integrasi Dapodik -->
            <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-700 rounded-3xl p-6 text-white shadow-soft-md">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center text-white flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-display font-bold">Impor Langsung Berkas Excel Hasil Unduh Dapodik:</h2>
                        <ol class="mt-2 text-xs text-white/90 space-y-1.5 list-decimal list-inside leading-relaxed">
                            <li>Buka aplikasi <strong>Dapodikdasmen</strong> sekolah Anda dan masuk sebagai Operator / Bagian Tata Usaha.</li>
                            <li>Buka menu <strong>Peserta Didik</strong> &gt; klik tab <strong>Peserta Didik</strong>.</li>
                            <li>Klik tombol <strong>Ekspor / Unduh</strong> untuk mendapatkan file Excel Profil Siswa (Data Pokok).</li>
                            <li><strong>Langsung unggah berkas Excel (.xlsx / .xls)</strong> tersebut di bawah ini tanpa perlu konversi atau ubah format!</li>
                            <li>Atau jika ingin input manual, Anda dapat mengunduh <strong>Template Excel (.xlsx)</strong> di atas, mengisi data siswa, lalu mengunggahnya ke sini.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-8 shadow-soft-sm">
                <form method="POST" action="<?= App::baseUrl('admin/buku-induk/import') ?>" enctype="multipart/form-data" class="space-y-6">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                    <div>
                        <label class="block font-display font-bold text-slate-900 text-sm mb-2">Pilih Berkas Excel Data Pokok Siswa Dapodik</label>
                        <div class="border-2 border-dashed border-emerald-300 bg-emerald-50/20 rounded-2xl p-8 text-center hover:border-emerald-500 hover:bg-emerald-50/40 transition cursor-pointer relative group">
                            <input type="file" name="file_dapodik" accept=".xlsx, .xls, .csv, .txt" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="updateFileName(this)">
                            <div class="space-y-2 pointer-events-none">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-100/80 group-hover:bg-emerald-200/80 transition flex items-center justify-center text-emerald-700">
                                    <svg class="w-8 h-8 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="font-bold text-slate-800 text-sm" id="file-label">
                                    Klik atau seret berkas Excel (.xlsx / .xls) ke sini
                                </div>
                                <div class="text-xs text-slate-500">
                                    Mendukung format Microsoft Excel <strong>.xlsx</strong> (disarankan), Excel 97-2003 <strong>.xls</strong>, maupun berkas <strong>.csv</strong>.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs text-slate-600 space-y-1">
                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Cerdas & Aman (Upsert Logic Otomatis):
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 pl-1">
                            <li>Jika siswa <strong>belum terdaftar</strong> (berdasarkan NISN), siswa otomatis dibuatkan akun presensi dan data profil Buku Induk.</li>
                            <li>Jika siswa <strong>sudah terdaftar</strong>, seluruh rincian Dapodik (NIK, Nama Ibu/Ayah, Alamat, dll) otomatis <strong>diperbarui (update)</strong> tanpa mengubah/menghapus riwayat absensi siswa!</li>
                            <li>Digit angka depan (angka 0 pada NISN, NIK, No. KK) tersimpan aman dan tidak akan hilang terpotong.</li>
                        </ul>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="<?= App::baseUrl('admin/buku-induk') ?>" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-emerald transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Mulai Proses Import & Sinkronisasi Excel
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>

    <script>
    function updateFileName(input) {
        if (input.files && input.files[0]) {
            document.getElementById('file-label').innerHTML = `<span class="text-brand-600 font-bold">Berkas terpilih:</span> ${input.files[0].name} (${(input.files[0].size / 1024).toFixed(1)} KB)`;
        }
    }
    </script>

</body>
</html>

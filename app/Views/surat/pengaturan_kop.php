<?php
use App\Config\App;
use App\Helpers\SuratHelper;

$activeNav = 'kop_surat';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan KOP Surat Dinas - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?></title>
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
                <a href="<?= App::baseUrl('admin/surat') ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Pengaturan KOP Surat & Identitas Dinas</h1>
                    <p class="text-xs text-slate-500">Konfigurasi logo, alamat resmi, NPSN, NSS, dan pejabat kepala sekolah</p>
                </div>
            </div>
            <div>
                <button type="submit" form="form-kop" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-5 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan KOP
                </button>
            </div>
        </header>

        <!-- Body Container -->
        <div class="p-8 max-w-5xl w-full mx-auto space-y-6">

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

            <!-- Live Preview Card of KOP Surat -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Pratinjau Langsung KOP Surat Resmi (Sesuai Tata Naskah Dinas Kemendikbud):
                </div>
                <div class="border border-slate-200 p-6 rounded-xl bg-white">
                    <?= SuratHelper::renderKopDinas($config) ?>
                </div>
            </div>

            <!-- Form Edit KOP -->
            <form id="form-kop" method="POST" action="<?= App::baseUrl('admin/surat/simpan-kop') ?>" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm space-y-5">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>
                <h3 class="font-display font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">Rincian Identitas KOP Surat</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Resmi Sekolah / Lembaga</label>
                        <input type="text" name="nama_sekolah" value="<?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK AL-FARIZI') ?>" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status Akreditasi</label>
                        <input type="text" name="akreditasi" value="<?= htmlspecialchars($config['akreditasi'] ?? 'A (Unggul)') ?>" placeholder="Contoh: A (Unggul)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Pokok Sekolah Nasional (NPSN)</label>
                        <input type="text" name="npsn" value="<?= htmlspecialchars($config['npsn'] ?? '69900000') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nomor Statistik Sekolah (NSS)</label>
                        <input type="text" name="nss" value="<?= htmlspecialchars($config['nss'] ?? '402020700000') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1">Alamat Lengkap Sekolah</label>
                        <input type="text" name="alamat_sekolah" value="<?= htmlspecialchars($config['alamat_sekolah'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Email Resmi Sekolah</label>
                        <input type="email" name="email_sekolah" value="<?= htmlspecialchars($config['email_sekolah'] ?? 'smkalfarizi@sch.id') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Website Sekolah</label>
                        <input type="text" name="website_sekolah" value="<?= htmlspecialchars($config['website_sekolah'] ?? 'https://smkalfarizi.sch.id') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Kepala Sekolah</label>
                        <input type="text" name="kepala_sekolah" value="<?= htmlspecialchars($config['kepala_sekolah'] ?? 'H. Ahmad Alfarizi, M.Pd') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Unggah Logo KOP Baru (Opsional)</label>
                        <input type="file" name="logo_kop" accept="image/png, image/jpeg, image/svg+xml" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs text-slate-600 focus:ring-2 focus:ring-brand-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                        <span class="text-[10px] text-slate-400">Format: PNG transparan atau JPG (disarankan persegi resolusi tinggi)</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan KOP Surat
                    </button>
                </div>
            </form>

        </div>
    </main>

</body>
</html>

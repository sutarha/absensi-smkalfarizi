<?php
use App\Config\App;

$activeNav = 'surat';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Surat Dinas & SPPD - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?></title>
    <?php require __DIR__ . '/../partials/tailwind_head.php'; ?>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-full flex overflow-hidden selection:bg-brand-500 selection:text-white">

    <!-- Shared Sidebar -->
    <?php require __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col min-w-0 h-full overflow-y-auto">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-soft-sm">
            <div>
                <h1 class="text-xl font-display font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Arsip Persuratan Resmi & SPPD Dinas
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Generator surat dinas otomatis, Surat Tugas, SPPD 10 kolom, dan Lembar Visum Pejabat Tujuan</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="<?= App::baseUrl('admin/surat/pengaturan-kop') ?>" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-slate-300">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Pengaturan KOP Surat
                </a>
                <a href="<?= App::baseUrl('admin/surat/buat') ?>" class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Surat Dinas Baru / SPPD
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

            <!-- Filter Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-soft-sm flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1">Filter Jenis:</span>
                    <a href="<?= App::baseUrl('admin/surat') ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= empty($jenisSurat) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                        Semua Surat
                    </a>
                    <a href="<?= App::baseUrl('admin/surat?jenis=SISWA_AKTIF') ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= ($jenisSurat === 'SISWA_AKTIF') ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                        Surat Siswa Aktif
                    </a>
                    <a href="<?= App::baseUrl('admin/surat?jenis=SPPD') ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= ($jenisSurat === 'SPPD') ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                        SPPD Perjalanan Dinas
                    </a>
                    <a href="<?= App::baseUrl('admin/surat?jenis=SURAT_TUGAS') ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= ($jenisSurat === 'SURAT_TUGAS') ? 'bg-purple-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                        Surat Perintah Tugas
                    </a>
                    <a href="<?= App::baseUrl('admin/surat?jenis=PINDAH_SEKOLAH') ?>" class="px-3 py-1.5 rounded-xl text-xs font-semibold transition <?= ($jenisSurat === 'PINDAH_SEKOLAH') ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">
                        Keterangan Pindah
                    </a>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-display font-bold text-slate-900 text-sm">Buku Agenda & Arsip Surat Keluar</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Total surat tercatat: <?= count($suratList) ?> surat</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nomor Surat Dinas</th>
                                <th class="py-3 px-4">Jenis Surat</th>
                                <th class="py-3 px-4">Penerima / Pegawai</th>
                                <th class="py-3 px-4">Perihal / Keperluan</th>
                                <th class="py-3 px-4">Tanggal Surat</th>
                                <th class="py-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (empty($suratList)): ?>
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">
                                        Belum ada surat yang diterbitkan. Silakan klik "Buat Surat Dinas Baru".
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($suratList as $s): ?>
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3.5 px-4 text-center text-slate-400"><?= $no++ ?></td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-mono font-bold text-slate-900 text-xs bg-slate-100 px-2 py-0.5 rounded">
                                                <?= htmlspecialchars($s['nomor_surat']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <?php
                                            $js = $s['jenis_surat'];
                                            $badge = match($js) {
                                                'SPPD' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                'SURAT_TUGAS' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                'SISWA_AKTIF' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                default => 'bg-amber-50 text-amber-700 border-amber-200'
                                            };
                                            ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border <?= $badge ?>">
                                                <?= htmlspecialchars(str_replace('_', ' ', $js)) ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-bold text-slate-800">
                                            <?= htmlspecialchars($s['nama_penerima'] ?? '-') ?>
                                            <div class="text-[11px] font-normal text-slate-400">Tipe: <?= htmlspecialchars($s['penerima_tipe']) ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 max-w-xs truncate text-slate-700">
                                            <div class="font-semibold text-slate-900"><?= htmlspecialchars($s['perihal']) ?></div>
                                            <div class="text-[11px] text-slate-400 truncate"><?= htmlspecialchars($s['keperluan'] ?? '-') ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-500 font-mono">
                                            <?= date('d/m/Y', strtotime($s['tanggal_surat'])) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="inline-flex items-center gap-1.5">
                                                <a href="<?= App::baseUrl("admin/surat/cetak/{$s['id']}") ?>" target="_blank" title="Cetak / Pratinjau Surat Resmi" class="inline-flex items-center gap-1 bg-brand-50 hover:bg-brand-100 text-brand-700 font-semibold px-2.5 py-1 rounded-lg transition border border-brand-200 text-[11px]">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                    Cetak
                                                </a>
                                                <form method="POST" action="<?= App::baseUrl("admin/surat/delete/{$s['id']}") ?>
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat ini dari arsip?');" class="inline">
                                                    <button type="submit" title="Hapus dari arsip" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</body>
</html>

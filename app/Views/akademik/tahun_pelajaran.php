<?php
use App\Config\App;

$activeNav = 'tapel';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Tahun Pelajaran - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?></title>
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
                    <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Tahun Pelajaran & Semester Aktif
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola siklus kalender akademik, semester berjalan, dan pergantian tahun ajaran</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= App::baseUrl('admin/akademik/kenaikan-kelas') ?>" class="inline-flex items-center gap-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs px-4 py-2 rounded-xl transition border border-indigo-200">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Proses Kenaikan Kelas
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

            <!-- Active Year Highlight Card -->
            <?php if ($activeTapel): ?>
                <div class="bg-gradient-to-r from-brand-600 to-indigo-700 rounded-3xl p-6 text-white shadow-soft-md flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Tahun Pelajaran & Semester Aktif Saat Ini
                        </div>
                        <h2 class="text-2xl font-display font-black tracking-tight">
                            Tahun Ajaran <?= htmlspecialchars($activeTapel['tahun_ajaran']) ?> (Semester <?= htmlspecialchars($activeTapel['semester']) ?>)
                        </h2>
                        <p class="text-xs text-white/80 mt-1">
                            Periode Kalender: 
                            <?= !empty($activeTapel['tanggal_mulai']) ? date('d/m/Y', strtotime($activeTapel['tanggal_mulai'])) : 'Awal Semester' ?> 
                            s/d 
                            <?= !empty($activeTapel['tanggal_selesai']) ? date('d/m/Y', strtotime($activeTapel['tanggal_selesai'])) : 'Akhir Semester' ?>
                        </p>
                    </div>
                    <div class="bg-white/10 backdrop-blur border border-white/20 p-4 rounded-2xl text-xs space-y-1">
                        <div class="font-bold">Dampak Semester Aktif:</div>
                        <div class="text-white/80">• Presensi harian siswa & guru otomatis dicatat pada tapel ini.</div>
                        <div class="text-white/80">• Perhitungan nilai rapor & transkrip mengacu pada tapel aktif.</div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 2 Column Layout: Form Tambah & Tabel Daftar -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Form Tambah Tahun Pelajaran -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm h-fit">
                    <h3 class="font-display font-bold text-slate-900 text-sm mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Tambah Tahun Pelajaran
                    </h3>

                    <form method="POST" action="<?= App::baseUrl('admin/akademik/tahun-pelajaran/store') ?>
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>" class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tahun Ajaran</label>
                            <input type="text" name="tahun_ajaran" placeholder="Contoh: 2026/2027" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            <span class="text-[10px] text-slate-400">Gunakan format tahun/tahun (contoh: 2026/2027)</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Semester</label>
                            <select name="semester" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                <option value="Ganjil">Semester Ganjil</option>
                                <option value="Genap">Semester Genap</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tanggal Mulai Periode</label>
                            <input type="date" name="tanggal_mulai" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tanggal Selesai Periode</label>
                            <input type="date" name="tanggal_selesai" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" name="is_aktif" id="is_aktif" value="1" class="rounded text-brand-600 focus:ring-brand-500 w-4 h-4">
                            <label for="is_aktif" class="text-xs font-semibold text-slate-700 cursor-pointer">Langsung jadikan sebagai semester aktif</label>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-soft-sm transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Simpan Tahun Pelajaran
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Tabel Daftar Tahun Pelajaran -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100">
                        <h3 class="font-display font-bold text-slate-900 text-sm">Daftar Riwayat Tahun Pelajaran</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh tahun ajaran dan semester yang tercatat pada sistem</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">No</th>
                                    <th class="py-3 px-4">Tahun Pelajaran</th>
                                    <th class="py-3 px-4">Semester</th>
                                    <th class="py-3 px-4">Rentang Waktu</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <?php if (empty($tapelList)): ?>
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-400">Belum ada tahun pelajaran terdaftar.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($tapelList as $t): ?>
                                        <tr class="hover:bg-slate-50/70 transition <?= ($t['is_aktif'] == 1) ? 'bg-emerald-50/30' : '' ?>">
                                            <td class="py-3.5 px-4 text-center text-slate-400"><?= $no++ ?></td>
                                            <td class="py-3.5 px-4 font-bold text-slate-900 text-sm"><?= htmlspecialchars($t['tahun_ajaran']) ?></td>
                                            <td class="py-3.5 px-4">
                                                <span class="font-semibold <?= ($t['semester'] === 'Ganjil') ? 'text-indigo-600' : 'text-purple-600' ?>">
                                                    Semester <?= htmlspecialchars($t['semester']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-4 text-slate-500">
                                                <?= !empty($t['tanggal_mulai']) ? date('d/m/Y', strtotime($t['tanggal_mulai'])) : '-' ?> 
                                                s/d 
                                                <?= !empty($t['tanggal_selesai']) ? date('d/m/Y', strtotime($t['tanggal_selesai'])) : '-' ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <?php if ($t['is_aktif'] == 1): ?>
                                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                                        Non-Aktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3.5 px-4 text-center">
                                                <?php if ($t['is_aktif'] != 1): ?>
                                                    <form method="POST" action="<?= App::baseUrl("admin/akademik/tahun-pelajaran/set-aktif/{$t['id']}") ?>
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>" onsubmit="return confirm('Apakah Anda yakin ingin mengaktifkan Tahun Pelajaran <?= htmlspecialchars($t['tahun_ajaran']) ?> (<?= htmlspecialchars($t['semester']) ?>)?');">
                                                        <button type="submit" class="inline-flex items-center gap-1 bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 font-semibold text-xs px-3 py-1 rounded-lg transition border border-slate-300">
                                                            Aktifkan
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-xs text-emerald-600 font-bold">Sedang Berjalan</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </main>

</body>
</html>

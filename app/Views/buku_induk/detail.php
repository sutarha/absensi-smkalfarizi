<?php
use App\Config\App;

$activeNav = 'buku_induk';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Buku Induk - <?= htmlspecialchars($siswa['nama_lengkap']) ?></title>
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
                    <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Lembar Profil Buku Induk Siswa</h1>
                    <p class="text-xs text-slate-500">Rekam Jejak Terverifikasi Data Pokok Pendidikan (Dapodik)</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="<?= App::baseUrl("admin/buku-induk/input-nilai-manual/{$siswa['id']}") ?>" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl transition shadow-soft-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Input Nilai Raport Manual
                </a>
                <a href="<?= App::baseUrl("admin/nilai/transkrip-ijazah/{$siswa['id']}") ?>" target="_blank" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl transition shadow-soft-sm">
                    <span>🎓</span>
                    <span>Transkrip Ijazah</span>
                </a>
                <a href="<?= App::baseUrl("admin/surat/buat?jenis=SISWA_AKTIF&siswa_id={$siswa['id']}") ?>" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-3.5 py-2 rounded-xl transition border border-slate-300">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Surat Siswa Aktif
                </a>
                <a href="<?= App::baseUrl("admin/buku-induk/edit/{$siswa['id']}") ?>" class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs px-3.5 py-2 rounded-xl transition shadow-soft-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Data
                </a>
                <a href="<?= App::baseUrl("admin/buku-induk/cetak/{$siswa['id']}") ?>" target="_blank" class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-4 py-2 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Cetak Buku Induk Komplit (Sem 1-6)
                </a>
            </div>
        </header>

        <!-- Content Body -->
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

            <!-- Hero Student Identity Card -->
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-soft-sm relative overflow-hidden">
                <div class="absolute right-0 top-0 w-96 h-96 bg-brand-50 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none opacity-60"></div>
                <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-600 flex items-center justify-center text-white text-3xl font-display font-black shadow-glow-brand flex-shrink-0">
                            <?= strtoupper(substr($siswa['nama_lengkap'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <h2 class="text-2xl font-display font-black text-slate-900"><?= htmlspecialchars($siswa['nama_lengkap']) ?></h2>
                                <span class="px-3 py-1 rounded-full text-xs font-bold border <?= ($siswa['status_siswa'] === 'AKTIF') ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' ?>">
                                    <?= htmlspecialchars($siswa['status_siswa'] ?? 'AKTIF') ?>
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-600 mt-2 font-medium">
                                <span class="flex items-center gap-1.5">
                                    <strong class="text-slate-900">NISN:</strong> <code class="font-mono bg-slate-100 px-2 py-0.5 rounded text-brand-700"><?= htmlspecialchars($siswa['nisn']) ?></code>
                                </span>
                                <?php if (!empty($siswa['nis'])): ?>
                                    <span class="flex items-center gap-1.5">
                                        <strong class="text-slate-900">NIS:</strong> <code class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-700"><?= htmlspecialchars($siswa['nis']) ?></code>
                                    </span>
                                <?php endif; ?>
                                <span class="flex items-center gap-1.5">
                                    <strong class="text-slate-900">Kelas:</strong> <span class="bg-indigo-50 text-indigo-700 font-bold px-2 py-0.5 rounded"><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></span>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <strong class="text-slate-900">Jurusan:</strong> <?= htmlspecialchars($siswa['jurusan'] ?? '-') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2-Column Information Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Bagian A: Data Pribadi Siswa -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                        <h3 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-brand-600 text-white flex items-center justify-center text-xs font-black">A</span>
                            Keterangan Pribadi Siswa
                        </h3>
                    </div>
                    <div class="p-5 text-xs">
                        <dl class="divide-y divide-slate-100">
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Nama Lengkap</dt>
                                <dd class="col-span-2 text-slate-900 font-bold"><?= htmlspecialchars($siswa['nama_lengkap']) ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Jenis Kelamin</dt>
                                <dd class="col-span-2 text-slate-800"><?= ($siswa['jenis_kelamin'] === 'L') ? 'Laki-laki (L)' : 'Perempuan (P)' ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Tempat, Tanggal Lahir</dt>
                                <dd class="col-span-2 text-slate-800 font-medium">
                                    <?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, 
                                    <?= !empty($siswa['tanggal_lahir']) ? date('d F Y', strtotime($siswa['tanggal_lahir'])) : '-' ?>
                                </dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">NIK (KTP/KIA)</dt>
                                <dd class="col-span-2 font-mono text-slate-800"><?= htmlspecialchars($siswa['nik'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Nomor KK</dt>
                                <dd class="col-span-2 font-mono text-slate-800"><?= htmlspecialchars($siswa['no_kk'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">No. Akta Kelahiran</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['no_akta_lahir'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Agama & Kepercayaan</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['agama'] ?? 'Islam') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Kewarganegaraan</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['kewarganegaraan'] ?? 'WNI') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Anak Ke / Saudara</dt>
                                <dd class="col-span-2 text-slate-800">Anak ke-<?= htmlspecialchars((string)($siswa['anak_ke'] ?? 1)) ?> dari <?= htmlspecialchars((string)($siswa['jumlah_saudara'] ?? 0)) ?> bersaudara</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Bagian B: Keterangan Tempat Tinggal -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                        <h3 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-black">B</span>
                            Keterangan Tempat Tinggal & Transportasi
                        </h3>
                    </div>
                    <div class="p-5 text-xs">
                        <dl class="divide-y divide-slate-100">
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Alamat Jalan</dt>
                                <dd class="col-span-2 text-slate-900 font-medium"><?= htmlspecialchars($siswa['alamat_jalan'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">RT / RW</dt>
                                <dd class="col-span-2 text-slate-800">RT <?= htmlspecialchars($siswa['rt'] ?? '-') ?> / RW <?= htmlspecialchars($siswa['rw'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Dusun / Kelurahan</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['dusun_kelurahan'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Kecamatan</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['kecamatan'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Kabupaten / Kota</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['kabupaten_kota'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Provinsi & Kode Pos</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['provinsi'] ?? '-') ?> (Kode Pos: <?= htmlspecialchars($siswa['kode_pos'] ?? '-') ?>)</dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Tinggal Bersama</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['tinggal_bersama'] ?? 'Orang Tua') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Moda Transportasi</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['transportasi'] ?? 'Sepeda Motor') ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Bagian C: Data Orang Tua & Wali -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                        <h3 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black">C</span>
                            Data Orang Tua Kandung & Wali
                        </h3>
                    </div>
                    <div class="p-5 text-xs">
                        <dl class="divide-y divide-slate-100">
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Nama Ayah Kandung</dt>
                                <dd class="col-span-2 text-slate-900 font-bold"><?= htmlspecialchars($siswa['nama_ayah'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">NIK & Thn Lahir Ayah</dt>
                                <dd class="col-span-2 text-slate-800">NIK: <?= htmlspecialchars($siswa['nik_ayah'] ?? '-') ?> (Tahun <?= htmlspecialchars($siswa['tahun_lahir_ayah'] ?? '-') ?>)</dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Pekerjaan & Gaji Ayah</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['pekerjaan_ayah'] ?? '-') ?> / <?= htmlspecialchars($siswa['penghasilan_ayah'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Nama Ibu Kandung</dt>
                                <dd class="col-span-2 text-slate-900 font-bold"><?= htmlspecialchars($siswa['nama_ibu'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">NIK & Thn Lahir Ibu</dt>
                                <dd class="col-span-2 text-slate-800">NIK: <?= htmlspecialchars($siswa['nik_ibu'] ?? '-') ?> (Tahun <?= htmlspecialchars($siswa['tahun_lahir_ibu'] ?? '-') ?>)</dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Pekerjaan & Gaji Ibu</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['pekerjaan_ibu'] ?? '-') ?> / <?= htmlspecialchars($siswa['penghasilan_ibu'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Nama Wali (Jika Ada)</dt>
                                <dd class="col-span-2 text-slate-800"><?= htmlspecialchars($siswa['nama_wali'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">No. Kontak HP / WA</dt>
                                <dd class="col-span-2 font-mono text-emerald-700 font-bold"><?= htmlspecialchars($siswa['no_hp_ortu'] ?? $siswa['no_telepon_orangtua'] ?? '-') ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Bagian D: Pendidikan Sebelumnya & Status Masuk -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                    <div class="p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                        <h3 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs font-black">D</span>
                            Asal Sekolah & Penerimaan di SMK
                        </h3>
                    </div>
                    <div class="p-5 text-xs">
                        <dl class="divide-y divide-slate-100">
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Sekolah Asal (SMP/MTs)</dt>
                                <dd class="col-span-2 text-slate-900 font-bold"><?= htmlspecialchars($siswa['sekolah_asal'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Nomor Ijazah SMP</dt>
                                <dd class="col-span-2 font-mono text-slate-800"><?= htmlspecialchars($siswa['no_ijazah_smp'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Nomor SKHUN SMP</dt>
                                <dd class="col-span-2 font-mono text-slate-800"><?= htmlspecialchars($siswa['no_skhun_smp'] ?? '-') ?></dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Tanggal Diterima</dt>
                                <dd class="col-span-2 text-slate-800 font-medium">
                                    <?= !empty($siswa['tanggal_masuk']) ? date('d F Y', strtotime($siswa['tanggal_masuk'])) : '-' ?>
                                </dd>
                            </div>
                            <div class="py-2.5 grid grid-cols-3">
                                <dt class="font-semibold text-slate-500">Barcode ID Presensi</dt>
                                <dd class="col-span-2 font-mono text-brand-700 font-bold"><?= htmlspecialchars($siswa['barcode_id'] ?? '-') ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>

            <!-- Bagian E: Riwayat Rombel & Kenaikan Kelas per Tahun Ajaran -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-black">E</span>
                            Riwayat Rombel Kelas & Perkembangan Akademik Siswa
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Catatan historis penempatan kelas, tingkat, dan status kenaikan kelas per tahun ajaran</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Tahun Pelajaran</th>
                                <th class="py-3 px-4">Semester</th>
                                <th class="py-3 px-4">Rombel / Kelas</th>
                                <th class="py-3 px-4">Tingkat</th>
                                <th class="py-3 px-4">Wali Kelas</th>
                                <th class="py-3 px-4">Status Kenaikan</th>
                                <th class="py-3 px-4">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (empty($riwayatKelas)): ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400">
                                        Belum ada riwayat kelas historis yang tercatat untuk siswa ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayatKelas as $rk): ?>
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-3 px-4 font-bold text-slate-900"><?= htmlspecialchars($rk['tahun_ajaran']) ?></td>
                                        <td class="py-3 px-4"><?= htmlspecialchars($rk['semester']) ?></td>
                                        <td class="py-3 px-4">
                                            <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">
                                                <?= htmlspecialchars($rk['nama_kelas']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-semibold">Tingkat <?= htmlspecialchars($rk['tingkat']) ?></td>
                                        <td class="py-3 px-4"><?= htmlspecialchars($rk['wali_kelas'] ?? '-') ?></td>
                                        <td class="py-3 px-4">
                                            <?php 
                                                $statusKenaikan = $rk['status_kenaikan'] ?? $rk['status_akhir'] ?? 'AKTIF';
                                                $catatanText = $rk['catatan'] ?? $rk['keterangan'] ?? '-';
                                            ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold <?= ($statusKenaikan === 'NAIK_KELAS' || $statusKenaikan === 'LULUS') ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' ?>">
                                                <?= htmlspecialchars(str_replace('_', ' ', (string)$statusKenaikan)) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500"><?= htmlspecialchars((string)$catatanText) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bagian F: Rekam Jejak Nilai Raport Tiap Semester (1 s/d 6) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h3 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-sky-600 text-white flex items-center justify-center text-xs font-black">F</span>
                            <span>Rekam Jejak Nilai Raport Siswa Semester 1 s/d 6 (Buku Induk)</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Riwayat nilai terpadu: kalkulasi harian guru semester berjalan & input nilai raport manual TU terintegrasi ke Transkrip Ijazah
                        </p>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="<?= App::baseUrl("admin/buku-induk/input-nilai-manual/{$siswa['id']}") ?>" 
                           class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-soft-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Input Nilai Raport</span>
                        </a>
                        <a href="<?= App::baseUrl("admin/buku-induk/cetak/{$siswa['id']}") ?>" target="_blank"
                           class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-soft-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>🖨️ Cetak Buku Induk Komplit</span>
                        </a>
                        <a href="<?= App::baseUrl("admin/nilai/transkrip-ijazah/{$siswa['id']}") ?>" target="_blank"
                           class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-soft-sm transition">
                            <span>🎓</span>
                            <span>Transkrip Ijazah</span>
                        </a>
                    </div>
                </div>

                <!-- Info Badges Origin -->
                <div class="px-5 py-3 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2 text-xs">
                    <div class="flex items-center gap-3 text-[11px]">
                        <span class="text-slate-500 font-medium">Keterangan Asal Data:</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-sky-50 text-sky-700 font-bold border border-sky-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span> KBM : Guru Semester Berjalan
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-bold border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> TU : Input Manual Raport Lalu
                        </span>
                    </div>
                    <div class="text-[11px] font-mono text-slate-600">
                        Total Nilai Terisi: <strong class="text-brand-600 font-bold"><?= $riwayatNilai['total_nilai_terisi'] ?? 0 ?></strong> • 
                        Rata-rata Kumulatif: <strong class="text-emerald-700 font-black text-xs"><?= ($riwayatNilai['rata_kumulatif'] ?? 0) > 0 ? number_format((float)$riwayatNilai['rata_kumulatif'], 2) : '-' ?></strong>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">No</th>
                                <th class="py-3 px-4 min-w-[200px]">Mata Pelajaran</th>
                                <th class="py-3 px-3 w-20 text-center">Kelompok</th>
                                <th class="py-3 px-3 w-20 text-center">Sem 1</th>
                                <th class="py-3 px-3 w-20 text-center">Sem 2</th>
                                <th class="py-3 px-3 w-20 text-center">Sem 3</th>
                                <th class="py-3 px-3 w-20 text-center">Sem 4</th>
                                <th class="py-3 px-3 w-20 text-center">Sem 5</th>
                                <th class="py-3 px-3 w-20 text-center">Sem 6</th>
                                <th class="py-3 px-3 w-24 text-center bg-slate-100/70 font-bold text-slate-900">Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (empty($riwayatNilai['mapel_matrix'])): ?>
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-slate-400">
                                        Belum ada mata pelajaran terdaftar dalam kurikulum.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($riwayatNilai['mapel_matrix'] as $mm): ?>
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="py-2.5 px-3 text-center text-slate-400 font-mono"><?= $no++ ?></td>
                                        <td class="py-2.5 px-4 font-bold text-slate-900">
                                            <?= htmlspecialchars($mm['nama_mapel']) ?>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                                <?= htmlspecialchars($mm['kelompok']) ?>
                                            </span>
                                        </td>
                                        <?php for ($s = 1; $s <= 6; $s++): 
                                            $semData = $mm['semesters'][$s] ?? null;
                                            $val = ($semData && isset($semData['nilai_akhir']) && $semData['nilai_akhir'] !== null) 
                                                ? (float)$semData['nilai_akhir'] 
                                                : (($semData && isset($semData['na']) && $semData['na'] !== null) ? (float)$semData['na'] : null);
                                            $isMan = !empty($semData['is_manual']);
                                        ?>
                                            <td class="py-2.5 px-3 text-center">
                                                <?php if ($val !== null): ?>
                                                    <div class="inline-flex flex-col items-center">
                                                        <span class="font-mono font-bold <?= ($val >= 75) ? 'text-emerald-700' : 'text-slate-800' ?>">
                                                            <?= number_format($val, 1) ?>
                                                        </span>
                                                        <span class="text-[9px] font-bold px-1.5 py-0.2 rounded mt-0.5 <?= $isMan ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' ?>">
                                                            <?= $isMan ? 'TU' : 'KBM' ?>
                                                        </span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-slate-300 font-mono">-</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                        <?php 
                                            $rataM = $mm['rata_mapel'] ?? $mm['rata_rata'] ?? null;
                                        ?>
                                        <td class="py-2.5 px-3 text-center bg-slate-50/70 font-mono font-black text-xs <?= ($rataM !== null && (float)$rataM >= 75) ? 'text-emerald-700' : 'text-slate-700' ?>">
                                            <?= ($rataM !== null && (float)$rataM > 0) ? number_format((float)$rataM, 2) : '-' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <!-- Footer Rata-rata Semester & Kumulatif -->
                        <tfoot class="bg-slate-50/90 border-t-2 border-slate-200 font-bold text-xs">
                            <tr>
                                <td colspan="3" class="py-3 px-4 text-slate-700 text-right uppercase tracking-wider text-[11px]">
                                    Rata-rata Tiap Semester :
                                </td>
                                <?php for ($s = 1; $s <= 6; $s++): 
                                    $rs = $riwayatNilai['rata_semester'][$s] ?? null;
                                ?>
                                    <td class="py-3 px-3 text-center font-mono font-bold <?= ($rs !== null && $rs >= 75) ? 'text-emerald-700' : 'text-slate-800' ?>">
                                        <?= ($rs !== null) ? number_format((float)$rs, 1) : '-' ?>
                                    </td>
                                <?php endfor; ?>
                                <td class="py-3 px-3 text-center font-mono font-black text-sm bg-indigo-50 text-indigo-900 border-l border-indigo-200">
                                    <?= ($riwayatNilai['rata_kumulatif'] ?? 0) > 0 ? number_format((float)$riwayatNilai['rata_kumulatif'], 2) : '-' ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
        </div>
                
        <!-- Bagian G: Catatan Khusus (PKL & Ekstrakurikuler) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft-sm overflow-hidden mt-6">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-teal-600 text-white flex items-center justify-center text-xs font-black">G</span>
                        Catatan Praktik Kerja Lapangan (PKL) & Ekstrakurikuler
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Catatan tambahan yang akan ditampilkan di lembar cetak Buku Induk.</p>
                </div>
                <button onclick="document.getElementById('modal-catatan').classList.remove('hidden')" class="inline-flex items-center gap-1.5 bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs px-3.5 py-2 rounded-xl transition shadow-soft-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Catatan
                </button>
            </div>
            <div class="p-5">
                <?php if (empty($catatanKhusus)): ?>
                    <p class="text-xs text-slate-500 text-center py-4">Belum ada catatan PKL atau Ekstrakurikuler.</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($catatanKhusus as $catatan): ?>
                            <div class="p-4 rounded-xl border border-slate-200 flex justify-between items-start bg-slate-50">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $catatan['jenis'] === 'PKL' ? 'bg-amber-100 text-amber-800' : 'bg-purple-100 text-purple-800' ?>"><?= $catatan['jenis'] ?></span>
                                        <span class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($catatan['nama_kegiatan']) ?></span>
                                        <?php if ($catatan['semester_ke']): ?>
                                            <span class="text-xs text-slate-500 font-medium"> (Semester <?= $catatan['semester_ke'] ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-slate-600 mb-1">
                                        <strong>Mitra/Instansi:</strong> <?= htmlspecialchars($catatan['mitra_instansi'] ?: '-') ?> &nbsp;|&nbsp; 
                                        <strong>Nilai/Predikat:</strong> <span class="font-bold text-emerald-700"><?= htmlspecialchars($catatan['nilai_predikat'] ?: '-') ?></span>
                                    </div>
                                    <div class="text-xs text-slate-500 italic">
                                        "<?= htmlspecialchars($catatan['keterangan'] ?: '-') ?>"
                                    </div>
                                </div>
                                <form action="<?= App::baseUrl('admin/buku-induk/catatan/delete/' . $catatan['id']) ?>" method="POST" onsubmit="return confirm('Hapus catatan ini?');">
                                    <input type="hidden" name="siswa_id" value="<?= $siswa['id'] ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>

    <!-- Modal Catatan -->
    <div id="modal-catatan" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-display font-bold text-slate-800 text-lg">Form Catatan Khusus</h3>
                <button onclick="document.getElementById('modal-catatan').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="<?= App::baseUrl('admin/buku-induk/catatan/save') ?>" method="POST" class="p-5 space-y-4">
                <input type="hidden" name="siswa_id" value="<?= $siswa['id'] ?>">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Catatan</label>
                        <select name="jenis" class="w-full text-sm border-slate-300 rounded-lg focus:ring-brand-500 focus:border-brand-500" required>
                            <option value="PKL">Praktik Kerja Lapangan (PKL)</option>
                            <option value="EKSTRAKURIKULER">Ekstrakurikuler</option>
                            <option value="INTEGRITAS">Integritas / Prestasi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Semester (Opsional)</label>
                        <input type="number" name="semester_ke" min="1" max="6" placeholder="Contoh: 4" class="w-full text-sm border-slate-300 rounded-lg focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kegiatan</label>
                    <input type="text" name="nama_kegiatan" placeholder="Contoh: Pramuka / PKL TKJ" class="w-full text-sm border-slate-300 rounded-lg focus:ring-brand-500 focus:border-brand-500" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mitra / Instansi (Opsional)</label>
                    <input type="text" name="mitra_instansi" placeholder="Contoh: PT Telkom / Kwarda Jabar" class="w-full text-sm border-slate-300 rounded-lg focus:ring-brand-500 focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai / Predikat</label>
                    <input type="text" name="nilai_predikat" placeholder="Contoh: 85 (A) / Amat Baik" class="w-full text-sm border-slate-300 rounded-lg focus:ring-brand-500 focus:border-brand-500" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Tambahan</label>
                    <textarea name="keterangan" rows="2" class="w-full text-sm border-slate-300 rounded-lg focus:ring-brand-500 focus:border-brand-500" placeholder="Keterangan singkat..."></textarea>
                </div>

                <div class="pt-4 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-catatan').classList.add('hidden')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-brand-600 hover:bg-brand-700 rounded-xl transition shadow-soft-sm">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
    </main>

</body>
</html>

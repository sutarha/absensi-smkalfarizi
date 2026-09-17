<?php
use App\Config\App;

$activeNav = 'surat';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Surat Dinas & SPPD Baru - <?= htmlspecialchars($config['nama_sekolah'] ?? 'SMK Al-Farizi') ?></title>
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
                    <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Formulir Penerbitan Surat Dinas Resmi</h1>
                    <p class="text-xs text-slate-500">Penomoran otomatis standar tata naskah dinas & integrasi lembar visum SPPD</p>
                </div>
            </div>
            <div>
                <button type="submit" form="form-surat" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-5 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Terbitkan & Cetak Surat
                </button>
            </div>
        </header>

        <!-- Body Form -->
        <div class="p-8 max-w-4xl w-full mx-auto space-y-6">

            <form id="form-surat" method="POST" action="<?= App::baseUrl('admin/surat/store') ?>
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>" class="space-y-6">

                <!-- Klasifikasi & Penerima -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                    <h2 class="font-display font-bold text-slate-900 text-sm mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-brand-600 text-white flex items-center justify-center text-xs font-black">1</span>
                        Klasifikasi Surat & Penerima
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jenis Surat Dinas</label>
                            <select name="jenis_surat" id="jenis_surat" onchange="toggleSppdSection(this.value)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-brand-700 focus:ring-2 focus:ring-brand-500">
                                <option value="SISWA_AKTIF" <?= ($selectedJenis === 'SISWA_AKTIF') ? 'selected' : '' ?>>Surat Keterangan Siswa Aktif</option>
                                <option value="SPPD" <?= ($selectedJenis === 'SPPD') ? 'selected' : '' ?>>Surat Perintah Perjalanan Dinas (SPPD)</option>
                                <option value="SURAT_TUGAS" <?= ($selectedJenis === 'SURAT_TUGAS') ? 'selected' : '' ?>>Surat Perintah Tugas (SPT)</option>
                                <option value="REKOMENDASI" <?= ($selectedJenis === 'REKOMENDASI') ? 'selected' : '' ?>>Surat Rekomendasi</option>
                                <option value="PINDAH_SEKOLAH" <?= ($selectedJenis === 'PINDAH_SEKOLAH') ? 'selected' : '' ?>>Surat Keterangan Pindah Sekolah</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tipe Penerima</label>
                            <select name="penerima_tipe" id="penerima_tipe" onchange="togglePenerimaDropdown(this.value)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                                <option value="SISWA">Siswa (Peserta Didik)</option>
                                <option value="GURU" <?= ($selectedJenis === 'SPPD' || $selectedJenis === 'SURAT_TUGAS') ? 'selected' : '' ?>>Guru / Tenaga Pendidik / Pegawai TU</option>
                            </select>
                        </div>

                        <!-- Dropdown Siswa -->
                        <div id="wrapper-siswa">
                            <label class="block font-semibold text-slate-700 mb-1">Pilih Siswa</label>
                            <select name="siswa_id" id="siswa_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                                <option value="">-- Pilih Siswa Terdaftar --</option>
                                <?php foreach ($allSiswa as $sw): ?>
                                    <option value="<?= $sw['id'] ?>" <?= ($prefillSiswaId === (int)$sw['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sw['nama_siswa']) ?> (NISN: <?= htmlspecialchars($sw['nisn']) ?> - <?= htmlspecialchars($sw['nama_kelas'] ?? '') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Dropdown Guru -->
                        <div id="wrapper-guru" class="hidden">
                            <label class="block font-semibold text-slate-700 mb-1">Pilih Guru / Pegawai</label>
                            <select name="guru_id" id="guru_id" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                                <option value="">-- Pilih Guru / Pegawai --</option>
                                <?php foreach ($allGuru as $gr): ?>
                                    <option value="<?= $gr['id'] ?>">
                                        <?= htmlspecialchars($gr['nama_guru']) ?> (NIP: <?= htmlspecialchars($gr['nip'] ?? '-') ?> - <?= htmlspecialchars($gr['jabatan'] ?? 'Guru') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal Surat Diterbitkan</label>
                            <input type="date" name="tanggal_surat" value="<?= date('Y-m-d') ?>" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Perihal</label>
                            <input type="text" name="perihal" id="input_perihal" value="Surat Keterangan Aktif Sekolah" required class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Keperluan / Keterangan Tambahan</label>
                            <textarea name="keperluan" rows="2" placeholder="Contoh: Mengikuti Lomba Kompetensi Siswa (LKS) Tingkat Provinsi Jawa Barat..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-brand-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- SECTION KHUSUS SPPD & SURAT TUGAS -->
                <div id="section-sppd" class="bg-white rounded-2xl border border-indigo-200/80 p-6 shadow-soft-sm <?= ($selectedJenis === 'SPPD' || $selectedJenis === 'SURAT_TUGAS') ? '' : 'hidden' ?>">
                    <div class="border-b border-indigo-100 pb-3 mb-5 flex items-center justify-between">
                        <h2 class="font-display font-bold text-indigo-950 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-black">2</span>
                            Rincian Perjalanan Dinas (SPPD & Lembar Visum)
                        </h2>
                        <span class="text-[11px] bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-full font-bold border border-indigo-200">Standar Permendiknas / Tata Naskah Dinas</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                        <div class="md:col-span-2">
                            <label class="block font-semibold text-slate-700 mb-1">Dasar Penugasan</label>
                            <input type="text" name="dasar_penugasan" placeholder="Contoh: Surat Undangan Kepala Dinas Pendidikan Provinsi Jawa Barat No. 421/123/Disdik" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tempat Berangkat</label>
                            <input type="text" name="tempat_berangkat" value="SMK AL-FARIZI (Pagelaran - Cianjur)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tempat / Kota Tujuan</label>
                            <input type="text" name="tempat_tujuan" placeholder="Contoh: Kota Bandung / Jakarta" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Instansi yang Dituju</label>
                            <input type="text" name="instansi_tujuan" placeholder="Contoh: Kantor Balai Besar Guru Penggerak (BBGP) Jabar" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 font-semibold">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Pejabat / Pihak yang Dituju (Untuk Tanda Tangan Visum)</label>
                            <input type="text" name="pejabat_tujuan" placeholder="Contoh: Kepala BBGP / Panitia Workshop" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 font-semibold text-indigo-900">
                            <span class="text-[10px] text-slate-400">Nama pejabat ini akan tercantum di Lembar Visum TTD yang Dituju</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal Berangkat</label>
                            <input type="date" name="tanggal_berangkat" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tanggal Kembali</label>
                            <input type="date" name="tanggal_kembali" value="<?= date('Y-m-d') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Lama Perjalanan (Hari)</label>
                            <input type="number" name="lama_hari" value="1" min="1" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Moda Alat Angkutan</label>
                            <input type="text" name="alat_angkut" value="Kendaraan Dinas / Umum" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Pembebanan Anggaran</label>
                            <input type="text" name="beban_anggaran" value="Dana BOS SMK Al-Farizi Tahun Anggaran <?= date('Y') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Pengikut (Jika Ada)</label>
                            <input type="text" name="pengikut" placeholder="Nama pengikut (pisahkan dengan koma)" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Pejabat Penandatangan -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                    <h2 class="font-display font-bold text-slate-900 text-sm mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black">3</span>
                        Pejabat Penandatangan Surat
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Nama Kepala Sekolah / Penandatangan</label>
                            <input type="text" name="pejabat_penandatangan" value="<?= htmlspecialchars($config['kepala_sekolah'] ?? 'Kepala Sekolah') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-brand-500">
                        </div>
                        <div class="flex items-center text-slate-500">
                            NIP: <strong class="ml-1 text-slate-800"><?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <a href="<?= App::baseUrl('admin/surat') ?>" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Simpan & Cetak Surat
                    </button>
                </div>

            </form>

        </div>
    </main>

    <script>
    function toggleSppdSection(jenis) {
        const section = document.getElementById('section-sppd');
        const perihalInput = document.getElementById('input_perihal');
        const penerimaSelect = document.getElementById('penerima_tipe');

        if (jenis === 'SPPD' || jenis === 'SURAT_TUGAS') {
            section.classList.remove('hidden');
            penerimaSelect.value = 'GURU';
            togglePenerimaDropdown('GURU');
            if (jenis === 'SPPD') {
                perihalInput.value = 'Surat Perintah Perjalanan Dinas (SPPD)';
            } else {
                perihalInput.value = 'Surat Perintah Tugas (SPT)';
            }
        } else {
            section.classList.add('hidden');
            if (jenis === 'SISWA_AKTIF') {
                perihalInput.value = 'Surat Keterangan Aktif Siswa';
                penerimaSelect.value = 'SISWA';
                togglePenerimaDropdown('SISWA');
            } else if (jenis === 'REKOMENDASI') {
                perihalInput.value = 'Surat Rekomendasi';
            } else if (jenis === 'PINDAH_SEKOLAH') {
                perihalInput.value = 'Surat Keterangan Pindah Sekolah';
                penerimaSelect.value = 'SISWA';
                togglePenerimaDropdown('SISWA');
            }
        }
    }

    function togglePenerimaDropdown(tipe) {
        const sw = document.getElementById('wrapper-siswa');
        const gr = document.getElementById('wrapper-guru');
        if (tipe === 'SISWA') {
            sw.classList.remove('hidden');
            gr.classList.add('hidden');
        } else {
            sw.classList.add('hidden');
            gr.classList.remove('hidden');
        }
    }

    // Run once on load
    document.addEventListener('DOMContentLoaded', () => {
        togglePenerimaDropdown(document.getElementById('penerima_tipe').value);
    });
    </script>

</body>
</html>

<?php
use App\Config\App;

$activeNav = 'buku_induk';
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku Induk - <?= htmlspecialchars($siswa['nama_lengkap']) ?></title>
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
                <a href="<?= App::baseUrl("admin/buku-induk/detail/{$siswa['id']}") ?>" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-display font-black text-slate-900 tracking-tight">Edit Data Buku Induk Siswa</h1>
                    <p class="text-xs text-slate-500">Perbarui rincian identitas siswa, domisili, orang tua, dan asal sekolah</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= App::baseUrl("admin/buku-induk/detail/{$siswa['id']}") ?>" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" form="form-edit-buku-induk" class="inline-flex items-center gap-1.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-5 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Perubahan Data
                </button>
            </div>
        </header>

        <!-- Body Form -->
        <div class="p-8 max-w-5xl w-full mx-auto space-y-6">

            <form id="form-edit-buku-induk" method="POST" action="<?= App::baseUrl("admin/buku-induk/update/{$siswa['id']}") ?>" class="space-y-6">
    <?= \App\Helpers\CsrfHelper::getTokenInput() ?>

                <!-- SECTION 1: Identitas Pribadi -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                    <div class="border-b border-slate-100 pb-3 mb-5 flex items-center justify-between">
                        <h2 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-brand-600 text-white flex items-center justify-center text-xs font-black">1</span>
                            Keterangan Pribadi Siswa
                        </h2>
                        <span class="text-[11px] text-slate-400">Nama & NISN dikunci dari Data Pokok Siswa</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs">
                        <div class="md:col-span-2">
                            <label class="block font-semibold text-slate-600 mb-1">Nama Siswa (Readonly)</label>
                            <input type="text" value="<?= htmlspecialchars($siswa['nama_lengkap']) ?>" readonly class="w-full bg-slate-100 border border-slate-300 rounded-xl px-3 py-2 text-slate-600 font-semibold cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">NISN (Readonly)</label>
                            <input type="text" value="<?= htmlspecialchars($siswa['nisn']) ?>" readonly class="w-full bg-slate-100 border border-slate-300 rounded-xl px-3 py-2 font-mono text-slate-600 font-bold cursor-not-allowed">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">NIS Sekolah</label>
                            <input type="text" name="nis" value="<?= htmlspecialchars($siswa['nis'] ?? '') ?>" placeholder="Misal: 24251001" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">NIK Siswa (KTP/KIA)</label>
                            <input type="text" name="nik" value="<?= htmlspecialchars($siswa['nik'] ?? '') ?>" placeholder="16 digit NIK" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nomor Kartu Keluarga (KK)</label>
                            <input type="text" name="no_kk" value="<?= htmlspecialchars($siswa['no_kk'] ?? '') ?>" placeholder="16 digit No KK" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">No. Registrasi Akta Lahir</label>
                            <input type="text" name="no_akta_lahir" value="<?= htmlspecialchars($siswa['no_akta_lahir'] ?? '') ?>" placeholder="No Akta Lahir" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" value="<?= htmlspecialchars($siswa['tempat_lahir'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($siswa['tanggal_lahir'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Agama</label>
                            <select name="agama" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                <?php foreach (['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $ag): ?>
                                    <option value="<?= $ag ?>" <?= (($siswa['agama'] ?? 'Islam') === $ag) ? 'selected' : '' ?>><?= $ag ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Kewarganegaraan</label>
                            <input type="text" name="kewarganegaraan" value="<?= htmlspecialchars($siswa['kewarganegaraan'] ?? 'WNI') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Status Siswa</label>
                            <select name="status_siswa" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-bold">
                                <?php foreach (['AKTIF', 'LULUS', 'MUTASI_KELUAR', 'KELUAR_DO'] as $st): ?>
                                    <option value="<?= $st ?>" <?= (($siswa['status_siswa'] ?? 'AKTIF') === $st) ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Anak Ke-</label>
                            <input type="number" name="anak_ke" value="<?= htmlspecialchars((string)($siswa['anak_ke'] ?? 1)) ?>" min="1" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Jumlah Saudara Kandung</label>
                            <input type="number" name="jumlah_saudara" value="<?= htmlspecialchars((string)($siswa['jumlah_saudara'] ?? 0)) ?>" min="0" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Tempat Tinggal & Transportasi -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                    <div class="border-b border-slate-100 pb-3 mb-5">
                        <h2 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-black">2</span>
                            Keterangan Tempat Tinggal & Transportasi
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 text-xs">
                        <div class="md:col-span-2">
                            <label class="block font-semibold text-slate-600 mb-1">Alamat Jalan</label>
                            <input type="text" name="alamat_jalan" value="<?= htmlspecialchars($siswa['alamat_jalan'] ?? '') ?>" placeholder="Jl. Raya / Dusun / Gang" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">RT</label>
                                <input type="text" name="rt" value="<?= htmlspecialchars($siswa['rt'] ?? '') ?>" placeholder="01" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">RW</label>
                                <input type="text" name="rw" value="<?= htmlspecialchars($siswa['rw'] ?? '') ?>" placeholder="02" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Dusun / Kelurahan / Desa</label>
                            <input type="text" name="dusun_kelurahan" value="<?= htmlspecialchars($siswa['dusun_kelurahan'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Kecamatan</label>
                            <input type="text" name="kecamatan" value="<?= htmlspecialchars($siswa['kecamatan'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Kabupaten / Kota</label>
                            <input type="text" name="kabupaten_kota" value="<?= htmlspecialchars($siswa['kabupaten_kota'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Provinsi</label>
                            <input type="text" name="provinsi" value="<?= htmlspecialchars($siswa['provinsi'] ?? 'Jawa Barat') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Kode Pos</label>
                            <input type="text" name="kode_pos" value="<?= htmlspecialchars($siswa['kode_pos'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tinggal Bersama</label>
                            <input type="text" name="tinggal_bersama" value="<?= htmlspecialchars($siswa['tinggal_bersama'] ?? 'Orang Tua') ?>" placeholder="Orang Tua / Wali / Kos" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Moda Transportasi</label>
                            <input type="text" name="transportasi" value="<?= htmlspecialchars($siswa['transportasi'] ?? 'Sepeda Motor') ?>" placeholder="Sepeda Motor / Jalan Kaki / Angkutan Umum" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: Orang Tua & Wali -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                    <div class="border-b border-slate-100 pb-3 mb-5">
                        <h2 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black">3</span>
                            Data Orang Tua Kandung & Wali
                        </h2>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div class="font-bold text-slate-800 bg-slate-50 p-2 rounded-lg border border-slate-200">A. Identitas Ayah Kandung</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Ayah</label>
                                <input type="text" name="nama_ayah" value="<?= htmlspecialchars($siswa['nama_ayah'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">NIK Ayah</label>
                                <input type="text" name="nik_ayah" value="<?= htmlspecialchars($siswa['nik_ayah'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Tahun Lahir Ayah</label>
                                <input type="text" name="tahun_lahir_ayah" value="<?= htmlspecialchars($siswa['tahun_lahir_ayah'] ?? '') ?>" placeholder="Misal: 1978" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Pendidikan Ayah</label>
                                <input type="text" name="pendidikan_ayah" value="<?= htmlspecialchars($siswa['pendidikan_ayah'] ?? '') ?>" placeholder="SMA/SMK Sederajat" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Pekerjaan Ayah</label>
                                <input type="text" name="pekerjaan_ayah" value="<?= htmlspecialchars($siswa['pekerjaan_ayah'] ?? '') ?>" placeholder="Wiraswasta / Karyawan" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Penghasilan Ayah</label>
                                <input type="text" name="penghasilan_ayah" value="<?= htmlspecialchars($siswa['penghasilan_ayah'] ?? '') ?>" placeholder="Rp 1.000.000 - Rp 2.000.000" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                        </div>

                        <div class="font-bold text-slate-800 bg-slate-50 p-2 rounded-lg border border-slate-200 mt-4">B. Identitas Ibu Kandung</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Ibu</label>
                                <input type="text" name="nama_ibu" value="<?= htmlspecialchars($siswa['nama_ibu'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">NIK Ibu</label>
                                <input type="text" name="nik_ibu" value="<?= htmlspecialchars($siswa['nik_ibu'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Tahun Lahir Ibu</label>
                                <input type="text" name="tahun_lahir_ibu" value="<?= htmlspecialchars($siswa['tahun_lahir_ibu'] ?? '') ?>" placeholder="Misal: 1982" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Pendidikan Ibu</label>
                                <input type="text" name="pendidikan_ibu" value="<?= htmlspecialchars($siswa['pendidikan_ibu'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Pekerjaan Ibu</label>
                                <input type="text" name="pekerjaan_ibu" value="<?= htmlspecialchars($siswa['pekerjaan_ibu'] ?? '') ?>" placeholder="Ibu Rumah Tangga" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Penghasilan Ibu</label>
                                <input type="text" name="penghasilan_ibu" value="<?= htmlspecialchars($siswa['penghasilan_ibu'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                        </div>

                        <div class="font-bold text-slate-800 bg-slate-50 p-2 rounded-lg border border-slate-200 mt-4">C. Kontak & Wali</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nama Wali (Jika Ada)</label>
                                <input type="text" name="nama_wali" value="<?= htmlspecialchars($siswa['nama_wali'] ?? '') ?>" placeholder="Kosongkan jika tidak ada" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Nomor HP / WhatsApp Orang Tua</label>
                                <input type="text" name="no_hp_ortu" value="<?= htmlspecialchars($siswa['no_hp_ortu'] ?? $siswa['no_telepon_orangtua'] ?? '') ?>" placeholder="08xxxxxxxxxx" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: Pendidikan Sebelumnya & Masuk -->
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-soft-sm">
                    <div class="border-b border-slate-100 pb-3 mb-5">
                        <h2 class="font-display font-bold text-slate-900 text-sm flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs font-black">4</span>
                            Pendidikan Sebelumnya & Tanggal Masuk
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Sekolah Asal (SMP / MTs)</label>
                            <input type="text" name="sekolah_asal" value="<?= htmlspecialchars($siswa['sekolah_asal'] ?? '') ?>" placeholder="Misal: SMP Negeri 1 Pagelaran" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Tanggal Diterima Masuk di SMK</label>
                            <input type="date" name="tanggal_masuk" value="<?= htmlspecialchars($siswa['tanggal_masuk'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nomor Ijazah SMP / MTs</label>
                            <input type="text" name="no_ijazah_smp" value="<?= htmlspecialchars($siswa['no_ijazah_smp'] ?? '') ?>" placeholder="DN-xx/xxxxxxx" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nomor SKHUN SMP / MTs</label>
                            <input type="text" name="no_skhun_smp" value="<?= htmlspecialchars($siswa['no_skhun_smp'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 font-mono">
                        </div>
                    </div>
                </div>

                <!-- Action Button Bottom -->
                <div class="flex items-center justify-end gap-3 pt-4">
                    <a href="<?= App::baseUrl("admin/buku-induk/detail/{$siswa['id']}") ?>" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs px-6 py-2.5 rounded-xl shadow-soft-sm hover:shadow-glow-brand transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan Buku Induk
                    </button>
                </div>

            </form>

        </div>
    </main>

</body>
</html>

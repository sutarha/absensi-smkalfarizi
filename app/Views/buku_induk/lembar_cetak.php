<?php
use App\Helpers\SuratHelper;
use App\Models\NilaiSiswa;
use App\Config\App;

$riwayatNilai = $riwayatNilai ?? NilaiSiswa::getRiwayatNilaiSiswaSemuaSemester($siswa['id']);
$daftarMapel = $riwayatNilai['daftar_mapel'] ?? [];
$semesterAverages = $riwayatNilai['semester_averages'] ?? [];
$rataRataKumulatif = (float)($riwayatNilai['rata_rata_kumulatif'] ?? 0);
$totalNilaiTerisi = (int)($riwayatNilai['total_nilai_terisi'] ?? 0);

$infoKumulatif = NilaiSiswa::getPredikatDanDeskripsi($rataRataKumulatif);

// Kelompokkan mapel berdasarkan kurikulum SMK
$mapelKelompokA = []; // Umum / Muatan Nasional
$mapelKelompokB = []; // Kejuruan / Konsentrasi Keahlian & Kewilayahan
$mapelKelompokC = []; // Muatan Lokal / Pilihan

foreach ($daftarMapel as $m) {
    $kel = strtolower($m['kelompok'] ?? 'umum');
    if ($kel === 'umum') {
        $mapelKelompokA[] = $m;
    } elseif ($kel === 'kejuruan') {
        $mapelKelompokB[] = $m;
    } else {
        $mapelKelompokC[] = $m;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Induk Lengkap - <?= htmlspecialchars($siswa['nama_lengkap']) ?> (<?= htmlspecialchars($siswa['nisn']) ?>)</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
            color: #000;
            background: #e2e8f0;
            margin: 0;
            padding: 0;
        }
        .page-container {
            width: 100%;
            max-width: 210mm;
            margin: 20px auto;
            background: #fff;
            padding: 15mm 18mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        .sheet-page {
            width: 100%;
            background: #fff;
        }
        .print-btn-bar {
            background: #0f172a;
            padding: 12px 24px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .print-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.15s ease;
        }
        .print-btn:hover {
            background: #1d4ed8;
        }
        .print-btn.btn-emerald {
            background: #059669;
        }
        .print-btn.btn-emerald:hover {
            background: #047857;
        }
        .print-btn.btn-slate {
            background: #334155;
            color: #cbd5e1;
        }
        .print-btn.btn-slate:hover {
            background: #475569;
            color: #fff;
        }
        .print-btn.btn-amber {
            background: #d97706;
        }
        .print-btn.btn-amber:hover {
            background: #b45309;
        }
        .title-doc {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            text-decoration: underline;
            margin: 10px 0 2px 0;
            letter-spacing: 0.5px;
        }
        .sub-title {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 12px;
        }
        .section-header {
            font-weight: bold;
            font-size: 10.5pt;
            background: #f1f5f9;
            padding: 3px 6px;
            border: 1px solid #cbd5e1;
            margin-top: 9px;
            margin-bottom: 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        table.data-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .w-no { width: 24px; text-align: center; }
        .w-label { width: 210px; }
        .w-sep { width: 14px; text-align: center; }
        
        table.table-border {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 4px;
        }
        table.table-border th, table.table-border td {
            border: 1px solid #333;
            padding: 3.5px 5px;
        }
        table.table-border th {
            background: #f8fafc;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px solid #333;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 8pt;
            color: #666;
            margin-right: 20px;
        }
        .ttd-box {
            margin-top: 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        /* Identity Box Lembar 2 */
        .info-siswa-card {
            width: 100%;
            border: 1px solid #333;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 9.5pt;
            background: #fcfcfc;
        }
        .info-siswa-card table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-siswa-card td {
            padding: 1.5px 3px;
            vertical-align: top;
        }

        /* Page Break untuk Cetak Multi Halaman Bersih */
        .page-break {
            page-break-before: always;
            break-before: page;
            clear: both;
            height: 0;
            margin: 0;
            border: none;
        }

        @media screen {
            .page-break {
                height: 30px;
                background: #cbd5e1;
                margin: 30px -18mm;
                border-top: 2px dashed #94a3b8;
                border-bottom: 2px dashed #94a3b8;
                position: relative;
            }
            .page-break::after {
                content: "✂️ PEMISAH HALAMAN CETAK A4 (Halaman 1: Biodata Siswa | Halaman 2: Rekap Nilai Raport Semester 1-6)";
                position: absolute;
                top: 5px;
                left: 50%;
                transform: translateX(-50%);
                font-family: sans-serif;
                font-size: 11px;
                font-weight: bold;
                color: #475569;
                background: #fff;
                padding: 2px 10px;
                border-radius: 4px;
            }
        }

        @media print {
            .print-btn-bar { display: none !important; }
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .page-container {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            .section-header {
                background: #eee !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            table.table-border th {
                background: #f0f0f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
                display: block !important;
                height: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Top Bar (Controls) -->
    <div class="print-btn-bar">
        <div>
            <div style="font-weight: bold; font-size: 14px;">
                📖 Buku Induk Peserta Didik Komplit - SMK AL-FARIZI
            </div>
            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                Siswa: <strong><?= htmlspecialchars($siswa['nama_lengkap']) ?></strong> (NISN: <?= htmlspecialchars($siswa['nisn']) ?>) • 
                Status: <span style="color: #34d399; font-weight: bold;"><?= htmlspecialchars($siswa['status_siswa'] ?? 'AKTIF') ?></span> • 
                Nilai Raport: <strong><?= $totalNilaiTerisi ?> terisi</strong> (Kumulatif: <strong><?= ($rataRataKumulatif > 0) ? number_format($rataRataKumulatif, 2) : '-' ?></strong>)
            </div>
        </div>

        <div class="btn-group">
            <a href="<?= App::baseUrl("admin/buku-induk/detail/{$siswa['id']}") ?>" class="print-btn btn-slate" title="Kembali ke Detail">
                ⬅️ Kembali
            </a>
            <a href="<?= App::baseUrl("admin/buku-induk/input-nilai-manual/{$siswa['id']}") ?>" class="print-btn btn-amber" title="Input / Perbaiki Nilai Semester 1-6">
                ✏️ Input/Edit Nilai Raport
            </a>
            <button class="print-btn btn-slate" onclick="printPage1()" title="Cetak Lembar Biodata Saja">
                📄 Cetak Lembar 1 (Biodata)
            </button>
            <button class="print-btn btn-slate" onclick="printPage2()" title="Cetak Lembar Nilai Saja">
                📊 Cetak Lembar 2 (Nilai 1-6)
            </button>
            <button class="print-btn btn-emerald" onclick="printAll()" title="Cetak Seluruh Lembar Buku Induk Komplit">
                🖨️ Cetak Komplit (Semua Lembar)
            </button>
        </div>
    </div>

    <div class="page-container">

        <!-- =================================================================== -->
        <!-- LEMBAR 1: KETERANGAN PRIBADI & MUTASI PESERTA DIDIK (BIODATA SISWA) -->
        <!-- =================================================================== -->
        <div class="sheet-page" id="sheet-page-1">

            <!-- KOP SEKOLAH RESMI -->
            <?= SuratHelper::renderKopDinas($config) ?>

            <div class="title-doc">LEMBAR BUKU INDUK PESERTA DIDIK</div>
            <div class="sub-title">
                Nomor Induk Siswa Nasional (NISN): <strong><?= htmlspecialchars($siswa['nisn']) ?></strong> / 
                NIS: <strong><?= htmlspecialchars($siswa['nis'] ?? '-') ?></strong>
            </div>

            <!-- Bagian A: Data Pribadi -->
            <div class="section-header">A. KETERANGAN PRIBADI PESERTA DIDIK</div>
            <table class="data-table">
                <tr>
                    <td class="w-no">1.</td>
                    <td class="w-label">Nama Lengkap Siswa</td>
                    <td class="w-sep">:</td>
                    <td><strong><?= htmlspecialchars($siswa['nama_lengkap']) ?></strong></td>
                </tr>
                <tr>
                    <td class="w-no">2.</td>
                    <td class="w-label">Jenis Kelamin</td>
                    <td class="w-sep">:</td>
                    <td><?= ($siswa['jenis_kelamin'] === 'L') ? 'Laki-laki (L)' : 'Perempuan (P)' ?></td>
                </tr>
                <tr>
                    <td class="w-no">3.</td>
                    <td class="w-label">Nomor Induk Kependudukan (NIK)</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['nik'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="w-no">4.</td>
                    <td class="w-label">Nomor Kartu Keluarga (KK)</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['no_kk'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="w-no">5.</td>
                    <td class="w-label">Nomor Registrasi Akta Kelahiran</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['no_akta_lahir'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="w-no">6.</td>
                    <td class="w-label">Tempat, Tanggal Lahir</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, <?= !empty($siswa['tanggal_lahir']) ? date('d F Y', strtotime($siswa['tanggal_lahir'])) : '-' ?></td>
                </tr>
                <tr>
                    <td class="w-no">7.</td>
                    <td class="w-label">Agama & Kepercayaan</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['agama'] ?? 'Islam') ?></td>
                </tr>
                <tr>
                    <td class="w-no">8.</td>
                    <td class="w-label">Kewarganegaraan</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['kewarganegaraan'] ?? 'WNI') ?></td>
                </tr>
                <tr>
                    <td class="w-no">9.</td>
                    <td class="w-label">Anak Ke / Jumlah Saudara</td>
                    <td class="w-sep">:</td>
                    <td>Anak ke-<?= htmlspecialchars((string)($siswa['anak_ke'] ?? 1)) ?> dari <?= htmlspecialchars((string)($siswa['jumlah_saudara'] ?? 0)) ?> bersaudara</td>
                </tr>
            </table>

            <!-- Bagian B: Alamat & Tempat Tinggal -->
            <div class="section-header">B. KETERANGAN TEMPAT TINGGAL</div>
            <table class="data-table">
                <tr>
                    <td class="w-no">10.</td>
                    <td class="w-label">Alamat Lengkap / Jalan</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['alamat_jalan'] ?? '-') ?> RT <?= htmlspecialchars($siswa['rt'] ?? '-') ?> / RW <?= htmlspecialchars($siswa['rw'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="w-no">11.</td>
                    <td class="w-label">Desa / Kelurahan</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['dusun_kelurahan'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="w-no">12.</td>
                    <td class="w-label">Kecamatan</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['kecamatan'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="w-no">13.</td>
                    <td class="w-label">Kabupaten / Kota & Provinsi</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['kabupaten_kota'] ?? '-') ?>, <?= htmlspecialchars($siswa['provinsi'] ?? '-') ?> (Kode Pos: <?= htmlspecialchars($siswa['kode_pos'] ?? '-') ?>)</td>
                </tr>
                <tr>
                    <td class="w-no">14.</td>
                    <td class="w-label">Tinggal Bersama / Transportasi</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['tinggal_bersama'] ?? 'Orang Tua') ?> / <?= htmlspecialchars($siswa['transportasi'] ?? 'Sepeda Motor') ?></td>
                </tr>
            </table>

            <!-- Bagian C: Data Orang Tua -->
            <div class="section-header">C. KETERANGAN ORANG TUA KANDUNG & WALI</div>
            <table class="data-table">
                <tr>
                    <td class="w-no">15.</td>
                    <td class="w-label">Nama Ayah Kandung</td>
                    <td class="w-sep">:</td>
                    <td><strong><?= htmlspecialchars($siswa['nama_ayah'] ?? '-') ?></strong> (NIK: <?= htmlspecialchars($siswa['nik_ayah'] ?? '-') ?>)</td>
                </tr>
                <tr>
                    <td class="w-no">16.</td>
                    <td class="w-label">Pendidikan & Pekerjaan Ayah</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['pendidikan_ayah'] ?? '-') ?> / <?= htmlspecialchars($siswa['pekerjaan_ayah'] ?? '-') ?> (Penghasilan: <?= htmlspecialchars($siswa['penghasilan_ayah'] ?? '-') ?>)</td>
                </tr>
                <tr>
                    <td class="w-no">17.</td>
                    <td class="w-label">Nama Ibu Kandung</td>
                    <td class="w-sep">:</td>
                    <td><strong><?= htmlspecialchars($siswa['nama_ibu'] ?? '-') ?></strong> (NIK: <?= htmlspecialchars($siswa['nik_ibu'] ?? '-') ?>)</td>
                </tr>
                <tr>
                    <td class="w-no">18.</td>
                    <td class="w-label">Pendidikan & Pekerjaan Ibu</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['pendidikan_ibu'] ?? '-') ?> / <?= htmlspecialchars($siswa['pekerjaan_ibu'] ?? '-') ?> (Penghasilan: <?= htmlspecialchars($siswa['penghasilan_ibu'] ?? '-') ?>)</td>
                </tr>
                <tr>
                    <td class="w-no">19.</td>
                    <td class="w-label">Nama Wali / No. Kontak HP</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['nama_wali'] ?? '-') ?> / Telp: <?= htmlspecialchars($siswa['no_hp_ortu'] ?? $siswa['no_telepon_orangtua'] ?? '-') ?></td>
                </tr>
            </table>

            <!-- Bagian D: Asal Sekolah & Penerimaan -->
            <div class="section-header">D. ASAL SEKOLAH & PENERIMAAN DI SMK</div>
            <table class="data-table">
                <tr>
                    <td class="w-no">20.</td>
                    <td class="w-label">Sekolah Asal (SMP/MTs)</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['sekolah_asal'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="w-no">21.</td>
                    <td class="w-label">Nomor Ijazah & SKHUN SMP</td>
                    <td class="w-sep">:</td>
                    <td><?= htmlspecialchars($siswa['no_ijazah_smp'] ?? '-') ?> / <?= htmlspecialchars($siswa['no_skhun_smp'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="w-no">22.</td>
                    <td class="w-label">Diterima di Kelas / Tanggal</td>
                    <td class="w-sep">:</td>
                    <td>Kelas <?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?> (<?= htmlspecialchars($siswa['jurusan'] ?? '-') ?>) / Tgl: <?= !empty($siswa['tanggal_masuk']) ? date('d F Y', strtotime($siswa['tanggal_masuk'])) : '-' ?></td>
                </tr>
            </table>

            <!-- Bagian E: Riwayat Rombel & Kenaikan Kelas -->
            <div class="section-header">E. RIWAYAT KELAS & PERKEMBANGAN AKADEMIK</div>
            <table class="table-border">
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th>Tahun Pelajaran</th>
                        <th>Semester</th>
                        <th>Kelas / Rombel</th>
                        <th>Tingkat</th>
                        <th>Status Akhir</th>
                        <th>Wali Kelas / Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riwayatKelas)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #666; font-style: italic;">Belum ada catatan riwayat kenaikan kelas.</td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($riwayatKelas as $rk): 
                            $sk = $rk['status_kenaikan'] ?? $rk['status_akhir'] ?? 'AKTIF';
                            $wk = !empty($rk['wali_kelas']) ? $rk['wali_kelas'] : ($rk['catatan'] ?? $rk['keterangan'] ?? '-');
                        ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td class="text-center"><?= htmlspecialchars($rk['tahun_ajaran']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($rk['semester']) ?></td>
                                <td class="text-center font-bold"><?= htmlspecialchars($rk['nama_kelas']) ?></td>
                                <td class="text-center">Tingkat <?= htmlspecialchars($rk['tingkat']) ?></td>
                                <td class="text-center font-bold"><?= htmlspecialchars(str_replace('_', ' ', (string)$sk)) ?></td>
                                <td><?= htmlspecialchars((string)$wk) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Tanda Tangan & Foto Lembar 1 -->
            <div class="ttd-box">
                <div style="display: flex; align-items: flex-end;">
                    <div class="photo-box">
                        Pas Foto<br>3 x 4 cm
                    </div>
                    <div style="font-size: 8.5pt; color: #555; line-height: 1.3;">
                        <em>Dicetak dari Sistem Presensi & Buku Induk Cerdas<br>SMK AL-FARIZI pada <?= date('d/m/Y H:i') ?> WIB</em>
                    </div>
                </div>
                <div style="text-align: center; width: 230px;">
                    <div>Pagelaran, <?= date('d F Y') ?></div>
                    <div style="margin-top: 4px;">Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>,</div>
                    <div style="height: 55px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($config['kepala_sekolah'] ?? 'Kepala Sekolah') ?></div>
                    <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
                </div>
            </div>

        </div> <!-- End Sheet Page 1 -->

        <!-- =================================================================== -->
        <!-- PEMISAH HALAMAN A4 (PAGE BREAK UNTUK CETAK DUA HALAMAN RAPI)       -->
        <!-- =================================================================== -->
        <div class="page-break"></div>

        <!-- =================================================================== -->
        <!-- LEMBAR 2: LEMBAR PENILAIAN HASIL BELAJAR (NILAI RAPOR SEMESTER 1-6) -->
        <!-- =================================================================== -->
        <div class="sheet-page" id="sheet-page-2">

            <!-- KOP SEKOLAH RESMI LEMBAR NILAI -->
            <?= SuratHelper::renderKopDinas($config) ?>

            <div class="title-doc">LEMBAR PENILAIAN HASIL BELAJAR PESERTA DIDIK</div>
            <div class="sub-title">
                REKAPITULASI NILAI RAPOR SEMESTER 1 S/D 6 (BUKU INDUK PESERTA DIDIK)
            </div>

            <!-- Identity Card Ringkas -->
            <div class="info-siswa-card">
                <table>
                    <tr>
                        <td style="width: 130px;">Nama Peserta Didik</td>
                        <td style="width: 10px;">:</td>
                        <td><strong><?= htmlspecialchars($siswa['nama_lengkap']) ?></strong></td>
                        <td style="width: 130px;">Program Keahlian</td>
                        <td style="width: 10px;">:</td>
                        <td><strong><?= htmlspecialchars($siswa['jurusan'] ?? '-') ?></strong></td>
                    </tr>
                    <tr>
                        <td>NISN / NIS</td>
                        <td>:</td>
                        <td><strong><?= htmlspecialchars($siswa['nisn']) ?></strong> / <?= htmlspecialchars($siswa['nis'] ?? '-') ?></td>
                        <td>Kelas / Rombel</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td>Tempat, Tgl Lahir</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, <?= !empty($siswa['tanggal_lahir']) ? date('d/m/Y', strtotime($siswa['tanggal_lahir'])) : '-' ?></td>
                        <td>Status Peserta Didik</td>
                        <td>:</td>
                        <td><strong><?= htmlspecialchars($siswa['status_siswa'] ?? 'AKTIF') ?></strong></td>
                    </tr>
                </table>
            </div>

            <!-- Section F: Tabel Rekapitulasi Nilai Rapor Semester 1 s/d 6 -->
            <div class="section-header" style="margin-top: 5px;">
                F. REKAPITULASI NILAI RAPOR SEMESTER 1 S/D 6 (SKALA 0 - 100)
            </div>

            <table class="table-border">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 25px;">No</th>
                        <th rowspan="2">Mata Pelajaran</th>
                        <th colspan="2">Kelas X</th>
                        <th colspan="2">Kelas XI</th>
                        <th colspan="2">Kelas XII</th>
                        <th rowspan="2" style="width: 50px;">Rata NA</th>
                        <th rowspan="2" style="width: 35px;">Pred</th>
                    </tr>
                    <tr>
                        <th style="width: 42px;">Sem 1</th>
                        <th style="width: 42px;">Sem 2</th>
                        <th style="width: 42px;">Sem 3</th>
                        <th style="width: 42px;">Sem 4</th>
                        <th style="width: 42px;">Sem 5</th>
                        <th style="width: 42px;">Sem 6</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarMapel)): ?>
                        <tr>
                            <td colspan="10" class="text-center" style="padding: 15px; color: #666; font-style: italic;">
                                Belum ada data mata pelajaran pada kurikulum.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php 
                        $renderGroup = function(array $mapelItems, string $groupTitle, int &$counter) {
                            if (empty($mapelItems)) return;
                            ?>
                            <tr style="background: #f1f5f9; font-weight: bold; font-size: 8.5pt;">
                                <td colspan="10" style="padding-left: 8px;">
                                    <?= htmlspecialchars($groupTitle) ?>
                                </td>
                            </tr>
                            <?php foreach ($mapelItems as $item): 
                                $rataMapel = (float)($item['rata_mapel'] ?? $item['rata_rata'] ?? 0);
                                $pred = '-';
                                if ($rataMapel > 0) {
                                    $pred = ($rataMapel >= 86.0) ? 'A' : (($rataMapel >= 71.0) ? 'B' : (($rataMapel >= 56.0) ? 'C' : 'D'));
                                }
                            ?>
                                <tr>
                                    <td class="text-center"><?= $counter++ ?></td>
                                    <td><?= htmlspecialchars($item['nama_mapel']) ?></td>
                                    <?php for ($s = 1; $s <= 6; $s++): 
                                        $semData = $item['semesters'][$s] ?? null;
                                        $val = ($semData && isset($semData['na']) && $semData['na'] !== null) 
                                            ? (float)$semData['na'] 
                                            : (($semData && isset($semData['nilai_akhir']) && $semData['nilai_akhir'] !== null) ? (float)$semData['nilai_akhir'] : null);
                                    ?>
                                        <td class="text-center font-bold">
                                            <?= ($val !== null && $val > 0) ? number_format($val, 1) : '<span style="color:#aaa;">-</span>' ?>
                                        </td>
                                    <?php endfor; ?>
                                    <td class="text-center font-bold" style="background: #f8fafc;">
                                        <?= ($rataMapel > 0) ? number_format($rataMapel, 1) : '<span style="color:#aaa;">-</span>' ?>
                                    </td>
                                    <td class="text-center font-bold" style="background: #f8fafc;">
                                        <?= $pred ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                        };

                        $noCounter = 1;
                        $renderGroup($mapelKelompokA, "KELOMPOK A (MUATAN UMUM / NASIONAL)", $noCounter);
                        $renderGroup($mapelKelompokB, "KELOMPOK B (MUATAN KEJURUAN / KONSENTRASI KEAHLIAN)", $noCounter);
                        $renderGroup($mapelKelompokC, "KELOMPOK C (MUATAN LOKAL & PILIHAN)", $noCounter);
                        ?>

                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <!-- Baris Rata-rata Semester -->
                    <tr style="background: #f1f5f9; font-weight: bold;">
                        <td colspan="2" class="text-right" style="padding-right: 8px;">
                            RATA-RATA SEMESTER :
                        </td>
                        <?php for ($s = 1; $s <= 6; $s++): 
                            $avgSem = $semesterAverages[$s] ?? 0;
                        ?>
                            <td class="text-center font-bold" style="font-size: 9pt;">
                                <?= ($avgSem > 0) ? number_format((float)$avgSem, 1) : '<span style="color:#aaa;">-</span>' ?>
                            </td>
                        <?php endfor; ?>
                        <td colspan="2" style="background: #e2e8f0;"></td>
                    </tr>

                    <!-- Baris Rata-rata Kumulatif -->
                    <tr style="background: #e2e8f0; font-weight: bold; font-size: 9.5pt;">
                        <td colspan="8" class="text-right" style="padding-right: 12px; letter-spacing: 0.5px;">
                            RATA-RATA NILAI RAPOR KUMULATIF SELURUH SEMESTER :
                        </td>
                        <td class="text-center font-bold" style="font-size: 11pt; color: #0f172a; background: #cbd5e1;">
                            <?= ($rataRataKumulatif > 0) ? number_format($rataRataKumulatif, 2) : '-' ?>
                        </td>
                        <td class="text-center font-bold" style="font-size: 11pt; color: #0f172a; background: #cbd5e1;">
                            <?= ($rataRataKumulatif > 0) ? $infoKumulatif['predikat'] : '-' ?>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Section G: Keterangan Predikat & Mutu -->
            <div style="margin-top: 6px; display: flex; justify-content: space-between; gap: 10px; font-size: 8.5pt;">
                <div style="flex: 1; border: 1px solid #94a3b8; padding: 4px 8px; background: #fafafa;">
                    <strong>Kriteria Rentang Predikat Nilai (Permendikbud):</strong><br>
                    <span><strong>A</strong> (86.00 - 100.00) : Sangat Baik (Optimal)</span> | 
                    <span><strong>B</strong> (71.00 - 85.99) : Baik (Tuntas)</span><br>
                    <span><strong>C</strong> (56.00 - 70.99) : Cukup (Bimbingan)</span> | 
                    <span><strong>D</strong> (&lt; 56.00) : Kurang (Remedial)</span>
                </div>
                <div style="flex: 1; border: 1px solid #94a3b8; padding: 4px 8px; background: #fafafa;">
                    <strong>Catatan Capaian Kompetensi Kumulatif:</strong><br>
                    <em><?= ($rataRataKumulatif > 0) ? htmlspecialchars($infoKumulatif['capaian_kompetensi']) : 'Nilai raport kumulatif sedang dalam proses penilaian berkelanjutan.' ?></em>
                </div>
            </div>

            <!-- Section H: Catatan PKL & Ekstrakurikuler -->
            <div class="section-header" style="margin-top: 8px;">
                G. CATATAN PRAKTIK KERJA LAPANGAN (PKL) & PENGEMBANGAN DIRI
            </div>
            <table class="table-border">
                <thead>
                    <tr>
                        <th style="width: 30px;">No</th>
                        <th style="width: 200px;">Kegiatan / Aspek</th>
                        <th>Mitra DU/DI / Instansi / Jenis</th>
                        <th style="width: 90px;">Nilai / Predikat</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($catatanKhusus)): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 10px; color: #64748b; font-style: italic;">
                                Belum ada catatan khusus (PKL, Ekstrakurikuler, atau Prestasi) yang ditambahkan untuk siswa ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($catatanKhusus as $ck): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($ck['nama_kegiatan']) ?></strong> <?= $ck['semester_ke'] ? '(Smstr '.$ck['semester_ke'].')' : '' ?></td>
                                <td><?= htmlspecialchars($ck['mitra_instansi'] ?: '-') ?></td>
                                <td class="text-center font-bold text-emerald-700"><?= htmlspecialchars($ck['nilai_predikat'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($ck['keterangan'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>


            <!-- Section I: Pengesahan Dua Pihak Lembar Nilai -->
            <div class="ttd-box" style="margin-top: 15px;">
                <div style="text-align: center; width: 230px;">
                    <div>Mengetahui,</div>
                    <div style="margin-top: 4px;">Wali Kelas / Ka. Program Keahlian,</div>
                    <div style="height: 50px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;">
                        <?php 
                        $waliKelasNama = 'Wali Kelas ' . ($siswa['nama_kelas'] ?? '');
                        if (!empty($riwayatKelas)) {
                            $lastRk = end($riwayatKelas);
                            if (!empty($lastRk['wali_kelas'])) {
                                $waliKelasNama = $lastRk['wali_kelas'];
                            }
                        }
                        echo htmlspecialchars($waliKelasNama);
                        ?>
                    </div>
                    <div>NIP. -</div>
                </div>

                <div style="text-align: center; width: 230px;">
                    <div>Pagelaran, <?= date('d F Y') ?></div>
                    <div style="margin-top: 4px;">Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>,</div>
                    <div style="height: 50px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($config['kepala_sekolah'] ?? 'Kepala Sekolah') ?></div>
                    <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
                </div>
            </div>

        </div> <!-- End Sheet Page 2 -->

    </div> <!-- End Page Container -->

    <script>
        function printAll() {
            document.getElementById('sheet-page-1').style.display = 'block';
            document.getElementById('sheet-page-2').style.display = 'block';
            window.print();
        }

        function printPage1() {
            document.getElementById('sheet-page-1').style.display = 'block';
            document.getElementById('sheet-page-2').style.display = 'none';
            window.print();
            setTimeout(function() {
                document.getElementById('sheet-page-2').style.display = 'block';
            }, 1000);
        }

        function printPage2() {
            document.getElementById('sheet-page-1').style.display = 'none';
            document.getElementById('sheet-page-2').style.display = 'block';
            window.print();
            setTimeout(function() {
                document.getElementById('sheet-page-1').style.display = 'block';
            }, 1000);
        }
    </script>

</body>
</html>

<?php
use App\Helpers\SuratHelper;

$jenis = $surat['jenis_surat'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($surat['nomor_surat']) ?> - <?= htmlspecialchars($surat['perihal']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .page-container {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            box-sizing: border-box;
        }
        .print-btn-bar {
            background: #1e293b;
            padding: 12px 20px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .print-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .print-btn:hover {
            background: #1d4ed8;
        }
        .title-doc {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            text-decoration: underline;
            margin: 10px 0 2px 0;
            letter-spacing: 0.5px;
        }
        .nomor-surat {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 18px;
        }
        .table-border {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-top: 10px;
        }
        .table-border th, .table-border td {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }
        .table-border th {
            background: #f1f5f9;
            text-align: center;
        }
        .table-no-border {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt;
            margin: 10px 0;
        }
        .table-no-border td {
            padding: 2.5px 4px;
            vertical-align: top;
        }
        .ttd-box {
            margin-top: 30px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }
        .page-break {
            page-break-before: always;
            margin-top: 30px;
        }
        @media print {
            .print-btn-bar { display: none !important; }
            body { margin: 0; }
            .page-break { page-break-before: always; }
            .table-border th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <div>
            <strong>Pratinjau Cetak Surat Dinas Resmi</strong>: <?= htmlspecialchars($surat['nomor_surat']) ?>
        </div>
        <div>
            <button class="print-btn" onclick="window.print()">🖨️ Cetak Dokumen Resmi (A4)</button>
        </div>
    </div>

    <div class="page-container">

        <!-- ============================================== -->
        <!-- KONDISI 1: JIKA SURAT BERJENIS SPPD -->
        <!-- ============================================== -->
        <?php if ($jenis === 'SPPD'): ?>

            <!-- LEMBAR 1: SURAT PERINTAH TUGAS (SPT) -->
            <?= SuratHelper::renderKopDinas($config) ?>

            <div class="title-doc">SURAT PERINTAH TUGAS (SPT)</div>
            <div class="nomor-surat">Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div>

            <p style="text-align: justify; text-indent: 30px;">
                Yang bertanda tangan di bawah ini, Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>, dengan ini memberikan tugas kedinasan kepada:
            </p>

            <table class="table-no-border" style="margin-left: 20px;">
                <tr>
                    <td style="width: 170px;">Nama Pegawai</td>
                    <td style="width: 15px;">:</td>
                    <td><strong><?= htmlspecialchars($surat['nama_penerima'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                    <td>NIP / NUPTK</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['nip_penerima'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Pangkat / Golongan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['pangkat_golongan'] ?? 'Penata Muda / Guru') ?></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['jabatan_penerima'] ?? 'Guru / Tenaga Kependidikan') ?></td>
                </tr>
                <?php if (!empty($surat['pengikut'])): ?>
                    <tr>
                        <td>Pengikut</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($surat['pengikut']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <p style="text-align: justify; text-indent: 30px; margin-top: 10px;">
                Untuk melaksanakan tugas kedinasan dalam rangka: <strong><?= htmlspecialchars($surat['keperluan'] ?? $surat['perihal']) ?></strong>, yang bertempat di:
            </p>

            <table class="table-no-border" style="margin-left: 20px;">
                <tr>
                    <td style="width: 170px;">Tempat / Instansi Tujuan</td>
                    <td style="width: 15px;">:</td>
                    <td><strong><?= htmlspecialchars($surat['instansi_tujuan'] ?? $surat['tempat_tujuan']) ?></strong> (<?= htmlspecialchars($surat['tempat_tujuan']) ?>)</td>
                </tr>
                <tr>
                    <td>Dasar Penugasan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['dasar_penugasan'] ?? 'Program Kerja Sekolah Tahun Anggaran ' . date('Y')) ?></td>
                </tr>
                <tr>
                    <td>Waktu Pelaksanaan</td>
                    <td>:</td>
                    <td>
                        <?= !empty($surat['tanggal_berangkat']) ? date('d F Y', strtotime($surat['tanggal_berangkat'])) : '-' ?> 
                        s/d 
                        <?= !empty($surat['tanggal_kembali']) ? date('d F Y', strtotime($surat['tanggal_kembali'])) : '-' ?> 
                        (Selama <?= htmlspecialchars((string)($surat['lama_hari'] ?? 1)) ?> hari)
                    </td>
                </tr>
                <tr>
                    <td>Pembebanan Anggaran</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['beban_anggaran'] ?? 'Dana BOS SMK Al-Farizi') ?></td>
                </tr>
            </table>

            <p style="text-align: justify; text-indent: 30px; margin-top: 10px;">
                Demikian Surat Perintah Tugas ini dibuat untuk dilaksanakan dengan penuh rasa tanggung jawab dan menyampaikan laporan hasil pelaksanaan tugas setelah selesai.
            </p>

            <!-- TTD Kepala Sekolah SPT -->
            <div class="ttd-box">
                <div style="text-align: center; width: 230px;">
                    <div>Pagelaran, <?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    <div style="margin-top: 4px;">Kepala Sekolah,</div>
                    <div style="height: 60px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($surat['pejabat_penandatangan'] ?? $config['kepala_sekolah']) ?></div>
                    <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
                </div>
            </div>

            <!-- LEMBAR 2: FORMULIR 10 KOLOM SPPD -->
            <div class="page-break"></div>
            <?= SuratHelper::renderKopDinas($config) ?>

            <div class="title-doc">SURAT PERINTAH PERJALANAN DINAS (SPPD)</div>
            <div class="nomor-surat">Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div>

            <table class="table-border">
                <tr>
                    <td style="width: 30px; text-align: center;">1.</td>
                    <td style="width: 220px;">Pejabat Pembuat Komitmen / Pemberi Perintah</td>
                    <td>Kepala <?= htmlspecialchars($config['nama_sekolah']) ?></td>
                </tr>
                <tr>
                    <td style="text-align: center;">2.</td>
                    <td>Nama Pegawai yang Diperintahkan</td>
                    <td><strong><?= htmlspecialchars($surat['nama_penerima'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: center;">3.</td>
                    <td>a. Pangkat dan Golongan<br>b. Jabatan / Instansi<br>c. Tingkat Biaya Perjalanan Dinas</td>
                    <td>
                        a. <?= htmlspecialchars($surat['pangkat_golongan'] ?? 'Penata Muda') ?><br>
                        b. <?= htmlspecialchars($surat['jabatan_penerima'] ?? 'Guru / Pegawai') ?> / <?= htmlspecialchars($config['nama_sekolah']) ?><br>
                        c. Tingkat C (Standar Daerah)
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">4.</td>
                    <td>Maksud Perjalanan Dinas</td>
                    <td><strong><?= htmlspecialchars($surat['keperluan'] ?? $surat['perihal']) ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: center;">5.</td>
                    <td>Alat Angkutan yang Digunakan</td>
                    <td><?= htmlspecialchars($surat['alat_angkut'] ?? 'Kendaraan Dinas / Umum') ?></td>
                </tr>
                <tr>
                    <td style="text-align: center;">6.</td>
                    <td>a. Tempat Berangkat<br>b. Tempat Tujuan</td>
                    <td>
                        a. <?= htmlspecialchars($surat['tempat_berangkat'] ?? 'SMK AL-FARIZI') ?><br>
                        b. <?= htmlspecialchars($surat['instansi_tujuan'] ?? $surat['tempat_tujuan']) ?> (<?= htmlspecialchars($surat['tempat_tujuan']) ?>)
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">7.</td>
                    <td>a. Lamanya Perjalanan Dinas<br>b. Tanggal Berangkat<br>c. Tanggal Harus Kembali</td>
                    <td>
                        a. <?= htmlspecialchars((string)($surat['lama_hari'] ?? 1)) ?> hari<br>
                        b. <?= !empty($surat['tanggal_berangkat']) ? date('d F Y', strtotime($surat['tanggal_berangkat'])) : '-' ?><br>
                        c. <?= !empty($surat['tanggal_kembali']) ? date('d F Y', strtotime($surat['tanggal_kembali'])) : '-' ?>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">8.</td>
                    <td>Pengikut : Nama</td>
                    <td><?= !empty($surat['pengikut']) ? htmlspecialchars($surat['pengikut']) : '-' ?></td>
                </tr>
                <tr>
                    <td style="text-align: center;">9.</td>
                    <td>Pembebanan Anggaran<br>a. Instansi<br>b. Mata Anggaran</td>
                    <td>
                        a. <?= htmlspecialchars($config['nama_sekolah']) ?><br>
                        b. <?= htmlspecialchars($surat['beban_anggaran'] ?? 'BOS SMK Al-Farizi') ?>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;">10.</td>
                    <td>Keterangan Lain-lain</td>
                    <td>Dasar: <?= htmlspecialchars($surat['dasar_penugasan'] ?? 'Surat Tugas Dinas') ?></td>
                </tr>
            </table>

            <div class="ttd-box">
                <div style="text-align: center; width: 230px;">
                    <div>Dikeluarkan di: Pagelaran</div>
                    <div>Pada tanggal: <?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    <div style="margin-top: 4px;">Kepala Sekolah,</div>
                    <div style="height: 60px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($surat['pejabat_penandatangan'] ?? $config['kepala_sekolah']) ?></div>
                    <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
                </div>
            </div>

            <!-- LEMBAR 3: LEMBAR VISUM TANDA TANGAN & CAP PEJABAT YANG DITUJU SESUAI PERMENDIKNAS -->
            <div class="page-break"></div>
            <?= SuratHelper::renderLembarVisumSppd($surat, $config) ?>

        <!-- ============================================== -->
        <!-- KONDISI 2: JIKA SURAT TUGAS SAJA (NON-SPPD) -->
        <!-- ============================================== -->
        <?php elseif ($jenis === 'SURAT_TUGAS'): ?>

            <?= SuratHelper::renderKopDinas($config) ?>

            <div class="title-doc">SURAT PERINTAH TUGAS (SPT)</div>
            <div class="nomor-surat">Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div>

            <p style="text-align: justify; text-indent: 30px;">
                Yang bertanda tangan di bawah ini, Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>, dengan ini menugaskan kepada:
            </p>

            <table class="table-no-border" style="margin-left: 20px;">
                <tr>
                    <td style="width: 170px;">Nama Pegawai</td>
                    <td style="width: 15px;">:</td>
                    <td><strong><?= htmlspecialchars($surat['nama_penerima'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                    <td>NIP / NUPTK</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['nip_penerima'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['jabatan_penerima'] ?? 'Guru / Staf') ?></td>
                </tr>
            </table>

            <p style="text-align: justify; text-indent: 30px; margin-top: 10px;">
                Untuk melaksanakan tugas: <strong><?= htmlspecialchars($surat['keperluan'] ?? $surat['perihal']) ?></strong>, yang dilaksanakan pada:
            </p>

            <table class="table-no-border" style="margin-left: 20px;">
                <tr>
                    <td style="width: 170px;">Hari / Tanggal</td>
                    <td style="width: 15px;">:</td>
                    <td><?= !empty($surat['tanggal_berangkat']) ? date('d F Y', strtotime($surat['tanggal_berangkat'])) : date('d F Y', strtotime($surat['tanggal_surat'])) ?></td>
                </tr>
                <tr>
                    <td>Tempat Pelaksanaan</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['instansi_tujuan'] ?? $surat['tempat_tujuan'] ?? 'Tempat yang Ditentukan') ?></td>
                </tr>
                <?php if (!empty($surat['dasar_penugasan'])): ?>
                    <tr>
                        <td>Dasar Penugasan</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($surat['dasar_penugasan']) ?></td>
                    </tr>
                <?php endif; ?>
            </table>

            <p style="text-align: justify; text-indent: 30px; margin-top: 10px;">
                Demikian Surat Perintah Tugas ini diberikan agar dapat dilaksanakan dengan penuh dedikasi dan rasa tanggung jawab.
            </p>

            <div class="ttd-box">
                <div style="text-align: center; width: 230px;">
                    <div>Pagelaran, <?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    <div style="margin-top: 4px;">Kepala Sekolah,</div>
                    <div style="height: 60px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($surat['pejabat_penandatangan'] ?? $config['kepala_sekolah']) ?></div>
                    <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
                </div>
            </div>

        <!-- ============================================== -->
        <!-- KONDISI 3: SURAT KETERANGAN SISWA AKTIF / LAINNYA -->
        <!-- ============================================== -->
        <?php else: ?>

            <?= SuratHelper::renderKopDinas($config) ?>

            <div class="title-doc"><?= strtoupper(htmlspecialchars($surat['perihal'])) ?></div>
            <div class="nomor-surat">Nomor: <?= htmlspecialchars($surat['nomor_surat']) ?></div>

            <p style="text-align: justify; text-indent: 30px;">
                Yang bertanda tangan di bawah ini, Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>, menerangkan dengan sebenarnya bahwa:
            </p>

            <table class="table-no-border" style="margin-left: 20px;">
                <tr>
                    <td style="width: 170px;">Nama Lengkap</td>
                    <td style="width: 15px;">:</td>
                    <td><strong><?= htmlspecialchars($surat['nama_penerima'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                    <td>NISN / NIS</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['nisn_penerima'] ?? '-') ?> / <?= htmlspecialchars($surat['nis_penerima'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Kelas / Tingkat</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['nama_kelas_penerima'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Kompetensi Keahlian</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['jurusan_penerima'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Tempat, Tanggal Lahir</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['tempat_lahir_penerima'] ?? '-') ?>, <?= !empty($surat['tanggal_lahir_penerima']) ? date('d F Y', strtotime($surat['tanggal_lahir_penerima'])) : '-' ?></td>
                </tr>
                <tr>
                    <td>Nama Orang Tua / Wali</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['nama_ayah_penerima'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>Alamat Tinggal</td>
                    <td>:</td>
                    <td><?= htmlspecialchars($surat['alamat_penerima'] ?? '-') ?></td>
                </tr>
            </table>

            <p style="text-align: justify; text-indent: 30px; margin-top: 15px;">
                Adalah benar siswa tersebut di atas terdaftar dan <strong>AKTIF</strong> mengikuti kegiatan belajar mengajar pada Tahun Pelajaran <?= date('Y') . '/' . (date('Y') + 1) ?> di <?= htmlspecialchars($config['nama_sekolah']) ?>.
            </p>

            <?php if (!empty($surat['keperluan'])): ?>
                <p style="text-align: justify; text-indent: 30px;">
                    Surat keterangan ini diberikan kepada yang bersangkutan untuk keperluan: <strong><?= htmlspecialchars($surat['keperluan']) ?></strong>.
                </p>
            <?php endif; ?>

            <p style="text-align: justify; text-indent: 30px; margin-top: 10px;">
                Demikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.
            </p>

            <div class="ttd-box">
                <div style="text-align: center; width: 230px;">
                    <div>Pagelaran, <?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    <div style="margin-top: 4px;">Kepala Sekolah,</div>
                    <div style="height: 60px;"></div>
                    <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($surat['pejabat_penandatangan'] ?? $config['kepala_sekolah']) ?></div>
                    <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
                </div>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>

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
        /* A4 Standard Print Margins */
        @page {
            size: A4 portrait;
            margin: 20mm 20mm 20mm 25mm; /* Top exactly 2cm (20mm) */
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt; /* Standard official letter size */
            line-height: 1.45; /* Slightly tighter line height to save space */
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .page-container {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }
        
        /* Print Utility Bar */
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
            transition: background 0.2s;
        }
        .print-btn:hover {
            background: #1d4ed8;
        }

        /* Typography & Spacing */
        p {
            text-align: justify;
            margin: 10px 0; /* Reduced margin */
        }
        .indented-paragraph {
            text-indent: 40px;
        }
        
        .title-doc {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            text-decoration: underline;
            margin: 15px 0 5px 0; /* Reduced top margin from 25px to 15px */
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .nomor-surat {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 25px;
        }

        /* Tables */
        .table-border {
            width: 100%;
            border-collapse: collapse;
            font-size: 11pt; /* Slightly smaller for dense tables to fit well */
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .table-border th, .table-border td {
            border: 1px solid #000;
            padding: 6px 10px;
            vertical-align: top;
            line-height: 1.4;
        }
        .table-border th {
            background: #f1f5f9;
            text-align: center;
        }

        .table-no-border {
            width: 90%;
            border-collapse: collapse;
            font-size: 12pt;
            margin: 10px 0 10px 40px; /* Aligned with text indent */
        }
        .table-no-border td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .col-label {
            width: 35%; /* Fixed width for labels */
        }
        .col-separator {
            width: 3%;
            text-align: center;
        }
        .col-value {
            width: 62%;
        }

        /* Signature Block */
        .ttd-box {
            margin-top: 40px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }
        .ttd-content {
            text-align: center;
            width: 300px;
        }
        .ttd-signature-space {
            height: 70px; /* Ample space for stamp and signature */
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }
        
        /* Print Breaks */
        .page-break {
            page-break-before: always;
            margin-top: 30px;
        }

        @media print {
            .print-btn-bar { display: none !important; }
            body { margin: 0; }
            .page-break { page-break-before: always; margin-top: 0; }
            .table-border th { background: #e5e5e5 !important; -webkit-print-color-adjust: exact; color-adjust: exact; }
            /* Force table borders to be solid black */
            .table-border th, .table-border td { border: 1pt solid #000 !important; }
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

            <p class="indented-paragraph">
                Yang bertanda tangan di bawah ini, Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>, dengan ini memberikan tugas kedinasan kepada:
            </p>

            <table class="table-no-border">
                <tr>
                    <td class="col-label">Nama Pegawai</td>
                    <td class="col-separator">:</td>
                    <td class="col-value"><strong><?= htmlspecialchars($surat['nama_penerima'] ?? '-') ?></strong></td>
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

            <p class="indented-paragraph">
                Untuk melaksanakan tugas kedinasan dalam rangka: <strong><?= htmlspecialchars($surat['keperluan'] ?? $surat['perihal']) ?></strong>, yang bertempat di:
            </p>

            <table class="table-no-border">
                <tr>
                    <td class="col-label">Tempat / Instansi Tujuan</td>
                    <td class="col-separator">:</td>
                    <td class="col-value"><strong><?= htmlspecialchars($surat['instansi_tujuan'] ?? $surat['tempat_tujuan']) ?></strong> (<?= htmlspecialchars($surat['tempat_tujuan']) ?>)</td>
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

            <p class="indented-paragraph">
                Demikian Surat Perintah Tugas ini dibuat untuk dilaksanakan dengan penuh rasa tanggung jawab dan menyampaikan laporan hasil pelaksanaan tugas setelah selesai.
            </p>

            <!-- TTD Kepala Sekolah SPT -->
            <div class="ttd-box">
                <div class="ttd-content">
                    <div>Pagelaran, <?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    <div style="margin-top: 4px;">Kepala Sekolah,</div>
                    <div class="ttd-signature-space"></div>
                    <div class="ttd-name"><?= htmlspecialchars($surat['pejabat_penandatangan'] ?? $config['kepala_sekolah']) ?></div>
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
                    <td style="width: 5%; text-align: center;">1.</td>
                    <td style="width: 40%;">Pejabat Pembuat Komitmen / Pemberi Perintah</td>
                    <td style="width: 55%;">Kepala <?= htmlspecialchars($config['nama_sekolah']) ?></td>
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
                <div class="ttd-content">
                    <div>Dikeluarkan di: Pagelaran</div>
                    <div>Pada tanggal: <?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    <div style="margin-top: 4px;">Kepala Sekolah,</div>
                    <div class="ttd-signature-space"></div>
                    <div class="ttd-name"><?= htmlspecialchars($surat['pejabat_penandatangan'] ?? $config['kepala_sekolah']) ?></div>
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

            <p class="indented-paragraph">
                Yang bertanda tangan di bawah ini, Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>, dengan ini menugaskan kepada:
            </p>

            <table class="table-no-border">
                <tr>
                    <td class="col-label">Nama Pegawai</td>
                    <td class="col-separator">:</td>
                    <td class="col-value"><strong><?= htmlspecialchars($surat['nama_penerima'] ?? '-') ?></strong></td>
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

            <p class="indented-paragraph">
                Untuk melaksanakan tugas: <strong><?= htmlspecialchars($surat['keperluan'] ?? $surat['perihal']) ?></strong>, yang dilaksanakan pada:
            </p>

            <table class="table-no-border">
                <tr>
                    <td class="col-label">Hari / Tanggal</td>
                    <td class="col-separator">:</td>
                    <td class="col-value"><?= !empty($surat['tanggal_berangkat']) ? date('d F Y', strtotime($surat['tanggal_berangkat'])) : date('d F Y', strtotime($surat['tanggal_surat'])) ?></td>
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

            <p class="indented-paragraph">
                Demikian Surat Perintah Tugas ini diberikan agar dapat dilaksanakan dengan penuh dedikasi dan rasa tanggung jawab.
            </p>

            <div class="ttd-box">
                <div class="ttd-content">
                    <div>Pagelaran, <?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    <div style="margin-top: 4px;">Kepala Sekolah,</div>
                    <div class="ttd-signature-space"></div>
                    <div class="ttd-name"><?= htmlspecialchars($surat['pejabat_penandatangan'] ?? $config['kepala_sekolah']) ?></div>
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

            <p class="indented-paragraph">
                Yang bertanda tangan di bawah ini, Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>, menerangkan dengan sebenarnya bahwa:
            </p>

            <table class="table-no-border">
                <tr>
                    <td class="col-label">Nama Lengkap</td>
                    <td class="col-separator">:</td>
                    <td class="col-value"><strong><?= htmlspecialchars($surat['nama_penerima'] ?? '-') ?></strong></td>
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

            <p class="indented-paragraph">
                Adalah benar siswa tersebut di atas terdaftar dan <strong>AKTIF</strong> mengikuti kegiatan belajar mengajar pada Tahun Pelajaran <?= date('Y') . '/' . (date('Y') + 1) ?> di <?= htmlspecialchars($config['nama_sekolah']) ?>.
            </p>

            <?php if (!empty($surat['keperluan'])): ?>
                <p class="indented-paragraph">
                    Surat keterangan ini diberikan kepada yang bersangkutan untuk keperluan: <strong><?= htmlspecialchars($surat['keperluan']) ?></strong>.
                </p>
            <?php endif; ?>

            <p class="indented-paragraph">
                Demikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.
            </p>

            <div class="ttd-box">
                <div class="ttd-content">
                    <div>Pagelaran, <?= date('d F Y', strtotime($surat['tanggal_surat'])) ?></div>
                    <div style="margin-top: 4px;">Kepala Sekolah,</div>
                    <div class="ttd-signature-space"></div>
                    <div class="ttd-name"><?= htmlspecialchars($surat['pejabat_penandatangan'] ?? $config['kepala_sekolah']) ?></div>
                    <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
                </div>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>

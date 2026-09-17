<?php
use App\Helpers\SuratHelper;

$transkrip = $transkripData['transkrip'] ?? [];
$rataRataKumulatif = $transkripData['rata_rata_kumulatif'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Nilai Semester 1-6 - <?= htmlspecialchars($siswa['nama_lengkap']) ?> (<?= htmlspecialchars($siswa['nisn']) ?>)</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 18mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
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
            margin: 8px 0 2px 0;
        }
        .sub-title {
            text-align: center;
            font-size: 10.5pt;
            margin-bottom: 12px;
        }
        table.info-siswa {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 10px;
        }
        table.info-siswa td {
            padding: 2px 4px;
            vertical-align: top;
        }
        table.transkrip-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-top: 5px;
        }
        table.transkrip-table th, table.transkrip-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        table.transkrip-table th {
            background: #f1f5f9;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .ttd-box {
            margin-top: 25px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
            page-break-inside: avoid;
        }
        @media print {
            .print-btn-bar { display: none !important; }
            body { margin: 0; }
            table.transkrip-table th { background: #f0f0f0 !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <div><strong>Transkrip Nilai Kumulatif Semester 1 s/d 6</strong> (Kurikulum SMK / Permendikbud)</div>
        <div>
            <button class="print-btn" onclick="window.print()">🖨️ Cetak Transkrip Nilai (A4)</button>
        </div>
    </div>

    <div class="page-container">

        <!-- KOP RESMI SEKOLAH -->
        <?= SuratHelper::renderKopDinas($config) ?>

        <div class="title-doc">TRANSKRIP NILAI AKADEMIK KUMULATIF</div>
        <div class="sub-title">SEMESTER 1 S/D 6 (TINGKAT X, XI, XII)</div>

        <table class="info-siswa">
            <tr>
                <td style="width: 140px;">Nama Lengkap</td>
                <td style="width: 10px;">:</td>
                <td><strong><?= htmlspecialchars($siswa['nama_lengkap']) ?></strong></td>
                <td style="width: 130px;">Kelas / Jurusan</td>
                <td style="width: 10px;">:</td>
                <td><?= htmlspecialchars($siswa['nama_kelas'] ?? '-') ?> (<?= htmlspecialchars($siswa['jurusan'] ?? '-') ?>)</td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>:</td>
                <td><strong><?= htmlspecialchars($siswa['nisn']) ?></strong></td>
                <td>Tempat, Tanggal Lahir</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['tempat_lahir'] ?? '-') ?>, <?= !empty($siswa['tanggal_lahir']) ? date('d/m/Y', strtotime($siswa['tanggal_lahir'])) : '-' ?></td>
            </tr>
            <tr>
                <td>NIS Sekolah</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['nis'] ?? '-') ?></td>
                <td>Status Siswa</td>
                <td>:</td>
                <td><strong><?= htmlspecialchars($siswa['status_siswa'] ?? 'AKTIF') ?></strong></td>
            </tr>
        </table>

        <table class="transkrip-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 25px;">No</th>
                    <th rowspan="2">Mata Pelajaran</th>
                    <th colspan="2">Kelas X</th>
                    <th colspan="2">Kelas XI</th>
                    <th colspan="2">Kelas XII</th>
                    <th rowspan="2" style="width: 55px;">Rata-rata NA</th>
                </tr>
                <tr>
                    <th style="width: 45px;">Sem 1</th>
                    <th style="width: 45px;">Sem 2</th>
                    <th style="width: 45px;">Sem 3</th>
                    <th style="width: 45px;">Sem 4</th>
                    <th style="width: 45px;">Sem 5</th>
                    <th style="width: 45px;">Sem 6</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $currGroup = '';
                $no = 1;
                foreach ($transkrip as $item): 
                    if ($currGroup !== $item['kelompok']):
                        $currGroup = $item['kelompok'];
                ?>
                    <tr style="background-color: #f8fafc; font-weight: bold;">
                        <td colspan="9" style="padding-left: 10px;">
                            KELOMPOK <?= strtoupper(htmlspecialchars($currGroup)) ?>
                        </td>
                    </tr>
                <?php endif; ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($item['nama_mapel']) ?></td>
                        <td class="text-center font-bold"><?= ($item['semesters'][1] > 0) ? number_format((float)$item['semesters'][1], 1) : '-' ?></td>
                        <td class="text-center font-bold"><?= ($item['semesters'][2] > 0) ? number_format((float)$item['semesters'][2], 1) : '-' ?></td>
                        <td class="text-center font-bold"><?= ($item['semesters'][3] > 0) ? number_format((float)$item['semesters'][3], 1) : '-' ?></td>
                        <td class="text-center font-bold"><?= ($item['semesters'][4] > 0) ? number_format((float)$item['semesters'][4], 1) : '-' ?></td>
                        <td class="text-center font-bold"><?= ($item['semesters'][5] > 0) ? number_format((float)$item['semesters'][5], 1) : '-' ?></td>
                        <td class="text-center font-bold"><?= ($item['semesters'][6] > 0) ? number_format((float)$item['semesters'][6], 1) : '-' ?></td>
                        <td class="text-center font-bold" style="background-color: #f1f5f9;">
                            <?= ($item['rata_rata'] > 0) ? number_format((float)$item['rata_rata'], 1) : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="8" class="text-right" style="padding-right: 15px;">
                        RATA-RATA NILAI KUMULATIF SELURUH SEMESTER :
                    </td>
                    <td class="text-center font-bold" style="font-size: 11pt;">
                        <?= ($rataRataKumulatif > 0) ? number_format((float)$rataRataKumulatif, 2) : '-' ?>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Tanda Tangan Kepala Sekolah -->
        <div class="ttd-box">
            <div style="text-align: center; width: 230px;">
                <div>Pagelaran, <?= date('d F Y') ?></div>
                <div style="margin-top: 4px;">Kepala <?= htmlspecialchars($config['nama_sekolah']) ?>,</div>
                <div style="height: 65px;"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($config['kepala_sekolah'] ?? 'Kepala Sekolah') ?></div>
                <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
            </div>
        </div>

    </div>

</body>
</html>

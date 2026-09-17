<?php
use App\Helpers\SuratHelper;
use App\Helpers\TimeHelper;

$daftarMapel = $ijazahData['daftar_mapel'] ?? [];
$rataRaporTotal = $ijazahData['rata_rapor_total'] ?? 0;
$rataUjianTotal = $ijazahData['rata_ujian_total'] ?? 0;
$rataIjazahTotal = $ijazahData['rata_ijazah_total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transkrip Nilai Ijazah SMK - <?= htmlspecialchars($siswa['nama_lengkap']) ?> (NISN: <?= htmlspecialchars($siswa['nisn']) ?>)</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm;
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
            background: #0f172a;
            padding: 12px 24px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .print-btn {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .print-btn:hover {
            background: #0369a1;
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
            margin-bottom: 14px;
            text-transform: uppercase;
            font-weight: bold;
        }
        table.info-siswa {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 12px;
        }
        table.info-siswa td {
            padding: 2.5px 4px;
            vertical-align: top;
        }
        table.ijazah-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-top: 6px;
        }
        table.ijazah-table th, table.ijazah-table td {
            border: 1px solid #000;
            padding: 4px 6px;
        }
        table.ijazah-table th {
            background: #f8fafc;
            text-align: center;
            font-weight: bold;
        }
        .kelompok-header {
            background: #f1f5f9;
            font-weight: bold;
            text-align: left;
            padding-left: 8px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .ttd-container {
            margin-top: 25px;
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }
        .ttd-content {
            width: 250px;
            text-align: center;
            font-size: 10pt;
        }
        .ttd-space {
            height: 75px;
        }
        @media print {
            .print-btn-bar { display: none !important; }
            body { margin: 0; padding: 0; }
            .page-container { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="page-container">
        <!-- Action Toolbar (Hidden during Print) -->
        <div class="print-btn-bar">
            <div>
                <strong>Blangko Resmi Transkrip Nilai Ijazah SMK</strong>
                <span style="opacity: 0.75; margin-left: 8px;">Standar Permendikbudristek RI</span>
            </div>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.close()" style="background: transparent; color: #cbd5e1; border: 1px solid #475569; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-weight: 600;">Tutup</button>
                <button class="print-btn" onclick="window.print()">
                    <span>🖨️ Cetak Transkrip Ijazah</span>
                </button>
            </div>
        </div>

        <!-- KOP SURAT RESMI -->
        <?= SuratHelper::renderKopHtml($config) ?>

        <!-- TITLE DOKUMEN -->
        <div class="title-doc">TRANSKRIP NILAI IJAZAH KELULUSAN</div>
        <div class="sub-title">SEKOLAH MENENGAH KEJURUAN (SMK)</div>

        <!-- IDENTITAS SISWA LENGKAP -->
        <table class="info-siswa">
            <tr>
                <td style="width: 28%;">Nama Peserta Didik</td>
                <td style="width: 2%;">:</td>
                <td style="width: 70%;"><strong><?= htmlspecialchars(strtoupper($siswa['nama_lengkap'])) ?></strong></td>
            </tr>
            <tr>
                <td>Tempat dan Tanggal Lahir</td>
                <td>:</td>
                <td>
                    <?= htmlspecialchars($siswa['tempat_lahir'] ?? 'Bandung') ?>, 
                    <?= !empty($siswa['tanggal_lahir']) ? TimeHelper::formatDateIndonesian($siswa['tanggal_lahir']) : '-' ?>
                </td>
            </tr>
            <tr>
                <td>Nomor Induk Siswa Nasional (NISN)</td>
                <td>:</td>
                <td style="font-family: monospace; font-weight: bold;"><?= htmlspecialchars($siswa['nisn']) ?></td>
            </tr>
            <tr>
                <td>Nomor Induk Siswa (NIS)</td>
                <td>:</td>
                <td style="font-family: monospace;"><?= htmlspecialchars($siswa['nis'] ?? '-') ?></td>
            </tr>
            <tr>
                <td>Program / Konsentrasi Keahlian</td>
                <td>:</td>
                <td><strong><?= htmlspecialchars($siswa['jurusan'] ?? $config['jurusan_default'] ?? 'Teknik Komputer dan Jaringan') ?></strong></td>
            </tr>
            <tr>
                <td>Sekolah Asal</td>
                <td>:</td>
                <td><?= htmlspecialchars($siswa['sekolah_asal'] ?? '-') ?></td>
            </tr>
        </table>

        <!-- TABEL NILAI IJAZAH -->
        <table class="ijazah-table">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 52%;">Mata Pelajaran</th>
                    <th style="width: 14%;">Rata-rata Nilai Rapor</th>
                    <th style="width: 14%;">Nilai Ujian Sekolah</th>
                    <th style="width: 14%;">Nilai Ijazah</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $kelompokCurrent = null;
                $no = 1;
                foreach ($daftarMapel as $m): 
                    if ($m['kelompok'] !== $kelompokCurrent): 
                        $kelompokCurrent = $m['kelompok'];
                        $labelKelompok = match($kelompokCurrent) {
                            'Umum' => 'Muatan Nasional / Umum (Kelompok A)',
                            'Kejuruan' => 'Muatan Peminatan Kejuruan (Kelompok B)',
                            'Muatan Lokal' => 'Muatan Lokal (Kelompok C)',
                            default => 'Mata Pelajaran ' . $kelompokCurrent
                        };
                ?>
                    <tr>
                        <td colspan="5" class="kelompok-header">
                            <?= htmlspecialchars($labelKelompok) ?>
                        </td>
                    </tr>
                <?php endif; ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($m['nama_mapel']) ?></td>
                        <td class="text-center font-bold">
                            <?= ($m['rata_rapor'] > 0) ? number_format((float)$m['rata_rapor'], 2) : '-' ?>
                        </td>
                        <td class="text-center font-bold">
                            <?= ($m['nilai_ujian'] > 0) ? number_format((float)$m['nilai_ujian'], 2) : '-' ?>
                        </td>
                        <td class="text-center font-bold" style="background: #fafafa;">
                            <?= ($m['nilai_ijazah'] > 0) ? number_format((float)$m['nilai_ijazah'], 2) : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f8fafc; font-weight: bold;">
                    <td colspan="2" class="text-right" style="padding-right: 12px;">
                        RATA-RATA NILAI :
                    </td>
                    <td class="text-center">
                        <?= ($rataRaporTotal > 0) ? number_format((float)$rataRaporTotal, 2) : '-' ?>
                    </td>
                    <td class="text-center">
                        <?= ($rataUjianTotal > 0) ? number_format((float)$rataUjianTotal, 2) : '-' ?>
                    </td>
                    <td class="text-center" style="font-size: 10.5pt; font-weight: 900; background: #eef2ff;">
                        <?= ($rataIjazahTotal > 0) ? number_format((float)$rataIjazahTotal, 2) : '-' ?>
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- KETERANGAN PREDIKAT & RUMUS -->
        <div style="font-size: 8.5pt; color: #475569; margin-top: 8px; font-style: italic;">
            * Nilai Ijazah dihitung berdasarkan bobot 60% Rata-rata Nilai Rapor Semester Terpadu + 40% Ujian Sekolah (Permendikbudristek).
        </div>

        <!-- TANDA TANGAN KEPALA SEKOLAH -->
        <div class="ttd-container">
            <div class="ttd-content">
                <div><?= htmlspecialchars($config['kota_sekolah'] ?? 'Bandung') ?>, <?= TimeHelper::formatDateIndonesian(date('Y-m-d')) ?></div>
                <div style="margin-top: 3px; font-weight: bold;">Kepala Sekolah,</div>
                <div class="ttd-space"></div>
                <div style="font-weight: bold; text-decoration: underline;"><?= htmlspecialchars($config['nama_kepala_sekolah'] ?? 'Drs. H. Ahmad Al-Farizi, M.Pd.') ?></div>
                <div>NIP. <?= htmlspecialchars($config['nip_kepala_sekolah'] ?? '-') ?></div>
            </div>
        </div>

    </div>

</body>
</html>

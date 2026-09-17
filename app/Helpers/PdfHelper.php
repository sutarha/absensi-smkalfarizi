<?php
namespace App\Helpers;

class PdfHelper
{
    public static function terbilang(float $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $temp = "";

        if ($nilai < 12) {
            $temp = " " . $huruf[(int)$nilai];
        } else if ($nilai < 20) {
            $temp = self::terbilang($nilai - 10) . " Belas";
        } else if ($nilai < 100) {
            $temp = self::terbilang($nilai / 10) . " Puluh" . self::terbilang($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . self::terbilang($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = self::terbilang($nilai / 100) . " Ratus" . self::terbilang($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . self::terbilang($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = self::terbilang($nilai / 1000) . " Ribu" . self::terbilang($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = self::terbilang($nilai / 1000000) . " Juta" . self::terbilang($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = self::terbilang($nilai / 1000000000) . " Milyar" . self::terbilang(fmod($nilai, 1000000000));
        }

        return trim($temp);
    }

    /**
     * Menghasilkan dokumen HTML Slip Gaji Resmi Siap Cetak / Save PDF
     */
    public static function renderSlipGajiHtml(array $payload): string
    {
        $guru = $payload['guru'];
        $config = $payload['config'];
        $bulan = (int)$payload['bulan'];
        $tahun = (int)$payload['tahun'];
        $sesiList = $payload['sesi_list'];
        $monthName = TimeHelper::MONTHS_ID[$bulan] ?? "Bulan {$bulan}";

        $totalJp = 0;
        $totalTerjadwal = 0.0;
        $totalMenitTelat = 0;
        $totalDenda = 0.0;
        $totalHonorKbm = 0.0;

        foreach ($sesiList as $s) {
            $totalJp += (int)$s['jumlah_jp'];
            $totalTerjadwal += (float)($s['jumlah_jp'] * $config['honor_per_jp']);
            $totalMenitTelat += (int)$s['menit_terlambat'];
            $totalDenda += (float)($s['menit_terlambat'] * $config['denda_per_menit']);
            $totalHonorKbm += (float)$s['honor_didapat'];
        }

        $tunjanganTugas = (float)($guru['tunjangan_tugas'] ?? 0);
        $totalDiterima = $totalHonorKbm + $tunjanganTugas;
        $terbilangStr = self::terbilang($totalDiterima) . " Rupiah";

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - <?= htmlspecialchars($guru['nama_lengkap']) ?> - <?= $monthName ?> <?= $tahun ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 20mm;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #0f172a;
            background: #ffffff;
            margin: 0;
            padding: 24px;
            font-size: 13px;
            line-height: 1.6;
        }
        .kop-surat {
            border-bottom: 3px double #1e293b;
            padding-bottom: 14px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .kop-logo {
            width: 76px;
            height: 76px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
        }
        .kop-text {
            flex: 1;
            text-align: center;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 13.5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #475569;
            font-weight: 600;
        }
        .kop-text h1 {
            margin: 2px 0;
            font-size: 22px;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .kop-text p {
            margin: 2px 0;
            font-size: 11.5px;
            color: #64748b;
        }
        .doc-title {
            text-align: center;
            margin: 15px 0 22px;
        }
        .doc-title h3 {
            margin: 0;
            font-size: 16px;
            color: #0f172a;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .doc-title span {
            font-size: 12.5px;
            color: #2563eb;
            font-weight: 600;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 5px 8px;
            vertical-align: top;
            font-size: 13px;
        }
        .rincian-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .rincian-table th, .rincian-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 12.5px;
        }
        .rincian-table th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: 700;
            text-align: center;
        }
        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            font-size: 13.5px;
        }
        .summary-row.total {
            border-top: 2px solid #cbd5e1;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 16px;
            font-weight: 800;
            color: #1e3a8a;
        }
        .terbilang-box {
            background: #f0f9ff;
            border-left: 4px solid #0284c7;
            padding: 10px 14px;
            font-style: italic;
            font-size: 12.5px;
            color: #0369a1;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        .ttd-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .ttd-box {
            width: 30%;
            text-align: center;
            font-size: 12px;
        }
        .ttd-space {
            height: 65px;
        }
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">🖨️ Cetak / Unduh PDF</button>

    <div class="kop-surat">
        <div class="kop-logo">SMK</div>
        <div class="kop-text">
            <h2>YAYASAN PENDIDIKAN ISLAM AL-FARIZI</h2>
            <h1><?= htmlspecialchars($config['nama_sekolah']) ?></h1>
            <p><?= htmlspecialchars($config['alamat_sekolah']) ?> | NSS/NPSN: 40205600123</p>
            <p>Email: tu@smkalfarizi.sch.id | Website: https://smkalfarizi.sch.id</p>
        </div>
    </div>

    <div class="doc-title">
        <h3>SLIP HONORARIUM MENGAJAR & TUNJANGAN GURU</h3>
        <span>Periode: <?= $monthName ?> <?= $tahun ?></span>
    </div>

    <table class="meta-table">
        <tr>
            <td width="18%"><strong>Nama Guru</strong></td>
            <td width="2%">:</td>
            <td width="35%"><strong><?= htmlspecialchars($guru['nama_lengkap']) ?></strong></td>
            <td width="18%"><strong>Bulan/Tahun</strong></td>
            <td width="2%">:</td>
            <td width="25%"><?= $monthName ?> <?= $tahun ?></td>
        </tr>
        <tr>
            <td><strong>NIK / NIP</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($guru['nik_nip']) ?></td>
            <td><strong>Tarif per JP</strong></td>
            <td>:</td>
            <td><?= TimeHelper::formatRupiah($config['honor_per_jp']) ?> (<?= (int)$config['durasi_jp_menit'] ?> mnt)</td>
        </tr>
        <tr>
            <td><strong>Tugas Tambahan</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($guru['tugas_tambahan'] ?: '-') ?></td>
            <td><strong>Tarif Denda Telat</strong></td>
            <td>:</td>
            <td><?= TimeHelper::formatRupiah($config['denda_per_menit']) ?> / menit</td>
        </tr>
    </table>

    <table class="rincian-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="14%">Tanggal</th>
                <th width="26%">Mata Pelajaran & Kelas</th>
                <th width="8%">JP</th>
                <th width="12%">Check-in</th>
                <th width="11%">Telat</th>
                <th width="11%">Denda</th>
                <th width="13%">Honor Bersih</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sesiList)): ?>
            <tr>
                <td colspan="8" style="text-align: center; color: #94a3b8; padding: 24px;">
                    Belum ada realisasi sesi mengajar KBM pada periode ini.
                </td>
            </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($sesiList as $s): ?>
                <tr>
                    <td style="text-align: center;"><?= $i++ ?></td>
                    <td><?= date('d/m/Y', strtotime($s['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($s['nama_mapel']) ?> (<?= htmlspecialchars($s['nama_kelas']) ?>)</td>
                    <td style="text-align: center; font-weight: 600;"><?= (int)$s['jumlah_jp'] ?> JP</td>
                    <td style="text-align: center;"><?= date('H:i', strtotime($s['waktu_checkin'])) ?></td>
                    <td style="text-align: center; color: <?= $s['menit_terlambat'] > 0 ? '#e11d48' : '#059669' ?>; font-weight: 600;">
                        <?= (int)$s['menit_terlambat'] ?> mnt
                    </td>
                    <td style="text-align: right; color: <?= $s['menit_terlambat'] > 0 ? '#e11d48' : '#64748b' ?>;">
                        <?= TimeHelper::formatRupiah($s['menit_terlambat'] * $config['denda_per_menit']) ?>
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #0f172a;">
                        <?= TimeHelper::formatRupiah($s['honor_didapat']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-row">
            <span>Total Beban Mengajar Terjadwal (<?= $totalJp ?> JP)</span>
            <span><?= TimeHelper::formatRupiah($totalTerjadwal) ?></span>
        </div>
        <div class="summary-row" style="color: #e11d48;">
            <span>Total Potongan Keterlambatan (<?= $totalMenitTelat ?> menit × <?= TimeHelper::formatRupiah($config['denda_per_menit']) ?>)</span>
            <span>- <?= TimeHelper::formatRupiah($totalDenda) ?></span>
        </div>
        <div class="summary-row">
            <span>Subtotal Honorarium Riil KBM</span>
            <span><strong><?= TimeHelper::formatRupiah($totalHonorKbm) ?></strong></span>
        </div>
        <?php if ($tunjanganTugas > 0): ?>
        <div class="summary-row" style="color: #059669;">
            <span>Tunjangan Tugas Tambahan (<?= htmlspecialchars($guru['tugas_tambahan']) ?>)</span>
            <span>+ <?= TimeHelper::formatRupiah($tunjanganTugas) ?></span>
        </div>
        <?php endif; ?>
        <div class="summary-row total">
            <span>TOTAL HONOR BERSIH DITERIMA (TAKE HOME PAY)</span>
            <span><?= TimeHelper::formatRupiah($totalDiterima) ?></span>
        </div>
    </div>

    <div class="terbilang-box">
        <strong>Terbilang:</strong> <?= $terbilangStr ?>
    </div>

    <div class="ttd-container">
        <div class="ttd-box">
            <p>Penerima,</p>
            <div class="ttd-space"></div>
            <p><strong><?= htmlspecialchars($guru['nama_lengkap']) ?></strong><br>NIK. <?= htmlspecialchars($guru['nik_nip']) ?></p>
        </div>
        <div class="ttd-box">
            <p>Bendahara TU,</p>
            <div class="ttd-space"></div>
            <p><strong><?= htmlspecialchars($config['bendahara_tu']) ?></strong><br>NIP. 198204122008012004</p>
        </div>
        <div class="ttd-box">
            <p>Mengetahui,<br>Kepala Sekolah</p>
            <div class="ttd-space"></div>
            <p><strong><?= htmlspecialchars($config['kepala_sekolah']) ?></strong><br>NIP. 197103151998021002</p>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    /**
     * Menghasilkan lembar cetak A4 kartu pelajar dengan Barcode Kotak (QR Code 2D)
     * Format modern beresolusi tajam, 8-10 kartu per halaman A4 portrait.
     */
    public static function renderKartuSiswaHtml(array $siswaList, array $config): string
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Pelajar Barcode Kotak (QR Code) - <?= htmlspecialchars($config['nama_sekolah']) ?></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 10px;
            color: #0f172a;
        }
        .page-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            max-width: 800px;
            margin: 0 auto;
        }
        .card-pelajar {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            position: relative;
            box-shadow: 0 2px 5px rgba(0,0,0,0.04);
            page-break-inside: avoid;
            width: 85.6mm;
            height: 53.98mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card-logo {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 13px;
        }
        .card-header-text h4 {
            margin: 0;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #bfdbfe;
            font-weight: 600;
        }
        .card-header-text h3 {
            margin: 1px 0 0;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .card-body {
            display: flex;
            padding: 8px 12px;
            gap: 10px;
            flex: 1;
            align-items: center;
        }
        .card-photo {
            width: 60px;
            height: 75px;
            background: #f1f5f9;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            flex-shrink: 0;
        }
        .card-info {
            flex: 1;
            font-size: 10px;
            line-height: 1.3;
            min-width: 0;
        }
        .card-info .name {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-info .meta-row {
            margin-bottom: 2px;
            color: #334155;
        }
        .card-info .meta-label {
            color: #64748b;
            font-weight: 600;
            display: inline-block;
            width: 44px;
        }
        .card-qr-box {
            width: 80px;
            height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .qr-image {
            width: 68px;
            height: 68px;
            display: block;
        }
        .qr-label {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            font-family: monospace;
            margin-top: 1px;
        }
        .card-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 4px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: #64748b;
        }
        .no-print-bar {
            background: #0f172a;
            color: white;
            padding: 14px 22px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-print {
            background: #2563eb;
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 13px;
        }
        @media print {
            .no-print-bar { display: none; }
            body { background: white; padding: 0; }
            .card-pelajar { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <div>
            <strong style="font-size: 15px;">Template Cetak Kartu Pelajar Barcode Kotak (QR Code 2D)</strong>
            <div style="font-size: 12px; color: #94a3b8; margin-top: 2px;">Format Standar A4 Portrait (8–10 Kartu Pelajar Siap Cetak & Potong)</div>
        </div>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Kartu Pelajar (Print / PDF)</button>
    </div>

    <div class="page-grid">
        <?php foreach ($siswaList as $s): ?>
        <div class="card-pelajar">
            <div class="card-header">
            <div class="card-logo">
            <?php if (!empty($config['logo_kop'])): ?>
                <img src="<?= \App\Config\App::baseUrl('uploads/' . $config['logo_kop']) ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
            <?php else: ?>
                SMK
            <?php endif; ?>
        </div>
                <div class="card-header-text">
                    <h4>KARTU IDENTITAS SISWA</h4>
                    <h3><?= htmlspecialchars($config['nama_sekolah']) ?></h3>
                </div>
            </div>

            <div class="card-body">
                <div class="card-photo">
                    <?= $s['jenis_kelamin'] === 'P' ? '👧' : '👦' ?>
                </div>

                <div class="card-info">
                    <div class="name"><?= htmlspecialchars($s['nama_siswa']) ?></div>
                    <div class="meta-row">
                        <span class="meta-label">NISN</span>: <strong><?= htmlspecialchars($s['nisn']) ?></strong>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Kelas</span>: <?= htmlspecialchars($s['nama_kelas']) ?>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Jurusan</span>: <?= htmlspecialchars($s['jurusan']) ?>
                    </div>
                </div>

                <!-- Barcode Kotak (QR Code 2D) -->
                <div class="card-qr-box">
                    <img class="qr-image" src="<?= BarcodeHelper::getQrCodeDataUri($s['barcode_code']) ?>" alt="QR Code">
                    <div class="qr-label">SCAN SAYA</div>
                </div>
            </div>

            <div class="card-footer">
                <span>Kode: <strong><?= htmlspecialchars($s['barcode_code']) ?></strong></span>
                <span>Kartu Presensi Gerbang & KBM</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}

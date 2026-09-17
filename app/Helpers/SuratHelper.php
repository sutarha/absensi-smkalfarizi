<?php
namespace App\Helpers;

use App\Config\App;

/**
 * Helper untuk Generator Surat Dinas Otomatis Sesuai Tata Naskah Dinas Kemendikbudristek
 */
class SuratHelper
{
    public const KLASIFIKASI_SURAT = [
        'SISWA_AKTIF' => '421.5',     // Kesiswaan / Keterangan
        'BERKELAKUAN_BAIK' => '421.5',
        'IZIN_PKL' => '421.7',        // Hubungan Industri / Kejuruan
        'PANGGILAN_ORTU' => '421.5',  // Disiplin Siswa / Bimbingan
        'SKL' => '421.5',             // Kelulusan
        'SPPD' => '090',              // Perjalanan Dinas
        'SURAT_TUGAS' => '090',       // Surat Tugas
        'LAINNYA' => '420'
    ];

    /**
     * Konversi Bulan ke Angka Romawi
     */
    public static function getBulanRomawi(int $bulan): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $romawi[$bulan] ?? 'I';
    }

    /**
     * Generate Nomor Surat Dinas Standar Otomatis
     */
    public static function generateNomorSurat(string $jenisSurat, int $urutan, string $tanggal = ''): string
    {
        $date = !empty($tanggal) ? strtotime($tanggal) : time();
        $bulan = (int)date('n', $date);
        $tahun = date('Y', $date);
        $romawi = self::getBulanRomawi($bulan);
        $kode = self::KLASIFIKASI_SURAT[$jenisSurat] ?? '421.5';
        $noPadded = str_pad((string)$urutan, 3, '0', STR_PAD_LEFT);

        return "{$kode}/{$noPadded}/SMK-AF/{$romawi}/{$tahun}";
    }

    public static function renderKopDinas(array $config): string
    {
        return self::renderKopSuratHtml($config);
    }

    public static function renderKopHtml(array $config): string
    {
        return self::renderKopSuratHtml($config);
    }

    /**
     * Render KOP Surat Resmi Sesuai Tata Naskah Dinas (Kemendikbud)
     */
    public static function renderKopSuratHtml(array $config): string
    {
        $namaSekolah = strtoupper($config['nama_sekolah'] ?? 'SMK AL-FARIZI');
        $alamat = $config['alamat_sekolah'] ?? 'Jl. Raya Bojongsoang No. 123, Kab. Bandung';
        $npsn = $config['npsn'] ?? '69912345';
        $nss = $config['nss'] ?? '402020202020';
        $akreditasi = $config['akreditasi'] ?? 'A (Unggul)';
        $email = $config['email_sekolah'] ?? 'info@smkalfarizi.sch.id';
        $web = $config['website_sekolah'] ?? 'www.smkalfarizi.sch.id';
        $logo = !empty($config['logo_kop']) ? App::baseUrl('uploads/' . $config['logo_kop']) : '';

        $logoHtml = '';
        if ($logo) {
            $logoHtml = "<img src='{$logo}' style='width: 75px; height: 75px; object-fit: contain;'>";
        } else {
            $logoHtml = "
            <div style='width: 70px; height: 70px; border-radius: 12px; background: #1e3a8a; color: white; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 24px;'>
                AF
            </div>";
        }

        return "
        <div style='border-bottom: 3px double #0f172a; padding-bottom: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 18px;'>
            <div>{$logoHtml}</div>
            <div style='flex: 1; text-align: center;'>
                <div style='font-size: 13px; font-weight: 700; letter-spacing: 1px; color: #334155; text-transform: uppercase;'>
                    YAYASAN PENDIDIKAN AL-FARIZI JAWA BARAT
                </div>
                <div style='font-size: 20px; font-weight: 900; color: #0f172a; letter-spacing: 0.5px; margin: 2px 0;'>
                    {$namaSekolah}
                </div>
                <div style='font-size: 11px; font-weight: 600; color: #475569;'>
                    KOMPETENSI KEAHLIAN: PENGEMBANGAN PERANGKAT LUNAK & GIM (PPLG) • TEKNIK JARINGAN (TJKT)
                </div>
                <div style='font-size: 10.5px; color: #64748b; margin-top: 3px;'>
                    NPSN: {$npsn} | NSS: {$nss} | Akreditasi: {$akreditasi}
                </div>
                <div style='font-size: 10px; color: #64748b;'>
                    {$alamat} | Telp/Email: {$email} | Web: {$web}
                </div>
            </div>
        </div>";
    }

    public static function renderLembarVisumSppd(array $surat, array $config): string
    {
        return self::renderVisumSppdHtml($surat, $config);
    }

    /**
     * Render Lembar Visum Perjalanan Dinas (SPPD) Sesuai Permendiknas
     * Berisi tanda tangan & stempel pejabat instansi / tempat yang dituju
     */
    public static function renderVisumSppdHtml(array $surat, array $config): string
    {
        $pejabatTujuan = htmlspecialchars($surat['pejabat_tujuan'] ?? 'Pimpinan / Pejabat yang Berwenang');
        $instansiTujuan = htmlspecialchars($surat['instansi_tujuan'] ?? ($surat['tempat_tujuan'] ?? 'Instansi Tujuan'));
        $tempatTujuan = htmlspecialchars($surat['tempat_tujuan'] ?? 'Tempat Pelaksanaan');
        $tglBerangkat = !empty($surat['tanggal_berangkat']) ? TimeHelper::formatDateIndonesian($surat['tanggal_berangkat']) : '-';
        $tglKembali = !empty($surat['tanggal_kembali']) ? TimeHelper::formatDateIndonesian($surat['tanggal_kembali']) : '-';
        $kepsek = htmlspecialchars($config['kepala_sekolah'] ?? 'Drs. H. Ahmad Farizi, M.Pd.');

        return "
        <div style='page-break-before: always; margin-top: 30px;'>
            <div style='text-align: center; font-weight: 800; font-size: 14px; margin-bottom: 16px; text-transform: uppercase;'>
                LEMBAR VISUM / PENGESAHAN PERJALANAN DINAS<br>
                <span style='font-size: 12px; font-weight: 600;'>Nomor SPPD: " . htmlspecialchars($surat['nomor_surat']) . "</span>
            </div>

            <table style='width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px;' border='1'>
                <tbody>
                    <!-- Bagian I: Berangkat dari Sekolah -->
                    <tr>
                        <td style='width: 50%; padding: 10px; vertical-align: top;'>
                            <strong>I. Berangkat dari</strong> : " . htmlspecialchars($surat['tempat_berangkat'] ?? 'SMK AL-FARIZI') . "<br>
                            <strong>Pada Tanggal</strong> : {$tglBerangkat}<br>
                            <strong>Ke</strong> : {$tempatTujuan}<br><br>
                            <div style='text-align: center;'>
                                Pejabat Pembuat Komitmen / Kepala Sekolah,<br><br><br><br>
                                <strong><u>{$kepsek}</u></strong><br>
                                NIP. 197508122000031002
                            </div>
                        </td>
                        <td style='width: 50%; padding: 10px; vertical-align: top; background: #fafafa;'>
                            <em>(Ruang catatan petugas)</em>
                        </td>
                    </tr>

                    <!-- Bagian II: Tiba di Tempat Tujuan (TTD PEJABAT YANG DITUJU) -->
                    <tr>
                        <td style='padding: 10px; vertical-align: top;'>
                            <strong>II. Tiba di</strong> : {$instansiTujuan}<br>
                            <strong>Pada Tanggal</strong> : {$tglBerangkat}<br>
                            <strong>Kepala / Pejabat yang Dituju</strong> :<br><br>
                            <div style='text-align: center;'>
                                <em>(Tanda Tangan & Cap Stempel)</em><br><br><br><br>
                                <strong><u>{$pejabatTujuan}</u></strong><br>
                                NIP / Jabatan: .......................................
                            </div>
                        </td>
                        <td style='padding: 10px; vertical-align: top;'>
                            <strong>Berangkat dari</strong> : {$instansiTujuan}<br>
                            <strong>Ke</strong> : " . htmlspecialchars($surat['tempat_berangkat'] ?? 'SMK AL-FARIZI') . "<br>
                            <strong>Pada Tanggal</strong> : {$tglKembali}<br><br>
                            <div style='text-align: center;'>
                                Pejabat / Pimpinan yang Dituju,<br><br><br><br>
                                <strong><u>{$pejabatTujuan}</u></strong><br>
                                NIP / Jabatan: .......................................
                            </div>
                        </td>
                    </tr>

                    <!-- Bagian III: Tiba Kembali di Tempat Kedudukan Sekolah -->
                    <tr>
                        <td style='padding: 10px; vertical-align: top;'>
                            <strong>III. Tiba di</strong> : " . htmlspecialchars($surat['tempat_berangkat'] ?? 'SMK AL-FARIZI') . "<br>
                            <strong>Pada Tanggal</strong> : {$tglKembali}<br><br>
                            <div style='text-align: center;'>
                                Kepala Sekolah / PPK,<br><br><br><br>
                                <strong><u>{$kepsek}</u></strong><br>
                                NIP. 197508122000031002
                            </div>
                        </td>
                        <td style='padding: 10px; vertical-align: top;'>
                            <strong>Telah diperiksa dengan keterangan bahwa:</strong><br>
                            Perjalanan tersebut di atas benar-benar dilakukan atas perintahnya dan semata-mata untuk kepentingan jabatan dalam waktu yang sesingkat-singkatnya.<br><br>
                            <div style='text-align: center;'>
                                Pejabat Pembuat Komitmen,<br><br><br><br>
                                <strong><u>{$kepsek}</u></strong><br>
                                NIP. 197508122000031002
                            </div>
                        </td>
                    </tr>

                    <!-- Bagian IV: Catatan Lain -->
                    <tr>
                        <td colspan='2' style='padding: 8px 10px;'>
                            <strong>IV. CATATAN LAIN-LAIN:</strong><br>
                            Pejabat yang berwenang menerbitkan SPPD, pegawai/siswa yang melakukan perjalanan dinas, para pejabat yang mengesahkan tanggal berangkat/tiba bertanggung jawab menurut peraturan-peraturan keuangan negara.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>";
    }
}

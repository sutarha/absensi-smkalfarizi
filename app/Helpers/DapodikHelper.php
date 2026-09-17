<?php
namespace App\Helpers;

/**
 * Helper untuk Import dan Parsing Data Siswa dari Format Dapodik (Excel .xlsx / .xls dan CSV)
 */
class DapodikHelper
{
    /**
     * Daftar Kolom Baku Dapodik Kemdikbudristek
     */
    public const DAPODIK_COLUMNS = [
        'nisn' => ['nisn', 'nomor_induk_siswa_nasional', 'no_nisn'],
        'nama_siswa' => ['nama', 'nama_siswa', 'nama_peserta_didik', 'nama_lengkap', 'peserta_didik'],
        'nama_kelas' => ['kelas', 'rombel', 'rombongan_belajar', 'nama_rombel', 'tingkat_kelas', 'rombel_kelas', 'rombel_saat_ini'],
        'jenis_kelamin' => ['jenis_kelamin', 'jk', 'l/p', 'gender', 'jenis_kelamin_l_p'],
        'nis' => ['nis', 'nipd', 'no_induk', 'nomor_induk', 'nis_nipd'],
        'nik' => ['nik', 'no_nik', 'nomor_induk_kependudukan', 'nik_siswa'],
        'no_kk' => ['no_kk', 'nomor_kartu_keluarga', 'kk'],
        'no_akta_lahir' => ['no_akta_lahir', 'akta_lahir', 'no_registrasi_akta_lahir', 'no_reg_akta_lahir'],
        'tempat_lahir' => ['tempat_lahir', 'tmp_lahir'],
        'tanggal_lahir' => ['tanggal_lahir', 'tgl_lahir', 'tgl_lhr', 'tanggal_lahir_yyyy_mm_dd'],
        'agama' => ['agama'],
        'alamat_jalan' => ['alamat', 'alamat_jalan', 'jalan'],
        'rt' => ['rt'],
        'rw' => ['rw'],
        'dusun' => ['dusun'],
        'kelurahan' => ['kelurahan', 'desa_kelurahan', 'desa'],
        'dusun_kelurahan' => ['dusun_kelurahan', 'desa_kelurahan', 'kelurahan', 'dusun'],
        'kecamatan' => ['kecamatan'],
        'kabupaten_kota' => ['kabupaten', 'kota', 'kabupaten_kota', 'kab_kota'],
        'provinsi' => ['provinsi'],
        'kode_pos' => ['kode_pos', 'kodepos'],
        'tinggal_bersama' => ['tinggal_bersama', 'status_tempat_tinggal', 'jenis_tinggal'],
        'transportasi' => ['transportasi', 'alat_transportasi', 'moda_transportasi'],
        'nama_ayah' => ['nama_ayah', 'ayah', 'data_ayah_nama', 'nama_ayah_kandung'],
        'nik_ayah' => ['nik_ayah', 'data_ayah_nik'],
        'tahun_lahir_ayah' => ['tahun_lahir_ayah', 'thn_lahir_ayah', 'data_ayah_tahun_lahir'],
        'pendidikan_ayah' => ['pendidikan_ayah', 'data_ayah_jenjang_pendidikan', 'data_ayah_pendidikan'],
        'pekerjaan_ayah' => ['pekerjaan_ayah', 'data_ayah_pekerjaan'],
        'penghasilan_ayah' => ['penghasilan_ayah', 'data_ayah_penghasilan'],
        'nama_ibu' => ['nama_ibu', 'ibu', 'nama_ibu_kandung', 'data_ibu_nama'],
        'nik_ibu' => ['nik_ibu', 'data_ibu_nik'],
        'tahun_lahir_ibu' => ['tahun_lahir_ibu', 'thn_lahir_ibu', 'data_ibu_tahun_lahir'],
        'pendidikan_ibu' => ['pendidikan_ibu', 'data_ibu_jenjang_pendidikan', 'data_ibu_pendidikan'],
        'pekerjaan_ibu' => ['pekerjaan_ibu', 'data_ibu_pekerjaan'],
        'penghasilan_ibu' => ['penghasilan_ibu', 'data_ibu_penghasilan'],
        'nama_wali' => ['nama_wali', 'wali', 'data_wali_nama'],
        'nik_wali' => ['nik_wali', 'data_wali_nik'],
        'pekerjaan_wali' => ['pekerjaan_wali', 'data_wali_pekerjaan'],
        'no_hp_ortu' => ['hp', 'no_hp', 'no_hp_ortu', 'no_telepon_ortu', 'kontak_ortu', 'no_hp_wa_ortu', 'telepon'],
        'sekolah_asal' => ['sekolah_asal', 'asal_sekolah', 'smp_asal'],
        'no_ijazah_smp' => ['no_ijazah_smp', 'no_seri_ijazah_smp', 'no_seri_ijazah', 'ijazah_smp'],
        'no_skhun_smp' => ['no_skhun_smp', 'skhun', 'no_skhun'],
        'anak_ke' => ['anak_ke', 'anak_ke_berapa'],
        'jumlah_saudara' => ['jumlah_saudara', 'jml_saudara_kandung', 'jml_saudara'],
        'tanggal_masuk' => ['tanggal_masuk', 'tgl_masuk', 'tanggal_masuk_yyyy_mm_dd']
    ];

    /**
     * Parse File Spreadsheet (Excel .xlsx, .xls, atau .csv) dari Dapodik
     * Mendukung otomatis berkas resmi ekspor Dapodik (dengan judul & subheader merged)
     * maupun berkas template standar mandiri.
     */
    public static function parseFile(string $filePath, ?string $originalName = null): array
    {
        $rawRows = ExcelHelper::parse($filePath, $originalName);
        if (empty($rawRows) || count($rawRows) < 2) {
            throw new \Exception("Berkas Excel harus memiliki minimal 1 baris header dan 1 baris data siswa.");
        }

        // 1. Ekstraksi Metadata Wilayah dari baris atas jika ada (misal: "Kabupaten Kab. Majalengka, Provinsi Prov. Jawa Barat")
        $metaKabupaten = null;
        $metaProvinsi = null;
        $scanLimit = min(15, count($rawRows));
        for ($i = 0; $i < $scanLimit; $i++) {
            $rowStr = implode(' ', array_map('strval', $rawRows[$i] ?? []));
            if (!$metaKabupaten && preg_match('/Kabupaten\s+([^,]+)/i', $rowStr, $m)) {
                $metaKabupaten = trim($m[1]);
            }
            if (!$metaProvinsi && preg_match('/Provinsi\s+([^,]+)/i', $rowStr, $m)) {
                $metaProvinsi = trim($m[1]);
            }
        }

        // 2. Deteksi Baris Header Utama secara Dinamis
        $headerRowIdx = null;
        for ($i = 0; $i < $scanLimit; $i++) {
            $row = $rawRows[$i] ?? [];
            $joined = strtolower(implode(' ', array_map('strval', $row)));
            if ((strpos($joined, 'nisn') !== false || strpos($joined, 'nipd') !== false) && 
                (strpos($joined, 'nama') !== false || strpos($joined, 'peserta didik') !== false)) {
                $headerRowIdx = $i;
                break;
            }
        }

        if ($headerRowIdx === null) {
            // Fallback baris 0
            $headerRowIdx = 0;
        }

        $mainHeaders = $rawRows[$headerRowIdx] ?? [];
        $nextRow = $rawRows[$headerRowIdx + 1] ?? [];

        // 3. Deteksi apakah baris setelahnya adalah Subheader (seperti kolom Data Ayah/Ibu/Wali di Dapodik)
        $isSubHeader = false;
        $subCount = 0;
        foreach ($nextRow as $val) {
            $v = strtolower(trim((string)$val));
            if (in_array($v, ['tahun lahir', 'jenjang pendidikan', 'pekerjaan', 'penghasilan', 'nik', 'nama'], true)) {
                $subCount++;
            }
        }
        if ($subCount >= 3) {
            $isSubHeader = true;
        }

        // 4. Susun Header Final
        $finalHeaders = [];
        $currentGroup = '';
        $maxCols = max(count($mainHeaders), count($nextRow));

        for ($col = 0; $col < $maxCols; $col++) {
            $mainVal = trim((string)($mainHeaders[$col] ?? ''));
            if ($mainVal !== '') {
                $currentGroup = $mainVal;
            }

            $subVal = $isSubHeader ? trim((string)($nextRow[$col] ?? '')) : '';

            if ($isSubHeader && $subVal !== '') {
                $finalHeaders[$col] = $currentGroup . ' ' . $subVal;
            } else {
                $finalHeaders[$col] = $mainVal !== '' ? $mainVal : $subVal;
            }
        }

        $headerMap = self::mapHeaders($finalHeaders);

        if (!isset($headerMap['nisn']) || !isset($headerMap['nama_siswa'])) {
            throw new \Exception("Format kolom tidak valid! Kolom 'NISN' dan 'Nama Siswa / Peserta Didik' wajib ditemukan dalam berkas Excel.");
        }

        $dataStartRow = $isSubHeader ? ($headerRowIdx + 2) : ($headerRowIdx + 1);
        $parsedRows = [];

        for ($r = $dataStartRow; $r < count($rawRows); $r++) {
            $rowVals = $rawRows[$r] ?? [];
            if (empty($rowVals)) continue;

            $rowData = [];
            foreach ($headerMap as $field => $colIndex) {
                $val = isset($rowVals[$colIndex]) ? trim((string)$rowVals[$colIndex]) : '';
                $rowData[$field] = $val !== '' ? $val : null;
            }

            // Gabungkan dusun dan kelurahan jika ada kolom terpisah
            $dusun = !empty($headerMap['dusun']) ? trim((string)($rowVals[$headerMap['dusun']] ?? '')) : '';
            $kelurahan = !empty($headerMap['kelurahan']) ? trim((string)($rowVals[$headerMap['kelurahan']] ?? '')) : '';
            if (!empty($dusun) || !empty($kelurahan)) {
                if (!empty($dusun) && !empty($kelurahan) && strcasecmp($dusun, $kelurahan) !== 0) {
                    $rowData['dusun_kelurahan'] = "Dusun " . $dusun . ", " . $kelurahan;
                } else {
                    $rowData['dusun_kelurahan'] = !empty($kelurahan) ? $kelurahan : $dusun;
                }
            }

            // Normalisasi Tanggal
            if (!empty($rowData['tanggal_lahir'])) {
                $rowData['tanggal_lahir'] = self::normalizeDate($rowData['tanggal_lahir']);
            }
            if (!empty($rowData['tanggal_masuk'])) {
                $rowData['tanggal_masuk'] = self::normalizeDate($rowData['tanggal_masuk']);
            }

            // Lengkapi Kabupaten & Provinsi dari metadata jika baris siswa kosong
            if (empty($rowData['kabupaten_kota']) && $metaKabupaten) {
                $rowData['kabupaten_kota'] = $metaKabupaten;
            }
            if (empty($rowData['provinsi']) && $metaProvinsi) {
                $rowData['provinsi'] = $metaProvinsi;
            }

            // Normalisasi Jenis Kelamin L/P
            if (!empty($rowData['jenis_kelamin'])) {
                $jk = strtoupper(trim((string)$rowData['jenis_kelamin']));
                $rowData['jenis_kelamin'] = (str_starts_with($jk, 'P') || str_starts_with($jk, 'W')) ? 'P' : 'L';
            } else {
                $rowData['jenis_kelamin'] = 'L';
            }

            // NISN wajib ada dan valid (pertahankan digit nol di depan)
            if (!empty($rowData['nisn']) && !empty($rowData['nama_siswa'])) {
                $cleanedNisn = preg_replace('/[^0-9]/', '', (string)$rowData['nisn']);
                if (strlen($cleanedNisn) >= 5) {
                    $rowData['nisn'] = $cleanedNisn;
                    $parsedRows[] = $rowData;
                }
            }
        }

        return $parsedRows;
    }

    /**
     * Fallback Parse CSV / TSV
     */
    public static function parseCsv(string $filePath): array
    {
        return self::parseFile($filePath, 'file.csv');
    }

    /**
     * Memetakan nama kolom Dapodik ke atribut internal sistem
     */
    private static function mapHeaders(array $rawHeaders): array
    {
        $map = [];
        foreach ($rawHeaders as $idx => $header) {
            $cleaned = strtolower(trim((string)$header));
            $cleaned = preg_replace('/[^a-z0-9]+/i', '_', $cleaned);
            $cleaned = trim($cleaned, '_');

            foreach (self::DAPODIK_COLUMNS as $field => $synonyms) {
                if (in_array($cleaned, $synonyms, true)) {
                    if (!isset($map[$field])) {
                        $map[$field] = $idx;
                    }
                    break;
                }
            }
        }
        return $map;
    }

    /**
     * Normalisasi format tanggal ke format database YYYY-MM-DD
     */
    public static function normalizeDate(string $dateStr): ?string
    {
        $dateStr = trim($dateStr);
        if (empty($dateStr)) return null;

        // Cek jika sudah YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        // Cek format DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $dateStr, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }

        // Cek strtotime
        $timestamp = strtotime($dateStr);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Daftar Kolom Template Standar Dapodik
     */
    public static function getStandardTemplateHeaders(): array
    {
        return [
            'NISN',
            'Nama Peserta Didik',
            'Rombel / Kelas',
            'Jenis Kelamin (L/P)',
            'NIS / NIPD',
            'NIK Siswa',
            'No KK',
            'No Akta Lahir',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Agama',
            'Alamat Jalan',
            'RT',
            'RW',
            'Dusun / Kelurahan',
            'Kecamatan',
            'Kabupaten / Kota',
            'Provinsi',
            'Kode Pos',
            'Tinggal Bersama',
            'Moda Transportasi',
            'Nama Ayah',
            'NIK Ayah',
            'Tahun Lahir Ayah',
            'Pendidikan Ayah',
            'Pekerjaan Ayah',
            'Penghasilan Ayah',
            'Nama Ibu',
            'NIK Ibu',
            'Tahun Lahir Ibu',
            'Pendidikan Ibu',
            'Pekerjaan Ibu',
            'Penghasilan Ibu',
            'Nama Wali',
            'No HP / WA Ortu',
            'Sekolah Asal',
            'No Ijazah SMP',
            'Tanggal Masuk (YYYY-MM-DD)'
        ];
    }

    /**
     * Contoh Data Dummy Realistis untuk Template Excel
     */
    public static function getSampleTemplateRows(): array
    {
        return [
            [
                '0081234567',
                'Muhammad Fauzan Al-Farizi',
                'X PPLG 1',
                'L',
                'AF-202601',
                '3204123456780001',
                '3204123456780002',
                'AL-00123/2008',
                'Bandung',
                '2008-04-12',
                'Islam',
                'Jl. Terusan Buah Batu No. 45',
                '03',
                '07',
                'Bojongsoang',
                'Bojongsoang',
                'Kab. Bandung',
                'Jawa Barat',
                '40287',
                'Orang Tua',
                'Sepeda Motor',
                'Ahmad Kurniawan',
                '3204120101750001',
                '1975',
                'S1',
                'Wiraswasta',
                'Rp. 3.000.000 - Rp. 5.000.000',
                'Siti Rahmawati',
                '3204120202780002',
                '1978',
                'SMA/Sederajat',
                'Ibu Rumah Tangga',
                'Tidak Berpenghasilan',
                '',
                '081234567890',
                'SMP Negeri 1 Bojongsoang',
                'DN-02/DI-06/1234567',
                '2025-07-15'
            ],
            [
                '0087654321',
                'Alya Nabila Putri',
                'X PPLG 1',
                'P',
                'AF-202602',
                '3204123456780003',
                '3204123456780004',
                'AL-00124/2008',
                'Bandung',
                '2008-09-21',
                'Islam',
                'Jl. Raya Bojongsoang No. 112',
                '01',
                '05',
                'Cipagalo',
                'Bojongsoang',
                'Kab. Bandung',
                'Jawa Barat',
                '40287',
                'Orang Tua',
                'Sepeda Motor',
                'Budi Hartono',
                '3204120303720003',
                '1972',
                'D3',
                'Karyawan Swasta',
                'Rp. 3.000.000 - Rp. 5.000.000',
                'Nurul Hidayah',
                '3204120404760004',
                '1976',
                'S1',
                'Guru',
                'Rp. 2.000.000 - Rp. 3.000.000',
                '',
                '085678901234',
                'SMP Negeri 2 Dayeuhkolot',
                'DN-02/DI-06/1234568',
                '2025-07-15'
            ]
        ];
    }

    /**
     * Generate Berkas Excel (.xlsx) Template Standar Dapodik untuk diunduh
     */
    public static function getExcelTemplateContent(): string
    {
        $headers = self::getStandardTemplateHeaders();
        $rows = self::getSampleTemplateRows();

        return ExcelHelper::createXlsx("Data Pokok Siswa Dapodik", $headers, $rows);
    }

    /**
     * Generate String CSV Template Standar Dapodik (Fallback)
     */
    public static function getCsvTemplateContent(): string
    {
        $headers = self::getStandardTemplateHeaders();
        $rows = self::getSampleTemplateRows();

        $out = fopen('php://memory', 'r+');
        fputcsv($out, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($out, $row, ';');
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv;
    }
}

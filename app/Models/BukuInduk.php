<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class BukuInduk
{
    public static function getBySiswaId(int $siswaId): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT s.id, s.id as siswa_id, s.nama_siswa, s.nama_siswa as nama_lengkap, s.nisn, s.barcode_code, s.barcode_code as barcode_id, s.kelas_id, s.jenis_kelamin,
                   k.nama_kelas, k.tingkat, k.jurusan,
                   b.nis, b.nik, b.no_kk, b.no_akta_lahir, b.tempat_lahir, b.tanggal_lahir, b.agama, b.kewarganegaraan,
                   b.anak_ke, b.jumlah_saudara, b.alamat_jalan, b.rt, b.rw, b.dusun_kelurahan, b.kecamatan, b.kabupaten_kota,
                   b.provinsi, b.kode_pos, b.tinggal_bersama, b.transportasi, b.nama_ayah, b.nik_ayah, b.tahun_lahir_ayah,
                   b.pendidikan_ayah, b.pekerjaan_ayah, b.penghasilan_ayah, b.nama_ibu, b.nik_ibu, b.tahun_lahir_ibu,
                   b.pendidikan_ibu, b.pekerjaan_ibu, b.penghasilan_ibu, b.nama_wali, b.no_hp_ortu, b.sekolah_asal,
                   b.no_ijazah_smp, b.no_skhun_smp, b.tanggal_masuk, COALESCE(b.status_siswa, 'AKTIF') as status_siswa
            FROM `siswa` s
            LEFT JOIN `buku_induk_siswa` b ON s.id = b.siswa_id
            JOIN `kelas` k ON s.kelas_id = k.id
            WHERE s.id = ?
        ");
        $stmt->execute([$siswaId]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getAllWithSiswa(?int $kelasId = null, string $search = '', int $limit = 50, int $offset = 0): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT s.id, s.id as siswa_id, s.nama_siswa, s.nama_siswa as nama_lengkap, s.nisn, s.barcode_code, s.jenis_kelamin,
                   k.nama_kelas, k.tingkat, k.jurusan,
                   b.nis, b.nik, b.tempat_lahir, b.tanggal_lahir, b.alamat_jalan, b.nama_ayah, b.nama_ibu,
                   COALESCE(b.status_siswa, 'AKTIF') as status_siswa
            FROM `siswa` s
            LEFT JOIN `buku_induk_siswa` b ON s.id = b.siswa_id
            JOIN `kelas` k ON s.kelas_id = k.id
            WHERE 1=1
        ";
        $params = [];

        if ($kelasId) {
            $sql .= " AND s.kelas_id = ?";
            $params[] = $kelasId;
        }

        if (!empty($search)) {
            $sql .= " AND (s.nama_siswa LIKE ? OR s.nisn LIKE ? OR b.nis LIKE ? OR b.nik LIKE ?)";
            $wildcard = "%{$search}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        $sql .= " ORDER BY k.tingkat ASC, k.nama_kelas ASC, s.nama_siswa ASC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countAllWithSiswa(?int $kelasId = null, string $search = ''): int
    {
        $db = Database::getConnection();
        $sql = "
            SELECT COUNT(*)
            FROM `siswa` s
            LEFT JOIN `buku_induk_siswa` b ON s.id = b.siswa_id
            JOIN `kelas` k ON s.kelas_id = k.id
            WHERE 1=1
        ";
        $params = [];

        if ($kelasId) {
            $sql .= " AND s.kelas_id = ?";
            $params[] = $kelasId;
        }

        if (!empty($search)) {
            $sql .= " AND (s.nama_siswa LIKE ? OR s.nisn LIKE ? OR b.nis LIKE ? OR b.nik LIKE ?)";
            $wildcard = "%{$search}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function getAllAlumni(string $search = '', int $limit = 50, int $offset = 0): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT s.id, s.id as siswa_id, s.nama_siswa, s.nama_siswa as nama_lengkap, s.nisn, s.barcode_code, s.jenis_kelamin,
                   b.nis, b.nik, b.tempat_lahir, b.tanggal_lahir, b.alamat_jalan, b.nama_ayah, b.nama_ibu,
                   COALESCE(b.status_siswa, 'AKTIF') as status_siswa
            FROM `siswa` s
            LEFT JOIN `buku_induk_siswa` b ON s.id = b.siswa_id
            WHERE s.kelas_id IS NULL OR COALESCE(b.status_siswa, 'AKTIF') != 'AKTIF'
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (s.nama_siswa LIKE ? OR s.nisn LIKE ? OR b.nis LIKE ? OR b.nik LIKE ?)";
            $wildcard = "%{$search}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        $sql .= " ORDER BY s.nama_siswa ASC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countAllAlumni(string $search = ''): int
    {
        $db = Database::getConnection();
        $sql = "
            SELECT COUNT(*)
            FROM `siswa` s
            LEFT JOIN `buku_induk_siswa` b ON s.id = b.siswa_id
            WHERE s.kelas_id IS NULL OR COALESCE(b.status_siswa, 'AKTIF') != 'AKTIF'
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (s.nama_siswa LIKE ? OR s.nisn LIKE ? OR b.nis LIKE ? OR b.nik LIKE ?)";
            $wildcard = "%{$search}%";
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
            $params[] = $wildcard;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function saveOrUpdate(int $siswaId, array $data): bool
    {
        $db = Database::getConnection();

        // Cek apakah sudah ada di buku_induk_siswa
        $exists = $db->prepare("SELECT id FROM `buku_induk_siswa` WHERE `siswa_id` = ?");
        $exists->execute([$siswaId]);
        $row = $exists->fetch();

        $fields = [
            'nis', 'nik', 'no_kk', 'no_akta_lahir', 'tempat_lahir', 'tanggal_lahir',
            'agama', 'kewarganegaraan', 'anak_ke', 'jumlah_saudara',
            'alamat_jalan', 'rt', 'rw', 'dusun_kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi', 'kode_pos',
            'tinggal_bersama', 'transportasi',
            'nama_ayah', 'nik_ayah', 'tahun_lahir_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
            'nama_ibu', 'nik_ibu', 'tahun_lahir_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
            'nama_wali', 'nik_wali', 'pekerjaan_wali', 'no_hp_ortu',
            'sekolah_asal', 'no_ijazah_smp', 'no_skhun_smp', 'tanggal_masuk', 'status_siswa'
        ];

        if (empty($data['status_siswa'])) {
            $data['status_siswa'] = 'AKTIF';
        }
        if (isset($data['anak_ke']) && $data['anak_ke'] === '') {
            $data['anak_ke'] = 1;
        }
        if (isset($data['jumlah_saudara']) && $data['jumlah_saudara'] === '') {
            $data['jumlah_saudara'] = 0;
        }

        if ($row) {
            // UPDATE
            $sets = [];
            $vals = [];
            foreach ($fields as $f) {
                if (array_key_exists($f, $data)) {
                    $sets[] = "`$f` = ?";
                    $vals[] = $data[$f];
                }
            }
            if (empty($sets)) return true;

            $vals[] = $siswaId;
            $sql = "UPDATE `buku_induk_siswa` SET " . implode(', ', $sets) . " WHERE `siswa_id` = ?";
            $stmt = $db->prepare($sql);
            return $stmt->execute($vals);
        } else {
            // INSERT
            $cols = ['siswa_id'];
            $placeholders = ['?'];
            $vals = [$siswaId];

            foreach ($fields as $f) {
                $cols[] = "`$f`";
                $placeholders[] = '?';
                $val = $data[$f] ?? null;
                if ($f === 'status_siswa' && empty($val)) {
                    $val = 'AKTIF';
                }
                $vals[] = $val;
            }

            $sql = "INSERT INTO `buku_induk_siswa` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $db->prepare($sql);
            return $stmt->execute($vals);
        }
    }

    /**
     * Bulk Import / Upsert dari Baris Dapodik yang telah di-parse
     */
    public static function bulkUpsertDapodik(array $dapodikRows, ?int $activeTapelId = null): array
    {
        $db = Database::getConnection();
        $stats = ['inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

        // Cache kelas
        $allKelas = $db->query("SELECT id, nama_kelas FROM `kelas`")->fetchAll();
        $kelasMap = [];
        foreach ($allKelas as $k) {
            $kelasMap[strtoupper(trim($k['nama_kelas']))] = (int)$k['id'];
        }

        $stmtFindSiswa = $db->prepare("SELECT id, kelas_id FROM `siswa` WHERE `nisn` = ? LIMIT 1");
        $stmtInsertSiswa = $db->prepare("
            INSERT INTO `siswa` (`nisn`, `barcode_code`, `nama_siswa`, `kelas_id`, `jenis_kelamin`)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtUpdateSiswa = $db->prepare("
            UPDATE `siswa` SET `nama_siswa` = ?, `kelas_id` = ?, `jenis_kelamin` = ? WHERE `id` = ?
        ");

        foreach ($dapodikRows as $idx => $row) {
            $lineNo = $idx + 2;
            $nisn = trim((string)($row['nisn'] ?? ''));
            $nama = trim((string)($row['nama_siswa'] ?? ''));
            $namaKelas = trim((string)($row['nama_kelas'] ?? ''));
            $jk = ($row['jenis_kelamin'] ?? 'L') === 'P' ? 'P' : 'L';

            if (empty($nisn) || empty($nama)) {
                $stats['failed']++;
                $stats['errors'][] = "Baris #{$lineNo}: NISN dan Nama Siswa wajib diisi.";
                continue;
            }

            // Cari atau buat Kelas
            $kelasId = null;
            if (!empty($namaKelas)) {
                $keyK = strtoupper($namaKelas);
                if (isset($kelasMap[$keyK])) {
                    $kelasId = $kelasMap[$keyK];
                } else {
                    // Deteksi tingkat (X, XI, XII atau 10, 11, 12)
                    $tingkat = 'X';
                    if (str_starts_with($keyK, 'XII') || str_starts_with($keyK, '12')) $tingkat = 'XII';
                    elseif (str_starts_with($keyK, 'XI') || str_starts_with($keyK, '11')) $tingkat = 'XI';
                    elseif (str_starts_with($keyK, 'X') || str_starts_with($keyK, '10')) $tingkat = 'X';

                    $jurusan = 'Pengembangan Perangkat Lunak dan GIM (PPLG)';
                    if (str_contains($keyK, 'KENDARAAN') || str_contains($keyK, 'TKR') || str_contains($keyK, 'OTOMOTIF')) {
                        $jurusan = 'Teknik Kendaraan Ringan (TKR)';
                    } elseif (str_contains($keyK, 'KOMPUTER') || str_contains($keyK, 'JARINGAN') || str_contains($keyK, 'TKJ') || str_contains($keyK, 'TJKT')) {
                        $jurusan = 'Teknik Jaringan Komputer dan Telekomunikasi (TJKT)';
                    } elseif (str_contains($keyK, 'AKUNTANSI') || str_contains($keyK, 'AKL') || str_contains($keyK, 'AK')) {
                        $jurusan = 'Akuntansi dan Keuangan Lembaga (AKL)';
                    }

                    $stmtCreateK = $db->prepare("INSERT INTO `kelas` (`nama_kelas`, `tingkat`, `jurusan`) VALUES (?, ?, ?)");
                    $stmtCreateK->execute([$namaKelas, $tingkat, $jurusan]);
                    $kelasId = (int)$db->lastInsertId();
                    $kelasMap[$keyK] = $kelasId;
                }
            } else {
                // Default ke kelas pertama yang ada
                $kelasId = reset($kelasMap) ?: 1;
            }

            try {
                $stmtFindSiswa->execute([$nisn]);
                $existing = $stmtFindSiswa->fetch();

                if ($existing) {
                    $siswaId = (int)$existing['id'];
                    $stmtUpdateSiswa->execute([$nama, $kelasId, $jk, $siswaId]);
                    $stats['updated']++;
                } else {
                    $barcode = 'ALF-' . $nisn;
                    $stmtInsertSiswa->execute([$nisn, $barcode, $nama, $kelasId, $jk]);
                    $siswaId = (int)$db->lastInsertId();
                    $stats['inserted']++;

                    // Catat ke riwayat kelas siswa awal
                    if ($activeTapelId) {
                        RiwayatKelas::record($siswaId, $kelasId, $activeTapelId, 'BARU', 'Import Dapodik');
                    }
                }

                // Simpan atribut lengkap Buku Induk
                self::saveOrUpdate($siswaId, $row);

            } catch (\Throwable $e) {
                $stats['failed']++;
                $stats['errors'][] = "Baris #{$lineNo} ({$nama}): " . $e->getMessage();
            }
        }

        return $stats;
    }
}

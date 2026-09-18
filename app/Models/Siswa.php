<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Siswa
{
    public static function getAll(?int $kelasId = null): array
    {
        $db = Database::getConnection();
        if ($kelasId) {
            $stmt = $db->prepare("SELECT s.*, k.nama_kelas, k.tingkat, k.jurusan, g.nama_lengkap as nama_wali_kelas,
                                  COALESCE(b.tanggal_lahir, s.tanggal_lahir) as tanggal_lahir 
                                  FROM siswa s 
                                  JOIN kelas k ON k.id = s.kelas_id 
                                  LEFT JOIN guru g ON k.wali_kelas_guru_id = g.id
                                  LEFT JOIN buku_induk_siswa b ON b.siswa_id = s.id
                                  WHERE s.kelas_id = :kid 
                                  ORDER BY s.nama_siswa ASC");
            $stmt->execute([':kid' => $kelasId]);
        } else {
            $stmt = $db->query("SELECT s.*, k.nama_kelas, k.tingkat, k.jurusan, g.nama_lengkap as nama_wali_kelas,
                                COALESCE(b.tanggal_lahir, s.tanggal_lahir) as tanggal_lahir 
                                FROM siswa s 
                                JOIN kelas k ON k.id = s.kelas_id 
                                LEFT JOIN guru g ON k.wali_kelas_guru_id = g.id
                                LEFT JOIN buku_induk_siswa b ON b.siswa_id = s.id
                                ORDER BY k.tingkat ASC, k.nama_kelas ASC, s.nama_siswa ASC");
        }
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT s.*, k.nama_kelas, k.tingkat, k.jurusan, g.nama_lengkap as nama_wali_kelas,
                              COALESCE(b.tanggal_lahir, s.tanggal_lahir) as tanggal_lahir 
                              FROM siswa s 
                              JOIN kelas k ON k.id = s.kelas_id 
                              LEFT JOIN guru g ON k.wali_kelas_guru_id = g.id
                              LEFT JOIN buku_induk_siswa b ON b.siswa_id = s.id
                              WHERE s.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByBarcode(string $barcode): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT s.*, k.nama_kelas, k.tingkat, k.jurusan 
                              FROM siswa s 
                              JOIN kelas k ON k.id = s.kelas_id 
                              WHERE s.barcode_code = :bc OR s.nisn = :nisn LIMIT 1");
        $stmt->execute([':bc' => trim($barcode), ':nisn' => trim($barcode)]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findByNisnAndTanggalLahir(string $nisn, string $tanggalLahir): ?array
    {
        $db = Database::getConnection();
        $nisn = trim($nisn);
        $tgl1 = trim($tanggalLahir);
        $tgl2 = $tgl1;
        $timestamp = strtotime(str_replace('/', '-', $tgl1));
        if ($timestamp !== false) {
            $tgl2 = date('Y-m-d', $timestamp);
        }

        $stmt = $db->prepare("SELECT s.*, k.nama_kelas, k.tingkat, k.jurusan,
                                     COALESCE(b.tanggal_lahir, s.tanggal_lahir) as tanggal_lahir
                              FROM siswa s 
                              LEFT JOIN kelas k ON k.id = s.kelas_id 
                              LEFT JOIN buku_induk_siswa b ON b.siswa_id = s.id
                              WHERE s.nisn = :nisn 
                              AND (COALESCE(b.tanggal_lahir, s.tanggal_lahir) = :tgl1 OR COALESCE(b.tanggal_lahir, s.tanggal_lahir) = :tgl2) 
                              LIMIT 1");
        $stmt->execute([':nisn' => $nisn, ':tgl1' => $tgl1, ':tgl2' => $tgl2]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $barcode = $data['barcode_code'] ?? ('ALF-' . $data['nisn']);
        $foto = $data['foto'] ?? ($data['jenis_kelamin'] === 'P' ? 'uploads/siswa/default_f.png' : 'uploads/siswa/default_m.png');

        $sql = "INSERT INTO siswa (nisn, barcode_code, nama_siswa, kelas_id, jenis_kelamin, foto, tanggal_lahir, no_hp_ortu)
                VALUES (:nisn, :bc, :nama, :kelas_id, :jk, :foto, :tgl_lahir, :no_hp)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nisn'      => $data['nisn'],
            ':bc'        => $barcode,
            ':nama'      => $data['nama_siswa'],
            ':kelas_id'  => (int)$data['kelas_id'],
            ':jk'        => $data['jenis_kelamin'] ?? 'L',
            ':foto'      => $foto,
            ':tgl_lahir' => $data['tanggal_lahir'] ?? null,
            ':no_hp'     => $data['no_hp_ortu'] ?? null,
        ]);
        $siswaId = (int)$db->lastInsertId();

        // Otomatis inisialisasi Buku Induk Siswa & Riwayat Kelas Awal
        try {
            $stmtInduk = $db->prepare("INSERT IGNORE INTO `buku_induk_siswa` (`siswa_id`, `tanggal_masuk`, `status_siswa`) VALUES (?, ?, 'AKTIF')");
            $stmtInduk->execute([$siswaId, date('Y-m-d')]);

            $activeTapel = TahunPelajaran::getActive();
            if ($activeTapel) {
                RiwayatKelas::record($siswaId, (int)$data['kelas_id'], (int)$activeTapel['id'], 'BARU', 'Pendaftaran Siswa Baru');
            }
        } catch (\Throwable $e) {
            // Non-blocking fallback
        }

        return $siswaId;
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $sql = "UPDATE siswa SET
                nisn          = :nisn,
                barcode_code  = :bc,
                nama_siswa    = :nama,
                kelas_id      = :kelas_id,
                jenis_kelamin = :jk,
                tanggal_lahir = :tgl_lahir,
                no_hp_ortu    = :no_hp";

        $params = [
            ':id'        => $id,
            ':nisn'      => $data['nisn'],
            ':bc'        => $data['barcode_code'],
            ':nama'      => $data['nama_siswa'],
            ':kelas_id'  => (int)$data['kelas_id'],
            ':jk'        => $data['jenis_kelamin'],
            ':tgl_lahir' => $data['tanggal_lahir'] ?? null,
            ':no_hp'     => $data['no_hp_ortu'] ?? null,
        ];

        if (!empty($data['foto'])) {
            $sql .= ", foto = :foto";
            $params[':foto'] = $data['foto'];
        }

        $sql .= " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM siswa WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Memasukkan / Memindahkan Siswa Secara Massal ke Suatu Kelas Berdasarkan Ceklis
     */
    public static function bulkAssignKelas(array $siswaIds, int $kelasId, ?int $tapelId = null): int
    {
        if (empty($siswaIds) || $kelasId <= 0) {
            return 0;
        }

        $db = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($siswaIds), '?'));
        
        $sql = "UPDATE siswa SET kelas_id = ? WHERE id IN ($placeholders)";
        $params = array_merge([$kelasId], $siswaIds);
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $affected = $stmt->rowCount();

        // Catat ke riwayat kelas siswa untuk tahun pelajaran aktif
        if ($tapelId) {
            foreach ($siswaIds as $sid) {
                RiwayatKelas::record((int)$sid, $kelasId, $tapelId, 'MUTASI', 'Penempatan Rombel Ceklis');
            }
        }

        return $affected;
    }
}

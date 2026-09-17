<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class JadwalPelajaran
{
    public static function getAll(?string $hari = null, ?int $guruId = null, ?int $kelasId = null): array
    {
        $db = Database::getConnection();
        $conditions = [];
        $params = [];

        if ($hari) {
            $conditions[] = "j.hari = :hari";
            $params[':hari'] = $hari;
        }
        if ($guruId) {
            $conditions[] = "j.guru_id = :guru_id";
            $params[':guru_id'] = $guruId;
        }
        if ($kelasId) {
            $conditions[] = "j.kelas_id = :kelas_id";
            $params[':kelas_id'] = $kelasId;
        }

        $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT j.*, m.kode_mapel, m.kelompok as kelompok_mapel, k.nama_kelas, k.tingkat, k.jurusan, g.nama_lengkap as nama_guru, g.nik_nip 
                FROM jadwal_pelajaran j 
                JOIN kelas k ON k.id = j.kelas_id 
                JOIN guru g ON g.id = j.guru_id 
                LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
                {$where}
                ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $sql = "SELECT j.*, m.kode_mapel, m.kelompok as kelompok_mapel, k.nama_kelas, k.tingkat, k.jurusan, g.nama_lengkap as nama_guru, g.nik_nip 
                FROM jadwal_pelajaran j 
                JOIN kelas k ON k.id = j.kelas_id 
                JOIN guru g ON g.id = j.guru_id 
                LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
                WHERE j.id = :id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getTodayByGuru(int $guruId, string $hari): array
    {
        $db = Database::getConnection();
        $sql = "SELECT j.*, m.kode_mapel, k.nama_kelas, k.tingkat, k.jurusan 
                FROM jadwal_pelajaran j 
                JOIN kelas k ON k.id = j.kelas_id 
                LEFT JOIN mata_pelajaran m ON j.mapel_id = m.id
                WHERE j.guru_id = :guru_id AND j.hari = :hari 
                ORDER BY j.jam_mulai ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':guru_id' => $guruId, ':hari' => $hari]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $mapelId = !empty($data['mapel_id']) ? (int)$data['mapel_id'] : null;
        $namaMapel = trim($data['nama_mapel'] ?? '');

        if ($mapelId && empty($namaMapel)) {
            $m = MataPelajaran::findById($mapelId);
            if ($m) $namaMapel = $m['nama_mapel'];
        }

        $sql = "INSERT INTO jadwal_pelajaran (hari, kelas_id, guru_id, mapel_id, nama_mapel, jumlah_jp, jam_mulai, jam_selesai) 
                VALUES (:hari, :kelas_id, :guru_id, :mapel_id, :mapel, :jp, :mulai, :selesai)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':hari' => $data['hari'],
            ':kelas_id' => (int)$data['kelas_id'],
            ':guru_id' => (int)$data['guru_id'],
            ':mapel_id' => $mapelId,
            ':mapel' => $namaMapel,
            ':jp' => (int)$data['jumlah_jp'],
            ':mulai' => $data['jam_mulai'],
            ':selesai' => $data['jam_selesai'],
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $mapelId = !empty($data['mapel_id']) ? (int)$data['mapel_id'] : null;
        $namaMapel = trim($data['nama_mapel'] ?? '');

        if ($mapelId && empty($namaMapel)) {
            $m = MataPelajaran::findById($mapelId);
            if ($m) $namaMapel = $m['nama_mapel'];
        }

        $sql = "UPDATE jadwal_pelajaran SET 
                hari = :hari, 
                kelas_id = :kelas_id, 
                guru_id = :guru_id, 
                mapel_id = :mapel_id,
                nama_mapel = :mapel, 
                jumlah_jp = :jp, 
                jam_mulai = :mulai, 
                jam_selesai = :selesai 
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':hari' => $data['hari'],
            ':kelas_id' => (int)$data['kelas_id'],
            ':guru_id' => (int)$data['guru_id'],
            ':mapel_id' => $mapelId,
            ':mapel' => $namaMapel,
            ':jp' => (int)$data['jumlah_jp'],
            ':mulai' => $data['jam_mulai'],
            ':selesai' => $data['jam_selesai'],
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM jadwal_pelajaran WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Memeriksa bentrok jadwal (Anti-Bentrok)
     * Mengembalikan array informasi bentrok jika terdeteksi, atau null jika aman.
     */
    public static function checkConflict(
        string $hari,
        int $kelasId,
        int $guruId,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeId = null
    ): ?array {
        $db = Database::getConnection();

        $excludeSql = $excludeId ? " AND j.id != :exclude_id" : "";

        // 1. Cek Bentrok Kelas: Kelas yang sama tidak boleh ada 2 mapel bersamaan di hari yang sama
        $sqlKelas = "SELECT j.*, m.nama_mapel, k.nama_kelas, g.nama_lengkap as nama_guru
                     FROM jadwal_pelajaran j
                     JOIN kelas k ON k.id = j.kelas_id
                     JOIN guru g ON g.id = j.guru_id
                     LEFT JOIN mata_pelajaran m ON m.id = j.mapel_id
                     WHERE j.hari = :hari 
                       AND j.kelas_id = :kelas_id
                       {$excludeSql}
                       AND (j.jam_mulai < :jam_selesai AND j.jam_selesai > :jam_mulai)
                     LIMIT 1";
        $stmtKelas = $db->prepare($sqlKelas);
        $paramsKelas = [
            ':hari' => $hari,
            ':kelas_id' => $kelasId,
            ':jam_mulai' => $jamMulai,
            ':jam_selesai' => $jamSelesai
        ];
        if ($excludeId) {
            $paramsKelas[':exclude_id'] = $excludeId;
        }
        $stmtKelas->execute($paramsKelas);
        $clashKelas = $stmtKelas->fetch(PDO::FETCH_ASSOC);

        if ($clashKelas) {
            $mulai = substr($clashKelas['jam_mulai'], 0, 5);
            $selesai = substr($clashKelas['jam_selesai'], 0, 5);
            return [
                'has_conflict' => true,
                'type' => 'KELAS',
                'title' => 'Bentrok Jadwal Kelas',
                'message' => "Kelas {$clashKelas['nama_kelas']} sudah memiliki jadwal mata pelajaran '{$clashKelas['nama_mapel']}' bersama {$clashKelas['nama_guru']} pada hari {$hari} jam {$mulai} - {$selesai}.",
                'data' => $clashKelas
            ];
        }

        // 2. Cek Bentrok Guru: Guru yang sama tidak boleh mengajar di kelas manapun di jam yang sama
        $sqlGuru = "SELECT j.*, m.nama_mapel, k.nama_kelas, g.nama_lengkap as nama_guru
                    FROM jadwal_pelajaran j
                    JOIN kelas k ON k.id = j.kelas_id
                    JOIN guru g ON g.id = j.guru_id
                    LEFT JOIN mata_pelajaran m ON m.id = j.mapel_id
                    WHERE j.hari = :hari 
                      AND j.guru_id = :guru_id
                      {$excludeSql}
                      AND (j.jam_mulai < :jam_selesai AND j.jam_selesai > :jam_mulai)
                    LIMIT 1";
        $stmtGuru = $db->prepare($sqlGuru);
        $paramsGuru = [
            ':hari' => $hari,
            ':guru_id' => $guruId,
            ':jam_mulai' => $jamMulai,
            ':jam_selesai' => $jamSelesai
        ];
        if ($excludeId) {
            $paramsGuru[':exclude_id'] = $excludeId;
        }
        $stmtGuru->execute($paramsGuru);
        $clashGuru = $stmtGuru->fetch(PDO::FETCH_ASSOC);

        if ($clashGuru) {
            $mulai = substr($clashGuru['jam_mulai'], 0, 5);
            $selesai = substr($clashGuru['jam_selesai'], 0, 5);
            return [
                'has_conflict' => true,
                'type' => 'GURU',
                'title' => 'Bentrok Jadwal Guru',
                'message' => "Guru {$clashGuru['nama_guru']} sudah memiliki jadwal mengajar di {$clashGuru['nama_kelas']} untuk mata pelajaran '{$clashGuru['nama_mapel']}' pada hari {$hari} jam {$mulai} - {$selesai}.",
                'data' => $clashGuru
            ];
        }

        return null;
    }

    public static function getByKelasGroupedByHari(int $kelasId): array
    {
        $db = Database::getConnection();
        $sql = "SELECT j.*, m.kode_mapel, m.kelompok as kelompok_mapel, g.nama_lengkap as nama_guru, k.nama_kelas
                FROM jadwal_pelajaran j
                JOIN kelas k ON k.id = j.kelas_id
                JOIN guru g ON g.id = j.guru_id
                LEFT JOIN mata_pelajaran m ON m.id = j.mapel_id
                WHERE j.kelas_id = ?
                ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$kelasId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $grouped = [];
        foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h) {
            $grouped[$h] = [];
        }
        foreach ($rows as $r) {
            $grouped[$r['hari']][] = $r;
        }
        return $grouped;
    }

    public static function getDistinctKelasMapelByGuru(int $guruId): array
    {
        $db = Database::getConnection();
        $sql = "SELECT DISTINCT j.kelas_id, j.nama_mapel, k.nama_kelas, k.tingkat, k.jurusan,
                COALESCE(j.mapel_id, (SELECT m.id FROM mata_pelajaran m WHERE LOWER(TRIM(m.nama_mapel)) = LOWER(TRIM(j.nama_mapel)) LIMIT 1)) as mapel_id
                FROM jadwal_pelajaran j
                JOIN kelas k ON k.id = j.kelas_id
                WHERE j.guru_id = :guru_id
                ORDER BY k.tingkat ASC, k.nama_kelas ASC, j.nama_mapel ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':guru_id' => $guruId]);
        $results = $stmt->fetchAll();

        foreach ($results as &$r) {
            if (empty($r['mapel_id'])) {
                $stmtM = $db->prepare("SELECT id FROM mata_pelajaran WHERE LOWER(nama_mapel) LIKE ? LIMIT 1");
                $firstWord = trim(explode(' ', $r['nama_mapel'])[0]);
                $stmtM->execute(['%' . strtolower($firstWord) . '%']);
                $found = $stmtM->fetch();
                $r['mapel_id'] = $found ? (int)$found['id'] : 1;
            } else {
                $r['mapel_id'] = (int)$r['mapel_id'];
            }
        }
        unset($r);

        return $results;
    }

    public static function getGuruKelasList(int $guruId): array
    {
        $db = Database::getConnection();
        $sql = "SELECT DISTINCT k.id, k.nama_kelas, k.tingkat, k.jurusan
                FROM jadwal_pelajaran j
                JOIN kelas k ON k.id = j.kelas_id
                WHERE j.guru_id = :guru_id
                ORDER BY k.tingkat ASC, k.nama_kelas ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':guru_id' => $guruId]);
        return $stmt->fetchAll();
    }

    public static function getGuruMapelList(int $guruId, ?int $kelasId = null): array
    {
        $db = Database::getConnection();
        $params = [':guru_id' => $guruId];
        $kelasFilter = "";
        if ($kelasId) {
            $kelasFilter = " AND j.kelas_id = :kelas_id";
            $params[':kelas_id'] = $kelasId;
        }

        $sql = "SELECT DISTINCT m.id, m.kode_mapel, m.nama_mapel, m.kelompok, m.tingkat
                FROM jadwal_pelajaran j
                JOIN mata_pelajaran m ON m.id = j.mapel_id
                WHERE j.guru_id = :guru_id {$kelasFilter}
                ORDER BY m.kelompok ASC, m.nama_mapel ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function isGuruTeaching(int $guruId, int $kelasId, int $mapelId): bool
    {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM jadwal_pelajaran 
                WHERE guru_id = :guru_id AND kelas_id = :kelas_id AND mapel_id = :mapel_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':guru_id' => $guruId,
            ':kelas_id' => $kelasId,
            ':mapel_id' => $mapelId
        ]);
        return ((int)$stmt->fetchColumn()) > 0;
    }
}

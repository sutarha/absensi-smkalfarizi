<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class KelasMapel
{
    /**
     * Ambil seluruh plotting mapel dan guru pengampu untuk suatu kelas
     */
    public static function getByKelas(int $kelasId): array
    {
        $db = Database::getConnection();
        $sql = "SELECT km.*, 
                       m.kode_mapel, m.nama_mapel, m.kelompok, m.tingkat,
                       g.nama_lengkap as nama_guru, g.nik_nip as nip_guru,
                       k.nama_kelas, k.tingkat as tingkat_kelas,
                       (SELECT COALESCE(SUM(j.jumlah_jp), 0) FROM jadwal_pelajaran j WHERE j.kelas_id = km.kelas_id AND j.mapel_id = km.mapel_id) as jp_terjadwal,
                       (SELECT COUNT(j.id) FROM jadwal_pelajaran j WHERE j.kelas_id = km.kelas_id AND j.mapel_id = km.mapel_id) as sesi_terjadwal
                FROM `kelas_mapel` km
                JOIN `mata_pelajaran` m ON m.id = km.mapel_id
                JOIN `guru` g ON g.id = km.guru_id
                JOIN `kelas` k ON k.id = km.kelas_id
                WHERE km.kelas_id = ?
                ORDER BY FIELD(m.kelompok, 'Umum', 'Kejuruan', 'Muatan Lokal', 'Pilihan'), m.nama_mapel ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$kelasId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $sql = "SELECT km.*, m.kode_mapel, m.nama_mapel, g.nama_lengkap as nama_guru, k.nama_kelas 
                FROM `kelas_mapel` km
                JOIN `mata_pelajaran` m ON m.id = km.mapel_id
                JOIN `guru` g ON g.id = km.guru_id
                JOIN `kelas` k ON k.id = km.kelas_id
                WHERE km.id = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Pasang / perbarui mapel dan guru pada suatu kelas
     */
    public static function assign(int $kelasId, int $mapelId, int $guruId, int $alokasiJp = 2): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `kelas_mapel` (`kelas_id`, `mapel_id`, `guru_id`, `alokasi_jp`) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `guru_id` = VALUES(`guru_id`), `alokasi_jp` = VALUES(`alokasi_jp`)
        ");
        $stmt->execute([$kelasId, $mapelId, $guruId, $alokasiJp]);
        
        $insertId = (int)$db->lastInsertId();
        if ($insertId > 0) return $insertId;

        // If updated, fetch existing id
        $stmtGet = $db->prepare("SELECT `id` FROM `kelas_mapel` WHERE `kelas_id` = ? AND `mapel_id` = ? LIMIT 1");
        $stmtGet->execute([$kelasId, $mapelId]);
        return (int)$stmtGet->fetchColumn();
    }

    /**
     * Hapus alokasi mapel dari kelas
     */
    public static function unassign(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM `kelas_mapel` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }
}

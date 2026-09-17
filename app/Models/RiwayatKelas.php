<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class RiwayatKelas
{
    public static function getBySiswa(int $siswaId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT rks.*, k.nama_kelas, k.tingkat, k.jurusan, tp.tahun_ajaran, tp.semester,
                   g.nama_lengkap as wali_kelas
            FROM `riwayat_kelas_siswa` rks
            JOIN `kelas` k ON rks.kelas_id = k.id
            LEFT JOIN `guru` g ON k.wali_kelas_guru_id = g.id
            JOIN `tahun_pelajaran` tp ON rks.tahun_pelajaran_id = tp.id
            WHERE rks.siswa_id = ?
            ORDER BY tp.tahun_ajaran ASC, tp.semester ASC
        ");
        $stmt->execute([$siswaId]);
        return $stmt->fetchAll();
    }

    public static function record(int $siswaId, int $kelasId, int $tapelId, string $statusKenaikan = 'BARU', ?string $catatan = null): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `riwayat_kelas_siswa` (`siswa_id`, `kelas_id`, `tahun_pelajaran_id`, `status_kenaikan`, `catatan`)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `kelas_id` = VALUES(`kelas_id`), `status_kenaikan` = VALUES(`status_kenaikan`), `catatan` = VALUES(`catatan`)
        ");
        return $stmt->execute([$siswaId, $kelasId, $tapelId, $statusKenaikan, $catatan]);
    }
}

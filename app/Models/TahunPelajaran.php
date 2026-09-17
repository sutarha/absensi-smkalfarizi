<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class TahunPelajaran
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM `tahun_pelajaran` ORDER BY `tahun_ajaran` DESC, `semester` ASC")->fetchAll();
    }

    public static function getActive(): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM `tahun_pelajaran` WHERE `is_aktif` = 1 LIMIT 1");
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `tahun_pelajaran` WHERE `id` = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function setActive(int $id): bool
    {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $db->exec("UPDATE `tahun_pelajaran` SET `is_aktif` = 0");
            $stmt = $db->prepare("UPDATE `tahun_pelajaran` SET `is_aktif` = 1 WHERE `id` = ?");
            $stmt->execute([$id]);
            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            return false;
        }
    }

    public static function create(string $tahunAjaran, string $semester, bool $isAktif = false, ?string $tglMulai = null, ?string $tglSelesai = null): int
    {
        $db = Database::getConnection();
        if ($isAktif) {
            $db->exec("UPDATE `tahun_pelajaran` SET `is_aktif` = 0");
        }

        $stmt = $db->prepare("
            INSERT INTO `tahun_pelajaran` (`tahun_ajaran`, `semester`, `is_aktif`, `tanggal_mulai`, `tanggal_selesai`)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `is_aktif` = VALUES(`is_aktif`), `tanggal_mulai` = VALUES(`tanggal_mulai`), `tanggal_selesai` = VALUES(`tanggal_selesai`)
        ");
        $stmt->execute([$tahunAjaran, $semester, $isAktif ? 1 : 0, $tglMulai, $tglSelesai]);
        return (int)$db->lastInsertId();
    }
}

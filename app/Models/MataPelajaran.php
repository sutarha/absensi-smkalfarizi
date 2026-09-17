<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class MataPelajaran
{
    public static function getAll(?string $tingkat = null, ?string $kelompok = null): array
    {
        $db = Database::getConnection();
        $sql = "SELECT * FROM `mata_pelajaran` WHERE 1=1";
        $params = [];

        if ($tingkat && $tingkat !== '') {
            if ($tingkat === 'SEMUA') {
                $sql .= " AND `tingkat` = 'SEMUA'";
            } else {
                $sql .= " AND (`tingkat` = 'SEMUA' OR FIND_IN_SET(?, REPLACE(`tingkat`, ' ', '')) > 0 OR `tingkat` = ?)";
                $params[] = $tingkat;
                $params[] = $tingkat;
            }
        }

        if ($kelompok) {
            $sql .= " AND `kelompok` = ?";
            $params[] = $kelompok;
        }

        $sql .= " ORDER BY FIELD(`kelompok`, 'Umum', 'Kejuruan', 'Muatan Lokal', 'Pilihan'), `nama_mapel` ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `mata_pelajaran` WHERE `id` = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(string $kode, string $nama, string $kelompok = 'Umum', string $tingkat = 'SEMUA'): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO `mata_pelajaran` (`kode_mapel`, `nama_mapel`, `kelompok`, `tingkat`) VALUES (?, ?, ?, ?)");
        $stmt->execute([$kode, $nama, $kelompok, $tingkat]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE `mata_pelajaran` 
            SET `kode_mapel` = ?, `nama_mapel` = ?, `kelompok` = ?, `tingkat` = ? 
            WHERE `id` = ?
        ");
        return $stmt->execute([
            $data['kode_mapel'],
            $data['nama_mapel'],
            $data['kelompok'] ?? 'Umum',
            $data['tingkat'] ?? 'SEMUA',
            $id
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM `mata_pelajaran` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }
}

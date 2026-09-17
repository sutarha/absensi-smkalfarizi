<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Kelas
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT k.*, g.nama_lengkap as nama_wali_kelas, g.nik_nip as nip_wali_kelas, COUNT(s.id) as total_siswa 
                            FROM kelas k 
                            LEFT JOIN guru g ON k.wali_kelas_guru_id = g.id
                            LEFT JOIN siswa s ON s.kelas_id = k.id 
                            GROUP BY k.id, g.nama_lengkap, g.nik_nip
                            ORDER BY k.tingkat ASC, k.nama_kelas ASC");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT k.*, g.nama_lengkap as nama_wali_kelas, g.nik_nip as nip_wali_kelas 
                              FROM kelas k 
                              LEFT JOIN guru g ON k.wali_kelas_guru_id = g.id 
                              WHERE k.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $waliId = !empty($data['wali_kelas_guru_id']) ? (int)$data['wali_kelas_guru_id'] : null;
        $stmt = $db->prepare("INSERT INTO kelas (nama_kelas, tingkat, jurusan, wali_kelas_guru_id) VALUES (:nama, :tingkat, :jurusan, :wali)");
        $stmt->execute([
            ':nama' => $data['nama_kelas'],
            ':tingkat' => $data['tingkat'],
            ':jurusan' => $data['jurusan'],
            ':wali' => $waliId,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $waliId = !empty($data['wali_kelas_guru_id']) ? (int)$data['wali_kelas_guru_id'] : null;
        $stmt = $db->prepare("UPDATE kelas SET nama_kelas = :nama, tingkat = :tingkat, jurusan = :jurusan, wali_kelas_guru_id = :wali WHERE id = :id");
        return $stmt->execute([
            ':nama' => $data['nama_kelas'],
            ':tingkat' => $data['tingkat'],
            ':jurusan' => $data['jurusan'],
            ':wali' => $waliId,
            ':id' => $id,
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM kelas WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

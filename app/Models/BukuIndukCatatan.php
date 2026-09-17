<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class BukuIndukCatatan
{
    public static function getBySiswaId(int $siswaId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `buku_induk_catatan` WHERE `siswa_id` = ? ORDER BY `semester_ke` ASC, `id` ASC");
        $stmt->execute([$siswaId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `buku_induk_catatan` (`siswa_id`, `jenis`, `nama_kegiatan`, `mitra_instansi`, `nilai_predikat`, `keterangan`, `semester_ke`)
            VALUES (:siswa_id, :jenis, :nama_kegiatan, :mitra_instansi, :nilai_predikat, :keterangan, :semester_ke)
        ");
        $stmt->execute([
            ':siswa_id' => $data['siswa_id'],
            ':jenis' => $data['jenis'],
            ':nama_kegiatan' => $data['nama_kegiatan'],
            ':mitra_instansi' => $data['mitra_instansi'] ?? null,
            ':nilai_predikat' => $data['nilai_predikat'] ?? null,
            ':keterangan' => $data['keterangan'] ?? null,
            ':semester_ke' => !empty($data['semester_ke']) ? (int)$data['semester_ke'] : null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE `buku_induk_catatan` 
            SET `jenis` = :jenis, 
                `nama_kegiatan` = :nama_kegiatan, 
                `mitra_instansi` = :mitra_instansi, 
                `nilai_predikat` = :nilai_predikat, 
                `keterangan` = :keterangan, 
                `semester_ke` = :semester_ke
            WHERE `id` = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':jenis' => $data['jenis'],
            ':nama_kegiatan' => $data['nama_kegiatan'],
            ':mitra_instansi' => $data['mitra_instansi'] ?? null,
            ':nilai_predikat' => $data['nilai_predikat'] ?? null,
            ':keterangan' => $data['keterangan'] ?? null,
            ':semester_ke' => !empty($data['semester_ke']) ? (int)$data['semester_ke'] : null,
        ]);
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM `buku_induk_catatan` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }
}

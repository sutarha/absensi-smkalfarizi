<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class TabunganSiswa
{
    public static function getByProgram(int $programId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT ts.*, s.nama_siswa, s.nisn, k.nama_kelas 
                              FROM tabungan_siswa ts
                              JOIN siswa s ON ts.siswa_id = s.id
                              JOIN kelas k ON s.kelas_id = k.id
                              WHERE ts.program_id = :program_id
                              ORDER BY k.nama_kelas ASC, s.nama_siswa ASC");
        $stmt->execute([':program_id' => $programId]);
        return $stmt->fetchAll();
    }

    public static function getBySiswa(int $siswaId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT ts.*, tp.nama_program, tp.target_nominal, tp.target_tanggal, tp.foto, tp.status as status_program
                              FROM tabungan_siswa ts
                              JOIN tabungan_program tp ON ts.program_id = tp.id
                              WHERE ts.siswa_id = :siswa_id
                              ORDER BY ts.created_at DESC");
        $stmt->execute([':siswa_id' => $siswaId]);
        return $stmt->fetchAll();
    }

    public static function register(int $siswaId, int $programId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT IGNORE INTO tabungan_siswa (siswa_id, program_id, total_terkumpul, status) 
                              VALUES (:siswa_id, :program_id, 0, 'berjalan')");
        return $stmt->execute([
            ':siswa_id' => $siswaId,
            ':program_id' => $programId
        ]);
    }

    public static function updateStatus(int $id, string $status): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE tabungan_siswa SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }
}

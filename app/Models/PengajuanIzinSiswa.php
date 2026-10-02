<?php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class PengajuanIzinSiswa
{
    public static function create(int $siswaId, string $tglMulai, string $tglSelesai, string $jenis, string $alasan, ?string $lampiran = null): int
    {
        $db = Database::getConnection();
        $sql = "INSERT INTO pengajuan_izin_siswa (siswa_id, tanggal_mulai, tanggal_selesai, jenis_izin, alasan, file_lampiran, status) 
                VALUES (:sid, :t1, :t2, :jenis, :alasan, :file, 'MENUNGGU')";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':sid' => $siswaId,
            ':t1' => $tglMulai,
            ':t2' => $tglSelesai,
            ':jenis' => $jenis,
            ':alasan' => $alasan,
            ':file' => $lampiran
        ]);
        
        return (int)$db->lastInsertId();
    }

    public static function getAll(string $status = ''): array
    {
        $db = Database::getConnection();
        $cond = "";
        $params = [];
        if ($status !== '') {
            $cond = "WHERE p.status = :status";
            $params[':status'] = $status;
        }

        $sql = "SELECT p.*, s.nama_siswa, s.nisn, k.nama_kelas, g.nama_lengkap as nama_guru_pemroses 
                FROM pengajuan_izin_siswa p 
                JOIN siswa s ON p.siswa_id = s.id 
                JOIN kelas k ON s.kelas_id = k.id 
                LEFT JOIN guru g ON p.diproses_oleh = g.id 
                {$cond} 
                ORDER BY p.created_at DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllForWaliKelas(int $guruId, string $status = ''): array
    {
        $db = Database::getConnection();
        $cond = "WHERE k.wali_kelas_guru_id = :guruId";
        $params = [':guruId' => $guruId];
        
        if ($status !== '') {
            $cond .= " AND p.status = :status";
            $params[':status'] = $status;
        }

        $sql = "SELECT p.*, s.nama_siswa, s.nisn, k.nama_kelas, g.nama_lengkap as nama_guru_pemroses 
                FROM pengajuan_izin_siswa p 
                JOIN siswa s ON p.siswa_id = s.id 
                JOIN kelas k ON s.kelas_id = k.id 
                LEFT JOIN guru g ON p.diproses_oleh = g.id 
                {$cond} 
                ORDER BY p.created_at DESC";
                
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getBySiswaId(int $siswaId): array
    {
        $db = Database::getConnection();
        $sql = "SELECT p.*, g.nama_lengkap as nama_guru_pemroses 
                FROM pengajuan_izin_siswa p 
                LEFT JOIN guru g ON p.diproses_oleh = g.id 
                WHERE p.siswa_id = :sid 
                ORDER BY p.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':sid' => $siswaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $sql = "SELECT p.*, s.nama_siswa, s.nisn, k.nama_kelas, g.nama_lengkap as nama_guru_pemroses 
                FROM pengajuan_izin_siswa p 
                JOIN siswa s ON p.siswa_id = s.id 
                JOIN kelas k ON s.kelas_id = k.id 
                LEFT JOIN guru g ON p.diproses_oleh = g.id 
                WHERE p.id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function process(int $id, string $status, int $guruId, ?string $catatan = null): bool
    {
        $db = Database::getConnection();
        $sql = "UPDATE pengajuan_izin_siswa 
                SET status = :status, diproses_oleh = :guru, diproses_at = CURRENT_TIMESTAMP, catatan_guru = :cat 
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':guru' => $guruId,
            ':cat' => $catatan,
            ':id' => $id
        ]);
    }
}

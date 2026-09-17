<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class TabunganProgram
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT tp.*, g.nama_lengkap as nama_pembuat, p.nama_lengkap as nama_pengelola, a.nama_lengkap as nama_asisten, k.nama_kelas 
                            FROM tabungan_program tp 
                            LEFT JOIN guru g ON tp.created_by_guru = g.id 
                            LEFT JOIN guru p ON tp.pengelola_id = p.id
                            LEFT JOIN guru a ON tp.asisten_pengelola_id = a.id
                            LEFT JOIN kelas k ON tp.kelas_id = k.id
                            ORDER BY tp.created_at DESC");
        return $stmt->fetchAll();
    }

    public static function getActive(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT tp.*, k.nama_kelas, p.nama_lengkap as nama_pengelola, a.nama_lengkap as nama_asisten 
                            FROM tabungan_program tp 
                            LEFT JOIN kelas k ON tp.kelas_id = k.id
                            LEFT JOIN guru p ON tp.pengelola_id = p.id
                            LEFT JOIN guru a ON tp.asisten_pengelola_id = a.id
                            WHERE tp.status = 'aktif'
                            ORDER BY tp.created_at DESC");
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT tp.*, g.nama_lengkap as nama_pembuat, p.nama_lengkap as nama_pengelola, a.nama_lengkap as nama_asisten, k.nama_kelas 
                              FROM tabungan_program tp 
                              LEFT JOIN guru g ON tp.created_by_guru = g.id 
                              LEFT JOIN guru p ON tp.pengelola_id = p.id
                              LEFT JOIN guru a ON tp.asisten_pengelola_id = a.id
                              LEFT JOIN kelas k ON tp.kelas_id = k.id
                              WHERE tp.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $sql = "INSERT INTO tabungan_program (nama_program, deskripsi, target_nominal, target_tanggal, kelas_id, foto, created_by_guru, status, pengelola_id, asisten_pengelola_id) 
                VALUES (:nama, :desc, :nominal, :tanggal, :kelas_id, :foto, :created_by, :status, :pengelola_id, :asisten_pengelola_id)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nama' => $data['nama_program'],
            ':desc' => $data['deskripsi'] ?? null,
            ':nominal' => (float)$data['target_nominal'],
            ':tanggal' => !empty($data['target_tanggal']) ? $data['target_tanggal'] : null,
            ':kelas_id' => !empty($data['kelas_id']) ? (int)$data['kelas_id'] : null,
            ':foto' => $data['foto'] ?? null,
            ':created_by' => $data['created_by_guru'] ?? null,
            ':status' => $data['status'] ?? 'aktif',
            ':pengelola_id' => !empty($data['pengelola_id']) ? (int)$data['pengelola_id'] : null,
            ':asisten_pengelola_id' => !empty($data['asisten_pengelola_id']) ? (int)$data['asisten_pengelola_id'] : null
        ]);
        
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $db = Database::getConnection();
        $sql = "UPDATE tabungan_program SET 
                nama_program = :nama,
                deskripsi = :desc,
                target_nominal = :nominal,
                target_tanggal = :tanggal,
                kelas_id = :kelas_id,
                status = :status,
                pengelola_id = :pengelola_id,
                asisten_pengelola_id = :asisten_pengelola_id";
                
        $params = [
            ':id' => $id,
            ':nama' => $data['nama_program'],
            ':desc' => $data['deskripsi'] ?? null,
            ':nominal' => (float)$data['target_nominal'],
            ':tanggal' => !empty($data['target_tanggal']) ? $data['target_tanggal'] : null,
            ':kelas_id' => !empty($data['kelas_id']) ? (int)$data['kelas_id'] : null,
            ':status' => $data['status'] ?? 'aktif',
            ':pengelola_id' => !empty($data['pengelola_id']) ? (int)$data['pengelola_id'] : null,
            ':asisten_pengelola_id' => !empty($data['asisten_pengelola_id']) ? (int)$data['asisten_pengelola_id'] : null
        ];

        if (array_key_exists('foto', $data)) {
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
        $stmt = $db->prepare("DELETE FROM tabungan_program WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class LmsMateri
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllByKelasAndMapel($kelas_id, $mapel_id)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, g.nama_lengkap as nama_guru, mp.nama_mapel 
            FROM lms_materi m
            LEFT JOIN guru g ON m.guru_id = g.id
            LEFT JOIN mata_pelajaran mp ON m.mapel_id = mp.id
            WHERE m.kelas_id = ? AND m.mapel_id = ?
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$kelas_id, $mapel_id]);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, g.nama_lengkap as nama_guru, mp.nama_mapel, k.nama_kelas
            FROM lms_materi m
            LEFT JOIN guru g ON m.guru_id = g.id
            LEFT JOIN mata_pelajaran mp ON m.mapel_id = mp.id
            LEFT JOIN kelas k ON m.kelas_id = k.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO lms_materi (judul, deskripsi, konten, file_path, tipe, mapel_id, kelas_id, guru_id, deadline)
            VALUES (:judul, :deskripsi, :konten, :file_path, :tipe, :mapel_id, :kelas_id, :guru_id, :deadline)
        ");
        return $stmt->execute([
            ':judul' => $data['judul'],
            ':deskripsi' => $data['deskripsi'],
            ':konten' => $data['konten'] ?? null,
            ':file_path' => $data['file_path'] ?? null,
            ':tipe' => $data['tipe'],
            ':mapel_id' => $data['mapel_id'],
            ':kelas_id' => $data['kelas_id'],
            ':guru_id' => $data['guru_id'],
            ':deadline' => $data['deadline'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE lms_materi SET 
                judul = :judul, 
                deskripsi = :deskripsi, 
                konten = :konten, 
                tipe = :tipe, 
                deadline = :deadline";
        
        $params = [
            ':judul' => $data['judul'],
            ':deskripsi' => $data['deskripsi'],
            ':konten' => $data['konten'] ?? null,
            ':tipe' => $data['tipe'],
            ':deadline' => $data['deadline'] ?? null,
            ':id' => $id
        ];

        if (isset($data['file_path'])) {
            $sql .= ", file_path = :file_path";
            $params[':file_path'] = $data['file_path'];
        }

        $sql .= " WHERE id = :id AND guru_id = :guru_id";
        $params[':guru_id'] = $data['guru_id']; // For safety

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $guru_id)
    {
        $stmt = $this->db->prepare("DELETE FROM lms_materi WHERE id = ? AND guru_id = ?");
        return $stmt->execute([$id, $guru_id]);
    }
}

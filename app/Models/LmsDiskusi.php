<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class LmsDiskusi
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByMapelAndKelas($mapel_id, $kelas_id)
    {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   CASE 
                       WHEN d.user_type = 'siswa' THEN s.nama_siswa
                       WHEN d.user_type = 'guru' THEN g.nama_lengkap
                   END as nama_pengirim,
                   CASE 
                       WHEN d.user_type = 'siswa' THEN s.foto
                       WHEN d.user_type = 'guru' THEN NULL
                   END as foto_pengirim
            FROM lms_diskusi d
            LEFT JOIN siswa s ON d.user_id = s.id AND d.user_type = 'siswa'
            LEFT JOIN guru g ON d.user_id = g.id AND d.user_type = 'guru'
            WHERE d.mapel_id = ? AND d.kelas_id = ?
            ORDER BY d.created_at ASC
        ");
        $stmt->execute([$mapel_id, $kelas_id]);
        return $stmt->fetchAll();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO lms_diskusi (kelas_id, mapel_id, materi_id, user_id, user_type, pesan)
            VALUES (:kelas_id, :mapel_id, :materi_id, :user_id, :user_type, :pesan)
        ");
        return $stmt->execute([
            'kelas_id' => $data['kelas_id'],
            'mapel_id' => $data['mapel_id'],
            'materi_id' => $data['materi_id'] ?? null,
            'user_id' => $data['user_id'],
            'user_type' => $data['user_type'],
            'pesan' => $data['pesan']
        ]);
    }
}

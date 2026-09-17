<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class LmsSubmission
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByMateri($materi_id)
    {
        $stmt = $this->db->prepare("
            SELECT s.*, s_siswa.nama_siswa as nama_siswa, s_siswa.nisn 
            FROM lms_submission s
            JOIN siswa s_siswa ON s.siswa_id = s_siswa.id
            WHERE s.materi_id = ?
            ORDER BY s.submitted_at DESC
        ");
        $stmt->execute([$materi_id]);
        return $stmt->fetchAll();
    }

    public function getBySiswaAndMateri($siswa_id, $materi_id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM lms_submission 
            WHERE siswa_id = ? AND materi_id = ?
        ");
        $stmt->execute([$siswa_id, $materi_id]);
        return $stmt->fetch();
    }

    public function submitTugas($data)
    {
        // Cek apakah sudah submit
        $existing = $this->getBySiswaAndMateri($data['siswa_id'], $data['materi_id']);

        if ($existing) {
            $sql = "UPDATE lms_submission SET jawaban = :jawaban, submitted_at = NOW()";
            $params = [
                ':jawaban' => $data['jawaban'],
                ':id' => $existing['id']
            ];

            if (isset($data['file_path'])) {
                $sql .= ", file_path = :file_path";
                $params[':file_path'] = $data['file_path'];
            }
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO lms_submission (materi_id, siswa_id, file_path, jawaban, submitted_at)
                VALUES (:materi_id, :siswa_id, :file_path, :jawaban, NOW())
            ");
            return $stmt->execute([
                ':materi_id' => $data['materi_id'],
                ':siswa_id' => $data['siswa_id'],
                ':file_path' => $data['file_path'] ?? null,
                ':jawaban' => $data['jawaban'] ?? null
            ]);
        }
    }

    public function nilaiSubmission($id, $nilai, $catatan)
    {
        $stmt = $this->db->prepare("
            UPDATE lms_submission 
            SET nilai = ?, catatan_guru = ?, dinilai_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$nilai, $catatan, $id]);
    }
}

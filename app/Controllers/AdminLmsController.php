<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\LmsMateri;

class AdminLmsController
{
    private array $user;

    public function __construct()
    {
        $this->user = AuthController::checkAuth();
        if (!$this->user || !in_array($this->user['role'], ['admin', 'wakasek_kurikulum'])) {
            App::redirect(App::baseUrl('login'));
        }
    }

    public function index()
    {
        $db = \App\Config\Database::getConnection();
        $stmt = $db->query("
            SELECT m.*, mp.nama_mapel, k.nama_kelas, g.nama_lengkap as nama_guru,
                   (SELECT COUNT(*) FROM lms_submission s WHERE s.materi_id = m.id) as total_submission
            FROM lms_materi m
            LEFT JOIN mata_pelajaran mp ON m.mapel_id = mp.id
            LEFT JOIN kelas k ON m.kelas_id = k.id
            LEFT JOIN guru g ON m.guru_id = g.id
            ORDER BY m.created_at DESC
        ");
        $lmsList = $stmt->fetchAll();

        $data = [
            'title' => 'Monitoring LMS',
            'user' => $this->user,
            'lmsList' => $lmsList
        ];
        
        require_once __DIR__ . '/../Views/admin/lms/index.php';
    }
}

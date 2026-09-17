<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\TabunganProgram;
use App\Models\Guru;
use App\Models\Kelas;
use App\Controllers\AuthController;

class AdminTabunganController
{
    private array $user;
    private array $config;

    public function __construct()
    {
        $this->user = AuthController::requireRole(['admin', 'superadmin']);
        $this->config = \App\Models\KonfigurasiSekolah::get();
    }

    // ==========================================
    // 1. KELOLA PROGRAM TABUNGAN
    // ==========================================
    public function programIndex(): void
    {
        $programList = TabunganProgram::getAll();
        $guruList = Guru::getAll();
        $kelasList = Kelas::getAll();
        $user = $this->user;
        $config = $this->config;
        
        require __DIR__ . '/../Views/admin/tabungan_program.php';
    }

    public function programStore(): void
    {
        $data = [
            'nama_program' => trim($_POST['nama_program']),
            'deskripsi' => trim($_POST['deskripsi']),
            'target_nominal' => str_replace(['Rp', '.', ','], '', $_POST['target_nominal']),
            'target_tanggal' => !empty($_POST['target_tanggal']) ? $_POST['target_tanggal'] : null,
            'kelas_id' => !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null,
            'pengelola_id' => !empty($_POST['pengelola_id']) ? (int)$_POST['pengelola_id'] : null,
            'asisten_pengelola_id' => !empty($_POST['asisten_pengelola_id']) ? (int)$_POST['asisten_pengelola_id'] : null,
            'created_by_guru' => null, // Oleh Admin
            'status' => 'aktif'
        ];

        // Handle File Upload for Foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/tabungan/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $filename = uniqid('tab_') . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                    $data['foto'] = 'uploads/tabungan/' . $filename;
                }
            }
        }

        TabunganProgram::create($data);
        $_SESSION['flash_success'] = 'Program tabungan berhasil dibuat.';
        App::redirect(App::baseUrl('admin/tabungan/program'));
    }

    public function programUpdate(int $id): void
    {
        $data = [
            'nama_program' => trim($_POST['nama_program']),
            'deskripsi' => trim($_POST['deskripsi']),
            'target_nominal' => str_replace(['Rp', '.', ','], '', $_POST['target_nominal']),
            'target_tanggal' => !empty($_POST['target_tanggal']) ? $_POST['target_tanggal'] : null,
            'kelas_id' => !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null,
            'pengelola_id' => !empty($_POST['pengelola_id']) ? (int)$_POST['pengelola_id'] : null,
            'asisten_pengelola_id' => !empty($_POST['asisten_pengelola_id']) ? (int)$_POST['asisten_pengelola_id'] : null,
            'status' => $_POST['status'] ?? 'aktif'
        ];

        // Handle File Upload for Foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/tabungan/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                $filename = uniqid('tab_') . '.' . $ext;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                    $data['foto'] = 'uploads/tabungan/' . $filename;
                }
            }
        }

        TabunganProgram::update($id, $data);
        $_SESSION['flash_success'] = 'Program tabungan berhasil diupdate.';
        App::redirect(App::baseUrl('admin/tabungan/program'));
    }

    public function programDelete(int $id): void
    {
        TabunganProgram::delete($id);
        $_SESSION['flash_success'] = 'Program tabungan berhasil dihapus.';
        App::redirect(App::baseUrl('admin/tabungan/program'));
    }

    // ==========================================
    // 2. MONITORING TABUNGAN
    // ==========================================
    public function monitoringIndex(): void
    {
        $db = \App\Config\Database::getConnection();
        
        // Ambil daftar program untuk filter
        $programList = $db->query("SELECT id, nama_program FROM tabungan_program ORDER BY id DESC")->fetchAll();
        
        $selectedProgramId = isset($_GET['program_id']) ? (int)$_GET['program_id'] : (count($programList) > 0 ? $programList[0]['id'] : 0);

        // Fetch all tabungan siswa grouped/ordered nicely, filtered by program_id
        if ($selectedProgramId > 0) {
            $stmt = $db->prepare("
                SELECT ts.*, s.nama_siswa, s.nisn, k.nama_kelas, tp.nama_program, tp.target_nominal
                FROM tabungan_siswa ts
                JOIN siswa s ON ts.siswa_id = s.id
                JOIN kelas k ON s.kelas_id = k.id
                JOIN tabungan_program tp ON ts.program_id = tp.id
                WHERE ts.program_id = :program_id
                ORDER BY k.nama_kelas ASC, s.nama_siswa ASC
            ");
            $stmt->execute([':program_id' => $selectedProgramId]);
            $tabunganList = $stmt->fetchAll();

            // Optional: Get recent transactions for a mini activity feed, filtered by program_id
            $stmt2 = $db->prepare("
                SELECT tt.*, s.nama_siswa, tp.nama_program, g.nama_lengkap as nama_petugas
                FROM tabungan_transaksi tt
                JOIN tabungan_siswa ts ON tt.tabungan_siswa_id = ts.id
                JOIN siswa s ON ts.siswa_id = s.id
                JOIN tabungan_program tp ON ts.program_id = tp.id
                LEFT JOIN guru g ON tt.dicatat_guru_id = g.id
                WHERE ts.program_id = :program_id
                ORDER BY tt.created_at DESC
                LIMIT 10
            ");
            $stmt2->execute([':program_id' => $selectedProgramId]);
            $recentTransactions = $stmt2->fetchAll();
        } else {
            $tabunganList = [];
            $recentTransactions = [];
        }

        $user = $this->user;
        $config = $this->config;
        
        require __DIR__ . '/../Views/admin/tabungan_monitoring.php';
    }
}

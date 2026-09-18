<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\KonfigurasiSekolah;
use App\Models\Siswa;
use App\Models\PresensiGerbang;
use App\Models\Kelas;

class PiketController
{
    private array $user;
    private array $config;

    public function __construct()
    {
        $this->user = AuthController::requireRole(['piket', 'admin']);
        $this->config = KonfigurasiSekolah::get();
    }

    public function scannerView(): void
    {
        $today = date('Y-m-d');
        $kelasList = Kelas::getAll();
        $siswaList = Siswa::getAll(); // Untuk panel simulasi tap cepat

        // Hitung statistik tap hari ini
        $rekapHariIni = PresensiGerbang::getByDate($today);
        $totalSiswa = count($rekapHariIni);
        $totalDatang = 0;
        $totalPulang = 0;
        $totalHadirLengkap = 0;

        foreach ($rekapHariIni as $r) {
            if (!empty($r['waktu_datang'])) {
                $totalDatang++;
            }
            if (!empty($r['waktu_pulang'])) {
                $totalPulang++;
            }
            if ($r['status_kehadiran'] === 'HADIR') {
                $totalHadirLengkap++;
            }
        }

        $user = $this->user;
        $config = $this->config;

        require __DIR__ . '/../Views/piket/scanner.php';
    }

    /**
     * AJAX Endpoint Pemrosesan Barcode Kartu Siswa (Barcode Kotak / QR Code)
     */
    public function ajaxScan(): void
    {
        $barcode = trim($_POST['barcode'] ?? $_POST['barcode_code'] ?? '');
        $mode = strtoupper(trim($_POST['mode'] ?? 'DATANG')); // DATANG atau PULANG

        if (empty($barcode)) {
            App::json(['success' => false, 'message' => 'Kode barcode/QR kosong!'], 400);
        }

        $siswa = Siswa::findByBarcode($barcode);
        if (!$siswa) {
            App::json([
                'success' => false,
                'message' => "Kartu [{$barcode}] tidak terdaftar di database siswa!",
            ], 404);
        }

        $siswaId = (int)$siswa['id'];

        if ($mode === 'PULANG') {
            $result = PresensiGerbang::recordTapOut($siswaId);
        } else {
            $result = PresensiGerbang::recordTapIn($siswaId);
        }

        App::json([
            'success' => true,
            'mode' => $mode,
            'is_repeat' => $result['is_repeat'] ?? false,
            'message' => $result['message'],
            'time' => $result['time'] ?? date('H:i:s'),
            'siswa' => [
                'id' => $siswa['id'],
                'nama_siswa' => $siswa['nama_siswa'],
                'nisn' => $siswa['nisn'],
                'barcode_code' => $siswa['barcode_code'],
                'nama_kelas' => $siswa['nama_kelas'],
                'jurusan' => $siswa['jurusan'],
                'jenis_kelamin' => $siswa['jenis_kelamin'],
                'foto' => $siswa['foto'] ?: ($siswa['jenis_kelamin'] === 'P' ? 'uploads/siswa/default_f.png' : 'uploads/siswa/default_m.png'),
            ]
        ]);
    }

    /**
     * Tampilan Riwayat Scan Gerbang Hari Ini
     */
    public function riwayatView(): void
    {
        $today = date('Y-m-d');
        $kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
        $filterBelumPulang = isset($_GET['belum_pulang']) && $_GET['belum_pulang'] === '1';

        $kelasList = Kelas::getAll();
        $presensiList = PresensiGerbang::getByDate($today, $kelasId, $filterBelumPulang);

        $user = $this->user;
        $config = $this->config;

        require __DIR__ . '/../Views/piket/riwayat.php';
    }

    /**
     * Endpoint Real-Time Live Feed & Counters Scanner Piket
     */
    public function liveFeed(): void
    {
        $today = date('Y-m-d');
        $rekapHariIni = PresensiGerbang::getByDate($today);
        $totalDatang = 0;
        $totalPulang = 0;
        $totalHadirLengkap = 0;

        foreach ($rekapHariIni as $r) {
            if (!empty($r['waktu_datang'])) {
                $totalDatang++;
            }
            if (!empty($r['waktu_pulang'])) {
                $totalPulang++;
            }
            if ($r['status_kehadiran'] === 'HADIR') {
                $totalHadirLengkap++;
            }
        }

        $recentScans = PresensiGerbang::getRecentScans($today, 15);

        App::json([
            'success' => true,
            'server_time' => date('H:i:s'),
            'counters' => [
                'total_datang' => $totalDatang,
                'total_pulang' => $totalPulang,
                'total_hadir_lengkap' => $totalHadirLengkap,
                'total_rekap' => count($rekapHariIni)
            ],
            'recent_scans' => $recentScans
        ]);
    }

    /**
     * Tampilan Input Manual Izin/Sakit/Tugas Luar
     */
    public function inputIzinView(): void
    {
        $kelasList = Kelas::getAll();
        $user = $this->user;
        $config = $this->config;

        require __DIR__ . '/../Views/piket/input_izin.php';
    }

    /**
     * AJAX Endpoint Pemrosesan Izin Manual Siswa
     */
    public function ajaxInputIzin(): void
    {
        $siswaId = isset($_POST['siswa_id']) ? (int)$_POST['siswa_id'] : 0;
        $status = $_POST['status_kehadiran'] ?? '';
        $keterangan = trim($_POST['keterangan'] ?? '');
        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        
        if (empty($siswaId) || !in_array($status, ['SAKIT', 'IZIN', 'TUGAS_LUAR'])) {
            App::json(['success' => false, 'message' => 'Data tidak valid atau status salah!'], 400);
        }

        $db = \App\Config\Database::getConnection();
        
        // Cek apakah sudah ada baris presensi hari ini
        $stmt = $db->prepare("SELECT * FROM presensi_gerbang_siswa WHERE siswa_id = :sid AND tanggal = :tgl LIMIT 1");
        $stmt->execute([':sid' => $siswaId, ':tgl' => $tanggal]);
        $existing = $stmt->fetch();

        if ($existing) {
            $upStmt = $db->prepare("UPDATE presensi_gerbang_siswa SET status_kehadiran = :st, keterangan = :ket WHERE id = :id");
            $upStmt->execute([':st' => $status, ':ket' => $keterangan, ':id' => $existing['id']]);
        } else {
            $inStmt = $db->prepare("INSERT INTO presensi_gerbang_siswa (siswa_id, tanggal, status_kehadiran, keterangan) VALUES (:sid, :tgl, :st, :ket)");
            $inStmt->execute([':sid' => $siswaId, ':tgl' => $tanggal, ':st' => $status, ':ket' => $keterangan]);
        }

        App::json([
            'success' => true,
            'message' => 'Status kehadiran berhasil diperbarui.'
        ]);
    }
}

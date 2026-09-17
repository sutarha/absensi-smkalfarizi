<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\ArsipSurat;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\BukuInduk;
use App\Models\KonfigurasiSekolah;
use App\Helpers\SuratHelper;
use App\Config\Database;

class SuratController
{
    private function authAdmin(): array
    {
        $user = AuthController::checkAuth();
        if (!$user || !in_array($user['role'], ['admin', 'kepala_sekolah'])) {
            App::redirect(App::baseUrl('login'));
            exit;
        }
        return $user;
    }

    public function index(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();

        $jenisSurat = !empty($_GET['jenis']) ? trim($_GET['jenis']) : null;
        $suratList = ArsipSurat::getAll($jenisSurat);

        require __DIR__ . '/../Views/surat/index.php';
    }

    public function create(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $allSiswa = Siswa::getAll();
        $allGuru = Guru::getAll();

        $selectedJenis = trim($_GET['jenis'] ?? 'SISWA_AKTIF');
        $prefillSiswaId = !empty($_GET['siswa_id']) ? (int)$_GET['siswa_id'] : null;

        require __DIR__ . '/../Views/surat/buat.php';
    }

    public function store(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();

        $jenisSurat = trim($_POST['jenis_surat'] ?? 'SISWA_AKTIF');
        $penerimaTipe = trim($_POST['penerima_tipe'] ?? 'SISWA');
        $siswaId = !empty($_POST['siswa_id']) ? (int)$_POST['siswa_id'] : null;
        $guruId = !empty($_POST['guru_id']) ? (int)$_POST['guru_id'] : null;
        $tanggalSurat = !empty($_POST['tanggal_surat']) ? $_POST['tanggal_surat'] : date('Y-m-d');
        $perihal = trim($_POST['perihal'] ?? 'Surat Keterangan');
        $keperluan = trim($_POST['keperluan'] ?? '');

        // Auto Generate Nomor Surat Dinas
        $tahun = (int)date('Y', strtotime($tanggalSurat));
        $urutan = ArsipSurat::getNextUrutan($jenisSurat, $tahun);
        $nomorSurat = SuratHelper::generateNomorSurat($jenisSurat, $urutan, $tanggalSurat);

        // Data khusus SPPD & Surat Tugas
        $dasarPenugasan = trim($_POST['dasar_penugasan'] ?? '');
        $tempatBerangkat = trim($_POST['tempat_berangkat'] ?? 'SMK AL-FARIZI');
        $tempatTujuan = trim($_POST['tempat_tujuan'] ?? '');
        $instansiTujuan = trim($_POST['instansi_tujuan'] ?? '');
        $pejabatTujuan = trim($_POST['pejabat_tujuan'] ?? '');
        $tanggalBerangkat = !empty($_POST['tanggal_berangkat']) ? $_POST['tanggal_berangkat'] : null;
        $tanggalKembali = !empty($_POST['tanggal_kembali']) ? $_POST['tanggal_kembali'] : null;
        $lamaHari = max(1, (int)($_POST['lama_hari'] ?? 1));
        $alatAngkut = trim($_POST['alat_angkut'] ?? 'Kendaraan Dinas / Umum');
        $bebanAnggaran = trim($_POST['beban_anggaran'] ?? 'Dana BOS SMK Al-Farizi');
        $pengikut = trim($_POST['pengikut'] ?? '');
        $pejabatTtd = trim($_POST['pejabat_penandatangan'] ?? ($config['kepala_sekolah'] ?? 'Kepala Sekolah'));

        $insertId = ArsipSurat::create([
            'nomor_surat' => $nomorSurat,
            'jenis_surat' => $jenisSurat,
            'penerima_tipe' => $penerimaTipe,
            'siswa_id' => $siswaId,
            'guru_id' => $guruId,
            'perihal' => $perihal,
            'keperluan' => $keperluan,
            'tanggal_surat' => $tanggalSurat,
            'dasar_penugasan' => $dasarPenugasan,
            'tempat_berangkat' => $tempatBerangkat,
            'tempat_tujuan' => $tempatTujuan,
            'instansi_tujuan' => $instansiTujuan,
            'pejabat_tujuan' => $pejabatTujuan,
            'tanggal_berangkat' => $tanggalBerangkat,
            'tanggal_kembali' => $tanggalKembali,
            'lama_hari' => $lamaHari,
            'alat_angkut' => $alatAngkut,
            'beban_anggaran' => $bebanAnggaran,
            'pengikut' => $pengikut,
            'pejabat_penandatangan' => $pejabatTtd,
            'created_by' => $user['id']
        ]);

        $_SESSION['flash_success'] = "Surat resmi berhasil diterbitkan dengan Nomor: {$nomorSurat}";
        App::redirect(App::baseUrl("admin/surat/cetak/{$insertId}"));
    }

    public function cetak(int $id): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $surat = ArsipSurat::findById($id);

        if (!$surat) {
            die("Surat tidak ditemukan.");
        }

        require __DIR__ . '/../Views/surat/cetak.php';
    }

    public function delete(int $id): void
    {
        $this->authAdmin();
        ArsipSurat::delete($id);
        $_SESSION['flash_success'] = "Surat berhasil dihapus dari arsip.";
        App::redirect(App::baseUrl('admin/surat'));
    }

    public function pengaturanKop(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();

        require __DIR__ . '/../Views/surat/pengaturan_kop.php';
    }

    public function simpanKop(): void
    {
        $this->authAdmin();

        $data = [
            'nama_sekolah' => trim($_POST['nama_sekolah'] ?? 'SMK AL-FARIZI'),
            'alamat_sekolah' => trim($_POST['alamat_sekolah'] ?? ''),
            'npsn' => trim($_POST['npsn'] ?? ''),
            'nss' => trim($_POST['nss'] ?? ''),
            'akreditasi' => trim($_POST['akreditasi'] ?? ''),
            'email_sekolah' => trim($_POST['email_sekolah'] ?? ''),
            'website_sekolah' => trim($_POST['website_sekolah'] ?? ''),
            'kepala_sekolah' => trim($_POST['kepala_sekolah'] ?? '')
        ];

        // Handle Logo KOP upload
        if (!empty($_FILES['logo_kop']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['logo_kop']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp'])) {
                $imgInfo = @getimagesize($_FILES['logo_kop']['tmp_name']);
                if ($imgInfo !== false) {
                    $newName = 'logo_kop_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['logo_kop']['tmp_name'], $uploadDir . $newName)) {
                        $data['logo_kop'] = $newName;
                    }
                }
            }
        }

        KonfigurasiSekolah::updateKopConfig($data);
        $_SESSION['flash_success'] = "Konfigurasi KOP Surat resmi berhasil diperbarui!";
        App::redirect(App::baseUrl('admin/surat/pengaturan-kop'));
    }
}

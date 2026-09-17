<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\TahunPelajaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RiwayatKelas;
use App\Models\KonfigurasiSekolah;
use App\Config\Database;

class AkademikController
{
    private function authAdmin(): array
    {
        $user = AuthController::checkAuth();
        if (!$user || !in_array($user['role'], ['admin', 'wakasek_kurikulum', 'kepala_sekolah'])) {
            App::redirect(App::baseUrl('login'));
            exit;
        }
        return $user;
    }

    public function tahunPelajaranIndex(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $tapelList = TahunPelajaran::getAll();
        $activeTapel = TahunPelajaran::getActive();

        require __DIR__ . '/../Views/akademik/tahun_pelajaran.php';
    }

    public function tahunPelajaranStore(): void
    {
        $this->authAdmin();

        $tahunAjaran = trim($_POST['tahun_ajaran'] ?? '');
        $semester = trim($_POST['semester'] ?? 'Ganjil');
        $isAktif = !empty($_POST['is_aktif']);
        $tglMulai = !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null;
        $tglSelesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;

        if (!empty($tahunAjaran)) {
            TahunPelajaran::create($tahunAjaran, $semester, $isAktif, $tglMulai, $tglSelesai);
            $_SESSION['flash_success'] = "Tahun Pelajaran {$tahunAjaran} ({$semester}) berhasil disimpan!";
        } else {
            $_SESSION['flash_error'] = "Tahun ajaran wajib diisi.";
        }

        App::redirect(App::baseUrl('admin/akademik/tahun-pelajaran'));
    }

    public function tahunPelajaranSetActive(int $id): void
    {
        $this->authAdmin();
        TahunPelajaran::setActive($id);
        $_SESSION['flash_success'] = "Tahun Pelajaran aktif berhasil diubah!";
        App::redirect(App::baseUrl('admin/akademik/tahun-pelajaran'));
    }

    public function kenaikanKelasIndex(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $kelasList = Kelas::getAll();
        $tapelList = TahunPelajaran::getAll();
        $activeTapel = TahunPelajaran::getActive();

        $kelasAsalId = !empty($_GET['kelas_asal_id']) ? (int)$_GET['kelas_asal_id'] : ($kelasList[0]['id'] ?? 1);
        $tapelTujuanId = !empty($_GET['tapel_tujuan_id']) ? (int)$_GET['tapel_tujuan_id'] : ($activeTapel['id'] ?? 1);

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `siswa` WHERE `kelas_id` = ? ORDER BY `nama_siswa` ASC");
        $stmt->execute([$kelasAsalId]);
        $siswaList = $stmt->fetchAll();

        $selectedKelasAsal = Kelas::findById($kelasAsalId);

        require __DIR__ . '/../Views/akademik/kenaikan_kelas.php';
    }

    public function kenaikanKelasProcess(): void
    {
        $this->authAdmin();

        $kelasAsalId = (int)($_POST['kelas_asal_id'] ?? 0);
        $tapelTujuanId = (int)($_POST['tapel_tujuan_id'] ?? 0);
        $kelasTujuanDefault = (int)($_POST['kelas_tujuan_default'] ?? 0);
        $siswaActions = $_POST['siswa_action'] ?? [];
        $kelasTujuanCustom = $_POST['kelas_tujuan'] ?? [];

        if (empty($siswaActions)) {
            $_SESSION['flash_error'] = "Tidak ada siswa yang diproses.";
            App::redirect(App::baseUrl('admin/akademik/kenaikan-kelas'));
            return;
        }

        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $stmtUpdateSiswa = $db->prepare("UPDATE `siswa` SET `kelas_id` = ? WHERE `id` = ?");
            $stmtUpdateStatusInduk = $db->prepare("UPDATE `buku_induk_siswa` SET `status_siswa` = ? WHERE `siswa_id` = ?");

            $countNaik = 0;
            $countTinggal = 0;
            $countLulus = 0;

            foreach ($siswaActions as $siswaId => $action) {
                $siswaId = (int)$siswaId;
                
                if ($action === 'NAIK') {
                    $targetKelas = !empty($kelasTujuanCustom[$siswaId]) ? (int)$kelasTujuanCustom[$siswaId] : $kelasTujuanDefault;
                    if ($targetKelas > 0) {
                        $stmtUpdateSiswa->execute([$targetKelas, $siswaId]);
                        RiwayatKelas::record($siswaId, $targetKelas, $tapelTujuanId, 'NAIK', 'Kenaikan Kelas');
                        $countNaik++;
                    }
                } elseif ($action === 'TINGGAL') {
                    // Siswa tetap di kelas asal
                    RiwayatKelas::record($siswaId, $kelasAsalId, $tapelTujuanId, 'TINGGAL', 'Tinggal di Kelas Semula');
                    $countTinggal++;
                } elseif ($action === 'LULUS') {
                    $stmtUpdateSiswaNull = $db->prepare("UPDATE `siswa` SET `kelas_id` = NULL WHERE `id` = ?");
                    $stmtUpdateSiswaNull->execute([$siswaId]);
                    $stmtUpdateStatusInduk->execute(['LULUS', $siswaId]);
                    RiwayatKelas::record($siswaId, $kelasAsalId, $tapelTujuanId, 'LULUS', 'Dinyatakan Lulus SMK');
                    $countLulus++;
                }
            }

            $db->commit();
            $_SESSION['flash_success'] = "Proses Kenaikan Kelas Berhasil! Naik Kelas: {$countNaik}, Tinggal Kelas: {$countTinggal}, Lulus: {$countLulus}.";

        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Gagal memproses kenaikan kelas: " . $e->getMessage();
        }

        App::redirect(App::baseUrl("admin/akademik/kenaikan-kelas?kelas_asal_id={$kelasAsalId}&tapel_tujuan_id={$tapelTujuanId}"));
    }
}

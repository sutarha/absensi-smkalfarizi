<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\TabunganProgram;
use App\Models\TabunganSiswa;
use App\Models\TabunganTransaksi;
use App\Controllers\AuthController;
use App\Models\Siswa;

class GuruTabunganController
{
    private array $user;

    public function __construct()
    {
        $this->user = AuthController::requireRole(['guru', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara', 'admin']);
    }

    public function index(): void
    {
        $guruId = $this->user['id'];

        // Ambil semua program tabungan aktif
        $programList = TabunganProgram::getActive();
        $user = $this->user;
        $config = \App\Models\KonfigurasiSekolah::get();
        $activeNav = 'tabungan';

        require __DIR__ . '/../Views/guru/tabungan_index.php';
    }

    public function detailProgram(int $programId): void
    {
        $guruId = $this->user['id'];

        $program = TabunganProgram::findById($programId);
        if (!$program) {
            $_SESSION['flash_error'] = 'Program tabungan tidak ditemukan.';
            App::redirect(App::baseUrl('guru/tabungan'));
        }

        $isPengelola = ($program['pengelola_id'] == $guruId || $program['asisten_pengelola_id'] == $guruId || $this->user['role'] === 'admin');

        $siswaPeserta = TabunganSiswa::getByProgram($programId);
        $siswaAll = Siswa::getAll(); // Untuk pendaftaran
        $kelasList = \App\Models\Kelas::getAll();
        $user = $this->user;
        $config = \App\Models\KonfigurasiSekolah::get();
        $activeNav = 'tabungan';

        require __DIR__ . '/../Views/guru/tabungan_detail.php';
    }

    public function catatSetoran(int $tabunganSiswaId): void
    {
        $guruId = $this->user['id'];
        $jumlah = str_replace(['Rp', '.', ','], '', $_POST['jumlah'] ?? '0');
        $catatan = trim($_POST['catatan'] ?? '');
        $programId = (int)$_POST['program_id'];

        $program = TabunganProgram::findById($programId);
        if (!$program || ($program['pengelola_id'] != $guruId && $program['asisten_pengelola_id'] != $guruId && $this->user['role'] !== 'admin')) {
            $_SESSION['flash_error'] = 'Anda tidak memiliki akses mengelola program ini.';
            App::redirect(App::baseUrl('guru/tabungan/program/' . $programId));
            return;
        }

        if ((float)$jumlah <= 0) {
            $_SESSION['flash_error'] = 'Jumlah setoran tidak valid.';
            App::redirect(App::baseUrl('guru/tabungan/program/' . $programId));
        }

        try {
            TabunganTransaksi::create([
                'tabungan_siswa_id' => $tabunganSiswaId,
                'jumlah' => $jumlah,
                'dicatat_guru_id' => $guruId,
                'catatan' => $catatan
            ]);
            $_SESSION['flash_success'] = 'Setoran berhasil dicatat.';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }

        App::redirect(App::baseUrl('guru/tabungan/program/' . $programId));
    }

    // Untuk pendaftaran siswa ke program tabungan
    public function daftarSiswa(int $programId): void
    {
        $guruId = $this->user['id'];

        $program = TabunganProgram::findById($programId);
        if (!$program || ($program['pengelola_id'] != $guruId && $program['asisten_pengelola_id'] != $guruId && $this->user['role'] !== 'admin')) {
            $_SESSION['flash_error'] = 'Anda tidak memiliki akses mengelola program ini.';
            App::redirect(App::baseUrl('guru/tabungan/program/' . $programId));
            return;
        }

        $siswaIds = $_POST['siswa_ids'] ?? [];
        
        if (empty($siswaIds) || !is_array($siswaIds)) {
            // Fallback for single select
            if (!empty($_POST['siswa_id'])) {
                $siswaIds = [$_POST['siswa_id']];
            } else {
                $_SESSION['flash_error'] = 'Tidak ada siswa yang dipilih.';
                App::redirect(App::baseUrl('guru/tabungan/program/' . $programId));
                return;
            }
        }

        $berhasil = 0;
        foreach ($siswaIds as $siswaId) {
            if (TabunganSiswa::register((int)$siswaId, $programId)) {
                $berhasil++;
            }
        }

        if ($berhasil > 0) {
            $_SESSION['flash_success'] = "$berhasil siswa berhasil didaftarkan ke program ini.";
        } else {
            $_SESSION['flash_error'] = 'Gagal mendaftar atau semua siswa yang dipilih sudah terdaftar.';
        }

        App::redirect(App::baseUrl('guru/tabungan/program/' . $programId));
    }
}

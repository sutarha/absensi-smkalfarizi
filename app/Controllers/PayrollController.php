<?php
namespace App\Controllers;

use App\Config\App;
use App\Helpers\PdfHelper;
use App\Helpers\TimeHelper;
use App\Models\KonfigurasiSekolah;
use App\Models\Guru;
use App\Models\SesiMengajar;

class PayrollController
{
    private array $user;
    private array $config;

    public function __construct()
    {
        $this->user = AuthController::requireRole(['admin', 'guru', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $this->config = KonfigurasiSekolah::get();
    }

    /**
     * Dashboard Rekapitulasi Penggajian Guru (Akses Super Admin / TU)
     */
    public function index(): void
    {
        // Hanya admin, kepala_sekolah, bendahara yang bisa melihat rekap seluruh guru
        if (!in_array($this->user['role'], ['admin', 'kepala_sekolah', 'bendahara'])) {
            App::redirect(App::baseUrl('guru/dompet'));
        }

        $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
        $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

        $guruList = Guru::getAll(['admin', 'guru', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $rekapData = [];

        $grandTotalJp = 0;
        $grandTotalPlafon = 0.0;
        $grandTotalMenitTelat = 0;
        $grandTotalDenda = 0.0;
        $grandTotalHonorKbm = 0.0;
        $grandTotalTunjangan = 0.0;
        $grandTotalGaji = 0.0;

        foreach ($guruList as $g) {
            $gid = (int)$g['id'];
            $sessions = SesiMengajar::getTeacherHistory($gid, $bulan, $tahun);

            $totalJp = 0;
            $totalPlafon = 0.0;
            $totalMenitTelat = 0;
            $totalDenda = 0.0;
            $totalHonorKbm = 0.0;

            foreach ($sessions as $s) {
                $totalJp += (int)$s['jumlah_jp'];
                $totalPlafon += (float)($s['jumlah_jp'] * $this->config['honor_per_jp']);
                $totalMenitTelat += (int)$s['menit_terlambat'];
                $totalDenda += (float)($s['menit_terlambat'] * $this->config['denda_per_menit']);
                $totalHonorKbm += (float)$s['honor_didapat'];
            }

            $tunjangan = (float)$g['tunjangan_tugas'];
            $takeHomePay = $totalHonorKbm + $tunjangan;

            $grandTotalJp += $totalJp;
            $grandTotalPlafon += $totalPlafon;
            $grandTotalMenitTelat += $totalMenitTelat;
            $grandTotalDenda += $totalDenda;
            $grandTotalHonorKbm += $totalHonorKbm;
            $grandTotalTunjangan += $tunjangan;
            $grandTotalGaji += $takeHomePay;

            $rekapData[] = [
                'guru' => $g,
                'total_sesi' => count($sessions),
                'total_jp' => $totalJp,
                'total_plafon' => $totalPlafon,
                'total_menit_telat' => $totalMenitTelat,
                'total_denda' => $totalDenda,
                'total_honor_kbm' => $totalHonorKbm,
                'tunjangan' => $tunjangan,
                'take_home_pay' => $takeHomePay,
            ];
        }

        $user = $this->user;
        $config = $this->config;

        require __DIR__ . '/../Views/payroll/index.php';
    }

    /**
     * Cetak Slip Gaji Resmi Guru Format PDF / Siap Print
     */
    public function cetakSlip(int $guruId, int $bulan, int $tahun): void
    {
        // Admin, kepala sekolah, bendahara bisa cetak semua, guru hanya miliknya sendiri
        if (!in_array($this->user['role'], ['admin', 'kepala_sekolah', 'bendahara']) && (int)$this->user['id'] !== $guruId) {
            App::redirect(App::baseUrl('guru/dompet'));
        }

        $guru = Guru::findById($guruId);
        if (!$guru) {
            die("Data guru tidak ditemukan.");
        }

        $sesiList = SesiMengajar::getTeacherHistory($guruId, $bulan, $tahun);

        echo PdfHelper::renderSlipGajiHtml([
            'guru' => $guru,
            'config' => $this->config,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'sesi_list' => $sesiList,
        ]);
        exit;
    }
}

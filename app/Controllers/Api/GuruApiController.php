<?php
namespace App\Controllers\Api;

use App\Config\App;
use App\Helpers\TimeHelper;
use App\Models\KonfigurasiSekolah;
use App\Models\JadwalPelajaran;
use App\Models\SesiMengajar;
use App\Models\Guru;

/**
 * GuruApiController - Dashboard, Jadwal, Honor, Profil
 * 
 * GET /api/v1/guru/dashboard
 * GET /api/v1/guru/jadwal
 * GET /api/v1/guru/honor
 * GET /api/v1/guru/profile
 * 
 * ROLLBACK: Hapus file ini
 */
class GuruApiController extends ApiController
{
    /**
     * GET /api/v1/guru/dashboard
     * 
     * Mengembalikan:
     * - Jadwal hari ini beserta status badge per sesi
     * - Ringkasan honor bulan ini
     * - Status presensi gerbang hari ini
     * 
     * Headers: Authorization: Bearer {token}
     */
    public function dashboard(): void
    {
        self::requireMethod('GET');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);

        $guruId = (int)$payload->uid;
        $config = KonfigurasiSekolah::get();
        $today  = date('Y-m-d');
        $hariIndo = TimeHelper::getDayName($today);
        $nowTimeStr = date('H:i:s');

        // Jadwal hari ini + status sesi
        $jadwalList = SesiMengajar::getTodayScheduleWithSession($guruId, $today, $hariIndo);

        foreach ($jadwalList as &$j) {
            $window = TimeHelper::checkActivationWindow(
                $j['jam_mulai'],
                (int)$config['toleransi_h_minus'],
                $nowTimeStr,
                $j['jam_selesai']
            );
            $j['window'] = $window;

            if (!empty($j['sesi_id'])) {
                $j['status_badge'] = !empty($j['waktu_checkout']) ? 'SELESAI' : 'SEDANG_MENGAJAR';
            } else {
                if ($window['status'] === 'CLOSED' || $nowTimeStr >= $j['jam_selesai']) {
                    $j['status_badge'] = 'TIDAK_HADIR';
                } elseif ($window['can_checkin']) {
                    $j['status_badge'] = 'SIAP_CHECKIN';
                } else {
                    $j['status_badge'] = 'AKAN_DATANG';
                }
            }

            // Format field numerik untuk JSON
            $j['jumlah_jp']          = (int)$j['jumlah_jp'];
            $j['menit_terlambat']    = (int)($j['menit_terlambat'] ?? 0);
            $j['honor_didapat']      = (float)($j['honor_didapat'] ?? 0);
            $j['jarak_meter']        = (float)($j['jarak_meter'] ?? 0);
            $j['sesi_id']            = isset($j['sesi_id']) ? (int)$j['sesi_id'] : null;
            $j['can_checkin']        = (bool)$window['can_checkin'];
            $j['seconds_remaining']  = (int)($window['seconds_remaining'] ?? 0);
        }
        unset($j);

        // Ringkasan honor bulan ini
        $bulan  = (int)date('m');
        $tahun  = (int)date('Y');
        $history = SesiMengajar::getTeacherHistory($guruId, $bulan, $tahun);

        $totalHonor = 0.0;
        $totalDenda = 0.0;
        $totalTelat = 0;
        foreach ($history as $h) {
            $totalHonor += (float)$h['honor_didapat'];
            $totalTelat += (int)$h['menit_terlambat'];
            $totalDenda += (float)($h['menit_terlambat'] * $config['denda_per_menit']);
        }

        // Presensi gerbang guru hari ini
        $presensiGerbang = \App\Models\PresensiGerbangGuru::getToday($guruId, $today);

        self::json([
            'success'   => true,
            'data'      => [
                'tanggal'     => $today,
                'hari'        => $hariIndo,
                'waktu_server'=> date('H:i:s'),
                'jadwal_hari_ini' => array_values($jadwalList),
                'ringkasan_honor' => [
                    'bulan'         => $bulan,
                    'tahun'         => $tahun,
                    'total_honor'   => $totalHonor,
                    'total_denda'   => $totalDenda,
                    'total_telat_menit' => $totalTelat,
                    'jumlah_sesi'   => count($history),
                ],
                'gerbang_hari_ini' => $presensiGerbang ? [
                    'waktu_datang'  => $presensiGerbang['waktu_datang'] ?? null,
                    'waktu_pulang'  => $presensiGerbang['waktu_pulang'] ?? null,
                    'status'        => $presensiGerbang['status_kehadiran'] ?? null,
                    'keterangan'    => $presensiGerbang['keterangan'] ?? null,
                ] : null,
                'config'      => [
                    'radius_meter'     => (int)$config['radius_meter'],
                    'toleransi_h_minus'=> (int)$config['toleransi_h_minus'],
                    'honor_per_jp'     => (float)$config['honor_per_jp'],
                    'denda_per_menit'  => (float)$config['denda_per_menit'],
                    'latitude_pusat'   => (float)$config['latitude_pusat'],
                    'longitude_pusat'  => (float)$config['longitude_pusat'],
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/jadwal
     * Seluruh jadwal guru (semua hari)
     * Query: ?hari=Senin (opsional filter)
     */
    public function jadwal(): void
    {
        self::requireMethod('GET');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);

        $guruId  = (int)$payload->uid;
        $hari    = $_GET['hari'] ?? null;

        $jadwal = JadwalPelajaran::getAll($hari, $guruId);

        // Format numerik
        foreach ($jadwal as &$j) {
            $j['jumlah_jp'] = (int)$j['jumlah_jp'];
        }
        unset($j);

        self::json([
            'success' => true,
            'data'    => array_values($jadwal),
            'total'   => count($jadwal),
        ]);
    }

    /**
     * GET /api/v1/guru/honor
     * Riwayat honor & denda guru
     * Query: ?bulan=9&tahun=2026 (default bulan & tahun ini)
     */
    public function honor(): void
    {
        self::requireMethod('GET');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);

        $guruId = (int)$payload->uid;
        $config = KonfigurasiSekolah::get();

        $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
        $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

        $history = SesiMengajar::getTeacherHistory($guruId, $bulan, $tahun);

        $totalJp     = 0;
        $totalPlafon = 0.0;
        $totalTelat  = 0;
        $totalDenda  = 0.0;
        $totalHonor  = 0.0;

        $rows = [];
        foreach ($history as $h) {
            $jp        = (int)$h['jumlah_jp'];
            $telat     = (int)$h['menit_terlambat'];
            $honor     = (float)$h['honor_didapat'];
            $plafon    = $jp * (float)$config['honor_per_jp'];
            $denda     = $telat * (float)$config['denda_per_menit'];

            $totalJp     += $jp;
            $totalPlafon += $plafon;
            $totalTelat  += $telat;
            $totalDenda  += $denda;
            $totalHonor  += $honor;

            $rows[] = [
                'sesi_id'           => (int)$h['id'],
                'tanggal'           => $h['tanggal'],
                'nama_mapel'        => $h['nama_mapel'],
                'nama_kelas'        => $h['nama_kelas'],
                'jumlah_jp'         => $jp,
                'jam_mulai'         => $h['jam_mulai'],
                'jam_selesai'       => $h['jam_selesai'],
                'waktu_checkin'     => $h['waktu_checkin'],
                'waktu_checkout'    => $h['waktu_checkout'],
                'menit_terlambat'   => $telat,
                'honor_plafon'      => round($plafon, 2),
                'potongan_denda'    => round($denda, 2),
                'honor_bersih'      => round($honor, 2),
                'status_verifikasi' => $h['status_verifikasi'],
            ];
        }

        // Ambil data tunjangan tugas dari DB guru
        $guruData = Guru::findById($guruId);
        $tunjangan = (float)($guruData['tunjangan_tugas'] ?? 0);

        self::json([
            'success'  => true,
            'data'     => [
                'bulan'           => $bulan,
                'tahun'           => $tahun,
                'riwayat'         => $rows,
                'ringkasan'       => [
                    'total_jp'        => $totalJp,
                    'total_plafon'    => round($totalPlafon, 2),
                    'total_telat_menit' => $totalTelat,
                    'total_denda'     => round($totalDenda, 2),
                    'total_honor_kbm' => round($totalHonor, 2),
                    'tunjangan_tugas' => $tunjangan,
                    'total_take_home' => round($totalHonor + $tunjangan, 2),
                ],
                'satuan_honor'    => [
                    'honor_per_jp'    => (float)$config['honor_per_jp'],
                    'denda_per_menit' => (float)$config['denda_per_menit'],
                ],
            ],
        ]);
    }

    /**
     * GET /api/v1/guru/profile
     * Data profil guru yang sedang login
     */
    public function profile(): void
    {
        self::requireMethod('GET');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);

        $guruId = (int)$payload->uid;
        $guru   = Guru::findById($guruId);

        if (!$guru) {
            self::json(['success' => false, 'message' => 'Data guru tidak ditemukan.'], 404);
        }

        unset($guru['password']);

        self::json([
            'success' => true,
            'data'    => [
                'id'             => (int)$guru['id'],
                'nik_nip'        => $guru['nik_nip'],
                'nama_lengkap'   => $guru['nama_lengkap'],
                'username'       => $guru['username'],
                'role'           => $guru['role'],
                'tugas_tambahan' => $guru['tugas_tambahan'] ?? null,
                'tunjangan_tugas'=> (float)($guru['tunjangan_tugas'] ?? 0),
                'no_hp'          => $guru['no_hp'] ?? null,
            ],
        ]);
    }
}

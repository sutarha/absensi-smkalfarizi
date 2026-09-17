<?php
namespace App\Controllers\Api;

use App\Config\App;
use App\Helpers\GeolocationHelper;
use App\Models\KonfigurasiSekolah;
use App\Models\PresensiGerbangGuru;

/**
 * GerbangApiController - Presensi Gerbang Mandiri Guru (Datang & Pulang)
 * 
 * Endpoints:
 * - GET  /api/v1/guru/gerbang/status
 * - POST /api/v1/guru/gerbang/datang
 * - POST /api/v1/guru/gerbang/pulang
 * 
 * ROLLBACK: Hapus file ini jika ingin membatalkan perubahan
 */
class GerbangApiController extends ApiController
{
    /**
     * GET /api/v1/guru/gerbang/status
     * Mendapatkan status presensi gerbang guru hari ini beserta info konfigurasi jam & radius
     */
    public function status(): void
    {
        self::requireMethod('GET');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $guruId  = (int)$payload->uid;

        $today = date('Y-m-d');
        $nowTime = date('H:i:s');
        $config = KonfigurasiSekolah::get();

        $todayRecord = PresensiGerbangGuru::getToday($guruId, $today);

        $jamMasukSelesai = $config['jam_guru_masuk_selesai'] ?? $config['jam_gerbang_masuk_selesai'] ?? '06:30:00';
        $jamPulangMulai   = $config['jam_guru_pulang_mulai'] ?? $config['jam_gerbang_pulang_mulai'] ?? '13:00:00';

        $isNonHadir = $todayRecord && in_array($todayRecord['status_kehadiran'] ?? '', ['TUGAS_LUAR', 'IZIN', 'SAKIT']);
        $sudahDatang = $todayRecord && (!empty($todayRecord['waktu_datang']) || $isNonHadir);
        $sudahPulang = $todayRecord && !empty($todayRecord['waktu_pulang']);

        $canTapDatang = !$sudahDatang;
        $pulangBelumBuka = $sudahDatang && !$sudahPulang && !$isNonHadir && ($nowTime < $jamPulangMulai);
        $canTapPulang = $sudahDatang && !$sudahPulang && !$isNonHadir && ($nowTime >= $jamPulangMulai);

        self::json([
            'success' => true,
            'data' => [
                'presensi_hari_ini' => $todayRecord,
                'config' => [
                    'jam_masuk_selesai' => substr($jamMasukSelesai, 0, 5),
                    'jam_pulang_mulai'   => substr($jamPulangMulai, 0, 5),
                    'latitude_pusat'    => (float)($config['latitude_pusat'] ?? -6.91746),
                    'longitude_pusat'   => (float)($config['longitude_pusat'] ?? 107.61912),
                    'radius_meter'      => (int)($config['radius_meter'] ?? 100),
                ],
                'flags' => [
                    'sudah_datang'      => (bool)$sudahDatang,
                    'sudah_pulang'      => (bool)$sudahPulang,
                    'is_non_hadir'      => (bool)$isNonHadir,
                    'can_tap_datang'    => (bool)$canTapDatang,
                    'can_tap_pulang'    => (bool)$canTapPulang,
                    'pulang_belum_buka' => (bool)$pulangBelumBuka,
                    'waktu_sekarang'    => date('H:i:s'),
                ]
            ]
        ]);
    }

    /**
     * POST /api/v1/guru/gerbang/datang
     * 
     * Body JSON:
     * {
     *   "status_pilihan": "HADIR" | "TUGAS_LUAR" | "IZIN" | "SAKIT",
     *   "keterangan": "alasan jika non-hadir",
     *   "latitude": -6.xxxx,
     *   "longitude": 107.xxxx,
     *   "bypass_gps": false
     * }
     */
    public function tapDatang(): void
    {
        self::requireMethod('POST');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $guruId  = (int)$payload->uid;

        $body = self::jsonBody();
        $statusPilihan = strtoupper(trim($body['status_pilihan'] ?? $_POST['status_pilihan'] ?? 'HADIR'));
        $keterangan    = trim($body['keterangan'] ?? $_POST['keterangan'] ?? '');
        $userLat       = isset($body['latitude']) ? (float)$body['latitude'] : (isset($_POST['latitude']) ? (float)$_POST['latitude'] : null);
        $userLng       = isset($body['longitude']) ? (float)$body['longitude'] : (isset($_POST['longitude']) ? (float)$_POST['longitude'] : null);
        
        $bypassRequested = (!empty($body['bypass_gps']) && $body['bypass_gps'] === true) || (isset($_POST['bypass_gps']) && $_POST['bypass_gps'] === '1');
        $canBypass = ($payload->role === 'admin') || App::isDevMode();
        $bypassGps = $bypassRequested && $canBypass;

        // 1. Non-Hadir (Tugas Luar, Izin, Sakit)
        if (in_array($statusPilihan, ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
            if (empty($keterangan)) {
                $nama = ($statusPilihan === 'TUGAS_LUAR') ? 'Tugas Luar' : ucfirst(strtolower($statusPilihan));
                self::json([
                    'success' => false,
                    'message' => "Harap masukkan keterangan/alasan untuk pengajuan {$nama}."
                ], 422);
            }

            $res = PresensiGerbangGuru::recordTapDatang(
                $guruId,
                $statusPilihan,
                null,
                null,
                0.0,
                0,
                $keterangan
            );

            self::json($res, $res['success'] ? 200 : 400);
        }

        // 2. HADIR (Wajib validasi GPS)
        $config = KonfigurasiSekolah::get();
        $schoolLat = (float)($config['latitude_pusat'] ?? -6.91746);
        $schoolLng = (float)($config['longitude_pusat'] ?? 107.61912);
        $radiusMeters = (int)($config['radius_meter'] ?? 100);

        $distance = 0.0;
        if ($userLat === null || $userLng === null) {
            if (!$bypassGps) {
                self::json([
                    'success' => false,
                    'message' => 'Koordinat GPS tidak terdeteksi. Harap aktifkan izin lokasi di smartphone Anda!'
                ], 400);
            }
            $userLat = $schoolLat;
            $userLng = $schoolLng;
            $distance = 5.0;
        } else {
            $geoCheck = GeolocationHelper::isWithinRadius($userLat, $userLng, $schoolLat, $schoolLng, $radiusMeters);
            $distance = $geoCheck['distance_meters'];

            if (!$geoCheck['is_valid'] && !$bypassGps) {
                self::json([
                    'success' => false,
                    'message' => "Presensi datang ditolak! Anda berada di luar radius sekolah. Jarak: {$distance} m (Batas: {$radiusMeters} m).",
                    'distance' => $distance,
                    'radius_limit' => $radiusMeters
                ], 403);
            }
        }

        // 3. Cek keterlambatan datang (Batas masuk: 06:30)
        $now = date('Y-m-d H:i:s');
        $nowTime = date('H:i:s');
        $jamBatas = $config['jam_guru_masuk_selesai'] ?? $config['jam_gerbang_masuk_selesai'] ?? '06:30:00';
        $menitTelat = 0;

        if ($nowTime > $jamBatas) {
            $menitTelat = (int)max(0, floor((strtotime($nowTime) - strtotime($jamBatas)) / 60));
        }

        $res = PresensiGerbangGuru::recordTapDatang(
            $guruId,
            'HADIR',
            $userLat,
            $userLng,
            $distance,
            $menitTelat,
            $keterangan ?: ($menitTelat > 0 ? "Terlambat {$menitTelat} menit" : "Tepat Waktu"),
            $now
        );

        self::json($res, $res['success'] ? 200 : 400);
    }

    /**
     * POST /api/v1/guru/gerbang/pulang
     * 
     * Body JSON:
     * {
     *   "keterangan": "catatan opsional",
     *   "latitude": -6.xxxx,
     *   "longitude": 107.xxxx,
     *   "bypass_gps": false
     * }
     */
    public function tapPulang(): void
    {
        self::requireMethod('POST');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $guruId  = (int)$payload->uid;

        $body = self::jsonBody();
        $keterangan = trim($body['keterangan'] ?? $_POST['keterangan'] ?? '');
        $userLat    = isset($body['latitude']) ? (float)$body['latitude'] : (isset($_POST['latitude']) ? (float)$_POST['latitude'] : null);
        $userLng    = isset($body['longitude']) ? (float)$body['longitude'] : (isset($_POST['longitude']) ? (float)$_POST['longitude'] : null);

        $bypassRequested = (!empty($body['bypass_gps']) && $body['bypass_gps'] === true) || (isset($_POST['bypass_gps']) && $_POST['bypass_gps'] === '1');
        $canBypass = ($payload->role === 'admin') || App::isDevMode();
        $bypassGps = $bypassRequested && $canBypass;

        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $nowTime = date('H:i:s');
        $config = KonfigurasiSekolah::get();

        $existing = PresensiGerbangGuru::getToday($guruId, $today);
        if (!$existing || empty($existing['waktu_datang'])) {
            self::json([
                'success' => false,
                'message' => 'Anda belum melakukan Tap Datang hari ini! Lakukan Tap Datang terlebih dahulu.'
            ], 400);
        }

        if (in_array($existing['status_kehadiran'], ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
            self::json([
                'success' => true,
                'is_repeat' => true,
                'message' => "Status presensi hari ini adalah [{$existing['status_kehadiran']}]. Tidak diperlukan Tap Pulang."
            ]);
        }

        if (!empty($existing['waktu_pulang'])) {
            self::json([
                'success' => true,
                'is_repeat' => true,
                'message' => 'Anda sudah melakukan Tap Pulang sebelumnya pada pukul ' . date('H:i', strtotime($existing['waktu_pulang'])) . ' WIB.'
            ]);
        }

        // Validasi jam pulang (default 13:00)
        $jamBukaPulang = $config['jam_guru_pulang_mulai'] ?? $config['jam_gerbang_pulang_mulai'] ?? '13:00:00';
        if ($nowTime < $jamBukaPulang && !$canBypass) {
            $jamBukaStr = substr($jamBukaPulang, 0, 5);
            self::json([
                'success' => false,
                'message' => "Presensi pulang belum dibuka. Jam kepulangan guru baru dapat dilakukan sesudah pukul {$jamBukaStr} WIB."
            ], 400);
        }

        // Geofencing
        $schoolLat = (float)($config['latitude_pusat'] ?? -6.91746);
        $schoolLng = (float)($config['longitude_pusat'] ?? 107.61912);
        $radiusMeters = (int)($config['radius_meter'] ?? 100);

        $distance = 0.0;
        if ($userLat === null || $userLng === null) {
            if (!$bypassGps) {
                self::json([
                    'success' => false,
                    'message' => 'Koordinat GPS tidak terdeteksi. Harap aktifkan izin lokasi di smartphone Anda!'
                ], 400);
            }
            $userLat = $schoolLat;
            $userLng = $schoolLng;
            $distance = 5.0;
        } else {
            $geoCheck = GeolocationHelper::isWithinRadius($userLat, $userLng, $schoolLat, $schoolLng, $radiusMeters);
            $distance = $geoCheck['distance_meters'];

            if (!$geoCheck['is_valid'] && !$bypassGps) {
                self::json([
                    'success' => false,
                    'message' => "Presensi pulang ditolak! Anda berada di luar radius sekolah. Jarak: {$distance} m (Batas: {$radiusMeters} m).",
                    'distance' => $distance,
                    'radius_limit' => $radiusMeters
                ], 403);
            }
        }

        $res = PresensiGerbangGuru::recordTapPulang(
            $guruId,
            $userLat,
            $userLng,
            $distance,
            $keterangan,
            $now
        );

        self::json($res, $res['success'] ? 200 : 400);
    }
}

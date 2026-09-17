<?php
namespace App\Controllers\Api;

use App\Config\App;
use App\Helpers\GeolocationHelper;
use App\Helpers\TimeHelper;
use App\Models\KonfigurasiSekolah;
use App\Models\JadwalPelajaran;
use App\Models\SesiMengajar;
use App\Models\PresensiMapel;

/**
 * PresensiApiController - Check-in KBM, Checkout, Presensi Siswa
 * 
 * POST /api/v1/guru/checkin
 * POST /api/v1/guru/checkout/{sesiId}
 * GET  /api/v1/guru/presensi/{sesiId}
 * POST /api/v1/guru/presensi/{sesiId}
 * 
 * ROLLBACK: Hapus file ini
 */
class PresensiApiController extends ApiController
{
    /**
     * POST /api/v1/guru/checkin
     * 
     * Request body (JSON):
     * {
     *   "jadwal_id": 5,
     *   "latitude": -6.91746,
     *   "longitude": 107.61912
     * }
     * 
     * Response sukses:
     * { "success": true, "sesi_id": 42, "menit_terlambat": 0, "honor_didapat": 10000 }
     */
    public function checkin(): void
    {
        self::requireMethod('POST');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);

        $guruId  = (int)$payload->uid;
        $config  = KonfigurasiSekolah::get();

        $body     = self::jsonBody();
        $jadwalId = (int)($body['jadwal_id'] ?? $_POST['jadwal_id'] ?? 0);
        $userLat  = isset($body['latitude'])  ? (float)$body['latitude']  : (isset($_POST['latitude'])  ? (float)$_POST['latitude']  : null);
        $userLng  = isset($body['longitude']) ? (float)$body['longitude'] : (isset($_POST['longitude']) ? (float)$_POST['longitude'] : null);

        if ($jadwalId <= 0) {
            self::json(['success' => false, 'message' => 'jadwal_id tidak valid.'], 422);
        }

        $jadwal = JadwalPelajaran::findById($jadwalId);
        if (!$jadwal || (int)$jadwal['guru_id'] !== $guruId) {
            self::json(['success' => false, 'message' => 'Jadwal pelajaran tidak ditemukan atau bukan milik Anda.'], 404);
        }

        $today      = date('Y-m-d');
        $now        = date('Y-m-d H:i:s');
        $nowTimeStr = date('H:i:s');

        // Cek apakah sudah check-in untuk sesi ini
        $existing = SesiMengajar::getSessionByJadwalDate($jadwalId, $today);
        if ($existing) {
            self::json([
                'success'  => false,
                'message'  => 'Anda sudah check-in untuk sesi ini pada jam ' . date('H:i', strtotime($existing['waktu_checkin'])) . '.',
                'sesi_id'  => (int)$existing['id'],
                'code'     => 'ALREADY_CHECKED_IN',
            ], 409);
        }

        // Validasi batas jam mengajar sudah lewat
        if ($nowTimeStr >= $jadwal['jam_selesai']) {
            self::json([
                'success' => false,
                'message' => 'Jam mengajar telah berakhir pukul ' . substr($jadwal['jam_selesai'], 0, 5) . ' WIB. Presensi KBM ditutup.',
                'code'    => 'TIME_EXPIRED',
            ], 400);
        }

        // Validasi jendela aktivasi (H-5 menit)
        $window = TimeHelper::checkActivationWindow(
            $jadwal['jam_mulai'],
            (int)$config['toleransi_h_minus'],
            $nowTimeStr,
            $jadwal['jam_selesai']
        );
        if (!$window['can_checkin']) {
            self::json([
                'success'           => false,
                'message'           => $window['message'],
                'seconds_remaining' => (int)($window['seconds_remaining'] ?? 0),
                'code'              => 'NOT_YET_OPEN',
            ], 400);
        }

        // Validasi GPS Geofence
        $schoolLat    = (float)$config['latitude_pusat'];
        $schoolLng    = (float)$config['longitude_pusat'];
        $radiusMeters = (int)$config['radius_meter'];

        if ($userLat === null || $userLng === null) {
            self::json([
                'success' => false,
                'message' => 'Koordinat GPS tidak terdeteksi. Harap aktifkan izin lokasi.',
                'code'    => 'GPS_NOT_FOUND',
            ], 400);
        }

        $geoCheck = GeolocationHelper::isWithinRadius($userLat, $userLng, $schoolLat, $schoolLng, $radiusMeters);
        $distance = $geoCheck['distance_meters'];

        if (!$geoCheck['is_valid']) {
            self::json([
                'success'       => false,
                'message'       => "Check-in ditolak! Anda berada di luar radius sekolah. Jarak Anda: {$distance} meter (batas: {$radiusMeters} meter).",
                'distance_meter'=> $distance,
                'radius_limit'  => $radiusMeters,
                'code'          => 'OUTSIDE_GEOFENCE',
            ], 403);
        }

        // Kalkulasi keterlambatan & honor
        $calc = TimeHelper::calculateHonor(
            (int)$jadwal['jumlah_jp'],
            $jadwal['jam_mulai'],
            $now,
            (float)$config['honor_per_jp'],
            (int)$config['durasi_jp_menit'],
            (float)$config['denda_per_menit']
        );

        // Simpan sesi mengajar
        $sesiId = SesiMengajar::startSession(
            $jadwalId, $guruId, $today, $now,
            $userLat, $userLng, $distance,
            $calc['menit_terlambat'],
            $calc['durasi_efektif_menit'],
            $calc['honor_didapat'],
            'VALID'
        );

        self::json([
            'success'          => true,
            'message'          => 'Check-in KBM berhasil! ' . ($calc['menit_terlambat'] > 0
                ? "Anda terlambat {$calc['menit_terlambat']} menit."
                : 'Anda tepat waktu.'),
            'sesi_id'          => $sesiId,
            'menit_terlambat'  => (int)$calc['menit_terlambat'],
            'potongan_denda'   => (float)$calc['potongan_denda'],
            'honor_didapat'    => (float)$calc['honor_didapat'],
            'status_telat'     => $calc['status_telat'],
            'distance_meter'   => $distance,
            'nama_mapel'       => $jadwal['nama_mapel'],
            'nama_kelas'       => $jadwal['nama_kelas'],
        ]);
    }

    /**
     * POST /api/v1/guru/checkout/{sesiId}
     * 
     * Request body (JSON) opsional:
     * { "catatan_guru": "Materi sudah selesai bab 3" }
     */
    public function checkout(int $sesiId): void
    {
        self::requireMethod('POST');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);

        $guruId = (int)$payload->uid;

        $sesi = SesiMengajar::getById($sesiId);
        if (!$sesi) {
            self::json(['success' => false, 'message' => 'Sesi mengajar tidak ditemukan.'], 404);
        }
        if ((int)$sesi['guru_id'] !== $guruId && $payload->role !== 'admin') {
            self::json(['success' => false, 'message' => 'Anda tidak berhak mengakhiri sesi ini.'], 403);
        }
        if (!empty($sesi['waktu_checkout'])) {
            self::json([
                'success'         => false,
                'message'         => 'Sesi ini sudah diakhiri pada ' . $sesi['waktu_checkout'] . '.',
                'waktu_checkout'  => $sesi['waktu_checkout'],
                'code'            => 'ALREADY_CHECKED_OUT',
            ], 409);
        }

        // Validasi belum melewati jam selesai (kecuali admin)
        $nowTime = date('H:i:s');
        if (date('Y-m-d') === $sesi['tanggal'] && $nowTime < $sesi['jam_selesai'] && $payload->role !== 'admin') {
            self::json([
                'success' => false,
                'message' => 'Sesi KBM belum dapat diakhiri sebelum jam ' . substr($sesi['jam_selesai'], 0, 5) . ' WIB.',
                'code'    => 'TOO_EARLY',
            ], 400);
        }

        $body    = self::jsonBody();
        $catatan = $body['catatan_guru'] ?? $_POST['catatan_guru'] ?? null;
        $now     = date('Y-m-d H:i:s');

        SesiMengajar::finishSession($sesiId, $now, $catatan);

        self::json([
            'success'        => true,
            'message'        => 'Sesi KBM ' . $sesi['nama_mapel'] . ' telah selesai. Saldo honor telah diperbarui.',
            'sesi_id'        => $sesiId,
            'waktu_checkout' => $now,
            'nama_mapel'     => $sesi['nama_mapel'],
            'honor_didapat'  => (float)$sesi['honor_didapat'],
        ]);
    }

    /**
     * GET /api/v1/guru/presensi/{sesiId}
     * Ambil daftar siswa + status presensi untuk sesi mengajar tertentu
     */
    public function getSiswaPresensi(int $sesiId): void
    {
        self::requireMethod('GET');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);

        $guruId = (int)$payload->uid;
        $sesi   = SesiMengajar::getById($sesiId);

        if (!$sesi) {
            self::json(['success' => false, 'message' => 'Sesi mengajar tidak ditemukan.'], 404);
        }
        if ((int)$sesi['guru_id'] !== $guruId && $payload->role !== 'admin') {
            self::json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $jadwal    = JadwalPelajaran::findById((int)$sesi['jadwal_id']);
        $siswaList = PresensiMapel::getBySesi($sesiId, (int)$jadwal['kelas_id']);

        // Format output siswa
        $students = [];
        foreach ($siswaList as $s) {
            $students[] = [
                'siswa_id'   => (int)$s['siswa_id'],
                'nisn'       => $s['nisn'],
                'nama_siswa' => $s['nama_siswa'],
                'jenis_kelamin' => $s['jenis_kelamin'],
                'status'     => $s['status'] ?? 'HADIR',
                'catatan'    => $s['catatan'] ?? null,
            ];
        }

        self::json([
            'success' => true,
            'data'    => [
                'sesi'     => [
                    'id'             => (int)$sesi['id'],
                    'tanggal'        => $sesi['tanggal'],
                    'nama_mapel'     => $sesi['nama_mapel'],
                    'nama_kelas'     => $sesi['nama_kelas'],
                    'jam_mulai'      => $sesi['jam_mulai'],
                    'jam_selesai'    => $sesi['jam_selesai'],
                    'waktu_checkin'  => $sesi['waktu_checkin'],
                    'waktu_checkout' => $sesi['waktu_checkout'],
                ],
                'siswa'    => $students,
                'total_siswa' => count($students),
            ],
        ]);
    }

    /**
     * POST /api/v1/guru/presensi/{sesiId}
     * Submit presensi siswa untuk sesi mengajar
     * 
     * Request body (JSON):
     * {
     *   "presensi": [
     *     { "siswa_id": 1, "status": "HADIR", "catatan": "" },
     *     { "siswa_id": 2, "status": "SAKIT", "catatan": "Demam" }
     *   ]
     * }
     */
    public function savePresensi(int $sesiId): void
    {
        self::requireMethod('POST');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);

        $guruId = (int)$payload->uid;
        $sesi   = SesiMengajar::getById($sesiId);

        if (!$sesi) {
            self::json(['success' => false, 'message' => 'Sesi mengajar tidak ditemukan.'], 404);
        }
        if ((int)$sesi['guru_id'] !== $guruId && $payload->role !== 'admin') {
            self::json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $body      = self::jsonBody();
        $presensiArr = $body['presensi'] ?? [];

        if (empty($presensiArr) || !is_array($presensiArr)) {
            self::json(['success' => false, 'message' => 'Data presensi tidak boleh kosong. Format: {"presensi": [...]}'], 422);
        }

        // Ubah ke format yang dipakai PresensiMapel::saveBatch()
        $dataToSave = [];
        $validStatuses = ['HADIR', 'SAKIT', 'IZIN', 'ALPHA'];
        foreach ($presensiArr as $item) {
            $siswaId = (int)($item['siswa_id'] ?? 0);
            $status  = strtoupper($item['status'] ?? 'HADIR');
            if (!in_array($status, $validStatuses)) {
                $status = 'HADIR';
            }
            if ($siswaId > 0) {
                $dataToSave[$siswaId] = [
                    'status'  => $status,
                    'catatan' => $item['catatan'] ?? null,
                ];
            }
        }

        if (empty($dataToSave)) {
            self::json(['success' => false, 'message' => 'Tidak ada data presensi siswa valid.'], 422);
        }

        $ok = PresensiMapel::saveBatch($sesiId, $dataToSave);

        if (!$ok) {
            self::json(['success' => false, 'message' => 'Gagal menyimpan presensi. Coba lagi.'], 500);
        }

        self::json([
            'success'        => true,
            'message'        => 'Presensi ' . count($dataToSave) . ' siswa berhasil disimpan.',
            'sesi_id'        => $sesiId,
            'jumlah_diproses'=> count($dataToSave),
        ]);
    }
}

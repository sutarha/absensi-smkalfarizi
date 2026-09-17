<?php
namespace App\Controllers;

use App\Config\App;
use App\Helpers\GeolocationHelper;
use App\Helpers\TimeHelper;
use App\Models\KonfigurasiSekolah;
use App\Models\JadwalPelajaran;
use App\Models\SesiMengajar;
use App\Models\PresensiMapel;
use App\Models\PresensiGerbangGuru;
use App\Models\Notifikasi;

class GuruController
{
    private array $user;
    private array $config;

    public function __construct()
    {
        $this->user = AuthController::requireRole(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $this->config = KonfigurasiSekolah::get();
    }

    private function canBypassGps(): bool
    {
        return ($this->user['role'] === 'admin') || App::isDevMode();
    }

    public function dashboard(): void
    {
        $guruId = (int)$this->user['id'];
        $today = date('Y-m-d');
        $hariIndo = TimeHelper::getDayName($today);

        // Ambil jadwal hari ini beserta sesi aktifnya
        $jadwalList = SesiMengajar::getTodayScheduleWithSession($guruId, $today, $hariIndo);

        $nowTimeStr = date('H:i:s');

        // Periksa status aktivasi H-5 dan batas jam selesai untuk setiap jadwal
        foreach ($jadwalList as &$j) {
            $window = TimeHelper::checkActivationWindow(
                $j['jam_mulai'], 
                (int)$this->config['toleransi_h_minus'], 
                $nowTimeStr, 
                $j['jam_selesai']
            );
            $j['window'] = $window;

            if (!empty($j['sesi_id'])) {
                if (!empty($j['waktu_checkout'])) {
                    $j['status_badge'] = 'SELESAI';
                } else {
                    $j['status_badge'] = 'SEDANG_MENGAJAR';
                }
            } else {
                if ($window['status'] === 'CLOSED' || $nowTimeStr >= $j['jam_selesai']) {
                    $j['status_badge'] = 'TIDAK_HADIR';
                } elseif ($window['can_checkin']) {
                    $j['status_badge'] = 'SIAP_CHECKIN';
                } else {
                    $j['status_badge'] = 'AKAN_DATANG';
                }
            }
        }
        unset($j);

        // Ringkasan dompet honor bulan ini
        $bulanIni = (int)date('m');
        $tahunIni = (int)date('Y');
        $history = SesiMengajar::getTeacherHistory($guruId, $bulanIni, $tahunIni);

        $totalHonorBulanIni = 0.0;
        $totalTelatBulanIni = 0;
        $totalDendaBulanIni = 0.0;
        foreach ($history as $h) {
            $totalHonorBulanIni += (float)$h['honor_didapat'];
            $totalTelatBulanIni += (int)$h['menit_terlambat'];
            $totalDendaBulanIni += (float)($h['menit_terlambat'] * $this->config['denda_per_menit']);
        }

        // Data Penilaian KBM Semester Berjalan
        $activeTapel = \App\Models\TahunPelajaran::getActive();
        $kelasMapelGuru = JadwalPelajaran::getDistinctKelasMapelByGuru($guruId);
        $isGenap = ($activeTapel && strtolower($activeTapel['semester']) === 'genap');
        foreach ($kelasMapelGuru as &$km) {
            $tingkat = strtoupper(trim((string)$km['tingkat']));
            if ($tingkat === 'X' || $tingkat === '10') {
                $km['semester_rekomendasi'] = $isGenap ? 2 : 1;
            } elseif ($tingkat === 'XI' || $tingkat === '11') {
                $km['semester_rekomendasi'] = $isGenap ? 4 : 3;
            } elseif ($tingkat === 'XII' || $tingkat === '12') {
                $km['semester_rekomendasi'] = $isGenap ? 6 : 5;
            } else {
                $km['semester_rekomendasi'] = $isGenap ? 2 : 1;
            }
        }
        unset($km);

        // Presensi Gerbang Harian Guru Hari Ini
        $presensiGerbangHariIni = PresensiGerbangGuru::getToday($guruId, $today);

        // Cek akses Tabungan (semua guru bisa masuk ke dashboard tabungan)
        $isPetugasTabungan = true;

        // Notifikasi Guru
        $notifikasiList = Notifikasi::getForGuru($guruId, 20);
        $unreadNotifCount = Notifikasi::getUnreadCountForGuru($guruId);

        $user = $this->user;
        $config = $this->config;
        $activeNav = 'beranda';

        require __DIR__ . '/../Views/guru/dashboard.php';
    }

    /**
     * AJAX Endpoint: Check-in KBM Guru dengan Validasi GPS Geofence & Perhitungan Denda Telat
     */
    public function checkinKbm(): void
    {
        $guruId = (int)$this->user['id'];
        $jadwalId = (int)($_POST['jadwal_id'] ?? 0);
        $userLat = isset($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $userLng = isset($_POST['longitude']) ? (float)$_POST['longitude'] : null;
        $bypassGps = $this->canBypassGps() && (isset($_POST['bypass_gps']) && $_POST['bypass_gps'] === '1');

        if ($jadwalId <= 0) {
            App::json(['success' => false, 'message' => 'Jadwal pelajaran tidak valid!'], 400);
        }

        $jadwal = JadwalPelajaran::findById($jadwalId);
        if (!$jadwal || (int)$jadwal['guru_id'] !== $guruId) {
            App::json(['success' => false, 'message' => 'Jadwal pelajaran tidak ditemukan atau bukan milik Anda!'], 404);
        }

        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        // Cek apakah sudah pernah check-in untuk sesi hari ini
        $existing = SesiMengajar::getSessionByJadwalDate($jadwalId, $today);
        if ($existing) {
            App::json([
                'success' => false,
                'message' => 'Anda sudah melakukan check-in untuk sesi mengajar ini pada jam ' . date('H:i', strtotime($existing['waktu_checkin'])),
                'sesi_id' => $existing['id']
            ], 400);
        }

        // 1. Validasi Batas Jam Mengajar: Jika sudah lewat jam_selesai, absen ditutup dan dianggap Tidak Hadir
        $nowTimeStr = date('H:i:s');
        if ($nowTimeStr >= $jadwal['jam_selesai'] && !$bypassGps) {
            App::json([
                'success' => false,
                'message' => "Jam mengajar untuk jadwal ini telah berakhir pada pukul " . substr($jadwal['jam_selesai'], 0, 5) . " WIB. Presensi KBM telah ditutup dan Anda dinyatakan Tidak Hadir."
            ], 400);
        }

        // 2. Validasi Jendela Aktivasi Tombol (H-5 Menit)
        $window = TimeHelper::checkActivationWindow(
            $jadwal['jam_mulai'], 
            (int)$this->config['toleransi_h_minus'], 
            $nowTimeStr, 
            $jadwal['jam_selesai']
        );
        if (!$window['can_checkin'] && !$bypassGps) {
            App::json([
                'success' => false,
                'message' => $window['message'],
                'seconds_remaining' => $window['seconds_remaining']
            ], 400);
        }

        // 2. Validasi Koordinat GPS Geofencing
        $schoolLat = (float)$this->config['latitude_pusat'];
        $schoolLng = (float)$this->config['longitude_pusat'];
        $radiusMeters = (int)$this->config['radius_meter'];

        $distance = 0.0;
        $statusVerif = 'VALID';

        if ($userLat === null || $userLng === null) {
            if (!$bypassGps) {
                App::json([
                    'success' => false,
                    'message' => 'Koordinat GPS tidak terdeteksi. Harap aktifkan izin lokasi di smartphone Anda!'
                ], 400);
            }
            // Jika bypass GPS untuk simulasi lokal:
            $userLat = $schoolLat;
            $userLng = $schoolLng;
            $distance = 5.0;
        } else {
            $geoCheck = GeolocationHelper::isWithinRadius($userLat, $userLng, $schoolLat, $schoolLng, $radiusMeters);
            $distance = $geoCheck['distance_meters'];

            if (!$geoCheck['is_valid'] && !$bypassGps) {
                App::json([
                    'success' => false,
                    'message' => "Check-in ditolak! Anda berada di luar radius sekolah. Jarak Anda: {$distance} meter (Batas toleransi: {$radiusMeters} meter).",
                    'distance' => $distance,
                    'radius_limit' => $radiusMeters
                ], 403);
            }
        }

        // 3. Kalkulasi Keterlambatan dan Honor Riil sesuai Aturan PRD
        $calc = TimeHelper::calculateHonor(
            (int)$jadwal['jumlah_jp'],
            $jadwal['jam_mulai'],
            $now,
            (float)$this->config['honor_per_jp'],
            (int)$this->config['durasi_jp_menit'],
            (float)$this->config['denda_per_menit']
        );

        // 4. Simpan Sesi Mengajar
        $sesiId = SesiMengajar::startSession(
            $jadwalId,
            $guruId,
            $today,
            $now,
            $userLat,
            $userLng,
            $distance,
            $calc['menit_terlambat'],
            $calc['durasi_efektif_menit'],
            $calc['honor_didapat'],
            $statusVerif
        );

        App::json([
            'success' => true,
            'message' => "Check-in KBM berhasil! Status: {$calc['status_telat']}.",
            'sesi_id' => $sesiId,
            'redirect_url' => App::baseUrl("guru/presensi/{$sesiId}"),
            'menit_terlambat' => $calc['menit_terlambat'],
            'potongan_denda' => $calc['potongan_denda'],
            'honor_didapat' => $calc['honor_didapat'],
            'status_telat' => $calc['status_telat'],
            'distance_meters' => $distance
        ]);
    }

    /**
     * Tampilan Presensi Siswa per Sesi Mapel
     */
    public function presensiMapelView(int $sesiId): void
    {
        $sesi = SesiMengajar::getById($sesiId);
        if (!$sesi) {
            die("Sesi mengajar tidak ditemukan.");
        }

        // Pastikan hanya guru yang bersangkutan atau admin yang dapat mengisi
        if ((int)$sesi['guru_id'] !== (int)$this->user['id'] && $this->user['role'] !== 'admin') {
            die("Akses ditolak.");
        }

        $jadwal = JadwalPelajaran::findById((int)$sesi['jadwal_id']);
        $siswaList = PresensiMapel::getBySesi($sesiId, (int)$jadwal['kelas_id']);

        $user = $this->user;
        $config = $this->config;

        require __DIR__ . '/../Views/guru/presensi_mapel.php';
    }

    /**
     * Simpan Presensi Siswa per Sesi Mapel
     */
    public function presensiMapelSave(int $sesiId): void
    {
        $sesi = SesiMengajar::getById($sesiId);
        if (!$sesi || ((int)$sesi['guru_id'] !== (int)$this->user['id'] && $this->user['role'] !== 'admin')) {
            die("Akses tidak sah.");
        }

        $presensiPost = $_POST['presensi'] ?? [];
        $catatanPost = $_POST['catatan'] ?? [];

        $dataToSave = [];
        foreach ($presensiPost as $siswaId => $status) {
            $dataToSave[$siswaId] = [
                'status' => $status,
                'catatan' => $catatanPost[$siswaId] ?? null,
            ];
        }

        PresensiMapel::saveBatch($sesiId, $dataToSave);

        // --- FASE 5: PUSH NOTIFICATION ---
        // Jika ada siswa yang statusnya bukan HADIR, kirim notifikasi
        try {
            $db = \App\Config\Database::getConnection();
            $stmtSiswa = $db->prepare("SELECT id, fcm_token, nama_siswa FROM siswa WHERE id = ?");
            $fcmService = new \App\Services\FcmService();

            foreach ($presensiPost as $siswaId => $status) {
                if (in_array($status, ['ALPHA', 'TELAT', 'IZIN'])) {
                    $stmtSiswa->execute([$siswaId]);
                    $siswaData = $stmtSiswa->fetch();
                    
                    if ($siswaData && !empty($siswaData['fcm_token'])) {
                        $mapelNama = $sesi['nama_mapel'] ?? 'Pelajaran';
                        $title = "Pemberitahuan Kehadiran";
                        $body = "Kamu tercatat " . $status . " pada mapel " . $mapelNama . ".";
                        if (!empty($catatanPost[$siswaId])) {
                            $body .= " Catatan: " . $catatanPost[$siswaId];
                        }
                        
                        $fcmService->sendToToken($siswaData['fcm_token'], $title, $body, '/siswa/absensi');
                    }
                }
            }
        } catch (\Throwable $th) {
            // Abaikan error FCM agar tidak memblokir save absensi
            error_log("Gagal mengirim FCM: " . $th->getMessage());
        }

        $_SESSION['flash_success'] = 'Presensi siswa berhasil diperbarui!';
        App::redirect(App::baseUrl("guru/presensi/{$sesiId}"));
    }

    /**
     * Guru menekan tombol "Selesai Mengajar"
     */
    public function selesaiMengajar(int $sesiId): void
    {
        $sesi = SesiMengajar::getById($sesiId);
        if (!$sesi || ((int)$sesi['guru_id'] !== (int)$this->user['id'] && $this->user['role'] !== 'admin')) {
            die("Akses tidak sah.");
        }

        // Cek apakah jam pembelajaran sudah berakhir (kecuali jika role admin)
        $nowTime = date('H:i:s');
        if (date('Y-m-d') === $sesi['tanggal'] && $nowTime < $sesi['jam_selesai'] && $this->user['role'] !== 'admin') {
            $_SESSION['flash_error'] = "Sesi KBM belum dapat diakhiri sebelum jam pelajaran selesai (pukul " . substr($sesi['jam_selesai'], 0, 5) . " WIB).";
            App::redirect(App::baseUrl("guru/presensi/{$sesiId}"));
            return;
        }

        $catatan = $_POST['catatan_guru'] ?? null;
        SesiMengajar::finishSession($sesiId, date('Y-m-d H:i:s'), $catatan);

        $_SESSION['flash_success'] = "Sesi KBM mata pelajaran {$sesi['nama_mapel']} telah selesai. Saldo honorarium telah diperbarui!";
        App::redirect(App::baseUrl('guru/dashboard'));
    }

    /**
     * Buku Saku Honor Guru (Transparansi Riwayat & Denda)
     */
    public function dompetHonor(): void
    {
        $guruId = (int)$this->user['id'];
        $bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
        $tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

        $history = SesiMengajar::getTeacherHistory($guruId, $bulan, $tahun);

        $totalJp = 0;
        $totalPlafon = 0.0;
        $totalTelat = 0;
        $totalDenda = 0.0;
        $totalHonorBersih = 0.0;

        foreach ($history as $h) {
            $totalJp += (int)$h['jumlah_jp'];
            $totalPlafon += (float)($h['jumlah_jp'] * $this->config['honor_per_jp']);
            $totalTelat += (int)$h['menit_terlambat'];
            $totalDenda += (float)($h['menit_terlambat'] * $this->config['denda_per_menit']);
            $totalHonorBersih += (float)$h['honor_didapat'];
        }

        $tunjanganTugas = (float)($this->user['tunjangan_tugas'] ?? 0);
        $totalTakeHome = $totalHonorBersih + $tunjanganTugas;

        $user = $this->user;
        $config = $this->config;
        $activeNav = 'honor';

        require __DIR__ . '/../Views/guru/dompet_honor.php';
    }

    /**
     * AJAX Endpoint: Tap Datang / Pengajuan Non-Hadir (Tugas Luar, Izin, Sakit)
     */
    public function ajaxTapDatangGerbang(): void
    {
        $guruId = (int)$this->user['id'];
        $statusPilihan = strtoupper(trim($_POST['status_pilihan'] ?? 'HADIR'));
        $keterangan = trim($_POST['keterangan'] ?? '');
        $userLat = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null;
        $userLng = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
        $bypassGps = $this->canBypassGps() && (isset($_POST['bypass_gps']) && $_POST['bypass_gps'] === '1');

        // 1. Jika Non-Hadir (Tugas Luar, Izin, Sakit), bypass GPS dan langsung simpan status final
        if (in_array($statusPilihan, ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
            if (empty($keterangan)) {
                $namaStatus = ($statusPilihan === 'TUGAS_LUAR') ? 'Tugas Luar' : ucfirst(strtolower($statusPilihan));
                App::json([
                    'success' => false,
                    'message' => "Harap masukkan keterangan/alasan untuk pengajuan {$namaStatus}!"
                ], 400);
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

            App::json($res);
        }

        // 2. Jika status HADIR di sekolah, wajib verifikasi GPS Geofence
        $schoolLat = (float)$this->config['latitude_pusat'];
        $schoolLng = (float)$this->config['longitude_pusat'];
        $radiusMeters = (int)$this->config['radius_meter'];

        $distance = 0.0;
        if ($userLat === null || $userLng === null) {
            if (!$bypassGps) {
                App::json([
                    'success' => false,
                    'message' => 'Koordinat GPS belum terdeteksi. Pastikan izin lokasi browser Anda aktif!'
                ], 400);
            }
            $userLat = $schoolLat;
            $userLng = $schoolLng;
            $distance = 5.0;
        } else {
            $geoCheck = GeolocationHelper::isWithinRadius($userLat, $userLng, $schoolLat, $schoolLng, $radiusMeters);
            $distance = $geoCheck['distance_meters'];

            if (!$geoCheck['is_valid'] && !$bypassGps) {
                App::json([
                    'success' => false,
                    'message' => "Presensi datang ditolak! Anda berada di luar radius sekolah. Jarak Anda: {$distance} meter (Batas toleransi: {$radiusMeters} meter).",
                    'distance' => $distance,
                    'radius_limit' => $radiusMeters
                ], 403);
            }
        }

        // 3. Hitung keterlambatan datang jika melewati jam batas masuk gerbang (06:30)
        $now = date('Y-m-d H:i:s');
        $nowTime = date('H:i:s');
        $jamBatas = $this->config['jam_guru_masuk_selesai'] ?? $this->config['jam_gerbang_masuk_selesai'] ?? '06:30:00';
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

        App::json($res);
    }

    /**
     * AJAX Endpoint: Tap Pulang Gerbang Guru (GPS Geofence)
     */
    public function ajaxTapPulangGerbang(): void
    {
        $guruId = (int)$this->user['id'];
        $keterangan = trim($_POST['keterangan'] ?? '');
        $userLat = isset($_POST['latitude']) && $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null;
        $userLng = isset($_POST['longitude']) && $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
        $bypassGps = $this->canBypassGps() && (isset($_POST['bypass_gps']) && $_POST['bypass_gps'] === '1');

        $today = date('Y-m-d');
        $existing = PresensiGerbangGuru::getToday($guruId, $today);
        if (!$existing || empty($existing['waktu_datang'])) {
            App::json([
                'success' => false,
                'message' => 'Anda belum melakukan Tap Datang hari ini! Silakan lakukan Tap Datang terlebih dahulu.'
            ], 400);
        }

        if (in_array($existing['status_kehadiran'], ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
            App::json([
                'success' => true,
                'is_repeat' => true,
                'message' => "Status presensi Anda hari ini adalah [{$existing['status_kehadiran']}]. Anda tidak perlu melakukan Tap Pulang."
            ]);
        }

        if (!empty($existing['waktu_pulang'])) {
            App::json([
                'success' => true,
                'is_repeat' => true,
                'message' => 'Anda sudah melakukan Tap Pulang sebelumnya pada jam ' . date('H:i', strtotime($existing['waktu_pulang'])) . ' WIB.'
            ]);
        }

        // Validasi jam aktif pulang: baru dapat dilakukan sesudah jam 13:00 WIB
        $now = date('Y-m-d H:i:s');
        $nowTime = date('H:i:s');
        $jamBukaPulang = $this->config['jam_guru_pulang_mulai'] ?? $this->config['jam_gerbang_pulang_mulai'] ?? '13:00:00';

        if ($nowTime < $jamBukaPulang && !$bypassGps) {
            App::json([
                'success' => false,
                'message' => "Presensi pulang belum dibuka! Jam kepulangan guru baru aktif sesudah pukul " . substr($jamBukaPulang, 0, 5) . " WIB."
            ], 400);
        }

        // Validasi GPS Geofence
        $schoolLat = (float)$this->config['latitude_pusat'];
        $schoolLng = (float)$this->config['longitude_pusat'];
        $radiusMeters = (int)$this->config['radius_meter'];

        $distance = 0.0;
        if ($userLat === null || $userLng === null) {
            if (!$bypassGps) {
                App::json([
                    'success' => false,
                    'message' => 'Koordinat GPS belum terdeteksi. Pastikan izin lokasi browser Anda aktif!'
                ], 400);
            }
            $userLat = $schoolLat;
            $userLng = $schoolLng;
            $distance = 5.0;
        } else {
            $geoCheck = GeolocationHelper::isWithinRadius($userLat, $userLng, $schoolLat, $schoolLng, $radiusMeters);
            $distance = $geoCheck['distance_meters'];

            if (!$geoCheck['is_valid'] && !$bypassGps) {
                App::json([
                    'success' => false,
                    'message' => "Presensi pulang ditolak! Anda berada di luar radius sekolah. Jarak: {$distance} meter (Batas toleransi: {$radiusMeters} meter).",
                    'distance' => $distance,
                    'radius_limit' => $radiusMeters
                ], 403);
            }
        }

        $now = date('Y-m-d H:i:s');
        $res = PresensiGerbangGuru::recordTapPulang(
            $guruId,
            $userLat,
            $userLng,
            $distance,
            $keterangan ?: null,
            $now
        );

        App::json($res);
    }

    /**
     * AJAX Endpoint: Real-time Live Status & Sync Jam Server untuk Dashboard Guru
     */
    public function ajaxLiveStatus(): void
    {
        $guruId = (int)$this->user['id'];
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        $nowTime = date('H:i:s');

        $presensi = PresensiGerbangGuru::getToday($guruId, $today);
        $jamMasukBatas = $this->config['jam_guru_masuk_selesai'] ?? '06:30:00';
        $jamPulangBuka = $this->config['jam_guru_pulang_mulai'] ?? '13:00:00';

        $isPulangOpen = ($nowTime >= $jamPulangBuka);
        $secondsToPulang = max(0, strtotime($today . ' ' . $jamPulangBuka) - strtotime($now));

        $hariIndo = TimeHelper::getDayName($today);
        $jadwalList = SesiMengajar::getTodayScheduleWithSession($guruId, $today, $hariIndo);
        foreach ($jadwalList as &$j) {
            $window = TimeHelper::checkActivationWindow(
                $j['jam_mulai'], 
                (int)$this->config['toleransi_h_minus'], 
                $nowTime, 
                $j['jam_selesai']
            );
            $j['window'] = $window;
            if (!empty($j['sesi_id'])) {
                if (!empty($j['waktu_checkout'])) {
                    $j['status_badge'] = 'SELESAI';
                } else {
                    $j['status_badge'] = 'SEDANG_MENGAJAR';
                }
            } else {
                if ($window['status'] === 'CLOSED' || $nowTime >= $j['jam_selesai']) {
                    $j['status_badge'] = 'TIDAK_HADIR';
                } elseif ($window['can_checkin']) {
                    $j['status_badge'] = 'SIAP_CHECKIN';
                } else {
                    $j['status_badge'] = 'AKAN_DATANG';
                }
            }
        }
        unset($j);

        // Notifikasi realtime Guru
        $unreadNotif = Notifikasi::getUnreadCountForGuru($guruId);
        $latestNotif = null;
        if ($unreadNotif > 0) {
            $recent = Notifikasi::getForGuru($guruId, 1);
            $latestNotif = $recent[0] ?? null;
        }

        App::json([
            'success' => true,
            'server_time' => $nowTime,
            'server_datetime' => $now,
            'jam_masuk_selesai' => $jamMasukBatas,
            'jam_pulang_mulai' => $jamPulangBuka,
            'is_pulang_open' => $isPulangOpen,
            'seconds_to_pulang' => $secondsToPulang,
            'presensi_gerbang' => $presensi,
            'jadwal_list' => $jadwalList,
            'unread_notif' => $unreadNotif,
            'latest_notif' => $latestNotif,
        ]);
    }

    /**
     * AJAX Endpoint: Mengambil daftar notifikasi guru secara dinamis
     */
    public function notifikasiList(): void
    {
        $guruId = (int)$this->user['id'];
        $list = Notifikasi::getForGuru($guruId, 30);
        $unread = Notifikasi::getUnreadCountForGuru($guruId);
        App::json(['success' => true, 'unread_count' => $unread, 'data' => $list]);
    }

    /**
     * Tandai 1 notifikasi sebagai telah dibaca oleh guru
     */
    public function notifikasiRead(int $id): void
    {
        $guruId = (int)$this->user['id'];
        $res = Notifikasi::markAsReadForGuru($id, $guruId);
        $unread = Notifikasi::getUnreadCountForGuru($guruId);
        App::json(['success' => $res, 'unread_count' => $unread]);
    }

    /**
     * Tandai seluruh notifikasi sebagai telah dibaca oleh guru
     */
    public function notifikasiReadAll(): void
    {
        $guruId = (int)$this->user['id'];
        $res = Notifikasi::markAllAsReadForGuru($guruId);
        App::json(['success' => $res, 'unread_count' => 0]);
    }
}


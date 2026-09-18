<?php
// public/index.php - Front Controller & Router SI Presensi SMK Al-Farizi

declare(strict_types=1);

// Built-in PHP server router check for static files
if (php_sapi_name() === 'cli-server') {
    $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $filePath = __DIR__ . $uriPath;
    if ($uriPath !== '/' && file_exists($filePath) && !is_dir($filePath)) {
        return false;
    }
}

// 0. Composer Vendor Autoload (untuk library pihak ketiga: JWT, QRCode, dll)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// 1. PSR-4 Autoloader Sederhana
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Config\App;
use App\Controllers\AuthController;
use App\Controllers\AdminController;
use App\Controllers\AdminLmsController;
use App\Controllers\GuruController;
use App\Controllers\PiketController;
use App\Controllers\PayrollController;
use App\Controllers\BukuIndukController;
use App\Controllers\NilaiController;
use App\Controllers\AkademikController;
use App\Controllers\SuratController;
use App\Controllers\AdminTabunganController;
use App\Controllers\GuruTabunganController;
use App\Controllers\GuruLmsController;
use App\Controllers\BackupController;
// ============ API CONTROLLERS (tambahan baru) ============
use App\Controllers\Api\ApiController;
use App\Controllers\Api\AuthApiController;
use App\Controllers\Api\GuruApiController;
use App\Controllers\Api\PresensiApiController;
use App\Controllers\Api\GerbangApiController;
use App\Controllers\Api\NilaiApiController;
use App\Controllers\Api\SiswaApiController;
use App\Controllers\Api\SiswaAbsensiApiController;
// ============ END API CONTROLLERS ============

App::init();

// 2. URI & Route Parsing
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

// Hapus query string
$uriPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';

// Normalisasi base directory jika berjalan di subfolder
$baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if (!empty($baseDir) && strpos($uriPath, $baseDir) === 0) {
    $uriPath = substr($uriPath, strlen($baseDir));
}

$path = trim($uriPath, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 3. Routing Engine & CSRF Protection
if ($method === 'POST' && strpos($path, 'api/v1/') !== 0) {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!\App\Helpers\CsrfHelper::validateToken($token)) {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if ($isAjax) {
            App::json(['success' => false, 'message' => 'Token keamanan (CSRF) tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.'], 403);
        } else {
            http_response_code(403);
            die("<strong>Akses Ditolak (403)</strong><br>Token keamanan (CSRF) tidak valid atau telah kedaluwarsa. Silakan <a href='javascript:history.back()'>kembali</a> dan muat ulang halaman.");
        }
    }
}

try {
    if ($path === '' || $path === 'index.php') {
        $user = AuthController::checkAuth();
        if (!$user) {
            App::redirect(App::baseUrl('login'));
        }
        switch ($user['role']) {
            case 'admin':
                App::redirect(App::baseUrl('admin/dashboard'));
                break;
            case 'piket':
                App::redirect(App::baseUrl('piket/scanner'));
                break;
            case 'guru':
            default:
                App::redirect(App::baseUrl('guru/dashboard'));
                break;
        }
    }

    // =====================================================================
    // REST API ROUTES v1 (tambahan baru — hapus blok ini untuk rollback)
    // Semua route diawali 'api/v1/' sehingga TIDAK konflik dengan web routes
    // =====================================================================
    if (strpos($path, 'api/v1/') === 0) {
        // Kirim CORS headers untuk setiap request API
        ApiController::setCorsHeaders();

        $apiPath = substr($path, strlen('api/v1/'));

        // --- AUTH ---
        // POST api/v1/auth/login
        if ($apiPath === 'auth/login' && $method === 'POST') {
            (new AuthApiController())->login();
            exit;
        }
        // POST api/v1/auth/logout
        if ($apiPath === 'auth/logout' && $method === 'POST') {
            (new AuthApiController())->logout();
            exit;
        }
        // GET api/v1/auth/me
        if ($apiPath === 'auth/me' && $method === 'GET') {
            (new AuthApiController())->me();
            exit;
        }

        // --- GURU ---
        // GET api/v1/guru/dashboard
        if ($apiPath === 'guru/dashboard' && $method === 'GET') {
            (new GuruApiController())->dashboard();
            exit;
        }
        // GET api/v1/guru/jadwal
        if ($apiPath === 'guru/jadwal' && $method === 'GET') {
            (new GuruApiController())->jadwal();
            exit;
        }
        // GET api/v1/guru/honor
        if ($apiPath === 'guru/honor' && $method === 'GET') {
            (new GuruApiController())->honor();
            exit;
        }
        // GET api/v1/guru/profile
        if ($apiPath === 'guru/profile' && $method === 'GET') {
            (new GuruApiController())->profile();
            exit;
        }

        // --- PRESENSI ---
        // POST api/v1/guru/checkin
        if ($apiPath === 'guru/checkin' && $method === 'POST') {
            (new PresensiApiController())->checkin();
            exit;
        }
        // POST api/v1/guru/checkout/{sesiId}
        if (preg_match('#^guru/checkout/(\d+)$#', $apiPath, $m) && $method === 'POST') {
            (new PresensiApiController())->checkout((int)$m[1]);
            exit;
        }
        // GET api/v1/guru/presensi/{sesiId}
        if (preg_match('#^guru/presensi/(\d+)$#', $apiPath, $m) && $method === 'GET') {
            (new PresensiApiController())->getSiswaPresensi((int)$m[1]);
            exit;
        }
        // POST api/v1/guru/presensi/{sesiId}
        if (preg_match('#^guru/presensi/(\d+)$#', $apiPath, $m) && $method === 'POST') {
            (new PresensiApiController())->savePresensi((int)$m[1]);
            exit;
        }

        // --- GERBANG SEKOLAH (DATANG & PULANG) ---
        // GET api/v1/guru/gerbang/status
        if ($apiPath === 'guru/gerbang/status' && $method === 'GET') {
            (new GerbangApiController())->status();
            exit;
        }
        // POST api/v1/guru/gerbang/datang
        if ($apiPath === 'guru/gerbang/datang' && $method === 'POST') {
            (new GerbangApiController())->tapDatang();
            exit;
        }
        // POST api/v1/guru/gerbang/pulang
        if ($apiPath === 'guru/gerbang/pulang' && $method === 'POST') {
            (new GerbangApiController())->tapPulang();
            exit;
        }

        // --- PENILAIAN SISWA ---
        // GET api/v1/guru/nilai
        if ($apiPath === 'guru/nilai' && $method === 'GET') {
            (new NilaiApiController())->index();
            exit;
        }
        // POST api/v1/guru/nilai/save
        if ($apiPath === 'guru/nilai/save' && $method === 'POST') {
            (new NilaiApiController())->save();
            exit;
        }

        // =====================================================================
        // SISWA PWA API ROUTES
        // =====================================================================
        // POST api/v1/siswa/login
        if ($apiPath === 'siswa/login' && $method === 'POST') {
            (new SiswaApiController())->login();
            exit;
        }
        // GET api/v1/siswa/me
        if ($apiPath === 'siswa/me' && $method === 'GET') {
            (new SiswaApiController())->me();
            exit;
        }
        // POST api/v1/siswa/fcm-token
        if ($apiPath === 'siswa/fcm-token' && $method === 'POST') {
            (new SiswaApiController())->saveFcmToken();
            exit;
        }
        // GET api/v1/siswa/dashboard
        if ($apiPath === 'siswa/dashboard' && $method === 'GET') {
            (new SiswaApiController())->dashboard();
            exit;
        }
        // GET api/v1/siswa/absensi
        if ($apiPath === 'siswa/absensi' && $method === 'GET') {
            (new SiswaAbsensiApiController())->absensi();
            exit;
        }
        // GET api/v1/siswa/nilai
        if ($apiPath === 'siswa/nilai' && $method === 'GET') {
            (new SiswaApiController())->nilai();
            exit;
        }
        // GET api/v1/siswa/tabungan
        if ($apiPath === 'siswa/tabungan' && $method === 'GET') {
            (new SiswaApiController())->tabungan();
            exit;
        }
        // GET api/v1/siswa/lms/materi
        if ($apiPath === 'siswa/lms/materi' && $method === 'GET') {
            (new \App\Controllers\Api\SiswaLmsApiController())->getMateri();
            exit;
        }
        // GET api/v1/siswa/lms/materi/{materiId}
        if (preg_match('#^siswa/lms/materi/(\d+)$#', $apiPath, $m) && $method === 'GET') {
            (new \App\Controllers\Api\SiswaLmsApiController())->getMateriDetail((int)$m[1]);
            exit;
        }
        // POST api/v1/siswa/lms/submit/{materiId}
        if (preg_match('#^siswa/lms/submit/(\d+)$#', $apiPath, $m) && $method === 'POST') {
            (new \App\Controllers\Api\SiswaLmsApiController())->submitTugas((int)$m[1]);
            exit;
        }
        // GET api/v1/siswa/lms/diskusi/{mapelId}
        if (preg_match('#^siswa/lms/diskusi/(\d+)$#', $apiPath, $m) && $method === 'GET') {
            (new \App\Controllers\Api\SiswaLmsApiController())->getDiskusi((int)$m[1]);
            exit;
        }
        // POST api/v1/siswa/lms/diskusi/{mapelId}
        if (preg_match('#^siswa/lms/diskusi/(\d+)$#', $apiPath, $m) && $method === 'POST') {
            (new \App\Controllers\Api\SiswaLmsApiController())->postDiskusi((int)$m[1]);
            exit;
        }
        // GET api/v1/siswa/lms/ujian
        if ($apiPath === 'siswa/lms/ujian' && $method === 'GET') {
            (new \App\Controllers\Api\SiswaLmsApiController())->getUjian();
            exit;
        }
        // POST api/v1/siswa/lms/ujian/start/{ujianId}
        if (preg_match('#^siswa/lms/ujian/start/(\d+)$#', $apiPath, $m) && $method === 'POST') {
            (new \App\Controllers\Api\SiswaLmsApiController())->startUjian((int)$m[1]);
            exit;
        }
        // POST api/v1/siswa/lms/ujian/save-jawaban/{ujianSiswaId}
        if (preg_match('#^siswa/lms/ujian/save-jawaban/(\d+)$#', $apiPath, $m) && $method === 'POST') {
            (new \App\Controllers\Api\SiswaLmsApiController())->saveJawabanUjian((int)$m[1]);
            exit;
        }
        // POST api/v1/siswa/lms/ujian/submit/{ujianSiswaId}
        if (preg_match('#^siswa/lms/ujian/submit/(\d+)$#', $apiPath, $m) && $method === 'POST') {
            (new \App\Controllers\Api\SiswaLmsApiController())->submitUjian((int)$m[1]);
            exit;
        }

        // --- NOTIFIKASI SISWA ---
        // GET api/v1/siswa/notifikasi
        if ($apiPath === 'siswa/notifikasi' && $method === 'GET') {
            (new SiswaApiController())->notifikasi();
            exit;
        }
        // POST api/v1/siswa/notifikasi/read/{id}
        if (preg_match('#^siswa/notifikasi/read/(\d+)$#', $apiPath, $m) && $method === 'POST') {
            (new SiswaApiController())->bacaNotifikasi((int)$m[1]);
            exit;
        }
        // POST api/v1/siswa/notifikasi/read-all
        if ($apiPath === 'siswa/notifikasi/read-all' && $method === 'POST') {
            (new SiswaApiController())->bacaSemuaNotifikasi();
            exit;
        }
        // GET api/v1/siswa/notifikasi/live-check
        if ($apiPath === 'siswa/notifikasi/live-check' && $method === 'GET') {
            (new SiswaApiController())->notifikasiLiveCheck();
            exit;
        }
        // =====================================================================
        // END SISWA PWA API ROUTES
        // =====================================================================

        // API endpoint tidak dikenali → 404 JSON
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => "API endpoint '{$apiPath}' tidak ditemukan.",
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    // =====================================================================
    // END REST API ROUTES v1
    // =====================================================================

    // AUTH
    if ($path === 'login') {
        $ctrl = new AuthController();
        if ($method === 'POST') {
            $ctrl->doLogin();
        } else {
            $ctrl->loginView();
        }
        exit;
    }

    if ($path === 'logout') {
        (new AuthController())->logout();
        exit;
    }

    // ADMIN ROUTES
    if (strpos($path, 'admin/') === 0 || $path === 'admin') {
        $admin = new AdminController();

        if ($path === 'admin/dashboard' || $path === 'admin') {
            $admin->dashboard();
            exit;
        }
        if ($path === 'admin/geofence') {
            if ($method === 'POST') $admin->geofenceSave();
            else $admin->geofenceView();
            exit;
        }
        if ($path === 'admin/reset-data') {
            $admin->resetDataView();
            exit;
        }
        if ($path === 'admin/backup') {
            (new BackupController())->index();
            exit;
        }
        if ($path === 'admin/backup/download' && $method === 'POST') {
            (new BackupController())->download();
            exit;
        }
        if ($path === 'admin/reset-data/execute' && $method === 'POST') {
            $admin->resetDataExecute();
            exit;
        }
        if ($path === 'admin/notifikasi') {
            $admin->notifikasiIndex();
            exit;
        }
        if ($path === 'admin/notifikasi/store' && $method === 'POST') {
            $admin->notifikasiStore();
            exit;
        }
        if (preg_match('#^admin/notifikasi/delete/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->notifikasiDelete((int)$m[1]);
            exit;
        }
        if ($path === 'admin/guru') {
            $admin->guruIndex();
            exit;
        }
        if ($path === 'admin/guru/import') {
            if ($method === 'POST') {
                $admin->guruImportProcess();
            } else {
                $admin->guruImportView();
            }
            exit;
        }
        if ($path === 'admin/guru/download-template') {
            $admin->guruDownloadTemplate();
            exit;
        }
        if ($path === 'admin/guru/store' && $method === 'POST') {
            $admin->guruStore();
            exit;
        }
        if (preg_match('#^admin/guru/update/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->guruUpdate((int)$m[1]);
            exit;
        }
        if (preg_match('#^admin/guru/delete/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->guruDelete((int)$m[1]);
            exit;
        }
        if ($path === 'admin/kelas') {
            $admin->kelasIndex();
            exit;
        }
        if ($path === 'admin/kelas/store' && $method === 'POST') {
            $admin->kelasStore();
            exit;
        }
        if (preg_match('#^admin/kelas/update/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->kelasUpdate((int)$m[1]);
            exit;
        }
        if (preg_match('#^admin/kelas/delete/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->kelasDelete((int)$m[1]);
            exit;
        }
        // PLOTTING MAPEL & GURU PER KELAS
        if (preg_match('#^admin/kelas/(\d+)/mapel/store$#', $path, $m) && $method === 'POST') {
            $admin->kelasMapelStore((int)$m[1]);
            exit;
        }
        if (preg_match('#^admin/kelas/mapel/delete/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->kelasMapelDelete((int)$m[1]);
            exit;
        }
        if (preg_match('#^admin/kelas/(\d+)/mapel-json$#', $path, $m)) {
            $admin->kelasMapelApi((int)$m[1]);
            exit;
        }
        if ($path === 'admin/siswa') {
            $admin->siswaIndex();
            exit;
        }
        if ($path === 'admin/siswa/store' && $method === 'POST') {
            $admin->siswaStore();
            exit;
        }
        if (preg_match('#^admin/siswa/update/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->siswaUpdate((int)$m[1]);
            exit;
        }
        if (preg_match('#^admin/siswa/delete/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->siswaDelete((int)$m[1]);
            exit;
        }
        if ($path === 'admin/siswa/bulk-assign-kelas' && $method === 'POST') {
            $admin->siswaBulkAssignKelas();
            exit;
        }
        if ($path === 'admin/siswa/cetak-kartu') {
            $admin->cetakKartuBarcode();
            exit;
        }
        // MASTER MATA PELAJARAN
        if ($path === 'admin/mapel') {
            $admin->mapelIndex();
            exit;
        }
        if ($path === 'admin/mapel/store' && $method === 'POST') {
            $admin->mapelStore();
            exit;
        }
        if (preg_match('#^admin/mapel/update/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->mapelUpdate((int)$m[1]);
            exit;
        }
        if (preg_match('#^admin/mapel/delete/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->mapelDelete((int)$m[1]);
            exit;
        }
        if ($path === 'admin/jadwal/check-conflict') {
            $admin->checkJadwalConflictApi();
            exit;
        }
        if ($path === 'admin/jadwal') {
            $admin->jadwalIndex();
            exit;
        }
        if ($path === 'admin/jadwal/store' && $method === 'POST') {
            $admin->jadwalStore();
            exit;
        }
        if (preg_match('#^admin/jadwal/update/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->jadwalUpdate((int)$m[1]);
            exit;
        }
        if (preg_match('#^admin/jadwal/delete/(\d+)$#', $path, $m) && $method === 'POST') {
            $admin->jadwalDelete((int)$m[1]);
            exit;
        }
        if ($path === 'admin/monitoring/guru') {
            $admin->monitoringGuru();
            exit;
        }
        if ($path === 'admin/monitoring/guru/update-manual' && $method === 'POST') {
            $admin->monitoringGuruUpdateManual();
            exit;
        }
        if ($path === 'admin/monitoring/guru/gerbang-update-manual' && $method === 'POST') {
            $admin->monitoringGuruGerbangUpdateManual();
            exit;
        }
        if ($path === 'admin/monitoring/guru-live') {
            $admin->monitoringGuruLive();
            exit;
        }
        if ($path === 'admin/monitoring/siswa') {
            $admin->monitoringSiswa();
            exit;
        }
        if ($path === 'admin/monitoring/siswa-live') {
            $admin->monitoringSiswaLive();
            exit;
        }
        if ($path === 'admin/monitoring/guru/export') {
            $admin->monitoringGuruExport();
            exit;
        }
        if ($path === 'admin/monitoring/siswa/export') {
            $admin->monitoringSiswaExport();
            exit;
        }
        if ($path === 'admin/monitoring/evaluasi-gerbang' && $method === 'POST') {
            $admin->triggerEvaluasiGerbang();
            exit;
        }

        // TABUNGAN ADMIN
        if (strpos($path, 'admin/tabungan') === 0) {
            $tabCtrl = new AdminTabunganController();
            if ($path === 'admin/tabungan/program') {
                $tabCtrl->programIndex();
                exit;
            }
            if ($path === 'admin/tabungan/program/store' && $method === 'POST') {
                $tabCtrl->programStore();
                exit;
            }
            if (preg_match('#^admin/tabungan/program/update/(\d+)$#', $path, $m) && $method === 'POST') {
                $tabCtrl->programUpdate((int)$m[1]);
                exit;
            }
            if (preg_match('#^admin/tabungan/program/delete/(\d+)$#', $path, $m) && $method === 'POST') {
                $tabCtrl->programDelete((int)$m[1]);
                exit;
            }

            if ($path === 'admin/tabungan/monitoring') {
                $tabCtrl->monitoringIndex();
                exit;
            }
        }

        // BUKU INDUK SISWA (DAPODIK INTEGRATED)
        if (strpos($path, 'admin/buku-induk') === 0) {
            $bukuCtrl = new BukuIndukController();
            if ($path === 'admin/buku-induk') {
                $bukuCtrl->index();
                exit;
            }
            if ($path === 'admin/buku-induk/alumni') {
                $bukuCtrl->alumniIndex();
                exit;
            }
            if ($path === 'admin/buku-induk/import') {
                if ($method === 'POST') $bukuCtrl->importProcess();
                else $bukuCtrl->importView();
                exit;
            }
            if ($path === 'admin/buku-induk/download-template') {
                $bukuCtrl->downloadTemplate();
                exit;
            }
            if (preg_match('#^admin/buku-induk/detail/(\d+)$#', $path, $m)) {
                $bukuCtrl->detail((int)$m[1]);
                exit;
            }
            if (preg_match('#^admin/buku-induk/edit/(\d+)$#', $path, $m)) {
                $bukuCtrl->edit((int)$m[1]);
                exit;
            }
            if (preg_match('#^admin/buku-induk/update/(\d+)$#', $path, $m) && $method === 'POST') {
                $bukuCtrl->update((int)$m[1]);
                exit;
            }
            if (preg_match('#^admin/buku-induk/cetak/(\d+)$#', $path, $m)) {
                $bukuCtrl->cetakLembar((int)$m[1]);
                exit;
            }
            if ($path === 'admin/buku-induk/input-kolektif') {
                if ($method === 'POST') $bukuCtrl->inputKolektifSave();
                else $bukuCtrl->inputKolektifView();
                exit;
            }
            if (preg_match('#^admin/buku-induk/input-nilai-manual/(\d+)$#', $path, $m)) {
                if ($method === 'POST') $bukuCtrl->saveNilaiManual((int)$m[1]);
                else $bukuCtrl->inputNilaiManualView((int)$m[1]);
                exit;
            }
        }

        // PENILAIAN SISWA BERKELANJUTAN (SEMESTER 1 - 6)
        if (strpos($path, 'admin/nilai') === 0) {
            $nilaiCtrl = new NilaiController();
            if ($path === 'admin/nilai') {
                $nilaiCtrl->index();
                exit;
            }
            if ($path === 'admin/nilai/input') {
                $nilaiCtrl->inputSemester();
                exit;
            }
            if ($path === 'admin/nilai/save' && $method === 'POST') {
                $nilaiCtrl->saveSemester();
                exit;
            }
            if (preg_match('#^admin/nilai/transkrip/(\d+)$#', $path, $m)) {
                $nilaiCtrl->transkrip((int)$m[1]);
                exit;
            }
            if (preg_match('#^admin/nilai/transkrip-ijazah/(\d+)$#', $path, $m)) {
                $nilaiCtrl->transkripIjazah((int)$m[1]);
                exit;
            }
        }

        // ADMIN LMS MONITORING
        if ($path === 'admin/lms') {
            (new AdminLmsController())->index();
            exit;
        }

        // AKADEMIK (TAHUN PELAJARAN & KENAIKAN KELAS ROLL-OVER)
        if (strpos($path, 'admin/akademik') === 0) {
            $akademikCtrl = new AkademikController();
            if ($path === 'admin/akademik/tahun-pelajaran') {
                $akademikCtrl->tahunPelajaranIndex();
                exit;
            }
            if ($path === 'admin/akademik/tahun-pelajaran/store' && $method === 'POST') {
                $akademikCtrl->tahunPelajaranStore();
                exit;
            }
            if (preg_match('#^admin/akademik/tahun-pelajaran/set-aktif/(\d+)$#', $path, $m) && $method === 'POST') {
                $akademikCtrl->tahunPelajaranSetActive((int)$m[1]);
                exit;
            }
            if ($path === 'admin/akademik/kenaikan-kelas') {
                $akademikCtrl->kenaikanKelasIndex();
                exit;
            }
            if ($path === 'admin/akademik/kenaikan-kelas/process' && $method === 'POST') {
                $akademikCtrl->kenaikanKelasProcess();
                exit;
            }
        }

        // GENERATOR SURAT RESMI, SPPD, LEMBAR VISUM & KOP DINAS
        if (strpos($path, 'admin/surat') === 0) {
            $suratCtrl = new SuratController();
            if ($path === 'admin/surat') {
                $suratCtrl->index();
                exit;
            }
            if ($path === 'admin/surat/buat') {
                $suratCtrl->create();
                exit;
            }
            if ($path === 'admin/surat/store' && $method === 'POST') {
                $suratCtrl->store();
                exit;
            }
            if (preg_match('#^admin/surat/cetak/(\d+)$#', $path, $m)) {
                $suratCtrl->cetak((int)$m[1]);
                exit;
            }
            if (preg_match('#^admin/surat/delete/(\d+)$#', $path, $m) && $method === 'POST') {
                $suratCtrl->delete((int)$m[1]);
                exit;
            }
            if ($path === 'admin/surat/pengaturan-kop') {
                $suratCtrl->pengaturanKop();
                exit;
            }
            if ($path === 'admin/surat/simpan-kop' && $method === 'POST') {
                $suratCtrl->simpanKop();
                exit;
            }
        }
    }

    // GURU ROUTES
    if (strpos($path, 'guru/') === 0 || $path === 'guru') {
        $guru = new GuruController();

        if ($path === 'guru/dashboard' || $path === 'guru') {
            $guru->dashboard();
            exit;
        }
        if ($path === 'guru/gerbang/datang' && $method === 'POST') {
            $guru->ajaxTapDatangGerbang();
            exit;
        }
        if ($path === 'guru/gerbang/pulang' && $method === 'POST') {
            $guru->ajaxTapPulangGerbang();
            exit;
        }
        if ($path === 'guru/live-status') {
            $guru->ajaxLiveStatus();
            exit;
        }
        if ($path === 'guru/checkin' && $method === 'POST') {
            $guru->checkinKbm();
            exit;
        }
        if (preg_match('#^guru/presensi/(\d+)$#', $path, $m)) {
            if ($method === 'POST') $guru->presensiMapelSave((int)$m[1]);
            else $guru->presensiMapelView((int)$m[1]);
            exit;
        }
        if (preg_match('#^guru/selesai/(\d+)$#', $path, $m) && $method === 'POST') {
            $guru->selesaiMengajar((int)$m[1]);
            exit;
        }
        if ($path === 'guru/dompet') {
            $guru->dompetHonor();
            exit;
        }
        if (preg_match('#^guru/notifikasi/read/(\d+)$#', $path, $m) && $method === 'POST') {
            $guru->notifikasiRead((int)$m[1]);
            exit;
        }
        if ($path === 'guru/notifikasi/read-all' && $method === 'POST') {
            $guru->notifikasiReadAll();
            exit;
        }
        if ($path === 'guru/notifikasi/list') {
            $guru->notifikasiList();
            exit;
        }
        
        // TABUNGAN GURU
        if (strpos($path, 'guru/tabungan') === 0) {
            $tabGuruCtrl = new GuruTabunganController();
            if ($path === 'guru/tabungan') {
                $tabGuruCtrl->index();
                exit;
            }
            if (preg_match('#^guru/tabungan/program/(\d+)$#', $path, $m)) {
                $tabGuruCtrl->detailProgram((int)$m[1]);
                exit;
            }
            if (preg_match('#^guru/tabungan/setoran/(\d+)$#', $path, $m) && $method === 'POST') {
                $tabGuruCtrl->catatSetoran((int)$m[1]);
                exit;
            }
            if (preg_match('#^guru/tabungan/daftar/(\d+)$#', $path, $m) && $method === 'POST') {
                $tabGuruCtrl->daftarSiswa((int)$m[1]);
                exit;
            }
        }

        // LMS GURU
        if (strpos($path, 'guru/lms') === 0) {
            $lmsGuruCtrl = new GuruLmsController();
            if ($path === 'guru/lms') {
                $lmsGuruCtrl->index();
                exit;
            }
            if (preg_match('#^guru/lms/materi/store/(\d+)/(\d+)$#', $path, $m) && $method === 'POST') {
                $lmsGuruCtrl->materiStore((int)$m[1], (int)$m[2]);
                exit;
            }
            if (preg_match('#^guru/lms/materi/(\d+)/(\d+)$#', $path, $m)) {
                if ($method === 'POST') {
                    $lmsGuruCtrl->materiStore((int)$m[1], (int)$m[2]);
                } else {
                    $lmsGuruCtrl->materiList((int)$m[1], (int)$m[2]);
                }
                exit;
            }
            if (preg_match('#^guru/lms/materi/delete/(\d+)/(\d+)/(\d+)$#', $path, $m) && $method === 'POST') {
                // url: delete/materi_id/kelas_id/mapel_id
                $lmsGuruCtrl->materiDelete((int)$m[1], (int)$m[2], (int)$m[3]);
                exit;
            }
            if (preg_match('#^guru/lms/submission/(\d+)$#', $path, $m)) {
                $lmsGuruCtrl->submissionList((int)$m[1]);
                exit;
            }
            if (preg_match('#^guru/lms/submission/nilai/(\d+)/(\d+)$#', $path, $m) && $method === 'POST') {
                // url: nilai/submission_id/materi_id
                $lmsGuruCtrl->submissionNilai((int)$m[1], (int)$m[2]);
                exit;
            }
            if (preg_match('#^guru/lms/diskusi/messages/(\d+)/(\d+)$#', $path, $m)) {
                $lmsGuruCtrl->diskusiMessages((int)$m[1], (int)$m[2]);
                exit;
            }
            if (preg_match('#^guru/lms/diskusi/(\d+)/(\d+)$#', $path, $m)) {
                if ($method === 'POST') $lmsGuruCtrl->diskusiStore((int)$m[1], (int)$m[2]);
                else $lmsGuruCtrl->diskusiIndex((int)$m[1], (int)$m[2]);
                exit;
            }
            // CBT / UJIAN ROUTES
            if (preg_match('#^guru/lms/ujian/store/(\d+)/(\d+)$#', $path, $m) && $method === 'POST') {
                $lmsGuruCtrl->ujianStore((int)$m[1], (int)$m[2]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/toggle/(\d+)/(\d+)/(\d+)$#', $path, $m) && $method === 'POST') {
                $lmsGuruCtrl->ujianToggle((int)$m[1], (int)$m[2], (int)$m[3]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/delete/(\d+)/(\d+)/(\d+)$#', $path, $m) && $method === 'POST') {
                $lmsGuruCtrl->ujianDelete((int)$m[1], (int)$m[2], (int)$m[3]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/soal/store/(\d+)$#', $path, $m) && $method === 'POST') {
                $lmsGuruCtrl->soalStore((int)$m[1]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/soal/delete/(\d+)/(\d+)$#', $path, $m) && $method === 'POST') {
                $lmsGuruCtrl->soalDelete((int)$m[1], (int)$m[2]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/soal/(\d+)$#', $path, $m)) {
                $lmsGuruCtrl->ujianSoal((int)$m[1]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/hasil/(\d+)$#', $path, $m)) {
                $lmsGuruCtrl->ujianHasil((int)$m[1]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/detail-siswa/(\d+)$#', $path, $m)) {
                $lmsGuruCtrl->ujianDetailSiswa((int)$m[1]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/nilai-essay/(\d+)$#', $path, $m) && $method === 'POST') {
                $lmsGuruCtrl->nilaiEssayStore((int)$m[1]);
                exit;
            }
            if (preg_match('#^guru/lms/ujian/(\d+)/(\d+)$#', $path, $m)) {
                if ($method === 'POST') $lmsGuruCtrl->ujianStore((int)$m[1], (int)$m[2]);
                else $lmsGuruCtrl->ujianIndex((int)$m[1], (int)$m[2]);
                exit;
            }
        }

        if ($path === 'guru/nilai') {
            (new NilaiController())->index();
            exit;
        }
        if ($path === 'guru/nilai/input') {
            (new NilaiController())->inputSemester();
            exit;
        }
        if ($path === 'guru/nilai/save' && $method === 'POST') {
            (new NilaiController())->saveSemester();
            exit;
        }
    }

    // =====================================================================
    // SISWA PWA ROUTES (Halaman web PWA)
    // =====================================================================
    if (strpos($path, 'siswa/') === 0 || $path === 'siswa') {
        // Redirect ke login siswa jika belum ada token
        if ($path === 'siswa' || $path === 'siswa/beranda') {
            // Serve PWA shell — auth check via JS
            require_once __DIR__ . '/../app/Views/siswa/pwa_shell.php';
            exit;
        }
        if ($path === 'siswa/login') {
            App::redirect(App::baseUrl('login'));
            exit;
        }
    }
    // =====================================================================
    // END SISWA PWA ROUTES
    // =====================================================================

    // PIKET ROUTES
    if (strpos($path, 'piket/') === 0 || $path === 'piket') {
        $piket = new PiketController();

        if ($path === 'piket/scanner' || $path === 'piket') {
            $piket->scannerView();
            exit;
        }
        if (($path === 'piket/scan-ajax' || $path === 'piket/scan-process') && $method === 'POST') {
            $piket->ajaxScan();
            exit;
        }
        if ($path === 'piket/riwayat') {
            $piket->riwayatView();
            exit;
        }
        if ($path === 'piket/live-feed') {
            $piket->liveFeed();
            exit;
        }
        if ($path === 'piket/input-izin') {
            if ($method === 'POST') $piket->ajaxInputIzin();
            else $piket->inputIzinView();
            exit;
        }
    }

    // PAYROLL ROUTES
    if (strpos($path, 'payroll') === 0) {
        $pay = new PayrollController();

        if ($path === 'payroll') {
            $pay->index();
            exit;
        }
        if (preg_match('#^payroll/slip/(\d+)/(\d+)/(\d+)$#', $path, $m)) {
            $pay->cetakSlip((int)$m[1], (int)$m[2], (int)$m[3]);
            exit;
        }
    }

    // 404 Fallback
    http_response_code(404);
    echo "<h1>404 - Halaman Tidak Ditemukan</h1><p>Halaman '{$path}' tidak tersedia.</p><a href='" . App::baseUrl() . "'>Kembali ke Beranda</a>";

} catch (\Throwable $e) {
    http_response_code(500);
    error_log("[SI Presensi Error] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    $isApi = (strpos($path, 'api/v1/') === 0);
    
    if ($isApi) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => App::isDevMode() ? $e->getMessage() : 'Terjadi kendala sistem internal.'
        ]);
        exit;
    }

    if (App::isDevMode()) {
        echo "<h1>Terjadi Kesalahan Sistem (Mode Pengembang)</h1>";
        echo "<p><strong>Pesan:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Lokasi:</strong> " . htmlspecialchars($e->getFile() . ":" . $e->getLine()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<div style='font-family:sans-serif; text-align:center; padding:60px 20px;'>";
        echo "<h1 style='color:#e11d48; font-size:24px;'>500 - Terjadi Kendala Sistem</h1>";
        echo "<p style='color:#64748b; font-size:14px; max-width:500px; margin:10px auto;'>Mohon maaf, sistem sedang memproses kendala teknis internal. Silakan muat ulang halaman atau hubungi administrator sekolah.</p>";
        echo "<a href='" . App::baseUrl() . "' style='display:inline-block; margin-top:20px; padding:10px 24px; background:#2563eb; color:#fff; font-weight:bold; font-size:14px; border-radius:10px; text-decoration:none;'>Kembali ke Beranda</a>";
        echo "</div>";
    }
}

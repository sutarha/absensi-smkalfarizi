<?php
namespace App\Controllers;

use App\Config\App;
use App\Config\Database;
use App\Helpers\PdfHelper;
use App\Helpers\DapodikGuruHelper;
use App\Models\KonfigurasiSekolah;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\JadwalPelajaran;
use App\Models\MataPelajaran;
use App\Models\KelasMapel;
use App\Models\SesiMengajar;
use App\Models\PresensiGerbang;
use App\Models\PresensiGerbangGuru;
use App\Models\PresensiMapel;
use App\Models\TahunPelajaran;
use App\Models\Notifikasi;

class AdminController
{
    private array $user;
    private array $config;

    public function __construct()
    {
        $this->user = AuthController::requireRole(['admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $this->config = KonfigurasiSekolah::get();
        $this->checkRbacAccess();
    }

    private function checkRbacAccess()
    {
        $role = $this->user['role'];
        if ($role === 'admin') return;

        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if (!empty($baseDir) && strpos($uriPath, $baseDir) === 0) {
            $uriPath = substr($uriPath, strlen($baseDir));
        }
        $path = trim($uriPath, '/');

        $allowedPaths = [
            'admin/dashboard' => ['kepala_sekolah', 'wakasek_kurikulum', 'bendahara'],
            'admin/monitoring/guru' => ['kepala_sekolah', 'wakasek_kurikulum', 'bendahara'],
            'admin/monitoring/siswa' => ['kepala_sekolah', 'wakasek_kurikulum'],
            'admin/siswa' => ['kepala_sekolah', 'wakasek_kurikulum'],
            'admin/kelas' => ['kepala_sekolah', 'wakasek_kurikulum'],
            'admin/mapel' => ['kepala_sekolah', 'wakasek_kurikulum'],
            'admin/jadwal' => ['kepala_sekolah', 'wakasek_kurikulum'],
            'admin/buku-induk' => ['kepala_sekolah', 'wakasek_kurikulum'],
            'admin/akademik' => ['kepala_sekolah', 'wakasek_kurikulum'],
            'admin/nilai' => ['kepala_sekolah', 'wakasek_kurikulum'],
            'admin/payroll' => ['kepala_sekolah', 'bendahara'],
            'admin/surat' => ['kepala_sekolah'],
            'admin/lms' => ['kepala_sekolah', 'wakasek_kurikulum']
        ];

        if ($path === 'admin') $path = 'admin/dashboard';

        $isAllowed = false;
        foreach ($allowedPaths as $prefix => $roles) {
            if (strpos($path, $prefix) === 0) {
                if (in_array($role, $roles)) {
                    $isAllowed = true;
                    break;
                }
            }
        }

        if (!$isAllowed) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                App::json(['success' => false, 'message' => 'Akses ditolak: role Anda tidak memiliki wewenang fitur ini.'], 403);
            }
            http_response_code(403);
            die("Akses ditolak. Anda tidak memiliki wewenang untuk fitur ini.");
        }
    }

    public function dashboard(): void
    {
        $today = date('Y-m-d');
        $allGuru = Guru::getAll();
        $allSiswa = Siswa::getAll();
        $allKelas = Kelas::getAll();
        $allJadwal = JadwalPelajaran::getAll();
        $allMapel = MataPelajaran::getAll();
        $activeTapel = TahunPelajaran::getActive();

        // Kehadiran guru hari ini
        $monitoringGuru = SesiMengajar::getLiveMonitoring($today);
        $guruCheckinCount = 0;
        $guruTelatCount = 0;
        foreach ($monitoringGuru as $mg) {
            if (!empty($mg['sesi_id'])) {
                $guruCheckinCount++;
                if ((int)$mg['menit_terlambat'] > 0) {
                    $guruTelatCount++;
                }
            }
        }

        // Kehadiran siswa gerbang hari ini
        $gerbangToday = PresensiGerbang::getByDate($today);
        $siswaDatangCount = 0;
        $siswaPulangCount = 0;
        $siswaHadirLengkap = 0;
        foreach ($gerbangToday as $gt) {
            if (!empty($gt['waktu_datang'])) $siswaDatangCount++;
            if (!empty($gt['waktu_pulang'])) $siswaPulangCount++;
            if ($gt['status_kehadiran'] === 'HADIR') $siswaHadirLengkap++;
        }

        $user = $this->user;
        $config = $this->config;

        require __DIR__ . '/../Views/admin/dashboard.php';
    }

    // ==========================================
    // GEOFENCING & KONFIGURASI SEKOLAH
    // ==========================================
    public function geofenceView(): void
    {
        $user = $this->user;
        $config = KonfigurasiSekolah::get();
        require __DIR__ . '/../Views/admin/geofence.php';
    }

    public function geofenceSave(): void
    {
        $rawLat = trim((string)($_POST['latitude_pusat'] ?? ''));
        $rawLng = trim((string)($_POST['longitude_pusat'] ?? ''));

        // Jika user mem-paste koordinat lengkap "lat, lng" di salah satu field
        if ((strpos($rawLat, ',') !== false || strpos($rawLat, ' ') !== false) && empty($rawLng)) {
            $parts = preg_split('/[,\s]+/', $rawLat);
            if (count($parts) >= 2) {
                $rawLat = $parts[0];
                $rawLng = $parts[1];
            }
        }

        // Normalisasi format desimal koma ke titik
        $cleanLat = str_replace([' ', ','], ['', '.'], $rawLat);
        $cleanLng = str_replace([' ', ','], ['', '.'], $rawLng);

        $lat = is_numeric($cleanLat) ? (float)$cleanLat : -6.917464;
        $lng = is_numeric($cleanLng) ? (float)$cleanLng : 107.619123;
        $radius = max(10, min(5000, (int)($_POST['radius_meter'] ?? 50)));

        // Handle logo upload
        $logoName = null;
        if (isset($_FILES['logo_kop']) && $_FILES['logo_kop']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['logo_kop']['tmp_name'];
            $fileName = $_FILES['logo_kop']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png'];

            if (in_array($fileExt, $allowedExts)) {
                $uploadDir = __DIR__ . '/../../public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $newFileName = 'logo_' . time() . '.' . $fileExt;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $logoName = $newFileName;
                }
            }
        }

        $data = [
            'id' => 1,
            'nama_sekolah' => trim($_POST['nama_sekolah'] ?? 'SMK AL-FARIZI'),
            'alamat_sekolah' => trim($_POST['alamat_sekolah'] ?? ''),
            'kepala_sekolah' => trim($_POST['kepala_sekolah'] ?? ''),
            'bendahara_tu' => trim($_POST['bendahara_tu'] ?? ''),
            'latitude_pusat' => $lat,
            'longitude_pusat' => $lng,
            'radius_meter' => $radius,
            'honor_per_jp' => (float)($_POST['honor_per_jp'] ?? 5000),
            'durasi_jp_menit' => (int)($_POST['durasi_jp_menit'] ?? 40),
            'denda_per_menit' => (float)($_POST['denda_per_menit'] ?? 125),
            'toleransi_h_minus' => (int)($_POST['toleransi_h_minus'] ?? 5),
            'jam_guru_masuk_selesai' => trim($_POST['jam_guru_masuk_selesai'] ?? '06:30:00'),
            'jam_guru_pulang_mulai' => trim($_POST['jam_guru_pulang_mulai'] ?? '13:00:00'),
        ];

        if ($logoName !== null) {
            $data['logo_kop'] = $logoName;
        }

        KonfigurasiSekolah::updateConfig($data);
        $_SESSION['flash_success'] = 'Konfigurasi GPS Geofencing, Jam Kerja Guru & Tarif Honor berhasil disimpan!';
        App::redirect(App::baseUrl('admin/geofence'));
    }

    // ==========================================
    // MASTER GURU
    // ==========================================
    public function guruIndex(): void
    {
        $guruList = Guru::getAll();
        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/guru_index.php';
    }

    public function guruStore(): void
    {
        $nikNip = trim($_POST['nik_nip'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if (empty($nikNip) || empty($nama) || empty($username)) {
            $_SESSION['flash_error'] = 'Gagal menyimpan: NIK/NIP, Nama Lengkap, dan Username wajib diisi!';
            App::redirect(App::baseUrl('admin/guru'));
            return;
        }

        Guru::create([
            'nik_nip' => $nikNip,
            'nama_lengkap' => $nama,
            'username' => $username,
            'password' => !empty($_POST['password']) ? $_POST['password'] : 'guru123',
            'role' => $_POST['role'] ?? 'guru',
            'tugas_tambahan' => trim($_POST['tugas_tambahan'] ?? ''),
            'tunjangan_tugas' => (float)($_POST['tunjangan_tugas'] ?? 0),
            'no_hp' => trim($_POST['no_hp'] ?? ''),
        ]);

        $_SESSION['flash_success'] = 'Data guru berhasil ditambahkan!';
        App::redirect(App::baseUrl('admin/guru'));
    }

    public function guruUpdate(int $id): void
    {
        $nikNip = trim($_POST['nik_nip'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if (empty($nikNip) || empty($nama) || empty($username)) {
            $_SESSION['flash_error'] = 'Gagal memperbarui: NIK/NIP, Nama Lengkap, dan Username wajib diisi!';
            App::redirect(App::baseUrl('admin/guru'));
            return;
        }

        Guru::update($id, [
            'nik_nip' => $nikNip,
            'nama_lengkap' => $nama,
            'username' => $username,
            'password' => !empty($_POST['password']) ? $_POST['password'] : null,
            'role' => $_POST['role'] ?? 'guru',
            'tugas_tambahan' => trim($_POST['tugas_tambahan'] ?? ''),
            'tunjangan_tugas' => (float)($_POST['tunjangan_tugas'] ?? 0),
            'no_hp' => trim($_POST['no_hp'] ?? ''),
        ]);

        $_SESSION['flash_success'] = 'Data guru berhasil diperbarui!';
        App::redirect(App::baseUrl('admin/guru'));
    }

    public function guruDelete(int $id): void
    {
        if ($id === (int)$this->user['id']) {
            $_SESSION['flash_error'] = 'Anda tidak dapat menghapus akun Anda sendiri!';
        } else {
            Guru::delete($id);
            $_SESSION['flash_success'] = 'Data guru berhasil dihapus!';
            \App\Helpers\AuditLog::log('DELETE_GURU', "Menghapus data guru ID: $id");
        }
        App::redirect(App::baseUrl('admin/guru'));
    }

    public function guruImportView(): void
    {
        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/guru_import.php';
    }

    public function guruImportProcess(): void
    {
        if (empty($_FILES['file_dapodik']['tmp_name'])) {
            $_SESSION['flash_error'] = "Silakan pilih berkas Excel (.xlsx / .xls) atau CSV Guru Dapodik terlebih dahulu.";
            App::redirect(App::baseUrl('admin/guru/import'));
            return;
        }

        $file = $_FILES['file_dapodik']['tmp_name'];
        $originalName = $_FILES['file_dapodik']['name'] ?? '';
        $fileNameLower = strtolower($originalName);

        $allowedExtensions = ['.xlsx', '.xls', '.csv', '.txt'];
        $isValid = false;
        foreach ($allowedExtensions as $ext) {
            if (str_ends_with($fileNameLower, $ext)) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            $_SESSION['flash_error'] = "Format berkas tidak didukung. Harap unggah berkas Excel (.xlsx / .xls) atau CSV (.csv).";
            App::redirect(App::baseUrl('admin/guru/import'));
            return;
        }

        try {
            $teachers = DapodikGuruHelper::parseFile($file, $originalName);
            if (empty($teachers)) {
                $_SESSION['flash_error'] = "Tidak ada baris data guru yang valid yang dapat dibaca dari berkas tersebut.";
                App::redirect(App::baseUrl('admin/guru/import'));
                return;
            }

            $defaultPassword = !empty($_POST['default_password']) ? trim($_POST['default_password']) : 'guru123';
            $stats = Guru::bulkUpsertDapodik($teachers, $defaultPassword);

            $msg = "Import Data Guru Dapodik Berhasil! Ditambah: {$stats['inserted']} guru baru, Diperbarui: {$stats['updated']} guru.";
            if ($stats['failed'] > 0) {
                $msg .= " (Gagal: {$stats['failed']}).";
            }
            $_SESSION['flash_success'] = $msg;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Gagal memproses berkas Dapodik Guru: " . $e->getMessage();
            App::redirect(App::baseUrl('admin/guru/import'));
            return;
        }

        App::redirect(App::baseUrl('admin/guru'));
    }

    public function guruDownloadTemplate(): void
    {
        $xlsx = DapodikGuruHelper::getExcelTemplateContent();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_data_guru_dapodik.xlsx"');
        header('Content-Length: ' . strlen($xlsx));
        header('Cache-Control: max-age=0');
        echo $xlsx;
        exit;
    }

    // ==========================================
    // MASTER KELAS & WALI KELAS
    // ==========================================
    public function kelasIndex(): void
    {
        $kelasList = Kelas::getAll();
        $guruList = Guru::getAll(['guru', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $mapelList = MataPelajaran::getAll();
        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/kelas_index.php';
    }

    public function kelasStore(): void
    {
        Kelas::create([
            'nama_kelas' => trim($_POST['nama_kelas']),
            'tingkat' => trim($_POST['tingkat']),
            'jurusan' => trim($_POST['jurusan']),
            'wali_kelas_guru_id' => !empty($_POST['wali_kelas_guru_id']) ? (int)$_POST['wali_kelas_guru_id'] : null,
        ]);
        $_SESSION['flash_success'] = 'Kelas baru dan Wali Kelas berhasil ditambahkan!';
        App::redirect(App::baseUrl('admin/kelas'));
    }

    public function kelasUpdate(int $id): void
    {
        Kelas::update($id, [
            'nama_kelas' => trim($_POST['nama_kelas']),
            'tingkat' => trim($_POST['tingkat']),
            'jurusan' => trim($_POST['jurusan']),
            'wali_kelas_guru_id' => !empty($_POST['wali_kelas_guru_id']) ? (int)$_POST['wali_kelas_guru_id'] : null,
        ]);
        $_SESSION['flash_success'] = 'Data kelas dan Wali Kelas berhasil diperbarui!';
        App::redirect(App::baseUrl('admin/kelas'));
    }

    public function kelasDelete(int $id): void
    {
        Kelas::delete($id);
        $_SESSION['flash_success'] = 'Kelas berhasil dihapus!';
        App::redirect(App::baseUrl('admin/kelas'));
    }

    // ==========================================
    // PLOTTING MAPEL & GURU PENGAMPU PER KELAS
    // ==========================================
    public function kelasMapelStore(int $kelasId): void
    {
        $mapelId = (int)($_POST['mapel_id'] ?? 0);
        $guruId = (int)($_POST['guru_id'] ?? 0);
        $alokasiJp = (int)($_POST['alokasi_jp'] ?? 2);

        if ($mapelId > 0 && $guruId > 0) {
            KelasMapel::assign($kelasId, $mapelId, $guruId, $alokasiJp);
            $_SESSION['flash_success'] = 'Mata pelajaran dan Guru Pengampu berhasil dipasang pada kelas ini!';
        } else {
            $_SESSION['flash_error'] = 'Mata pelajaran dan Guru Pengampu wajib dipilih.';
        }

        App::redirect(App::baseUrl("admin/kelas?open_mapel={$kelasId}"));
    }

    public function kelasMapelDelete(int $id): void
    {
        $km = KelasMapel::findById($id);
        $kelasId = $km['kelas_id'] ?? null;
        KelasMapel::unassign($id);
        $_SESSION['flash_success'] = 'Alokasi mata pelajaran berhasil dihapus dari kelas!';
        if ($kelasId) {
            App::redirect(App::baseUrl("admin/kelas?open_mapel={$kelasId}"));
        } else {
            App::redirect(App::baseUrl('admin/kelas'));
        }
    }

    public function kelasMapelApi(int $kelasId): void
    {
        header('Content-Type: application/json');
        $data = KelasMapel::getByKelas($kelasId);
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // ==========================================
    // MASTER SISWA & BARCODE
    // ==========================================
    public function siswaIndex(): void
    {
        $kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
        $kelasList = Kelas::getAll();
        $siswaList = Siswa::getAll($kelasId);
        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/siswa_index.php';
    }

    public function siswaStore(): void
    {
        $nisn = trim($_POST['nisn']);
        $barcode = trim($_POST['barcode_code'] ?: ('ALF-' . $nisn));
        $tglLahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
        $noHpOrtu = !empty($_POST['no_hp_ortu']) ? trim($_POST['no_hp_ortu']) : null;

        Siswa::create([
            'nisn'          => $nisn,
            'barcode_code'  => $barcode,
            'nama_siswa'    => trim($_POST['nama_siswa']),
            'kelas_id'      => (int)$_POST['kelas_id'],
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? 'L',
            'tanggal_lahir' => $tglLahir,
            'no_hp_ortu'    => $noHpOrtu,
        ]);

        $_SESSION['flash_success'] = 'Data siswa baru berhasil dibuat! Siswa sudah bisa login ke Portal PWA.';
        App::redirect(App::baseUrl('admin/siswa'));
    }

    public function siswaUpdate(int $id): void
    {
        $tglLahir = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
        $noHpOrtu = !empty($_POST['no_hp_ortu']) ? trim($_POST['no_hp_ortu']) : null;

        Siswa::update($id, [
            'nisn'          => trim($_POST['nisn']),
            'barcode_code'  => trim($_POST['barcode_code']),
            'nama_siswa'    => trim($_POST['nama_siswa']),
            'kelas_id'      => (int)$_POST['kelas_id'],
            'jenis_kelamin' => $_POST['jenis_kelamin'] ?? 'L',
            'tanggal_lahir' => $tglLahir,
            'no_hp_ortu'    => $noHpOrtu,
        ]);

        $_SESSION['flash_success'] = 'Data siswa berhasil diperbarui!';
        App::redirect(App::baseUrl('admin/siswa'));
    }

    public function siswaDelete(int $id): void
    {
        Siswa::delete($id);
        $_SESSION['flash_success'] = 'Data siswa berhasil dihapus!';
        \App\Helpers\AuditLog::log('DELETE_SISWA', "Menghapus data siswa ID: $id");
        App::redirect(App::baseUrl('admin/siswa'));
    }

    /**
     * Memasukkan / Memindahkan Siswa ke Kelas Secara Massal (Ceklis)
     */
    public function siswaBulkAssignKelas(): void
    {
        $siswaIds = $_POST['siswa_ids'] ?? [];
        $kelasId = (int)($_POST['target_kelas_id'] ?? 0);

        if (empty($siswaIds) || $kelasId <= 0) {
            $_SESSION['flash_error'] = 'Harap pilih minimal satu siswa dan pilih kelas tujuan yang valid.';
            App::redirect(App::baseUrl('admin/siswa'));
            return;
        }

        $activeTapel = TahunPelajaran::getActive();
        $tapelId = $activeTapel ? (int)$activeTapel['id'] : null;

        $count = Siswa::bulkAssignKelas($siswaIds, $kelasId, $tapelId);
        $targetKelas = Kelas::findById($kelasId);
        $namaK = $targetKelas ? $targetKelas['nama_kelas'] : 'kelas tujuan';

        $_SESSION['flash_success'] = "Berhasil memasukkan {$count} siswa ke dalam rombel {$namaK}!";
        
        $returnUrl = !empty($_POST['return_url']) ? $_POST['return_url'] : App::baseUrl('admin/siswa?kelas_id=' . $kelasId);
        App::redirect($returnUrl);
    }

    public function cetakKartuBarcode(): void
    {
        $kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
        $siswaList = Siswa::getAll($kelasId);
        $config = KonfigurasiSekolah::get();

        echo PdfHelper::renderKartuSiswaHtml($siswaList, $config);
        exit;
    }

    // ==========================================
    // MASTER MATA PELAJARAN (KURIKULUM)
    // ==========================================
    public function mapelIndex(): void
    {
        $kelompok = $_GET['kelompok'] ?? null;
        $tingkat = $_GET['tingkat'] ?? null;
        $mapelList = MataPelajaran::getAll($tingkat, $kelompok);
        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/mapel_index.php';
    }

    private function parseTingkatInput($tingkatInput): string
    {
        if (is_array($tingkatInput)) {
            $valid = ['X', 'XI', 'XII'];
            $chosen = [];
            foreach ($valid as $v) {
                if (in_array($v, $tingkatInput, true)) {
                    $chosen[] = $v;
                }
            }
            if (in_array('SEMUA', $tingkatInput, true) || count($chosen) === 3) {
                return 'SEMUA';
            }
            if (!empty($chosen)) {
                return implode(', ', $chosen);
            }
            return 'SEMUA';
        }

        $t = trim((string)$tingkatInput);
        return !empty($t) ? $t : 'SEMUA';
    }

    public function mapelStore(): void
    {
        $kode = trim($_POST['kode_mapel'] ?? '');
        $nama = trim($_POST['nama_mapel'] ?? '');
        $kelompok = trim($_POST['kelompok'] ?? 'Umum');
        $tingkat = $this->parseTingkatInput($_POST['tingkat'] ?? 'SEMUA');

        if (!empty($kode) && !empty($nama)) {
            MataPelajaran::create($kode, $nama, $kelompok, $tingkat);
            $_SESSION['flash_success'] = "Mata pelajaran {$nama} ({$kode}) berhasil ditambahkan!";
        } else {
            $_SESSION['flash_error'] = 'Kode dan nama mata pelajaran wajib diisi.';
        }
        
        $redirectTo = $_POST['redirect_to'] ?? App::baseUrl('admin/mapel');
        App::redirect($redirectTo);
    }

    public function mapelUpdate(int $id): void
    {
        $tingkat = $this->parseTingkatInput($_POST['tingkat'] ?? 'SEMUA');
        MataPelajaran::update($id, [
            'kode_mapel' => trim($_POST['kode_mapel'] ?? ''),
            'nama_mapel' => trim($_POST['nama_mapel'] ?? ''),
            'kelompok' => trim($_POST['kelompok'] ?? 'Umum'),
            'tingkat' => $tingkat,
        ]);
        $_SESSION['flash_success'] = 'Data mata pelajaran kurikulum berhasil diperbarui!';
        App::redirect(App::baseUrl('admin/mapel'));
    }

    public function mapelDelete(int $id): void
    {
        MataPelajaran::delete($id);
        $_SESSION['flash_success'] = 'Mata pelajaran berhasil dihapus!';
        App::redirect(App::baseUrl('admin/mapel'));
    }

    // ==========================================
    // MASTER JADWAL KBM & RELASI MAPEL
    // ==========================================
    public function jadwalIndex(): void
    {
        $hari = !empty($_GET['hari']) ? $_GET['hari'] : null;
        $filterKelasId = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : null;
        $filterGuruId = !empty($_GET['guru_id']) ? (int)$_GET['guru_id'] : null;

        $jadwalList = JadwalPelajaran::getAll($hari, $filterGuruId, $filterKelasId);
        $kelasList = Kelas::getAll();
        $guruList = Guru::getAll(['guru', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $mapelList = MataPelajaran::getAll();
        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/jadwal_index.php';
    }

    public function checkJadwalConflictApi(): void
    {
        header('Content-Type: application/json');
        $hari = $_POST['hari'] ?? $_GET['hari'] ?? '';
        $kelasId = (int)($_POST['kelas_id'] ?? $_GET['kelas_id'] ?? 0);
        $guruId = (int)($_POST['guru_id'] ?? $_GET['guru_id'] ?? 0);
        $jamMulai = trim($_POST['jam_mulai'] ?? $_GET['jam_mulai'] ?? '');
        $jamSelesai = trim($_POST['jam_selesai'] ?? $_GET['jam_selesai'] ?? '');
        $excludeId = !empty($_POST['exclude_id']) ? (int)$_POST['exclude_id'] : (!empty($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null);

        if (empty($hari) || empty($kelasId) || empty($guruId) || empty($jamMulai) || empty($jamSelesai)) {
            echo json_encode(['status' => 'ok', 'has_conflict' => false, 'message' => 'Parameter belum lengkap']);
            exit;
        }

        $conflict = JadwalPelajaran::checkConflict($hari, $kelasId, $guruId, $jamMulai, $jamSelesai, $excludeId);
        if ($conflict) {
            echo json_encode([
                'status' => 'conflict',
                'has_conflict' => true,
                'type' => $conflict['type'],
                'title' => $conflict['title'],
                'message' => $conflict['message']
            ]);
        } else {
            echo json_encode([
                'status' => 'ok',
                'has_conflict' => false,
                'message' => 'Jam Aman: Tidak ada bentrok kelas maupun guru pada waktu ini.'
            ]);
        }
        exit;
    }

    public function jadwalStore(): void
    {
        $hari = trim($_POST['hari'] ?? '');
        $kelasId = (int)($_POST['kelas_id'] ?? 0);
        $guruId = (int)($_POST['guru_id'] ?? 0);
        $jamMulai = trim($_POST['jam_mulai'] ?? '');
        $jamSelesai = trim($_POST['jam_selesai'] ?? '');
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : App::baseUrl('admin/jadwal');

        // Validasi Anti-Bentrok Waktu KBM
        $conflict = JadwalPelajaran::checkConflict($hari, $kelasId, $guruId, $jamMulai, $jamSelesai);
        if ($conflict) {
            $_SESSION['flash_error'] = "⛔ GAGAL MENYIMPAN: " . $conflict['message'];
            App::redirect($redirectUrl);
            return;
        }

        $mapelId = !empty($_POST['mapel_id']) ? (int)$_POST['mapel_id'] : null;
        $namaMapel = trim($_POST['nama_mapel'] ?? '');

        if ($mapelId && empty($namaMapel)) {
            $m = MataPelajaran::findById($mapelId);
            if ($m) $namaMapel = $m['nama_mapel'];
        }

        // Jika kelas_mapel belum ada alokasi untuk pasangan ini, buat otomatis
        if ($mapelId && $kelasId && $guruId) {
            KelasMapel::assign($kelasId, $mapelId, $guruId, (int)($_POST['jumlah_jp'] ?? 2));
        }

        JadwalPelajaran::create([
            'hari' => $hari,
            'kelas_id' => $kelasId,
            'guru_id' => $guruId,
            'mapel_id' => $mapelId,
            'nama_mapel' => $namaMapel,
            'jumlah_jp' => (int)$_POST['jumlah_jp'],
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
        ]);

        $_SESSION['flash_success'] = 'Jadwal KBM berhasil ditambahkan dan terbebas dari bentrok waktu!';
        App::redirect($redirectUrl);
    }

    public function jadwalUpdate(int $id): void
    {
        $hari = trim($_POST['hari'] ?? '');
        $kelasId = (int)($_POST['kelas_id'] ?? 0);
        $guruId = (int)($_POST['guru_id'] ?? 0);
        $jamMulai = trim($_POST['jam_mulai'] ?? '');
        $jamSelesai = trim($_POST['jam_selesai'] ?? '');
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : App::baseUrl('admin/jadwal');

        // Validasi Anti-Bentrok Waktu KBM (kecualikan jadwal yang sedang diedit)
        $conflict = JadwalPelajaran::checkConflict($hari, $kelasId, $guruId, $jamMulai, $jamSelesai, $id);
        if ($conflict) {
            $_SESSION['flash_error'] = "⛔ GAGAL MEMPERBARUI: " . $conflict['message'];
            App::redirect($redirectUrl);
            return;
        }

        $mapelId = !empty($_POST['mapel_id']) ? (int)$_POST['mapel_id'] : null;
        $namaMapel = trim($_POST['nama_mapel'] ?? '');

        if ($mapelId && empty($namaMapel)) {
            $m = MataPelajaran::findById($mapelId);
            if ($m) $namaMapel = $m['nama_mapel'];
        }

        JadwalPelajaran::update($id, [
            'hari' => $hari,
            'kelas_id' => $kelasId,
            'guru_id' => $guruId,
            'mapel_id' => $mapelId,
            'nama_mapel' => $namaMapel,
            'jumlah_jp' => (int)$_POST['jumlah_jp'],
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
        ]);

        $_SESSION['flash_success'] = 'Jadwal KBM berhasil diperbarui!';
        App::redirect($redirectUrl);
    }

    public function jadwalDelete(int $id): void
    {
        JadwalPelajaran::delete($id);
        $_SESSION['flash_success'] = 'Jadwal KBM berhasil dihapus!';
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : App::baseUrl('admin/jadwal');
        App::redirect($redirectUrl);
    }

    // ==========================================
    // MONITORING REAL-TIME (GURU & SISWA)
    // ==========================================
    public function monitoringGuru(): void
    {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $monitoringList = SesiMengajar::getLiveMonitoring($tanggal);
        $presensiGerbangGuruList = PresensiGerbangGuru::getByDate($tanggal);
        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/monitoring_guru.php';
    }

    public function monitoringGuruUpdateManual(): void
    {
        $this->checkRbacAccess('admin/monitoring/guru');

        // Hanya super admin yang boleh melakukan update manual
        if ($this->user['role'] !== 'admin') {
            $_SESSION['flash_error'] = 'Akses ditolak. Hanya Super Admin yang dapat mengedit presensi manual.';
            App::redirect(App::baseUrl('admin/monitoring/guru'));
            exit;
        }

        $jadwalId = (int)$_POST['jadwal_id'];
        $guruId = (int)$_POST['guru_id'];
        $tanggal = $_POST['tanggal'];
        
        $waktuCheckinStr = !empty($_POST['waktu_checkin']) ? $_POST['waktu_checkin'] : null;
        $waktuCheckoutStr = !empty($_POST['waktu_checkout']) ? $_POST['waktu_checkout'] : null;
        
        $waktuCheckin = $waktuCheckinStr ? $tanggal . ' ' . $waktuCheckinStr . ':00' : null;
        $waktuCheckout = $waktuCheckoutStr ? $tanggal . ' ' . $waktuCheckoutStr . ':00' : null;
        $menitTerlambat = (int)($_POST['menit_terlambat'] ?? 0);

        if (!$jadwalId || !$guruId || !$tanggal) {
            $_SESSION['flash_error'] = 'Data tidak lengkap untuk update manual.';
        } else {
            SesiMengajar::upsertManual($jadwalId, $guruId, $tanggal, $waktuCheckin, $waktuCheckout, $menitTerlambat);
            $_SESSION['flash_success'] = 'Data presensi mengajar berhasil diupdate manual.';
        }

        App::redirect(App::baseUrl('admin/monitoring/guru?tanggal=' . $tanggal));
    }

    public function monitoringGuruGerbangUpdateManual(): void
    {
        $this->checkRbacAccess('admin/monitoring/guru');

        if ($this->user['role'] !== 'admin') {
            $_SESSION['flash_error'] = 'Akses ditolak. Hanya Super Admin yang dapat mengedit presensi manual.';
            App::redirect(App::baseUrl('admin/monitoring/guru'));
            exit;
        }

        $guruId = (int)$_POST['guru_id'];
        $tanggal = $_POST['tanggal'];
        
        $waktuDatangStr = !empty($_POST['waktu_datang']) ? $_POST['waktu_datang'] : null;
        $waktuPulangStr = !empty($_POST['waktu_pulang']) ? $_POST['waktu_pulang'] : null;
        
        $waktuDatang = $waktuDatangStr ? $tanggal . ' ' . $waktuDatangStr . ':00' : null;
        $waktuPulang = $waktuPulangStr ? $tanggal . ' ' . $waktuPulangStr . ':00' : null;
        $statusKehadiran = $_POST['status_kehadiran'] ?? 'HADIR';
        $menitTerlambat = (int)($_POST['menit_terlambat_datang'] ?? 0);
        $keterangan = !empty($_POST['keterangan']) ? $_POST['keterangan'] : null;

        if (!$guruId || !$tanggal) {
            $_SESSION['flash_error'] = 'Data tidak lengkap untuk update manual.';
        } else {
            PresensiGerbangGuru::upsertManual($guruId, $tanggal, $waktuDatang, $waktuPulang, $statusKehadiran, $menitTerlambat, $keterangan);
            $_SESSION['flash_success'] = 'Data presensi gerbang berhasil diupdate manual.';
        }

        App::redirect(App::baseUrl('admin/monitoring/guru?tanggal=' . $tanggal));
    }

    public function monitoringSiswa(): void
    {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
        $filterBelumPulang = isset($_GET['belum_pulang']) && $_GET['belum_pulang'] === '1';

        $kelasList = Kelas::getAll();
        $gerbangList = PresensiGerbang::getByDate($tanggal, $kelasId, $filterBelumPulang);
        $ketuntasanMapel = PresensiMapel::getKetuntasanHarian($tanggal, $kelasId);
        $rincianMapelData = PresensiMapel::getRincianMapelHarian($tanggal, $kelasId);

        $jadwalHariIni = $rincianMapelData['jadwal_list'];
        $siswaRincianMapel = $rincianMapelData['siswa_list'];
        
        $mapelSummaryBySiswa = [];
        foreach ($siswaRincianMapel as $s) {
            $mapelSummaryBySiswa[$s['siswa_id']] = $s;
        }

        $hariIndo = $rincianMapelData['hari'];

        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/monitoring_siswa.php';
    }

    /**
     * Endpoint Real-Time Live Sync Monitoring Guru
     */
    public function monitoringGuruLive(): void
    {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $monitoringList = SesiMengajar::getLiveMonitoring($tanggal);
        $presensiGerbangGuruList = PresensiGerbangGuru::getByDate($tanggal);

        $cntHadirLengkap = 0;
        $cntDiSekolah = 0;
        $cntTugasLuar = 0;
        $cntIzinSakit = 0;
        $cntBelum = 0;

        foreach ($presensiGerbangGuruList as $pg) {
            if ($pg['status_kehadiran'] === 'HADIR' || $pg['status_kehadiran'] === 'TERLAMBAT') {
                if (!empty($pg['waktu_pulang'])) {
                    $cntHadirLengkap++;
                } else {
                    $cntDiSekolah++;
                }
            } elseif ($pg['status_kehadiran'] === 'TUGAS_LUAR') {
                $cntTugasLuar++;
            } elseif (in_array($pg['status_kehadiran'], ['IZIN', 'SAKIT'])) {
                $cntIzinSakit++;
            } else {
                $cntBelum++;
            }
        }

        $totalSesi = count($monitoringList);
        $sesiAktif = 0;
        $sesiSelesai = 0;
        $totalTelat = 0;
        $totalDenda = 0.0;

        foreach ($monitoringList as $m) {
            if (!empty($m['waktu_checkin'])) {
                if (!empty($m['waktu_checkout'])) {
                    $sesiSelesai++;
                } else {
                    $sesiAktif++;
                }
                $telat = (int)($m['menit_terlambat'] ?? 0);
                $totalTelat += $telat;
                $totalDenda += ($telat * (float)$this->config['denda_per_menit']);
            }
        }

        App::json([
            'success' => true,
            'server_time' => date('H:i:s'),
            'server_date' => $tanggal,
            'counters' => [
                'hadir_lengkap' => $cntHadirLengkap,
                'di_sekolah' => $cntDiSekolah,
                'tugas_luar' => $cntTugasLuar,
                'izin_sakit' => $cntIzinSakit,
                'belum_hadir' => $cntBelum,
                'total_sesi' => $totalSesi,
                'sesi_aktif' => $sesiAktif,
                'sesi_selesai' => $sesiSelesai,
                'total_telat_menit' => $totalTelat,
                'total_denda' => $totalDenda,
            ],
            'gerbang_list' => $presensiGerbangGuruList,
            'sessions' => $monitoringList
        ]);
    }

    /**
     * Endpoint Real-Time Live Sync Monitoring Siswa
     */
    public function monitoringSiswaLive(): void
    {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
        $filterBelumPulang = isset($_GET['belum_pulang']) && $_GET['belum_pulang'] === '1';

        $gerbangList = PresensiGerbang::getByDate($tanggal, $kelasId, $filterBelumPulang);
        $ketuntasanMapel = PresensiMapel::getKetuntasanHarian($tanggal, $kelasId);
        $rincianMapelData = PresensiMapel::getRincianMapelHarian($tanggal, $kelasId);
        
        $mapelSummaryBySiswa = [];
        foreach ($rincianMapelData['siswa_list'] as $s) {
            $mapelSummaryBySiswa[$s['siswa_id']] = $s['summary'];
        }

        $totalSiswa = count($gerbangList);
        $totalDatang = 0;
        $totalPulang = 0;
        $totalHadirLengkap = 0;
        $totalBelumPulang = 0;
        $totalAlpha = 0;

        foreach ($gerbangList as $r) {
            if (!empty($r['waktu_datang'])) {
                $totalDatang++;
            }
            if (!empty($r['waktu_pulang'])) {
                $totalPulang++;
            }
            if ($r['status_kehadiran'] === 'HADIR') {
                $totalHadirLengkap++;
            } elseif (!empty($r['waktu_datang']) && empty($r['waktu_pulang'])) {
                $totalBelumPulang++;
            } elseif ($r['status_kehadiran'] === 'ALPHA') {
                $totalAlpha++;
            }
        }

        App::json([
            'success' => true,
            'server_time' => date('H:i:s'),
            'server_date' => $tanggal,
            'counters' => [
                'total_siswa' => $totalSiswa,
                'total_datang' => $totalDatang,
                'total_pulang' => $totalPulang,
                'total_hadir_lengkap' => $totalHadirLengkap,
                'total_belum_pulang' => $totalBelumPulang,
                'total_alpha' => $totalAlpha,
            ],
            'gerbang_list' => $gerbangList,
            'ketuntasan' => $ketuntasanMapel,
            'mapel_summary' => $mapelSummaryBySiswa
        ]);
    }

    public function monitoringGuruExport(): void
    {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $monitoringList = SesiMengajar::getLiveMonitoring($tanggal);
        
        $headers = ['No', 'Nama Guru', 'Sesi Jadwal', 'Mata Pelajaran', 'Waktu Checkin', 'Waktu Checkout', 'Menit Terlambat', 'Status'];
        $data = [];
        
        $no = 1;
        foreach ($monitoringList as $m) {
            $status = !empty($m['waktu_checkout']) ? 'Selesai Mengajar' : (!empty($m['waktu_checkin']) ? 'Sedang Mengajar' : 'Belum Checkin');
            
            $data[] = [
                $no++,
                $m['nama_lengkap'],
                $m['jam_mulai'] . ' - ' . $m['jam_selesai'],
                $m['nama_mapel'],
                $m['waktu_checkin'] ?? '-',
                $m['waktu_checkout'] ?? '-',
                $m['menit_terlambat'] ?? '0',
                $status
            ];
        }
        
        \App\Helpers\ExportHelper::toExcel('Laporan_Mengajar_Guru_' . $tanggal, $headers, $data);
    }

    public function monitoringSiswaExport(): void
    {
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $kelasId = isset($_GET['kelas_id']) && $_GET['kelas_id'] !== '' ? (int)$_GET['kelas_id'] : null;
        
        $gerbangList = PresensiGerbang::getByDate($tanggal, $kelasId);
        
        $headers = ['No', 'Nama Siswa', 'Kelas', 'Waktu Datang', 'Waktu Pulang', 'Status Kehadiran', 'Keterangan'];
        $data = [];
        
        $no = 1;
        foreach ($gerbangList as $r) {
            $data[] = [
                $no++,
                $r['nama_siswa'],
                $r['nama_kelas'],
                $r['waktu_datang'] ?? '-',
                $r['waktu_pulang'] ?? '-',
                $r['status_kehadiran'],
                $r['keterangan'] ?? '-'
            ];
        }
        
        $kelasSuffix = $kelasId ? '_Kelas_' . $kelasId : '_Semua_Kelas';
        \App\Helpers\ExportHelper::toExcel('Laporan_Absensi_Gerbang_Siswa_' . $tanggal . $kelasSuffix, $headers, $data);
    }


    public function triggerEvaluasiGerbang(): void
    {
        $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
        $affectedSiswa = PresensiGerbang::runDailyEvaluation($tanggal);
        $affectedGuru = PresensiGerbangGuru::runDailyEvaluation($tanggal);

        $_SESSION['flash_success'] = "Audit presensi gerbang dua arah berhasil dijalankan! {$affectedSiswa} data siswa dan {$affectedGuru} data guru yang tidak lengkap telah dievaluasi.";
        App::redirect(App::baseUrl('admin/monitoring/guru?tanggal=' . $tanggal));
    }

    /**
     * Halaman Pusat Pembersihan & Reset Data Dummy
     */
    public function resetDataView(): void
    {
        $db = Database::getConnection();

        $counts = [
            'siswa' => (int)$db->query("SELECT COUNT(*) FROM siswa")->fetchColumn(),
            'buku_induk' => (int)$db->query("SELECT COUNT(*) FROM buku_induk_siswa")->fetchColumn(),
            'guru' => (int)$db->query("SELECT COUNT(*) FROM guru")->fetchColumn(),
            'guru_non_admin' => (int)$db->query("SELECT COUNT(*) FROM guru WHERE role != 'admin'")->fetchColumn(),
            'kelas' => (int)$db->query("SELECT COUNT(*) FROM kelas")->fetchColumn(),
            'mapel' => (int)$db->query("SELECT COUNT(*) FROM mata_pelajaran")->fetchColumn(),
            'jadwal' => (int)$db->query("SELECT COUNT(*) FROM jadwal_pelajaran")->fetchColumn(),
            'presensi_gerbang' => (int)$db->query("SELECT COUNT(*) FROM presensi_gerbang_siswa")->fetchColumn(),
            'presensi_gerbang_guru' => (int)$db->query("SELECT COUNT(*) FROM presensi_gerbang_guru")->fetchColumn(),
            'presensi_mapel' => (int)$db->query("SELECT COUNT(*) FROM presensi_mapel_siswa")->fetchColumn(),
            'sesi_guru' => (int)$db->query("SELECT COUNT(*) FROM sesi_mengajar_guru")->fetchColumn(),
            'nilai_siswa' => (int)$db->query("SELECT COUNT(*) FROM nilai_siswa")->fetchColumn(),
        ];

        $user = $this->user;
        $config = $this->config;
        require __DIR__ . '/../Views/admin/reset_data.php';
    }

    /**
     * Eksekusi Pembersihan Data
     */
    public function resetDataExecute(): void
    {
        $action = $_POST['action'] ?? '';
        $db = Database::getConnection();

        try {
            if ($action === 'presensi') {
                $db->exec("DELETE FROM presensi_mapel_siswa");
                $db->exec("DELETE FROM sesi_mengajar_guru");
                $db->exec("DELETE FROM presensi_gerbang_siswa");
                $db->exec("DELETE FROM presensi_gerbang_guru");
                $_SESSION['flash_success'] = "Seluruh riwayat presensi gerbang (siswa & guru), sesi mengajar, dan absensi mapel berhasil dikosongkan!";
            } elseif ($action === 'nilai') {
                $db->exec("DELETE FROM nilai_siswa");
                $_SESSION['flash_success'] = "Seluruh data nilai raport semester 1-6 siswa berhasil dikosongkan!";
            } elseif ($action === 'siswa') {
                $db->exec("DELETE FROM siswa");
                $_SESSION['flash_success'] = "Seluruh data siswa, buku induk, dan data presensi terkait berhasil dihapus!";
            } elseif ($action === 'guru') {
                $db->exec("DELETE FROM presensi_gerbang_guru WHERE guru_id IN (SELECT id FROM guru WHERE role != 'admin')");
                $db->exec("DELETE FROM jadwal_pelajaran WHERE guru_id IN (SELECT id FROM guru WHERE role != 'admin')");
                $db->exec("DELETE FROM guru WHERE role != 'admin'");
                $_SESSION['flash_success'] = "Seluruh akun guru dummy non-admin berhasil dihapus! Akun Super Admin ('admin') tetap terjaga.";
            } elseif ($action === 'all') {
                $confirmText = $_POST['confirm_text'] ?? '';
                if (strtoupper(trim($confirmText)) !== 'BERSIHKAN') {
                    $_SESSION['flash_error'] = "Konfirmasi tidak valid! Harap ketik kata BERSIHKAN dengan benar.";
                    App::redirect(App::baseUrl('admin/reset-data'));
                    return;
                }
                $db->exec("DELETE FROM presensi_mapel_siswa");
                $db->exec("DELETE FROM sesi_mengajar_guru");
                $db->exec("DELETE FROM presensi_gerbang_siswa");
                $db->exec("DELETE FROM presensi_gerbang_guru");
                $db->exec("DELETE FROM nilai_siswa");
                $db->exec("DELETE FROM siswa");
                $db->exec("DELETE FROM jadwal_pelajaran");
                $db->exec("DELETE FROM guru WHERE role != 'admin'");
                $_SESSION['flash_success'] = "Reset Total Berhasil! Seluruh data dummy (siswa, guru non-admin, jadwal, nilai, dan absensi gerbang) telah dibersihkan. Sistem siap untuk data riil sekolah.";
                \App\Helpers\AuditLog::log('RESET_DATA', "Pembersihan total (all) dieksekusi.");
            } else {
                $_SESSION['flash_error'] = "Aksi pembersihan tidak dikenali.";
            }
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Gagal mengeksekusi pembersihan data: " . $e->getMessage();
        }

        App::redirect(App::baseUrl('admin/reset-data'));
    }

    /**
     * Halaman Manajemen Notifikasi & Pengumuman Admin
     */
    public function notifikasiIndex(): void
    {
        $allBroadcast = Notifikasi::getAllBroadcast(50);
        $stats        = Notifikasi::getAdminStats();
        $allKelas     = Kelas::getAll();
        $allGuru      = Guru::getAll();
        $allSiswa     = Siswa::getAll();
        $activeTapel  = TahunPelajaran::getActive();

        $user   = $this->user;
        $config = $this->config;

        require __DIR__ . '/../Views/admin/notifikasi.php';
    }

    /**
     * Proses kirim / broadcast notifikasi baru
     */
    public function notifikasiStore(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            App::redirect(App::baseUrl('admin/notifikasi'));
            return;
        }

        $judul      = trim($_POST['judul'] ?? '');
        $pesan      = trim($_POST['pesan'] ?? '');
        $tipe       = trim($_POST['tipe'] ?? 'pengumuman');
        $targetRole = trim($_POST['target_role'] ?? 'semua');
        $targetKelasId = !empty($_POST['target_kelas_id']) ? (int)$_POST['target_kelas_id'] : null;
        $targetGuruId  = !empty($_POST['target_guru_id']) ? (int)$_POST['target_guru_id'] : null;
        $targetSiswaId = !empty($_POST['target_siswa_id']) ? (int)$_POST['target_siswa_id'] : null;
        $linkUrl    = trim($_POST['link_url'] ?? '');
        $senderNama = trim($_POST['sender_nama'] ?? 'Admin TU');

        if (empty($judul) || empty($pesan)) {
            $_SESSION['flash_error'] = 'Judul dan isi pesan notifikasi wajib diisi!';
            App::redirect(App::baseUrl('admin/notifikasi'));
            return;
        }

        try {
            $broadcastId = Notifikasi::broadcast([
                'judul'           => $judul,
                'pesan'           => $pesan,
                'tipe'            => $tipe,
                'target_role'     => $targetRole,
                'target_kelas_id' => $targetKelasId,
                'target_guru_id'  => $targetGuruId,
                'target_siswa_id' => $targetSiswaId,
                'link_url'        => $linkUrl ?: null,
                'sender_nama'     => $senderNama ?: 'Admin TU',
            ]);

            $_SESSION['flash_success'] = "Notifikasi & Pengumuman '{$judul}' berhasil disebarkan ke sasaran penerima!";
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Gagal menyebarkan notifikasi: " . $e->getMessage();
        }

        App::redirect(App::baseUrl('admin/notifikasi'));
    }

    /**
     * Hapus pengumuman broadcast
     */
    public function notifikasiDelete(int $id): void
    {
        if ($id <= 0) {
            App::redirect(App::baseUrl('admin/notifikasi'));
            return;
        }

        $deleted = Notifikasi::deleteBroadcast($id);
        if ($deleted) {
            $_SESSION['flash_success'] = 'Pengumuman / notifikasi berhasil dihapus dari sistem!';
        } else {
            $_SESSION['flash_error'] = 'Gagal menghapus notifikasi.';
        }

        App::redirect(App::baseUrl('admin/notifikasi'));
    }
}


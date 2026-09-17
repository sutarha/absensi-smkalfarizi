<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\NilaiSiswa;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\KonfigurasiSekolah;
use App\Models\TahunPelajaran;
use App\Models\BukuInduk;
use App\Models\JadwalPelajaran;

class NilaiController
{
    private function authAdminOrGuru(): array
    {
        $user = AuthController::checkAuth();
        if (!$user || !in_array($user['role'], ['admin', 'guru', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara'])) {
            App::redirect(App::baseUrl('login'));
            exit;
        }
        return $user;
    }

    public function index(): void
    {
        $user = $this->authAdminOrGuru();
        $config = KonfigurasiSekolah::get();
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $isGuruRoute = (strpos($uriPath, '/guru/') !== false);
        $isGuru = ($user['role'] === 'guru' || $isGuruRoute);
        $tapelList = TahunPelajaran::getAll();
        $activeTapel = TahunPelajaran::getActive();
        $isGenap = ($activeTapel && strtolower($activeTapel['semester']) === 'genap');

        if ($isGuru) {
            $guruId = (int)$user['id'];
            $kelasList = JadwalPelajaran::getGuruKelasList($guruId);

            if (empty($kelasList)) {
                $hasSchedule = false;
                $mapelList = [];
                $daftarNilai = [];
                $selectedKelas = null;
                $selectedMapel = null;
                $kelasId = 0;
                $mapelId = 0;
                $semesterKe = $isGenap ? 2 : 1;
                require __DIR__ . '/../Views/nilai/index.php';
                return;
            }

            $hasSchedule = true;
            $allowedKelasIds = array_column($kelasList, 'id');
            $requestedKelasId = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
            $kelasId = in_array($requestedKelasId, $allowedKelasIds) ? $requestedKelasId : (int)$kelasList[0]['id'];

            // Mapel hanya yang diajarkan oleh guru ini pada kelas terpilih
            $mapelList = JadwalPelajaran::getGuruMapelList($guruId, $kelasId);
            if (empty($mapelList)) {
                $mapelList = JadwalPelajaran::getGuruMapelList($guruId);
            }

            $allowedMapelIds = array_column($mapelList, 'id');
            $requestedMapelId = !empty($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;
            $mapelId = in_array($requestedMapelId, $allowedMapelIds) ? $requestedMapelId : (int)($mapelList[0]['id'] ?? 0);

            // Rekomendasi semester sesuai tingkat kelas
            $selectedKelas = Kelas::findById($kelasId);
            $tingkat = strtoupper(trim((string)($selectedKelas['tingkat'] ?? 'X')));
            $defaultSem = 1;
            if ($tingkat === 'X' || $tingkat === '10') {
                $defaultSem = $isGenap ? 2 : 1;
            } elseif ($tingkat === 'XI' || $tingkat === '11') {
                $defaultSem = $isGenap ? 4 : 3;
            } elseif ($tingkat === 'XII' || $tingkat === '12') {
                $defaultSem = $isGenap ? 6 : 5;
            }
            $semesterKe = !empty($_GET['semester_ke']) ? (int)$_GET['semester_ke'] : $defaultSem;

            $daftarNilai = ($mapelId > 0 && $kelasId > 0) ? NilaiSiswa::getNilaiKelasMapel($kelasId, $mapelId, $semesterKe) : [];
            $selectedMapel = MataPelajaran::findById($mapelId);
        } else {
            // Hak Akses Admin: Bisa melihat dan mengelola seluruh rombel & mata pelajaran
            $hasSchedule = true;
            $kelasList = Kelas::getAll();
            $kelasId = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : ($kelasList[0]['id'] ?? 1);
            $semesterKe = !empty($_GET['semester_ke']) ? (int)$_GET['semester_ke'] : 1;
            
            $selectedKelas = Kelas::findById($kelasId);
            $tingkatKelas = $selectedKelas['tingkat'] ?? null;
            $mapelList = MataPelajaran::getAll($tingkatKelas);
            if (empty($mapelList)) {
                $mapelList = MataPelajaran::getAll();
            }

            $mapelId = !empty($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : ($mapelList[0]['id'] ?? 1);
            $daftarNilai = NilaiSiswa::getNilaiKelasMapel($kelasId, $mapelId, $semesterKe);
            $selectedMapel = MataPelajaran::findById($mapelId);
        }

        require __DIR__ . '/../Views/nilai/index.php';
    }

    public function inputSemester(): void
    {
        $user = $this->authAdminOrGuru();
        $config = KonfigurasiSekolah::get();
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $isGuruRoute = (strpos($uriPath, '/guru/') !== false);
        $isGuru = ($user['role'] === 'guru' || $isGuruRoute);
        $activeTapel = TahunPelajaran::getActive();

        $kelasId = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
        $mapelId = !empty($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;
        $semesterKe = !empty($_GET['semester_ke']) ? (int)$_GET['semester_ke'] : 1;

        if ($isGuru) {
            $guruId = (int)$user['id'];

            // Otorisasi Ketat: Periksa apakah guru mengampu mata pelajaran ini di kelas tersebut
            $isAuthorized = JadwalPelajaran::isGuruTeaching($guruId, $kelasId, $mapelId);
            if (!$isAuthorized) {
                // Cari jadwal pertama guru sebagai fallback aman
                $guruSchedule = JadwalPelajaran::getDistinctKelasMapelByGuru($guruId);
                if (!empty($guruSchedule)) {
                    $fallbackKelas = $guruSchedule[0]['kelas_id'];
                    $fallbackMapel = $guruSchedule[0]['mapel_id'];
                    $_SESSION['flash_error'] = "Akses ditolak: Anda hanya dapat mengisi nilai untuk mata pelajaran dan kelas yang Anda ampu!";
                    App::redirect(App::baseUrl("guru/nilai/input?kelas_id={$fallbackKelas}&mapel_id={$fallbackMapel}&semester_ke={$semesterKe}"));
                    return;
                }

                $_SESSION['flash_error'] = "Akses ditolak: Anda belum memiliki jadwal mengajar aktif pada sistem!";
                App::redirect(App::baseUrl('guru/nilai'));
                return;
            }

            $kelasList = JadwalPelajaran::getGuruKelasList($guruId);
            $mapelList = JadwalPelajaran::getGuruMapelList($guruId, $kelasId);
        } else {
            $kelasList = Kelas::getAll();
            if ($kelasId === 0) $kelasId = $kelasList[0]['id'] ?? 1;
            $selectedKelas = Kelas::findById($kelasId);
            $tingkatKelas = $selectedKelas['tingkat'] ?? null;
            $mapelList = MataPelajaran::getAll($tingkatKelas);
            if (empty($mapelList)) {
                $mapelList = MataPelajaran::getAll();
            }
            if ($mapelId === 0) $mapelId = $mapelList[0]['id'] ?? 1;
        }

        $daftarNilai = NilaiSiswa::getNilaiKelasMapel($kelasId, $mapelId, $semesterKe);
        if (empty($selectedKelas)) $selectedKelas = Kelas::findById($kelasId);
        $selectedMapel = MataPelajaran::findById($mapelId);

        require __DIR__ . '/../Views/nilai/input_semester.php';
    }

    public function saveSemester(): void
    {
        $user = $this->authAdminOrGuru();

        $kelasId = (int)($_POST['kelas_id'] ?? 0);
        $mapelId = (int)($_POST['mapel_id'] ?? 0);
        $semesterKe = (int)($_POST['semester_ke'] ?? 1);
        $tapelId = !empty($_POST['tahun_pelajaran_id']) ? (int)$_POST['tahun_pelajaran_id'] : null;

        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $isGuruRoute = (strpos($uriPath, '/guru/') !== false);
        $isGuru = ($user['role'] === 'guru' || $isGuruRoute);

        // Security Guard: Blokir jika guru mencoba menyimpan nilai mapel yang bukan diampunya
        if ($isGuru) {
            $guruId = (int)$user['id'];
            $isAuthorized = JadwalPelajaran::isGuruTeaching($guruId, $kelasId, $mapelId);
            if (!$isAuthorized) {
                $_SESSION['flash_error'] = "Akses ditolak: Anda tidak memiliki wewenang untuk mengisi atau mengubah nilai pada mata pelajaran ini!";
                App::redirect(App::baseUrl('guru/nilai'));
                return;
            }
        }

        $nilaiList = $_POST['nilai'] ?? [];

        foreach ($nilaiList as $siswaId => $n) {
            $tugas = (float)($n['tugas'] ?? ($n['formatif'] ?? 0));
            $uh = (float)($n['uh'] ?? ($n['formatif'] ?? 0));
            $uts = (float)($n['uts'] ?? ($n['sumatif_materi'] ?? 0));
            $uas = (float)($n['uas'] ?? ($n['sas'] ?? 0));
            $capaian = trim($n['capaian'] ?? '');

            NilaiSiswa::saveNilaiKBM(
                (int)$siswaId,
                $mapelId,
                $semesterKe,
                $tugas,
                $uh,
                $uts,
                $uas,
                $tapelId,
                $capaian
            );
        }

        $_SESSION['flash_success'] = "Nilai mata pelajaran semester {$semesterKe} (Tugas, UH, UTS, UAS) berhasil disimpan dan dikalkulasi otomatis menjadi Nilai Rapor Jadi!";
        $redirectUrl = $isGuru 
            ? App::baseUrl("guru/nilai?kelas_id={$kelasId}&mapel_id={$mapelId}&semester_ke={$semesterKe}")
            : App::baseUrl("admin/nilai?kelas_id={$kelasId}&mapel_id={$mapelId}&semester_ke={$semesterKe}");
        App::redirect($redirectUrl);
    }

    public function transkrip(int $siswaId): void
    {
        $user = $this->authAdminOrGuru();
        $config = KonfigurasiSekolah::get();
        $siswa = BukuInduk::getBySiswaId($siswaId);

        if (!$siswa) {
            die("Data siswa tidak ditemukan.");
        }

        $transkripData = NilaiSiswa::getTranskripLengkap($siswaId);
        require __DIR__ . '/../Views/nilai/transkrip_cetak.php';
    }

    public function transkripIjazah(int $siswaId): void
    {
        $user = $this->authAdminOrGuru();
        $config = KonfigurasiSekolah::get();
        $siswa = BukuInduk::getBySiswaId($siswaId);

        if (!$siswa) {
            die("Data siswa tidak ditemukan.");
        }

        $ijazahData = NilaiSiswa::getTranskripIjazah($siswaId);
        require __DIR__ . '/../Views/nilai/transkrip_ijazah_cetak.php';
    }
}

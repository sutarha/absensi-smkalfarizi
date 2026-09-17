<?php
namespace App\Controllers;

use App\Config\App;
use App\Controllers\AuthController;
use App\Models\JadwalPelajaran;
use App\Models\LmsMateri;
use App\Models\LmsSubmission;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\LmsDiskusi;
use App\Models\LmsUjian;
use App\Models\KonfigurasiSekolah;

class GuruLmsController
{
    private array $user;
    private int $guruId;
    private LmsMateri $lmsMateri;
    private LmsSubmission $lmsSubmission;
    private LmsDiskusi $lmsDiskusi;
    private LmsUjian $lmsUjian;

    public function __construct()
    {
        $this->user = AuthController::requireRole(['guru', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara', 'admin']);
        $this->guruId = (int)$this->user['id'];
        $this->lmsMateri = new LmsMateri();
        $this->lmsSubmission = new LmsSubmission();
        $this->lmsDiskusi = new LmsDiskusi();
        $this->lmsUjian = new LmsUjian();
    }

    public function index()
    {
        $kelasMapel = JadwalPelajaran::getDistinctKelasMapelByGuru($this->guruId);
        $user = $this->user;
        $config = KonfigurasiSekolah::get();
        $activeNav = 'lms';
        
        $data = [
            'title' => 'LMS Guru',
            'user' => $user,
            'kelasMapel' => $kelasMapel,
            'config' => $config,
            'activeNav' => $activeNav
        ];
        require_once __DIR__ . '/../Views/guru/lms/index.php';
    }

    public function materiList($kelas_id, $mapel_id)
    {
        $kelas = Kelas::findById($kelas_id);
        $mapel = MataPelajaran::findById($mapel_id);
        
        if (!$kelas || !$mapel) {
            die('Data tidak valid');
        }

        $materi = $this->lmsMateri->getAllByKelasAndMapel($kelas_id, $mapel_id);

        $data = [
            'title' => 'Materi & Tugas: ' . $mapel['nama_mapel'] . ' - ' . $kelas['nama_kelas'],
            'user' => $this->user,
            'kelas' => $kelas,
            'kelasInfo' => $kelas,
            'mapel' => $mapel,
            'mapelInfo' => $mapel,
            'kelas_id' => $kelas_id,
            'mapel_id' => $mapel_id,
            'materiList' => $materi,
            'config' => KonfigurasiSekolah::get()
        ];
        extract($data);
        require_once __DIR__ . '/../Views/guru/lms/materi.php';
    }

    public function materiStore($kelas_id, $mapel_id)
    {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $tipe = strtolower($_POST['tipe'] ?? 'materi');
        if (!in_array($tipe, ['materi', 'tugas', 'pengumuman'])) {
            $tipe = 'materi';
        }
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;

        $filePath = null;
        $fileUpload = $_FILES['file_lampiran'] ?? $_FILES['file_materi'] ?? null;
        if ($fileUpload && $fileUpload['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/lms/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($fileUpload['name'], PATHINFO_EXTENSION));
            $filename = uniqid('lms_') . '.' . $ext;
            if (move_uploaded_file($fileUpload['tmp_name'], $uploadDir . $filename)) {
                $filePath = 'uploads/lms/' . $filename;
            }
        }

        $this->lmsMateri->create([
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'file_path' => $filePath,
            'tipe' => $tipe,
            'mapel_id' => $mapel_id,
            'kelas_id' => $kelas_id,
            'guru_id' => $this->guruId,
            'deadline' => $deadline
        ]);

        $_SESSION['flash_success'] = ucfirst($tipe) . ' berhasil ditambahkan!';
        App::redirect(App::baseUrl("guru/lms/materi/{$kelas_id}/{$mapel_id}"));
    }

    public function materiDelete($materi_id, $kelas_id, $mapel_id)
    {
        $materi = $this->lmsMateri->getById($materi_id);
        if ($materi && $materi['guru_id'] == $this->guruId) {
            if ($materi['file_path']) {
                $filePath = __DIR__ . '/../../public/' . $materi['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $this->lmsMateri->delete($materi_id, $this->guruId);
            $_SESSION['flash_success'] = 'Konten berhasil dihapus.';
        }
        App::redirect(App::baseUrl("guru/lms/materi/{$kelas_id}/{$mapel_id}"));
    }

    public function submissionList($materi_id)
    {
        $materi = $this->lmsMateri->getById($materi_id);
        if (!$materi || $materi['guru_id'] != $this->guruId) {
            die('Data tidak valid atau bukan milik anda');
        }

        $submissions = $this->lmsSubmission->getByMateri($materi_id);

        $data = [
            'title' => 'Review Tugas: ' . $materi['judul'],
            'user' => $this->user,
            'materi' => $materi,
            'submissions' => $submissions
        ];
        require_once __DIR__ . '/../Views/guru/lms/submission.php';
    }

    public function submissionNilai($submission_id, $materi_id)
    {
        $nilai = $_POST['nilai'] ?? 0;
        $catatan = $_POST['catatan'] ?? '';

        $this->lmsSubmission->nilaiSubmission($submission_id, $nilai, $catatan);
        
        $_SESSION['flash_success'] = 'Nilai tugas berhasil disimpan!';
        App::redirect(App::baseUrl("guru/lms/submission/{$materi_id}"));
    }

    // ==========================================
    // FORUM DISKUSI
    // ==========================================

    public function diskusiIndex($kelas_id, $mapel_id)
    {
        $kelas = Kelas::findById($kelas_id);
        $mapel = MataPelajaran::findById($mapel_id);
        
        if (!$kelas || !$mapel) {
            die('Data tidak valid');
        }

        $diskusiList = $this->lmsDiskusi->getByMapelAndKelas($mapel_id, $kelas_id);

        $data = [
            'kelasInfo' => $kelas,
            'mapelInfo' => $mapel,
            'diskusiList' => $diskusiList,
            'user' => $this->user,
            'config' => KonfigurasiSekolah::get()
        ];
        
        extract($data);
        require_once __DIR__ . '/../Views/guru/lms/diskusi.php';
    }

    public function diskusiStore($kelas_id, $mapel_id)
    {
        $pesan = trim($_POST['pesan'] ?? '');
        if (!empty($pesan)) {
            $this->lmsDiskusi->create([
                'kelas_id' => $kelas_id,
                'mapel_id' => $mapel_id,
                'user_id' => $this->guruId,
                'user_type' => 'guru',
                'pesan' => $pesan
            ]);
        }

        // Check if AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            App::json(['success' => true]);
        }

        App::redirect(App::baseUrl("guru/lms/diskusi/{$kelas_id}/{$mapel_id}"));
    }

    public function diskusiMessages($kelas_id, $mapel_id)
    {
        $list = $this->lmsDiskusi->getByMapelAndKelas($mapel_id, $kelas_id);
        App::json([
            'success' => true,
            'data' => $list,
            'current_user_id' => $this->guruId
        ]);
    }

    // ==========================================
    // TES / UJIAN (PILIHAN GANDA & ESSAY)
    // ==========================================

    public function ujianIndex($kelas_id, $mapel_id)
    {
        $kelas = Kelas::findById($kelas_id);
        $mapel = MataPelajaran::findById($mapel_id);
        if (!$kelas || !$mapel) {
            die('Data tidak valid');
        }

        $ujianList = $this->lmsUjian->getByKelasAndMapel($kelas_id, $mapel_id);

        $data = [
            'title' => 'Tes / CBT: ' . $mapel['nama_mapel'] . ' - ' . $kelas['nama_kelas'],
            'kelasInfo' => $kelas,
            'mapelInfo' => $mapel,
            'ujianList' => $ujianList,
            'user' => $this->user,
            'config' => KonfigurasiSekolah::get()
        ];
        extract($data);
        require_once __DIR__ . '/../Views/guru/lms/ujian_index.php';
    }

    public function ujianStore($kelas_id, $mapel_id)
    {
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $durasi_menit = max(5, (int)($_POST['durasi_menit'] ?? 60));
        $kkm = max(0, min(100, (float)($_POST['kkm'] ?? 75.0)));
        $acak_soal = isset($_POST['acak_soal']) ? 1 : 0;

        if (empty($judul)) {
            $_SESSION['flash_error'] = 'Judul tes/ujian wajib diisi!';
            App::redirect(App::baseUrl("guru/lms/ujian/{$kelas_id}/{$mapel_id}"));
        }

        $ujianId = $this->lmsUjian->createUjian([
            'kelas_id' => $kelas_id,
            'mapel_id' => $mapel_id,
            'guru_id' => $this->guruId,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'durasi_menit' => $durasi_menit,
            'kkm' => $kkm,
            'acak_soal' => $acak_soal,
            'is_aktif' => 1
        ]);

        $_SESSION['flash_success'] = "Ujian '$judul' berhasil dibuat! Silakan tambahkan butir soal.";
        App::redirect(App::baseUrl("guru/lms/ujian/soal/{$ujianId}"));
    }

    public function ujianDelete($ujian_id, $kelas_id, $mapel_id)
    {
        $this->lmsUjian->deleteUjian($ujian_id, $this->guruId);
        $_SESSION['flash_success'] = 'Ujian berhasil dihapus.';
        App::redirect(App::baseUrl("guru/lms/ujian/{$kelas_id}/{$mapel_id}"));
    }

    public function ujianToggle($ujian_id, $kelas_id, $mapel_id)
    {
        $this->lmsUjian->toggleAktif($ujian_id, $this->guruId);
        $_SESSION['flash_success'] = 'Status aktif ujian berhasil diperbarui.';
        App::redirect(App::baseUrl("guru/lms/ujian/{$kelas_id}/{$mapel_id}"));
    }

    public function ujianSoal($ujian_id)
    {
        $ujian = $this->lmsUjian->getById($ujian_id);
        if (!$ujian || $ujian['guru_id'] != $this->guruId) {
            die('Ujian tidak ditemukan atau bukan milik Anda.');
        }

        $soalList = $this->lmsUjian->getSoalByUjian($ujian_id);

        $data = [
            'title' => 'Kelola Soal: ' . $ujian['judul'],
            'ujian' => $ujian,
            'soalList' => $soalList,
            'user' => $this->user,
            'config' => KonfigurasiSekolah::get()
        ];
        extract($data);
        require_once __DIR__ . '/../Views/guru/lms/ujian_soal.php';
    }

    public function soalStore($ujian_id)
    {
        $ujian = $this->lmsUjian->getById($ujian_id);
        if (!$ujian || $ujian['guru_id'] != $this->guruId) {
            die('Akses ditolak.');
        }

        $tipe_soal = $_POST['tipe_soal'] === 'essay' ? 'essay' : 'pilihan_ganda';
        $pertanyaan = trim($_POST['pertanyaan'] ?? '');
        $bobot_nilai = max(0.5, (float)($_POST['bobot_nilai'] ?? ($tipe_soal === 'essay' ? 5.0 : 1.0)));

        if (empty($pertanyaan)) {
            $_SESSION['flash_error'] = 'Pertanyaan tidak boleh kosong!';
            App::redirect(App::baseUrl("guru/lms/ujian/soal/{$ujian_id}"));
        }

        // Upload gambar jika ada
        $gambarPath = null;
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/lms/soal/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $filename = uniqid('soal_') . '.' . $ext;
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . $filename)) {
                $gambarPath = 'uploads/lms/soal/' . $filename;
            }
        }

        if ($tipe_soal === 'pilihan_ganda') {
            $kunci_jawaban = strtoupper(trim($_POST['kunci_jawaban'] ?? ''));
            if (!in_array($kunci_jawaban, ['A', 'B', 'C', 'D', 'E'])) {
                $_SESSION['flash_error'] = 'Pilih kunci jawaban yang valid (A, B, C, D, atau E)!';
                App::redirect(App::baseUrl("guru/lms/ujian/soal/{$ujian_id}"));
            }

            $this->lmsUjian->createSoal([
                'ujian_id' => $ujian_id,
                'tipe_soal' => 'pilihan_ganda',
                'pertanyaan' => $pertanyaan,
                'gambar' => $gambarPath,
                'pilihan_a' => trim($_POST['pilihan_a'] ?? ''),
                'pilihan_b' => trim($_POST['pilihan_b'] ?? ''),
                'pilihan_c' => trim($_POST['pilihan_c'] ?? ''),
                'pilihan_d' => trim($_POST['pilihan_d'] ?? ''),
                'pilihan_e' => trim($_POST['pilihan_e'] ?? ''),
                'kunci_jawaban' => $kunci_jawaban,
                'bobot_nilai' => $bobot_nilai
            ]);
        } else {
            // ESSAY
            $this->lmsUjian->createSoal([
                'ujian_id' => $ujian_id,
                'tipe_soal' => 'essay',
                'pertanyaan' => $pertanyaan,
                'gambar' => $gambarPath,
                'pilihan_a' => null,
                'pilihan_b' => null,
                'pilihan_c' => null,
                'pilihan_d' => null,
                'pilihan_e' => null,
                'kunci_jawaban' => null,
                'bobot_nilai' => $bobot_nilai
            ]);
        }

        $_SESSION['flash_success'] = 'Butir soal (' . ($tipe_soal === 'essay' ? 'Essay' : 'Pilihan Ganda') . ') berhasil ditambahkan!';
        App::redirect(App::baseUrl("guru/lms/ujian/soal/{$ujian_id}"));
    }

    public function soalDelete($soal_id, $ujian_id)
    {
        $ujian = $this->lmsUjian->getById($ujian_id);
        if (!$ujian || $ujian['guru_id'] != $this->guruId) {
            die('Akses ditolak.');
        }

        $this->lmsUjian->deleteSoal($soal_id);
        $_SESSION['flash_success'] = 'Butir soal berhasil dihapus.';
        App::redirect(App::baseUrl("guru/lms/ujian/soal/{$ujian_id}"));
    }

    public function ujianHasil($ujian_id)
    {
        $ujian = $this->lmsUjian->getById($ujian_id);
        if (!$ujian || $ujian['guru_id'] != $this->guruId) {
            die('Akses ditolak.');
        }

        $hasilSiswa = $this->lmsUjian->getHasilSiswaByUjian($ujian_id);

        $data = [
            'title' => 'Hasil Ujian Siswa: ' . $ujian['judul'],
            'ujian' => $ujian,
            'hasilSiswa' => $hasilSiswa,
            'user' => $this->user,
            'config' => KonfigurasiSekolah::get()
        ];
        extract($data);
        require_once __DIR__ . '/../Views/guru/lms/ujian_hasil.php';
    }

    public function ujianDetailSiswa($ujian_siswa_id)
    {
        $detail = $this->lmsUjian->getUjianSiswaDetail($ujian_siswa_id);
        if (!$detail) {
            die('Data pengerjaan siswa tidak ditemukan.');
        }

        $soalJawaban = $this->lmsUjian->getJawabanSiswaWithSoal($ujian_siswa_id);

        $data = [
            'title' => 'Koreksi & Detail Ujian: ' . $detail['nama_siswa'],
            'detail' => $detail,
            'soalJawaban' => $soalJawaban,
            'user' => $this->user,
            'config' => KonfigurasiSekolah::get()
        ];
        extract($data);
        require_once __DIR__ . '/../Views/guru/lms/ujian_detail_siswa.php';
    }

    public function nilaiEssayStore($ujian_siswa_id)
    {
        $soal_id = (int)($_POST['soal_id'] ?? 0);
        $nilai_butir = max(0, (float)($_POST['nilai_butir'] ?? 0));
        $catatan = trim($_POST['catatan_guru'] ?? '');

        $this->lmsUjian->beriNilaiEssay($ujian_siswa_id, $soal_id, $nilai_butir, $catatan);
        
        $_SESSION['flash_success'] = 'Nilai butir essay berhasil disimpan!';
        App::redirect(App::baseUrl("guru/lms/ujian/detail-siswa/{$ujian_siswa_id}"));
    }
}

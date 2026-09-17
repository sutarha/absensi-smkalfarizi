<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\BukuInduk;
use App\Models\Kelas;
use App\Models\KonfigurasiSekolah;
use App\Models\TahunPelajaran;
use App\Models\RiwayatKelas;
use App\Models\NilaiSiswa;
use App\Models\MataPelajaran;
use App\Models\BukuIndukCatatan;
use App\Helpers\DapodikHelper;

class BukuIndukController
{
    private function authAdmin(): array
    {
        $user = AuthController::checkAuth();
        if (!$user || !in_array($user['role'], ['admin', 'wakasek_kurikulum', 'kepala_sekolah'])) {
            App::redirect(App::baseUrl('login'));
            exit;
        }
        return $user;
    }

    public function index(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $kelasList = Kelas::getAll();

        $kelasId = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : null;
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $bukuList = BukuInduk::getAllWithSiswa($kelasId, $search, $limit, $offset);
        $totalRows = BukuInduk::countAllWithSiswa($kelasId, $search);
        $totalPages = ceil($totalRows / $limit);

        require __DIR__ . '/../Views/buku_induk/index.php';
    }

    public function alumniIndex(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();

        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $bukuList = BukuInduk::getAllAlumni($search, $limit, $offset);
        $totalRows = BukuInduk::countAllAlumni($search);
        $totalPages = ceil($totalRows / $limit);

        require __DIR__ . '/../Views/buku_induk/alumni.php';
    }

    public function detail(int $siswaId): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $siswa = BukuInduk::getBySiswaId($siswaId);

        if (!$siswa) {
            $_SESSION['flash_error'] = "Data siswa tidak ditemukan.";
            App::redirect(App::baseUrl('admin/buku-induk'));
            return;
        }

        $riwayatKelas = RiwayatKelas::getBySiswa($siswaId);
        $riwayatNilai = NilaiSiswa::getRiwayatNilaiSiswaSemuaSemester($siswaId);
        $catatanKhusus = BukuIndukCatatan::getBySiswaId($siswaId);
        require __DIR__ . '/../Views/buku_induk/detail.php';
    }

    public function edit(int $siswaId): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $siswa = BukuInduk::getBySiswaId($siswaId);
        $kelasList = Kelas::getAll();

        if (!$siswa) {
            $_SESSION['flash_error'] = "Data siswa tidak ditemukan.";
            App::redirect(App::baseUrl('admin/buku-induk'));
            return;
        }

        require __DIR__ . '/../Views/buku_induk/edit.php';
    }

    public function update(int $siswaId): void
    {
        $user = $this->authAdmin();

        $data = [
            'nis' => trim($_POST['nis'] ?? ''),
            'nik' => trim($_POST['nik'] ?? ''),
            'no_kk' => trim($_POST['no_kk'] ?? ''),
            'no_akta_lahir' => trim($_POST['no_akta_lahir'] ?? ''),
            'tempat_lahir' => trim($_POST['tempat_lahir'] ?? ''),
            'tanggal_lahir' => !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null,
            'agama' => trim($_POST['agama'] ?? 'Islam'),
            'kewarganegaraan' => trim($_POST['kewarganegaraan'] ?? 'WNI'),
            'anak_ke' => (int)($_POST['anak_ke'] ?? 1),
            'jumlah_saudara' => (int)($_POST['jumlah_saudara'] ?? 0),
            'alamat_jalan' => trim($_POST['alamat_jalan'] ?? ''),
            'rt' => trim($_POST['rt'] ?? ''),
            'rw' => trim($_POST['rw'] ?? ''),
            'dusun_kelurahan' => trim($_POST['dusun_kelurahan'] ?? ''),
            'kecamatan' => trim($_POST['kecamatan'] ?? ''),
            'kabupaten_kota' => trim($_POST['kabupaten_kota'] ?? ''),
            'provinsi' => trim($_POST['provinsi'] ?? ''),
            'kode_pos' => trim($_POST['kode_pos'] ?? ''),
            'tinggal_bersama' => trim($_POST['tinggal_bersama'] ?? 'Orang Tua'),
            'transportasi' => trim($_POST['transportasi'] ?? 'Sepeda Motor'),
            'nama_ayah' => trim($_POST['nama_ayah'] ?? ''),
            'nik_ayah' => trim($_POST['nik_ayah'] ?? ''),
            'tahun_lahir_ayah' => trim($_POST['tahun_lahir_ayah'] ?? ''),
            'pendidikan_ayah' => trim($_POST['pendidikan_ayah'] ?? ''),
            'pekerjaan_ayah' => trim($_POST['pekerjaan_ayah'] ?? ''),
            'penghasilan_ayah' => trim($_POST['penghasilan_ayah'] ?? ''),
            'nama_ibu' => trim($_POST['nama_ibu'] ?? ''),
            'nik_ibu' => trim($_POST['nik_ibu'] ?? ''),
            'tahun_lahir_ibu' => trim($_POST['tahun_lahir_ibu'] ?? ''),
            'pendidikan_ibu' => trim($_POST['pendidikan_ibu'] ?? ''),
            'pekerjaan_ibu' => trim($_POST['pekerjaan_ibu'] ?? ''),
            'penghasilan_ibu' => trim($_POST['penghasilan_ibu'] ?? ''),
            'nama_wali' => trim($_POST['nama_wali'] ?? ''),
            'no_hp_ortu' => trim($_POST['no_hp_ortu'] ?? ''),
            'sekolah_asal' => trim($_POST['sekolah_asal'] ?? ''),
            'no_ijazah_smp' => trim($_POST['no_ijazah_smp'] ?? ''),
            'no_skhun_smp' => trim($_POST['no_skhun_smp'] ?? ''),
            'tanggal_masuk' => !empty($_POST['tanggal_masuk']) ? $_POST['tanggal_masuk'] : null,
            'status_siswa' => $_POST['status_siswa'] ?? 'AKTIF'
        ];

        BukuInduk::saveOrUpdate($siswaId, $data);
        $_SESSION['flash_success'] = "Data Buku Induk siswa berhasil diperbarui!";
        App::redirect(App::baseUrl("admin/buku-induk/detail/{$siswaId}"));
    }

    public function importView(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        require __DIR__ . '/../Views/buku_induk/import.php';
    }

    public function importProcess(): void
    {
        $user = $this->authAdmin();

        if (empty($_FILES['file_dapodik']['tmp_name'])) {
            $_SESSION['flash_error'] = "Silakan pilih berkas Excel (.xlsx / .xls) atau CSV Dapodik terlebih dahulu.";
            App::redirect(App::baseUrl('admin/buku-induk/import'));
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
            App::redirect(App::baseUrl('admin/buku-induk/import'));
            return;
        }

        try {
            $rows = DapodikHelper::parseFile($file, $originalName);
            if (empty($rows)) {
                $_SESSION['flash_error'] = "Tidak ada baris data siswa valid yang dapat dibaca dari berkas Excel tersebut.";
                App::redirect(App::baseUrl('admin/buku-induk/import'));
                return;
            }

            $activeTapel = TahunPelajaran::getActive();
            $tapelId = $activeTapel ? (int)$activeTapel['id'] : null;

            $stats = BukuInduk::bulkUpsertDapodik($rows, $tapelId);

            $msg = "Import Data Pokok Siswa Dapodik Berhasil! Ditambah: {$stats['inserted']} siswa baru, Diperbarui: {$stats['updated']} siswa.";
            if ($stats['failed'] > 0) {
                $msg .= " (Gagal/Dilewati: {$stats['failed']}).";
            }
            $_SESSION['flash_success'] = $msg;

        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = "Gagal memproses berkas Excel Dapodik: " . $e->getMessage();
            App::redirect(App::baseUrl('admin/buku-induk/import'));
            return;
        }

        App::redirect(App::baseUrl('admin/buku-induk'));
    }

    public function downloadTemplate(): void
    {
        $this->authAdmin();
        $format = strtolower($_GET['format'] ?? 'xlsx');

        if ($format === 'csv') {
            $csv = DapodikHelper::getCsvTemplateContent();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="template_data_pokok_siswa_dapodik.csv"');
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            echo $csv;
            exit;
        }

        // Default: Format Excel Asli (.xlsx)
        $xlsx = DapodikHelper::getExcelTemplateContent();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_data_pokok_siswa_dapodik.xlsx"');
        header('Content-Length: ' . strlen($xlsx));
        header('Cache-Control: max-age=0');
        echo $xlsx;
        exit;
    }

    public function cetakLembar(int $siswaId): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $siswa = BukuInduk::getBySiswaId($siswaId);

        if (!$siswa) {
            die("Data siswa tidak ditemukan.");
        }

        $riwayatKelas = RiwayatKelas::getBySiswa($siswaId);
        $riwayatNilai = NilaiSiswa::getRiwayatNilaiSiswaSemuaSemester($siswaId);
        require __DIR__ . '/../Views/buku_induk/lembar_cetak.php';
    }

    public function inputNilaiManualView(int $siswaId): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();
        $siswa = BukuInduk::getBySiswaId($siswaId);

        if (!$siswa) {
            $_SESSION['flash_error'] = "Data siswa tidak ditemukan.";
            App::redirect(App::baseUrl('admin/buku-induk'));
            return;
        }

        $semesterKe = !empty($_GET['semester_ke']) ? max(1, min(6, (int)$_GET['semester_ke'])) : 1;
        $mapelList = MataPelajaran::getAll();
        $existingNilai = NilaiSiswa::getNilaiBySiswaSemester($siswaId, $semesterKe);

        $nilaiMap = [];
        foreach ($existingNilai as $en) {
            $nilaiMap[$en['mapel_id']] = $en;
        }

        require __DIR__ . '/../Views/buku_induk/input_nilai_manual.php';
    }

    public function saveNilaiManual(int $siswaId): void
    {
        $this->authAdmin();

        $semesterKe = (int)($_POST['semester_ke'] ?? 1);
        $tapelId = !empty($_POST['tahun_pelajaran_id']) ? (int)$_POST['tahun_pelajaran_id'] : null;
        $nilaiMapel = $_POST['nilai_mapel'] ?? [];

        if (empty($nilaiMapel)) {
            $_SESSION['flash_error'] = "Tidak ada nilai yang dikirimkan.";
            App::redirect(App::baseUrl("admin/buku-induk/detail/{$siswaId}"));
            return;
        }

        $savedCount = NilaiSiswa::saveNilaiManualRaport($siswaId, $semesterKe, $nilaiMapel, $tapelId);

        $_SESSION['flash_success'] = "Berhasil menyimpan {$savedCount} nilai raport semester {$semesterKe} untuk siswa ini!";
        App::redirect(App::baseUrl("admin/buku-induk/input-nilai-manual/{$siswaId}?semester_ke={$semesterKe}"));
    }

    public function inputKolektifView(): void
    {
        $user = $this->authAdmin();
        $kelasId = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : null;
        $semesterKe = !empty($_GET['semester_ke']) ? max(1, min(6, (int)$_GET['semester_ke'])) : 1;
        
        $kelasList = \App\Models\Kelas::getAll();
        $semuaMapel = \App\Models\MataPelajaran::getAll();
        
        $isPastSemester = false;
        if ($kelasId) {
            $kelasInfo = \App\Models\Kelas::findById($kelasId);
            if ($kelasInfo) {
                $baseSemester = 1;
                if ($kelasInfo['tingkat'] === 'XI') $baseSemester = 3;
                if ($kelasInfo['tingkat'] === 'XII') $baseSemester = 5;
                
                $activeTapel = \App\Models\TahunPelajaran::getActive();
                if ($activeTapel && strtolower($activeTapel['semester']) === 'genap') {
                    $baseSemester += 1;
                }
                
                if ($semesterKe < $baseSemester) {
                    $isPastSemester = true;
                }
            }
        }
        
        $siswaList = [];
        $selectedMapel = [];
        $nilaiMatrix = [];

        if ($kelasId) {
            $siswaList = \App\Models\Siswa::getAll($kelasId);
            
            // Ambil mapel yang di-plot untuk kelas ini
            $plottedMapel = \App\Models\KelasMapel::getByKelas($kelasId);
            $selectedMapel = [];
            $mapelIds = [];
            foreach ($plottedMapel as $pm) {
                // Konversi format KelasMapel ke format MataPelajaran standar untuk view
                $selectedMapel[] = [
                    'id' => $pm['mapel_id'],
                    'nama_mapel' => $pm['nama_mapel'],
                    'kode_mapel' => $pm['kode_mapel'],
                    'kelompok' => $pm['kelompok']
                ];
                $mapelIds[] = $pm['mapel_id'];
            }
            
            // Tambahkan mapel manual/tambahan jika ada (untuk mapel masa lampau)
            $mapelTambahanIds = !empty($_GET['mapel_tambahan']) ? (array)$_GET['mapel_tambahan'] : [];
            if (!empty($mapelTambahanIds)) {
                $semuaMapel = \App\Models\MataPelajaran::getAll();
                foreach ($semuaMapel as $m) {
                    if (in_array($m['id'], $mapelTambahanIds) && !in_array($m['id'], $mapelIds)) {
                        $selectedMapel[] = $m;
                        $mapelIds[] = $m['id'];
                    }
                }
            }

            // Fetch existing nilai untuk siswa-siswa di kelas ini pada semester ini
            $db = \App\Config\Database::getConnection();
            $sIds = array_column($siswaList, 'id');
            if (!empty($sIds)) {
                $placeholders = implode(',', array_fill(0, count($sIds), '?'));
                $stmt = $db->prepare("SELECT * FROM `nilai_siswa` WHERE `semester_ke` = ? AND `siswa_id` IN ($placeholders)");
                $params = array_merge([$semesterKe], $sIds);
                $stmt->execute($params);
                $rows = $stmt->fetchAll();

                foreach ($rows as $r) {
                    $nilaiMatrix[$r['siswa_id']][$r['mapel_id']] = $r;
                }
            }
        }

        require __DIR__ . '/../Views/buku_induk/input_kolektif.php';
    }

    public function inputKolektifSave(): void
    {
        $this->authAdmin();
        $kelasId = (int)($_POST['kelas_id'] ?? 0);
        $semesterKe = (int)($_POST['semester_ke'] ?? 0);
        $nilaiData = $_POST['nilai'] ?? []; // Format: $nilaiData[siswa_id][mapel_id] = nilai

        if (!$kelasId || !$semesterKe || empty($nilaiData)) {
            $_SESSION['flash_error'] = "Data tidak valid.";
            App::redirect(App::baseUrl('admin/buku-induk/input-kolektif'));
            return;
        }

        $activeTapel = \App\Models\TahunPelajaran::getActive();
        $tapelId = $activeTapel ? (int)$activeTapel['id'] : null;

        $db = \App\Config\Database::getConnection();
        $db->beginTransaction();

        try {
            $totalSaved = 0;
            $stmt = $db->prepare("
                INSERT INTO `nilai_siswa` 
                (`siswa_id`, `mapel_id`, `semester_ke`, `tahun_pelajaran_id`, 
                 `nilai_tugas`, `nilai_uh`, `nilai_uts`, `nilai_uas`,
                 `nilai_formatif`, `nilai_sumatif_materi`, `nilai_sas`, `nilai_akhir`, `predikat`, `capaian_kompetensi`, `is_manual`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE 
                    `tahun_pelajaran_id` = VALUES(`tahun_pelajaran_id`),
                    `nilai_akhir` = VALUES(`nilai_akhir`),
                    `predikat` = VALUES(`predikat`),
                    `capaian_kompetensi` = VALUES(`capaian_kompetensi`),
                    `is_manual` = 1
            ");

            foreach ($nilaiData as $siswaId => $mapelData) {
                foreach ($mapelData as $mapelId => $nilaiStr) {
                    $na = (float)$nilaiStr;
                    if ($na <= 0) continue;

                    $info = \App\Models\NilaiSiswa::getPredikatDanDeskripsi($na);
                    
                    $stmt->execute([
                        $siswaId, (int)$mapelId, $semesterKe, $tapelId,
                        $na, $na, $na, $na,
                        $na, $na, $na, $na, $info['predikat'], $info['capaian_kompetensi']
                    ]);
                    $totalSaved++;
                }
            }

            $db->commit();
            $_SESSION['flash_success'] = "Berhasil menyimpan $totalSaved data nilai kolektif secara manual.";
        } catch (\Throwable $e) {
            $db->rollBack();
            $_SESSION['flash_error'] = "Gagal menyimpan nilai kolektif: " . $e->getMessage();
        }

        // Redirect back with selected mapel_ids
        $mapelQuery = "";
        if (!empty($_POST['mapel_ids'])) {
            foreach ($_POST['mapel_ids'] as $mid) {
                $mapelQuery .= "&mapel_ids[]=" . $mid;
            }
        }
        App::redirect(App::baseUrl("admin/buku-induk/input-kolektif?kelas_id={$kelasId}&semester_ke={$semesterKe}{$mapelQuery}"));
    }

    public function catatanSave(): void
    {
        $this->authAdmin();
        $siswaId = (int)($_POST['siswa_id'] ?? 0);
        $id = (int)($_POST['id'] ?? 0);

        if (!$siswaId) {
            App::redirect(App::baseUrl('admin/buku-induk'));
            return;
        }

        $data = [
            'siswa_id' => $siswaId,
            'jenis' => $_POST['jenis'] ?? 'PKL',
            'nama_kegiatan' => $_POST['nama_kegiatan'] ?? '',
            'mitra_instansi' => $_POST['mitra_instansi'] ?? '',
            'nilai_predikat' => $_POST['nilai_predikat'] ?? '',
            'keterangan' => $_POST['keterangan'] ?? '',
            'semester_ke' => !empty($_POST['semester_ke']) ? (int)$_POST['semester_ke'] : null,
        ];

        if ($id > 0) {
            BukuIndukCatatan::update($id, $data);
            $_SESSION['flash_success'] = "Catatan berhasil diperbarui.";
        } else {
            BukuIndukCatatan::create($data);
            $_SESSION['flash_success'] = "Catatan berhasil ditambahkan.";
        }

        App::redirect(App::baseUrl("admin/buku-induk/detail/{$siswaId}"));
    }

    public function catatanDelete(int $id): void
    {
        $this->authAdmin();
        $siswaId = (int)($_POST['siswa_id'] ?? 0);
        
        if ($id > 0) {
            BukuIndukCatatan::delete($id);
            $_SESSION['flash_success'] = "Catatan berhasil dihapus.";
        }

        if ($siswaId) {
            App::redirect(App::baseUrl("admin/buku-induk/detail/{$siswaId}"));
        } else {
            App::redirect(App::baseUrl('admin/buku-induk'));
        }
    }
}

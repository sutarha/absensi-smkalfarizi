<?php
namespace App\Controllers\Api;

use App\Config\App;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\NilaiSiswa;
use App\Models\JadwalPelajaran;
use App\Models\TahunPelajaran;

/**
 * NilaiApiController - Penilaian Siswa KBM oleh Guru (Tugas, UH, UTS, UAS)
 * 
 * Endpoints:
 * - GET  /api/v1/guru/nilai
 * - POST /api/v1/guru/nilai/save
 * 
 * ROLLBACK: Hapus file ini jika ingin membatalkan perubahan
 */
class NilaiApiController extends ApiController
{
    /**
     * GET /api/v1/guru/nilai
     * Query params:
     * - kelas_id (opsional)
     * - mapel_id (opsional)
     * - semester_ke (opsional)
     */
    public function index(): void
    {
        self::requireMethod('GET');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $guruId  = (int)$payload->uid;
        $isAdmin = ($payload->role === 'admin');

        $activeTapel = TahunPelajaran::getActive();
        $tapelList = TahunPelajaran::getAll();
        $isGenap = ($activeTapel && strtolower($activeTapel['semester'] ?? '') === 'genap');

        if ($isAdmin) {
            $kelasList = Kelas::getAll();
        } else {
            $kelasList = JadwalPelajaran::getGuruKelasList($guruId);
        }

        if (empty($kelasList)) {
            self::json([
                'success' => true,
                'data' => [
                    'kelas_list' => [],
                    'mapel_list' => [],
                    'selected' => null,
                    'daftar_nilai' => []
                ],
                'message' => 'Belum ada jadwal mengajar atau kelas yang ditugaskan.'
            ]);
        }

        // Tentukan kelas terpilih
        $allowedKelasIds = array_column($kelasList, 'id');
        $reqKelasId = !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : 0;
        $kelasId = in_array($reqKelasId, $allowedKelasIds) ? $reqKelasId : (int)$kelasList[0]['id'];
        $selectedKelas = Kelas::findById($kelasId);

        // Tentukan mapel yang diajar
        if ($isAdmin) {
            $tingkat = $selectedKelas['tingkat'] ?? null;
            $mapelList = MataPelajaran::getAll($tingkat) ?: MataPelajaran::getAll();
        } else {
            $mapelList = JadwalPelajaran::getGuruMapelList($guruId, $kelasId) ?: JadwalPelajaran::getGuruMapelList($guruId);
        }

        $allowedMapelIds = array_column($mapelList, 'id');
        $reqMapelId = !empty($_GET['mapel_id']) ? (int)$_GET['mapel_id'] : 0;
        $mapelId = in_array($reqMapelId, $allowedMapelIds) ? $reqMapelId : (int)($mapelList[0]['id'] ?? 0);
        $selectedMapel = $mapelId > 0 ? MataPelajaran::findById($mapelId) : null;

        // Rekomendasi semester berdasarkan tingkat kelas
        $tingkatStr = strtoupper(trim((string)($selectedKelas['tingkat'] ?? 'X')));
        $defaultSem = 1;
        if ($tingkatStr === 'X' || $tingkatStr === '10') {
            $defaultSem = $isGenap ? 2 : 1;
        } elseif ($tingkatStr === 'XI' || $tingkatStr === '11') {
            $defaultSem = $isGenap ? 4 : 3;
        } elseif ($tingkatStr === 'XII' || $tingkatStr === '12') {
            $defaultSem = $isGenap ? 6 : 5;
        }
        $semesterKe = !empty($_GET['semester_ke']) ? (int)$_GET['semester_ke'] : $defaultSem;

        // Ambil daftar siswa dan nilai
        $daftarNilaiRaw = ($mapelId > 0 && $kelasId > 0) ? NilaiSiswa::getNilaiKelasMapel($kelasId, $mapelId, $semesterKe) : [];

        // Format data siswa agar mudah dikonsumsi Android
        $daftarNilai = [];
        foreach ($daftarNilaiRaw as $row) {
            $tugas = (float)($row['nilai_tugas'] ?? 0);
            $uh    = (float)($row['nilai_uh'] ?? 0);
            $uts   = (float)($row['nilai_uts'] ?? 0);
            $uas   = (float)($row['nilai_uas'] ?? 0);
            
            // Hitung ulang atau ambil nilai akhir
            $calc = NilaiSiswa::hitungNilaiRaporKBM($tugas, $uh, $uts, $uas);

            $daftarNilai[] = [
                'siswa_id'           => (int)$row['siswa_id'],
                'nisn'               => (string)($row['nisn'] ?? ''),
                'nama_siswa'         => (string)$row['nama_siswa'],
                'nilai_tugas'        => $tugas,
                'nilai_uh'           => $uh,
                'nilai_uts'          => $uts,
                'nilai_uas'          => $uas,
                'rata_harian'        => $calc['rata_harian'],
                'nilai_akhir'        => (float)($row['nilai_akhir'] ?? $calc['nilai_akhir']),
                'predikat'           => (string)($row['predikat'] ?? $calc['predikat']),
                'capaian_kompetensi' => (string)($row['capaian_kompetensi'] ?? $calc['capaian_kompetensi']),
            ];
        }

        self::json([
            'success' => true,
            'data' => [
                'kelas_list' => $kelasList,
                'mapel_list' => $mapelList,
                'selected'   => [
                    'kelas_id'       => $kelasId,
                    'nama_kelas'     => $selectedKelas['nama_kelas'] ?? '',
                    'tingkat'        => $selectedKelas['tingkat'] ?? '',
                    'mapel_id'       => $mapelId,
                    'nama_mapel'     => $selectedMapel['nama_mapel'] ?? '',
                    'semester_ke'    => $semesterKe,
                    'tapel_id'       => (int)($activeTapel['id'] ?? 0),
                    'tahun_ajaran'   => (string)($activeTapel['tahun_ajaran'] ?? ''),
                    'tapel_semester' => (string)($activeTapel['semester'] ?? ''),
                ],
                'daftar_nilai' => $daftarNilai
            ]
        ]);
    }

    /**
     * POST /api/v1/guru/nilai/save
     * 
     * Body JSON:
     * {
     *   "kelas_id": 1,
     *   "mapel_id": 2,
     *   "semester_ke": 1,
     *   "tapel_id": 1,
     *   "nilai": [
     *     {
     *       "siswa_id": 5,
     *       "tugas": 85,
     *       "uh": 80,
     *       "uts": 85,
     *       "uas": 90,
     *       "capaian": "opsional"
     *     }
     *   ]
     * }
     */
    public function save(): void
    {
        self::requireMethod('POST');
        $payload = self::requireAuth(['guru', 'admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara']);
        $guruId  = (int)$payload->uid;
        $isAdmin = ($payload->role === 'admin');

        $body = self::jsonBody();

        $kelasId    = (int)($body['kelas_id'] ?? $_POST['kelas_id'] ?? 0);
        $mapelId    = (int)($body['mapel_id'] ?? $_POST['mapel_id'] ?? 0);
        $semesterKe = (int)($body['semester_ke'] ?? $_POST['semester_ke'] ?? 1);
        $tapelId    = !empty($body['tapel_id']) ? (int)$body['tapel_id'] : (!empty($_POST['tapel_id']) ? (int)$_POST['tapel_id'] : null);

        if ($kelasId <= 0 || $mapelId <= 0) {
            self::json([
                'success' => false,
                'message' => 'kelas_id dan mapel_id wajib diisi.'
            ], 422);
        }

        // Verifikasi hak akses guru untuk kelas & mapel ini
        if (!$isAdmin) {
            $isTeaching = JadwalPelajaran::isGuruTeaching($guruId, $kelasId, $mapelId);
            if (!$isTeaching) {
                self::json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki jadwal mengajar mapel ini di kelas terpilih.'
                ], 403);
            }
        }

        if (!$tapelId) {
            $activeTapel = TahunPelajaran::getActive();
            $tapelId = $activeTapel ? (int)$activeTapel['id'] : null;
        }

        // Ambil array nilai siswa
        $nilaiList = $body['nilai'] ?? $_POST['nilai'] ?? [];
        if (!is_array($nilaiList) || empty($nilaiList)) {
            self::json([
                'success' => false,
                'message' => 'Data array nilai tidak boleh kosong.'
            ], 422);
        }

        $savedCount = 0;
        foreach ($nilaiList as $item) {
            $siswaId = (int)($item['siswa_id'] ?? 0);
            if ($siswaId <= 0) continue;

            $tugas   = (float)($item['tugas'] ?? 0);
            $uh      = (float)($item['uh'] ?? 0);
            $uts     = (float)($item['uts'] ?? 0);
            $uas     = (float)($item['uas'] ?? 0);
            $capaian = isset($item['capaian']) ? trim((string)$item['capaian']) : null;

            $ok = NilaiSiswa::saveNilaiKBM(
                $siswaId,
                $mapelId,
                $semesterKe,
                $tugas,
                $uh,
                $uts,
                $uas,
                $tapelId,
                $capaian
            );

            if ($ok) {
                $savedCount++;
            }
        }

        self::json([
            'success'     => true,
            'message'     => "Nilai untuk {$savedCount} siswa berhasil disimpan dan dikalkulasi otomatis menjadi Nilai Rapor Jadi!",
            'saved_count' => $savedCount
        ]);
    }
}

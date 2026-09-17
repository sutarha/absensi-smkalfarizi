<?php
namespace App\Controllers\Api;

use App\Config\Database;
use App\Models\LmsMateri;
use App\Models\LmsSubmission;
use App\Models\LmsDiskusi;
use App\Models\LmsUjian;

class SiswaLmsApiController extends ApiController
{
    private function requireSiswa(): array
    {
        $payload = self::requireAuth();
        if (($payload->role ?? '') !== 'siswa') {
            self::json(['success' => false, 'message' => 'Akses ditolak. Khusus untuk siswa.'], 403);
        }
        return [
            'id' => (int)$payload->siswa_id,
            'kelas_id' => (int)$payload->kelas_id,
            'nisn' => $payload->nisn ?? '',
            'nama' => $payload->nama ?? '',
        ];
    }

    public function getMateri()
    {
        $siswa = $this->requireSiswa();
        $kelas_id = $siswa['kelas_id'];

        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT m.*, mp.nama_mapel, g.nama_lengkap as nama_guru, 
                   (SELECT COUNT(*) FROM lms_submission s WHERE s.materi_id = m.id AND s.siswa_id = ?) as is_submitted
            FROM lms_materi m
            JOIN mata_pelajaran mp ON m.mapel_id = mp.id
            JOIN guru g ON m.guru_id = g.id
            WHERE m.kelas_id = ?
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$siswa['id'], $kelas_id]);
        $materiList = $stmt->fetchAll();

        // Kelompokkan berdasarkan mapel
        $grouped = [];
        foreach ($materiList as $m) {
            $mapelId = $m['mapel_id'];
            if (!isset($grouped[$mapelId])) {
                $grouped[$mapelId] = [
                    'mapel_id' => $mapelId,
                    'nama_mapel' => $m['nama_mapel'],
                    'nama_guru' => $m['nama_guru'],
                    'items' => []
                ];
            }
            $grouped[$mapelId]['items'][] = $m;
        }

        self::json([
            'success' => true,
            'data' => array_values($grouped)
        ]);
    }

    public function getMateriDetail($materiId)
    {
        $siswa = $this->requireSiswa();
        $materiModel = new LmsMateri();
        $materi = $materiModel->getById($materiId);
        
        if (!$materi || $materi['kelas_id'] != $siswa['kelas_id']) {
            self::json(['success' => false, 'message' => 'Materi tidak ditemukan'], 404);
        }

        $submissionModel = new LmsSubmission();
        $submission = $submissionModel->getBySiswaAndMateri($siswa['id'], $materiId) ?: null;

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT nama_mapel FROM mata_pelajaran WHERE id = ?");
        $stmt->execute([$materi['mapel_id']]);
        $mapel = $stmt->fetch();
        $materi['nama_mapel'] = $mapel['nama_mapel'] ?? 'Unknown';

        self::json([
            'success' => true,
            'materi' => $materi,
            'submission' => $submission
        ]);
    }

    public function submitTugas($materiId)
    {
        $siswa = $this->requireSiswa();
        $materiModel = new LmsMateri();
        $materi = $materiModel->getById($materiId);
        
        if (!$materi || strtoupper($materi['tipe']) !== 'TUGAS' || $materi['kelas_id'] != $siswa['kelas_id']) {
            self::json(['success' => false, 'message' => 'Tugas tidak valid'], 400);
        }

        $jawaban = $_POST['jawaban'] ?? '';
        $fileLms = $_FILES['file_lampiran'] ?? null;
        $filePath = null;

        if ($fileLms && $fileLms['error'] == 0) {
            $ext = pathinfo($fileLms['name'], PATHINFO_EXTENSION);
            $filename = uniqid('tugas_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../../../public/uploads/lms/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if (move_uploaded_file($fileLms['tmp_name'], $uploadDir . $filename)) {
                $filePath = 'uploads/lms/' . $filename;
            }
        }

        $submissionModel = new LmsSubmission();
        $submissionModel->submitTugas([
            'materi_id' => $materiId,
            'siswa_id' => $siswa['id'],
            'jawaban' => $jawaban,
            'file_path' => $filePath
        ]);

        self::json([
            'success' => true,
            'message' => 'Tugas berhasil dikumpulkan'
        ]);
    }

    public function getDiskusi($mapelId)
    {
        $siswa = $this->requireSiswa();
        $diskusiModel = new LmsDiskusi();
        $diskusiList = $diskusiModel->getByMapelAndKelas($mapelId, $siswa['kelas_id']);

        self::json([
            'success' => true,
            'data' => $diskusiList,
            'me' => ['id' => $siswa['id'], 'type' => 'siswa']
        ]);
    }

    public function postDiskusi($mapelId)
    {
        $siswa = $this->requireSiswa();
        $pesan = trim($_POST['pesan'] ?? '');
        
        if (empty($pesan)) {
            self::json(['success' => false, 'message' => 'Pesan tidak boleh kosong'], 400);
        }

        $diskusiModel = new LmsDiskusi();
        $diskusiModel->create([
            'kelas_id' => $siswa['kelas_id'],
            'mapel_id' => $mapelId,
            'user_id' => $siswa['id'],
            'user_type' => 'siswa',
            'pesan' => $pesan
        ]);

        self::json([
            'success' => true,
            'message' => 'Pesan terkirim'
        ]);
    }

    // ==========================================
    // CBT / TES / UJIAN (PG & ESSAY + TIMER)
    // ==========================================

    public function getUjian()
    {
        $siswa = $this->requireSiswa();
        $ujianModel = new LmsUjian();
        $list = $ujianModel->getUjianListForSiswa($siswa['kelas_id'], $siswa['id']);

        self::json([
            'success' => true,
            'data' => $list
        ]);
    }

    public function startUjian($ujianId)
    {
        $siswa = $this->requireSiswa();
        $ujianModel = new LmsUjian();
        $res = $ujianModel->startOrResumeUjian($ujianId, $siswa['id']);

        if (!$res['success']) {
            self::json($res, 400);
        }

        self::json($res);
    }

    public function saveJawabanUjian($ujianSiswaId)
    {
        $siswa = $this->requireSiswa();
        $ujianModel = new LmsUjian();

        // Verifikasi kepemilikan sesi
        $detail = $ujianModel->getUjianSiswaDetail($ujianSiswaId);
        if (!$detail || (int)$detail['siswa_id'] !== $siswa['id']) {
            self::json(['success' => false, 'message' => 'Sesi tidak sah.'], 403);
        }

        $soal_id = (int)($_POST['soal_id'] ?? 0);
        $jawaban_pg = !empty($_POST['jawaban_pg']) ? trim($_POST['jawaban_pg']) : null;
        $jawaban_essay = isset($_POST['jawaban_essay']) ? trim($_POST['jawaban_essay']) : null;
        $is_ragu = isset($_POST['is_ragu']) ? (int)$_POST['is_ragu'] : 0;
        $sisa_detik = isset($_POST['sisa_detik']) ? (int)$_POST['sisa_detik'] : null;

        $ok = $ujianModel->saveJawaban($ujianSiswaId, $soal_id, $jawaban_pg, $jawaban_essay, $is_ragu, $sisa_detik);

        self::json(['success' => $ok]);
    }

    public function submitUjian($ujianSiswaId)
    {
        $siswa = $this->requireSiswa();
        $ujianModel = new LmsUjian();

        $detail = $ujianModel->getUjianSiswaDetail($ujianSiswaId);
        if (!$detail || (int)$detail['siswa_id'] !== $siswa['id']) {
            self::json(['success' => false, 'message' => 'Sesi tidak sah.'], 403);
        }

        $isTimeout = isset($_POST['is_timeout']) && (int)$_POST['is_timeout'] === 1;
        $res = $ujianModel->submitUjianSiswa($ujianSiswaId, $isTimeout);

        self::json($res);
    }
}

<?php
namespace App\Controllers\Api;

use App\Config\Database;

/**
 * SiswaAbsensiApiController
 * Endpoint data absensi dan nilai untuk Dashboard PWA Siswa
 *
 * GET /api/v1/siswa/absensi   — Rekap absensi gerbang per bulan
 * GET /api/v1/siswa/nilai     — Nilai per semester
 * GET /api/v1/siswa/tabungan  — Program tabungan siswa
 */
class SiswaAbsensiApiController extends ApiController
{
    private function requireSiswaAuth(): object
    {
        $payload = self::requireAuth();
        if (($payload->role ?? '') !== 'siswa') {
            self::json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        return $payload;
    }

    // ===========================================================
    // GET /api/v1/siswa/absensi?bulan=&tahun=
    // ===========================================================
    public function absensi(): void
    {
        self::requireMethod('GET');
        $payload  = $this->requireSiswaAuth();
        $siswaId  = $payload->siswa_id;

        $bulan = (int)($_GET['bulan'] ?? date('n'));
        $tahun = (int)($_GET['tahun'] ?? date('Y'));

        if ($bulan < 1 || $bulan > 12 || $tahun < 2020) {
            self::json(['success' => false, 'message' => 'Parameter bulan/tahun tidak valid.'], 422);
        }

        $db = Database::getConnection();

        // Rekap gerbang
        $stmt = $db->prepare("
            SELECT
                tanggal,
                status_kehadiran AS status,
                waktu_datang AS jam_masuk,
                waktu_pulang AS jam_keluar
            FROM presensi_gerbang_siswa
            WHERE siswa_id = :id
              AND MONTH(tanggal) = :bulan
              AND YEAR(tanggal)  = :tahun
            ORDER BY tanggal ASC
        ");
        $stmt->execute([':id' => $siswaId, ':bulan' => $bulan, ':tahun' => $tahun]);
        $records = $stmt->fetchAll();

        $today = date('Y-m-d');
        $hasToday = false;
        foreach ($records as $r) {
            if ($r['tanggal'] === $today) {
                $hasToday = true;
                break;
            }
        }

        // Inject evaluasi hari ini jika belum ada (dan ini bulan berjalan)
        if (!$hasToday && $bulan == date('n') && $tahun == date('Y') && date('N') <= 5) {
            $stmtKelas = $db->prepare("SELECT kelas_id FROM siswa WHERE id = :id");
            $stmtKelas->execute([':id' => $siswaId]);
            $kelasId = $stmtKelas->fetchColumn();

            if ($kelasId) {
                $dynamicData = \App\Models\PresensiGerbang::getByDate($today, $kelasId);
                $myStatus = 'ALPHA';
                foreach ($dynamicData as $d) {
                    if ($d['siswa_id'] == $siswaId) {
                        $myStatus = $d['status_kehadiran'];
                        break;
                    }
                }
                
                $records[] = [
                    'tanggal' => $today,
                    'status' => $myStatus,
                    'jam_masuk' => null,
                    'jam_keluar' => null
                ];
                
                // Urutkan kembali berdasarkan tanggal ASC
                usort($records, function($a, $b) {
                    return strtotime($a['tanggal']) - strtotime($b['tanggal']);
                });
            }
        }

        // Summary
        $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0];
        foreach ($records as $r) {
            $key = strtolower($r['status']);
            if (isset($summary[$key])) $summary[$key]++;
        }

        self::json([
            'success' => true,
            'bulan'   => $bulan,
            'tahun'   => $tahun,
            'summary' => $summary,
            'records' => $records,
        ]);
    }

    // ===========================================================
    // GET /api/v1/siswa/nilai?semester=
    // ===========================================================
    public function nilai(): void
    {
        self::requireMethod('GET');
        $payload  = $this->requireSiswaAuth();
        $siswaId  = $payload->siswa_id;

        $semester = (int)($_GET['semester'] ?? 1);
        if ($semester < 1 || $semester > 6) {
            self::json(['success' => false, 'message' => 'Semester harus antara 1-6.'], 422);
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT
                ns.semester,
                ns.nilai_akhir,
                ns.catatan,
                mp.nama_mapel,
                g.nama_lengkap AS nama_guru
            FROM   nilai_siswa ns
            JOIN   mata_pelajaran mp ON mp.id = ns.mapel_id
            LEFT JOIN guru g         ON g.id  = ns.guru_id
            WHERE  ns.siswa_id = :id
              AND  ns.semester  = :sem
            ORDER  BY mp.nama_mapel ASC
        ");
        $stmt->execute([':id' => $siswaId, ':sem' => $semester]);
        $nilai = $stmt->fetchAll();

        $rata = 0;
        if (count($nilai) > 0) {
            $rata = array_sum(array_column($nilai, 'nilai_akhir')) / count($nilai);
        }

        self::json([
            'success'  => true,
            'semester' => $semester,
            'nilai'    => $nilai,
            'rata_rata'=> round($rata, 2),
        ]);
    }

    // ===========================================================
    // GET /api/v1/siswa/tabungan
    // ===========================================================
    public function tabungan(): void
    {
        self::requireMethod('GET');
        $payload = $this->requireSiswaAuth();
        $siswaId = $payload->siswa_id;

        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT
                tp.id,
                tp.nama_program,
                tp.deskripsi,
                tp.target_nominal,
                tp.target_tanggal,
                tp.foto,
                tp.status AS status_program,
                COALESCE(ts.total_terkumpul, 0) AS total_terkumpul,
                COALESCE(ts.status, 'berjalan')  AS status
            FROM   tabungan_program tp
            LEFT JOIN tabungan_siswa ts ON ts.program_id = tp.id AND ts.siswa_id = :id
            WHERE  tp.status = 'aktif'
              AND  (tp.kelas_id IS NULL OR tp.kelas_id = (SELECT kelas_id FROM siswa WHERE id = :id2))
            ORDER  BY tp.created_at DESC
        ");
        $stmt->execute([':id' => $siswaId, ':id2' => $siswaId]);
        $programs = $stmt->fetchAll();

        // Riwayat setoran untuk program yang diikuti
        foreach ($programs as &$p) {
            $stmt2 = $db->prepare("
                SELECT tt.jumlah, tt.tanggal, tt.catatan, g.nama_lengkap AS dicatat_oleh
                FROM   tabungan_transaksi tt
                JOIN   tabungan_siswa ts ON ts.id = tt.tabungan_siswa_id
                LEFT JOIN guru g         ON g.id  = tt.dicatat_guru_id
                WHERE  ts.siswa_id   = :sid
                  AND  ts.program_id = :pid
                ORDER  BY tt.tanggal DESC
                LIMIT 5
            ");
            $stmt2->execute([':sid' => $siswaId, ':pid' => $p['id']]);
            $p['riwayat_setoran'] = $stmt2->fetchAll();
        }

        self::json([
            'success'  => true,
            'programs' => $programs,
        ]);
    }
}

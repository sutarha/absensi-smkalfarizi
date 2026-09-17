<?php
namespace App\Controllers\Api;

use App\Config\Database;
use Firebase\JWT\JWT;

/**
 * SiswaApiController
 * Endpoint autentikasi dan profil untuk Dashboard PWA Siswa
 *
 * POST /api/v1/siswa/login      — Login NISN + tanggal_lahir
 * GET  /api/v1/siswa/me         — Profil siswa (butuh token)
 * POST /api/v1/siswa/fcm-token  — Simpan FCM token untuk push notif
 * GET  /api/v1/siswa/dashboard  — Data ringkasan dashboard
 */
class SiswaApiController extends ApiController
{
    private const SISWA_ROLE = 'siswa';

    // ===========================================================
    // POST /api/v1/siswa/login
    // ===========================================================
    public function login(): void
    {
        self::requireMethod('POST');

        $body  = self::jsonBody();
        $nisn  = trim($body['nisn'] ?? $_POST['nisn'] ?? '');
        $tglLahir = trim($body['tanggal_lahir'] ?? $_POST['tanggal_lahir'] ?? '');

        if (empty($nisn) || empty($tglLahir)) {
            self::json(['success' => false, 'message' => 'NISN dan tanggal lahir wajib diisi.'], 422);
        }

        // Validasi format tanggal lahir (YYYY-MM-DD)
        $d = \DateTime::createFromFormat('Y-m-d', $tglLahir);
        if (!$d || $d->format('Y-m-d') !== $tglLahir) {
            self::json(['success' => false, 'message' => 'Format tanggal lahir harus YYYY-MM-DD (contoh: 2007-05-14).'], 422);
        }

        $db   = Database::getConnection();
        $stmt = $db->prepare("
            SELECT s.*, k.nama_kelas, k.tingkat, k.jurusan
            FROM   siswa s
            LEFT JOIN kelas k ON k.id = s.kelas_id
            WHERE  s.nisn = :nisn
            LIMIT  1
        ");
        $stmt->execute([':nisn' => $nisn]);
        $siswa = $stmt->fetch();

        if (!$siswa) {
            self::json(['success' => false, 'message' => 'NISN tidak ditemukan.'], 401);
        }

        // Verifikasi tanggal lahir
        $tglLahirDb = $siswa['tanggal_lahir'] ?? null;
        if ($tglLahirDb === null || $tglLahirDb !== $tglLahir) {
            self::json(['success' => false, 'message' => 'Tanggal lahir tidak sesuai.'], 401);
        }

        // Update last login
        $db->prepare("UPDATE siswa SET last_login_pwa = NOW() WHERE id = :id")
           ->execute([':id' => $siswa['id']]);

        $token = $this->createSiswaToken($siswa);

        self::json([
            'success'    => true,
            'message'    => 'Login berhasil. Selamat datang, ' . $siswa['nama_siswa'] . '!',
            'token'      => $token,
            'expires_in' => self::TOKEN_TTL,
            'token_type' => 'Bearer',
            'siswa'      => $this->formatSiswa($siswa),
        ]);
    }

    // ===========================================================
    // GET /api/v1/siswa/me
    // ===========================================================
    public function me(): void
    {
        self::requireMethod('GET');
        $payload = $this->requireSiswaAuth();

        $db   = Database::getConnection();
        $stmt = $db->prepare("
            SELECT s.*, k.nama_kelas, k.tingkat, k.jurusan
            FROM   siswa s
            LEFT JOIN kelas k ON k.id = s.kelas_id
            WHERE  s.id = :id
            LIMIT  1
        ");
        $stmt->execute([':id' => $payload->siswa_id]);
        $siswa = $stmt->fetch();

        if (!$siswa) {
            self::json(['success' => false, 'message' => 'Data siswa tidak ditemukan.'], 404);
        }

        self::json(['success' => true, 'siswa' => $this->formatSiswa($siswa)]);
    }

    // ===========================================================
    // POST /api/v1/siswa/fcm-token
    // Simpan FCM token dari device untuk push notification
    // ===========================================================
    public function saveFcmToken(): void
    {
        self::requireMethod('POST');
        $payload = $this->requireSiswaAuth();

        $body  = self::jsonBody();
        $token = trim($body['fcm_token'] ?? $_POST['fcm_token'] ?? '');

        if (empty($token)) {
            self::json(['success' => false, 'message' => 'fcm_token wajib diisi.'], 422);
        }

        $db = Database::getConnection();
        $db->prepare("UPDATE siswa SET fcm_token = :t WHERE id = :id")
           ->execute([':t' => $token, ':id' => $payload->siswa_id]);

        self::json(['success' => true, 'message' => 'FCM token berhasil disimpan.']);
    }

    // ===========================================================
    // GET /api/v1/siswa/dashboard
    // Data ringkasan untuk beranda PWA
    // ===========================================================
    public function dashboard(): void
    {
        self::requireMethod('GET');
        $payload = $this->requireSiswaAuth();
        $siswaId = $payload->siswa_id;

        $db = Database::getConnection();

        // Absensi gerbang bulan ini
        $stmt = $db->prepare("
            SELECT
                COUNT(*) AS total_hari,
                COALESCE(SUM(CASE WHEN status_kehadiran = 'HADIR' THEN 1 ELSE 0 END), 0)  AS hadir,
                COALESCE(SUM(CASE WHEN status_kehadiran = 'TERLAMBAT' THEN 1 ELSE 0 END), 0)  AS terlambat,
                COALESCE(SUM(CASE WHEN status_kehadiran = 'IZIN'  THEN 1 ELSE 0 END), 0)  AS izin,
                COALESCE(SUM(CASE WHEN status_kehadiran = 'SAKIT' THEN 1 ELSE 0 END), 0)  AS sakit,
                COALESCE(SUM(CASE WHEN status_kehadiran = 'ALPHA' THEN 1 ELSE 0 END), 0)  AS alpha
            FROM presensi_gerbang_siswa
            WHERE siswa_id = :id
              AND MONTH(tanggal) = MONTH(CURDATE())
              AND YEAR(tanggal)  = YEAR(CURDATE())
        ");
        $stmt->execute([':id' => $siswaId]);
        $absensi = $stmt->fetch();

        // Inject evaluasi hari ini jika belum ada di database
        $today = date('Y-m-d');
        $stmtCheck = $db->prepare("SELECT id FROM presensi_gerbang_siswa WHERE siswa_id = :id AND tanggal = :tgl");
        $stmtCheck->execute([':id' => $siswaId, ':tgl' => $today]);
        if (!$stmtCheck->fetch() && date('N') <= 5) { // Senin-Jumat
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
                $key = strtolower($myStatus);
                if (isset($absensi[$key])) {
                    $absensi[$key]++;
                }
            }
        }

        // Jadwal hari ini
        $hariIni = strtoupper(['MINGGU','SENIN','SELASA','RABU','KAMIS','JUMAT','SABTU'][date('w')]);
        $stmt = $db->prepare("
            SELECT jp.jam_mulai, jp.jam_selesai, mp.nama_mapel, g.nama_lengkap AS nama_guru
            FROM   jadwal_pelajaran jp
            JOIN   mata_pelajaran mp ON mp.id = jp.mapel_id
            JOIN   guru g            ON g.id  = jp.guru_id
            JOIN   siswa s           ON s.kelas_id = jp.kelas_id
            WHERE  s.id   = :id
              AND  jp.hari = :hari
            ORDER  BY jp.jam_mulai ASC
        ");
        $stmt->execute([':id' => $siswaId, ':hari' => $hariIni]);
        $jadwal = $stmt->fetchAll();

        // Notifikasi belum dibaca
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifikasi_siswa WHERE siswa_id = :id AND is_read = 0");
        $stmt->execute([':id' => $siswaId]);
        $unreadNotif = (int)$stmt->fetchColumn();

        // Tugas belum dikumpulkan
        $stmt = $db->prepare("
            SELECT m.id, m.judul, m.deadline, mp.nama_mapel, g.nama_lengkap AS nama_guru
            FROM lms_materi m
            JOIN mata_pelajaran mp ON m.mapel_id = mp.id
            JOIN guru g ON m.guru_id = g.id
            JOIN siswa s ON s.kelas_id = m.kelas_id
            WHERE s.id = :id
              AND LOWER(m.tipe) = 'tugas'
              AND NOT EXISTS (
                  SELECT 1 FROM lms_submission sub 
                  WHERE sub.materi_id = m.id AND sub.siswa_id = s.id
              )
            ORDER BY m.deadline ASC, m.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([':id' => $siswaId]);
        $tugasPending = $stmt->fetchAll();

        // Rapor ringkasan (semester terbaru yang memiliki nilai)
        $stmt = $db->prepare("
            SELECT semester_ke, AVG(nilai_akhir) as rata_rata, COUNT(*) as total_mapel,
                   SUM(CASE WHEN nilai_akhir >= 75 THEN 1 ELSE 0 END) as mapel_tuntas
            FROM nilai_siswa
            WHERE siswa_id = :id
            GROUP BY semester_ke
            ORDER BY semester_ke DESC
            LIMIT 1
        ");
        $stmt->execute([':id' => $siswaId]);
        $raporRingkasan = $stmt->fetch();
        if ($raporRingkasan) {
            $rata = round((float)$raporRingkasan['rata_rata'], 2);
            $predikat = ($rata >= 86) ? 'A' : (($rata >= 71) ? 'B' : (($rata >= 56) ? 'C' : 'D'));
            $raporRingkasan['rata_rata'] = $rata;
            $raporRingkasan['predikat'] = $predikat;
            $raporRingkasan['total_mapel'] = (int)$raporRingkasan['total_mapel'];
            $raporRingkasan['mapel_tuntas'] = (int)$raporRingkasan['mapel_tuntas'];
        } else {
            $raporRingkasan = null;
        }

        // Pengumuman / notifikasi terbaru belum dibaca
        $stmt = $db->prepare("
            SELECT ns.id, ns.judul, ns.pesan, ns.tipe, ns.created_at, COALESCE(nb.sender_nama, 'Admin SMK') as pengirim
            FROM notifikasi_siswa ns
            LEFT JOIN notifikasi_broadcast nb ON ns.broadcast_id = nb.id
            WHERE ns.siswa_id = :id AND ns.is_read = 0
            ORDER BY ns.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([':id' => $siswaId]);
        $latestNotif = $stmt->fetch() ?: null;

        self::json([
            'success'            => true,
            'absensi_bulan'      => $absensi,
            'jadwal_hari_ini'    => $jadwal,
            'unread_notif'       => $unreadNotif,
            'pengumuman_terbaru' => $latestNotif,
            'tugas_pending'      => $tugasPending,
            'rapor_ringkasan'    => $raporRingkasan,
        ]);
    }

    // ===========================================================
    // GET /api/v1/siswa/tabungan
    // Data tabungan siswa
    // ===========================================================
    public function tabungan(): void
    {
        self::requireMethod('GET');
        $payload = $this->requireSiswaAuth();
        $siswaId = $payload->siswa_id;

        $db = Database::getConnection();
        
        $stmt = $db->prepare("
            SELECT ts.id as tabungan_siswa_id, ts.total_terkumpul, ts.status, 
                   tp.nama_program, tp.deskripsi, tp.target_nominal, tp.target_tanggal
            FROM tabungan_siswa ts
            JOIN tabungan_program tp ON ts.program_id = tp.id
            WHERE ts.siswa_id = :id
            ORDER BY tp.status ASC, ts.created_at DESC
        ");
        $stmt->execute([':id' => $siswaId]);
        $programs = $stmt->fetchAll();

        foreach ($programs as &$p) {
            $stmtTx = $db->prepare("
                SELECT jumlah, tanggal, catatan 
                FROM tabungan_transaksi 
                WHERE tabungan_siswa_id = :ts_id 
                ORDER BY tanggal DESC, created_at DESC LIMIT 5
            ");
            $stmtTx->execute([':ts_id' => $p['tabungan_siswa_id']]);
            $p['riwayat_setoran'] = $stmtTx->fetchAll();
        }
        unset($p);

        self::json([
            'success'  => true,
            'programs' => $programs
        ]);
    }

    // ===========================================================
    // GET /api/v1/siswa/nilai
    // Data rapor digital siswa per semester
    // ===========================================================
    public function nilai(): void
    {
        self::requireMethod('GET');
        $payload = $this->requireSiswaAuth();
        $siswaId = $payload->siswa_id;
        $semester = (int)($_GET['semester'] ?? 1);

        $db = Database::getConnection();
        
        try {
            // Ambil daftar nilai per mapel dengan rincian lengkap
            $stmt = $db->prepare("
                SELECT ns.id, ns.mapel_id, ns.semester_ke,
                       ns.nilai_tugas, ns.nilai_uh, ns.nilai_uts, ns.nilai_uas,
                       ns.nilai_formatif, ns.nilai_sumatif_materi, ns.nilai_sas,
                       ns.nilai_akhir, ns.predikat, ns.capaian_kompetensi, ns.is_manual,
                       mp.nama_mapel, mp.kode_mapel,
                       COALESCE((
                           SELECT g.nama_lengkap 
                           FROM jadwal_pelajaran jp 
                           JOIN guru g ON jp.guru_id = g.id 
                           WHERE jp.mapel_id = ns.mapel_id AND jp.kelas_id = :kelas_id 
                           LIMIT 1
                       ), '-') as nama_guru
                FROM nilai_siswa ns
                JOIN mata_pelajaran mp ON ns.mapel_id = mp.id
                WHERE ns.siswa_id = :id AND ns.semester_ke = :sem
                ORDER BY mp.nama_mapel ASC
            ");
            $stmt->execute([':id' => $siswaId, ':sem' => $semester, ':kelas_id' => $payload->kelas_id]);
            $nilai = $stmt->fetchAll();

            // Hitung statistik nilai semester
            $totalNilai = 0;
            $tuntasCount = 0;
            foreach ($nilai as &$n) {
                $na = (float)$n['nilai_akhir'];
                $totalNilai += $na;
                $isTuntas = $na >= 75.0;
                if ($isTuntas) $tuntasCount++;
                $n['is_tuntas'] = $isTuntas;
                $n['kkm'] = 75;
                if (empty($n['predikat'])) {
                    $n['predikat'] = ($na >= 86) ? 'A' : (($na >= 71) ? 'B' : (($na >= 56) ? 'C' : 'D'));
                }
            }
            unset($n);

            $count = count($nilai);
            $rataRata = $count > 0 ? round($totalNilai / $count, 2) : 0;
            $predikatUmum = ($rataRata >= 86) ? 'A' : (($rataRata >= 71) ? 'B' : (($rataRata >= 56) ? 'C' : ($rataRata > 0 ? 'D' : '-')));

            // Ambil info semester mana saja yang sudah ada nilainya
            $stmtSem = $db->prepare("SELECT DISTINCT semester_ke FROM nilai_siswa WHERE siswa_id = :id ORDER BY semester_ke ASC");
            $stmtSem->execute([':id' => $siswaId]);
            $availableSemesters = array_map('intval', $stmtSem->fetchAll(\PDO::FETCH_COLUMN) ?: []);

            // Ambil biodata siswa & info sekolah
            $stmtSiswa = $db->prepare("
                SELECT s.id, s.nama_siswa, s.nisn, s.tanggal_lahir, s.jenis_kelamin, s.alamat,
                       k.nama_kelas, k.jurusan,
                       g.nama_lengkap as wali_kelas
                FROM siswa s
                LEFT JOIN kelas k ON s.kelas_id = k.id
                LEFT JOIN guru g ON k.wali_kelas_guru_id = g.id
                WHERE s.id = :id
            ");
            $stmtSiswa->execute([':id' => $siswaId]);
            $siswaInfo = $stmtSiswa->fetch();

            self::json([
                'success'             => true,
                'semester'            => $semester,
                'available_semesters' => $availableSemesters,
                'siswa'               => $siswaInfo,
                'sekolah'             => [
                    'nama' => 'SMK Al-Farizi',
                    'npsn' => '69888999',
                    'alamat' => 'Tasikmalaya, Jawa Barat'
                ],
                'statistik' => [
                    'total_mapel'   => $count,
                    'mapel_tuntas'  => $tuntasCount,
                    'rata_rata'     => $rataRata,
                    'predikat_umum' => $predikatUmum,
                ],
                'nilai'               => $nilai
            ]);
        } catch (\PDOException $e) {
            self::json([
                'success'   => false,
                'message'   => 'Gagal memuat rapor digital: ' . $e->getMessage(),
                'nilai'     => []
            ]);
        }
    }

    // ===========================================================
    // PRIVATE HELPERS
    // ===========================================================

    /** Buat JWT khusus siswa (role = 'siswa') */
    private function createSiswaToken(array $siswa): string
    {
        $now = time();
        $payload = [
            'iss'      => 'smk-alfarizi-pwa',
            'iat'      => $now,
            'exp'      => $now + self::TOKEN_TTL,
            'siswa_id' => (int)$siswa['id'],
            'nisn'     => $siswa['nisn'],
            'nama'     => $siswa['nama_siswa'],
            'kelas_id' => (int)$siswa['kelas_id'],
            'role'     => self::SISWA_ROLE,
        ];
        return JWT::encode($payload, self::getJwtSecret(), self::JWT_ALGO);
    }

    /** Validasi JWT siswa — return payload atau kirim 401 */
    private function requireSiswaAuth(): object
    {
        $payload = self::requireAuth();
        if (($payload->role ?? '') !== self::SISWA_ROLE) {
            self::json(['success' => false, 'message' => 'Akses ditolak. Endpoint ini khusus untuk siswa.'], 403);
        }
        return $payload;
    }

    /** Format data siswa untuk response (tanpa kolom sensitif) */
    private function formatSiswa(array $s): array
    {
        return [
            'id'          => (int)$s['id'],
            'nisn'        => $s['nisn'],
            'nama_siswa'  => $s['nama_siswa'],
            'jenis_kelamin' => $s['jenis_kelamin'],
            'foto'        => $s['foto'],
            'kelas_id'    => (int)$s['kelas_id'],
            'nama_kelas'  => $s['nama_kelas'],
            'tingkat'     => $s['tingkat'],
            'jurusan'     => $s['jurusan'],
            'tanggal_lahir' => $s['tanggal_lahir'],
            'no_hp_ortu'  => $s['no_hp_ortu'] ?? null,
            'alamat'      => $s['alamat'] ?? null,
            'last_login'  => $s['last_login_pwa'] ?? null,
        ];
    }

    /**
     * GET /api/v1/siswa/notifikasi
     * Mengambil daftar notifikasi untuk siswa yang sedang login
     */
    public function notifikasi(): void
    {
        $user = $this->requireSiswaAuth();
        $siswaId = (int)$user->siswa_id;

        $notif = \App\Models\Notifikasi::getForSiswa($siswaId, 50);
        $unread = \App\Models\Notifikasi::getUnreadCountForSiswa($siswaId);

        self::json([
            'success'      => true,
            'unread_count' => $unread,
            'data'         => $notif,
        ]);
    }

    /**
     * POST /api/v1/siswa/notifikasi/read/{id}
     * Tandai 1 notifikasi telah dibaca
     */
    public function bacaNotifikasi(int $id): void
    {
        $user = $this->requireSiswaAuth();
        $siswaId = (int)$user->siswa_id;

        $res = \App\Models\Notifikasi::markAsReadForSiswa($id, $siswaId);
        $unread = \App\Models\Notifikasi::getUnreadCountForSiswa($siswaId);

        self::json([
            'success'      => $res,
            'unread_count' => $unread,
        ]);
    }

    /**
     * POST /api/v1/siswa/notifikasi/read-all
     * Tandai semua notifikasi telah dibaca
     */
    public function bacaSemuaNotifikasi(): void
    {
        $user = $this->requireSiswaAuth();
        $siswaId = (int)$user->siswa_id;

        $res = \App\Models\Notifikasi::markAllAsReadForSiswa($siswaId);

        self::json([
            'success'      => $res,
            'unread_count' => 0,
        ]);
    }

    /**
     * GET /api/v1/siswa/notifikasi/live-check
     * Polling ringan untuk live realtime check notifikasi siswa
     */
    public function notifikasiLiveCheck(): void
    {
        $user = $this->requireSiswaAuth();
        $siswaId = (int)$user->siswa_id;

        $unread = \App\Models\Notifikasi::getUnreadCountForSiswa($siswaId);
        $latest = null;
        if ($unread > 0) {
            $recent = \App\Models\Notifikasi::getForSiswa($siswaId, 1);
            $latest = $recent[0] ?? null;
        }

        self::json([
            'success'      => true,
            'unread_count' => $unread,
            'latest'       => $latest,
        ]);
    }
}


<?php
namespace App\Models;

use App\Config\Database;
use App\Config\App;
use PDO;

class LmsUjian
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getByKelasAndMapel(int $kelas_id, int $mapel_id): array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM lms_soal s WHERE s.ujian_id = u.id) AS total_soal,
                   (SELECT COUNT(*) FROM lms_soal s WHERE s.ujian_id = u.id AND s.tipe_soal = 'pilihan_ganda') AS total_pg,
                   (SELECT COUNT(*) FROM lms_soal s WHERE s.ujian_id = u.id AND s.tipe_soal = 'essay') AS total_essay,
                   (SELECT COUNT(*) FROM lms_ujian_siswa us WHERE us.ujian_id = u.id) AS total_peserta
            FROM lms_ujian u
            WHERE u.kelas_id = ? AND u.mapel_id = ?
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$kelas_id, $mapel_id]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, mp.nama_mapel, k.nama_kelas, g.nama_lengkap AS nama_guru
            FROM lms_ujian u
            JOIN mata_pelajaran mp ON u.mapel_id = mp.id
            JOIN kelas k ON u.kelas_id = k.id
            JOIN guru g ON u.guru_id = g.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function createUjian(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO lms_ujian (kelas_id, mapel_id, guru_id, judul, deskripsi, durasi_menit, kkm, acak_soal, is_aktif)
            VALUES (:kelas_id, :mapel_id, :guru_id, :judul, :deskripsi, :durasi_menit, :kkm, :acak_soal, :is_aktif)
        ");
        $stmt->execute([
            'kelas_id' => $data['kelas_id'],
            'mapel_id' => $data['mapel_id'],
            'guru_id' => $data['guru_id'],
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? '',
            'durasi_menit' => (int)($data['durasi_menit'] ?? 60),
            'kkm' => (float)($data['kkm'] ?? 75.0),
            'acak_soal' => (int)($data['acak_soal'] ?? 0),
            'is_aktif' => (int)($data['is_aktif'] ?? 1)
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteUjian(int $id, int $guru_id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM lms_ujian WHERE id = ? AND guru_id = ?");
        return $stmt->execute([$id, $guru_id]);
    }

    public function toggleAktif(int $id, int $guru_id): bool
    {
        $stmt = $this->db->prepare("UPDATE lms_ujian SET is_aktif = IF(is_aktif = 1, 0, 1) WHERE id = ? AND guru_id = ?");
        return $stmt->execute([$id, $guru_id]);
    }

    public function getSoalByUjian(int $ujian_id): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM lms_soal 
            WHERE ujian_id = ? 
            ORDER BY nomor_urut ASC, id ASC
        ");
        $stmt->execute([$ujian_id]);
        return $stmt->fetchAll();
    }

    public function getSoalById(int $soal_id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM lms_soal WHERE id = ?");
        $stmt->execute([$soal_id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function createSoal(array $data): int
    {
        // Hitung nomor urut berikutnya
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(nomor_urut), 0) + 1 FROM lms_soal WHERE ujian_id = ?");
        $stmt->execute([$data['ujian_id']]);
        $nomorUrut = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("
            INSERT INTO lms_soal 
            (ujian_id, nomor_urut, tipe_soal, pertanyaan, gambar, pilihan_a, pilihan_b, pilihan_c, pilihan_d, pilihan_e, kunci_jawaban, bobot_nilai)
            VALUES 
            (:ujian_id, :nomor_urut, :tipe_soal, :pertanyaan, :gambar, :pilihan_a, :pilihan_b, :pilihan_c, :pilihan_d, :pilihan_e, :kunci_jawaban, :bobot_nilai)
        ");

        $stmt->execute([
            'ujian_id' => $data['ujian_id'],
            'nomor_urut' => $nomorUrut,
            'tipe_soal' => $data['tipe_soal'] ?? 'pilihan_ganda',
            'pertanyaan' => $data['pertanyaan'],
            'gambar' => $data['gambar'] ?? null,
            'pilihan_a' => $data['pilihan_a'] ?? null,
            'pilihan_b' => $data['pilihan_b'] ?? null,
            'pilihan_c' => $data['pilihan_c'] ?? null,
            'pilihan_d' => $data['pilihan_d'] ?? null,
            'pilihan_e' => $data['pilihan_e'] ?? null,
            'kunci_jawaban' => !empty($data['kunci_jawaban']) ? strtoupper($data['kunci_jawaban']) : null,
            'bobot_nilai' => (float)($data['bobot_nilai'] ?? 1.0)
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function deleteSoal(int $soal_id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM lms_soal WHERE id = ?");
        return $stmt->execute([$soal_id]);
    }

    public function getHasilSiswaByUjian(int $ujian_id): array
    {
        $stmt = $this->db->prepare("
            SELECT us.*, s.nama_siswa, s.nisn, s.foto
            FROM lms_ujian_siswa us
            JOIN siswa s ON us.siswa_id = s.id
            WHERE us.ujian_id = ?
            ORDER BY us.nilai_akhir DESC, s.nama_siswa ASC
        ");
        $stmt->execute([$ujian_id]);
        return $stmt->fetchAll();
    }

    public function getUjianSiswaDetail(int $ujian_siswa_id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT us.*, s.nama_siswa, s.nisn, u.judul AS judul_ujian, u.durasi_menit, u.kkm, mp.nama_mapel, k.nama_kelas
            FROM lms_ujian_siswa us
            JOIN siswa s ON us.siswa_id = s.id
            JOIN lms_ujian u ON us.ujian_id = u.id
            JOIN mata_pelajaran mp ON u.mapel_id = mp.id
            JOIN kelas k ON u.kelas_id = k.id
            WHERE us.id = ?
        ");
        $stmt->execute([$ujian_siswa_id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public function getJawabanSiswaWithSoal(int $ujian_siswa_id): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, j.id AS jawaban_id, j.jawaban_pg, j.jawaban_essay, j.is_ragu, j.is_benar, j.nilai_butir
            FROM lms_soal s
            JOIN lms_ujian_siswa us ON s.ujian_id = us.ujian_id
            LEFT JOIN lms_ujian_jawaban j ON j.ujian_siswa_id = us.id AND j.soal_id = s.id
            WHERE us.id = ?
            ORDER BY s.nomor_urut ASC, s.id ASC
        ");
        $stmt->execute([$ujian_siswa_id]);
        return $stmt->fetchAll();
    }

    public function beriNilaiEssay(int $ujian_siswa_id, int $soal_id, float $nilai_butir, ?string $catatan = null): bool
    {
        // 1. Update nilai butir di tabel lms_ujian_jawaban
        $stmt = $this->db->prepare("
            UPDATE lms_ujian_jawaban 
            SET nilai_butir = :nilai_butir
            WHERE ujian_siswa_id = :us_id AND soal_id = :soal_id
        ");
        $stmt->execute([
            'nilai_butir' => $nilai_butir,
            'us_id' => $ujian_siswa_id,
            'soal_id' => $soal_id
        ]);

        // 2. Hitung ulang total nilai essay & nilai akhir
        $us = $this->getUjianSiswaDetail($ujian_siswa_id);
        if (!$us) return false;

        $this->recalculateNilaiAkhir($ujian_siswa_id);

        if ($catatan !== null) {
            $stmt = $this->db->prepare("UPDATE lms_ujian_siswa SET catatan_guru = ? WHERE id = ?");
            $stmt->execute([$catatan, $ujian_siswa_id]);
        }

        return true;
    }

    public function recalculateNilaiAkhir(int $ujian_siswa_id): void
    {
        $us = $this->getUjianSiswaDetail($ujian_siswa_id);
        if (!$us) return;

        $ujianId = (int)$us['ujian_id'];
        $soalList = $this->getSoalByUjian($ujianId);

        $totalBobotPG = 0;
        $totalBobotEssay = 0;
        foreach ($soalList as $s) {
            if ($s['tipe_soal'] === 'essay') {
                $totalBobotEssay += (float)$s['bobot_nilai'];
            } else {
                $totalBobotPG += (float)$s['bobot_nilai'];
            }
        }

        $jawabans = $this->getJawabanSiswaWithSoal($ujian_siswa_id);
        $skorPG = 0;
        $skorEssay = 0;
        $jmlBenar = 0;
        $jmlSalah = 0;

        foreach ($jawabans as $j) {
            if ($j['tipe_soal'] === 'essay') {
                $skorEssay += (float)($j['nilai_butir'] ?? 0);
            } else {
                if ($j['jawaban_pg'] && strtoupper($j['jawaban_pg']) === strtoupper($j['kunci_jawaban'])) {
                    $skorPG += (float)$j['bobot_nilai'];
                    $jmlBenar++;
                } else {
                    $jmlSalah++;
                }
            }
        }

        // Normalisasi skala 0 - 100
        $totalBobot = $totalBobotPG + $totalBobotEssay;
        $totalSkor = $skorPG + $skorEssay;

        $nilaiAkhir = ($totalBobot > 0) ? round(($totalSkor / $totalBobot) * 100, 2) : 0;
        $nilaiPgNorm = ($totalBobotPG > 0) ? round(($skorPG / $totalBobotPG) * 100, 2) : 0;
        $nilaiEssayNorm = ($totalBobotEssay > 0) ? round(($skorEssay / $totalBobotEssay) * 100, 2) : 0;

        $stmt = $this->db->prepare("
            UPDATE lms_ujian_siswa
            SET nilai_pg = :n_pg,
                nilai_essay = :n_essay,
                nilai_akhir = :n_akhir,
                jumlah_benar = :benar,
                jumlah_salah = :salah
            WHERE id = :id
        ");
        $stmt->execute([
            'n_pg' => $nilaiPgNorm,
            'n_essay' => $nilaiEssayNorm,
            'n_akhir' => $nilaiAkhir,
            'benar' => $jmlBenar,
            'salah' => $jmlSalah,
            'id' => $ujian_siswa_id
        ]);
    }

    // ==========================================
    // SISWA API METHODS
    // ==========================================

    public function getUjianListForSiswa(int $kelas_id, int $siswa_id): array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, mp.nama_mapel, g.nama_lengkap AS nama_guru,
                   (SELECT COUNT(*) FROM lms_soal s WHERE s.ujian_id = u.id) AS total_soal,
                   (SELECT COUNT(*) FROM lms_soal s WHERE s.ujian_id = u.id AND s.tipe_soal = 'pilihan_ganda') AS total_pg,
                   (SELECT COUNT(*) FROM lms_soal s WHERE s.ujian_id = u.id AND s.tipe_soal = 'essay') AS total_essay,
                   us.id AS ujian_siswa_id,
                   us.status AS status_pengerjaan,
                   us.sisa_detik,
                   us.nilai_akhir,
                   us.waktu_mulai,
                   us.waktu_selesai
            FROM lms_ujian u
            JOIN mata_pelajaran mp ON u.mapel_id = mp.id
            JOIN guru g ON u.guru_id = g.id
            LEFT JOIN lms_ujian_siswa us ON us.ujian_id = u.id AND us.siswa_id = ?
            WHERE u.kelas_id = ? AND u.is_aktif = 1
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$siswa_id, $kelas_id]);
        return $stmt->fetchAll();
    }

    public function startOrResumeUjian(int $ujian_id, int $siswa_id): array
    {
        $ujian = $this->getById($ujian_id);
        if (!$ujian || (int)$ujian['is_aktif'] !== 1) {
            return ['success' => false, 'message' => 'Ujian tidak aktif atau tidak ditemukan.'];
        }

        // Cek sesi ujian siswa
        $stmt = $this->db->prepare("SELECT * FROM lms_ujian_siswa WHERE ujian_id = ? AND siswa_id = ?");
        $stmt->execute([$ujian_id, $siswa_id]);
        $sesi = $stmt->fetch();

        $durasiDetik = (int)$ujian['durasi_menit'] * 60;

        if (!$sesi) {
            // Buat sesi baru
            $stmt = $this->db->prepare("
                INSERT INTO lms_ujian_siswa (ujian_id, siswa_id, waktu_mulai, sisa_detik, status)
                VALUES (?, ?, NOW(), ?, 'sedang_mengerjakan')
            ");
            $stmt->execute([$ujian_id, $siswa_id, $durasiDetik]);
            $sesiId = (int)$this->db->lastInsertId();

            $stmt = $this->db->prepare("SELECT * FROM lms_ujian_siswa WHERE id = ?");
            $stmt->execute([$sesiId]);
            $sesi = $stmt->fetch();
        } else {
            // Jika sudah selesai
            if ($sesi['status'] !== 'sedang_mengerjakan') {
                return [
                    'success' => false,
                    'status' => $sesi['status'],
                    'message' => 'Anda sudah menyelesaikan ujian ini.',
                    'nilai_akhir' => $sesi['nilai_akhir']
                ];
            }

            // Hitung real-time countdown berdasarkan waktu mulai jika sisa_detik belum pernah disinkronkan
            $waktuMulai = strtotime($sesi['waktu_mulai']);
            $lewatDetik = time() - $waktuMulai;
            $sisaReal = max(0, $durasiDetik - $lewatDetik);

            if ($sisaReal <= 0) {
                // Waktu habis otomatis
                $this->submitUjianSiswa((int)$sesi['id'], true);
                return [
                    'success' => false,
                    'status' => 'waktu_habis',
                    'message' => 'Waktu pengerjaan telah habis!'
                ];
            }

            // Update sisa detik
            $stmt = $this->db->prepare("UPDATE lms_ujian_siswa SET sisa_detik = ? WHERE id = ?");
            $stmt->execute([$sisaReal, $sesi['id']]);
            $sesi['sisa_detik'] = $sisaReal;
        }

        // Ambil butir soal (tanpa kunci jawaban!)
        $soalList = $this->getSoalByUjian($ujian_id);
        $sanitizedSoal = [];
        foreach ($soalList as $s) {
            $sanitizedSoal[] = [
                'id' => (int)$s['id'],
                'nomor_urut' => (int)$s['nomor_urut'],
                'tipe_soal' => $s['tipe_soal'],
                'pertanyaan' => $s['pertanyaan'],
                'gambar' => $s['gambar'] ? App::baseUrl($s['gambar']) : null,
                'pilihan_a' => $s['pilihan_a'],
                'pilihan_b' => $s['pilihan_b'],
                'pilihan_c' => $s['pilihan_c'],
                'pilihan_d' => $s['pilihan_d'],
                'pilihan_e' => $s['pilihan_e'],
                'bobot_nilai' => (float)$s['bobot_nilai']
            ];
        }

        // Ambil jawaban yang tersimpan
        $stmt = $this->db->prepare("SELECT soal_id, jawaban_pg, jawaban_essay, is_ragu FROM lms_ujian_jawaban WHERE ujian_siswa_id = ?");
        $stmt->execute([$sesi['id']]);
        $jawabanMap = [];
        while ($row = $stmt->fetch()) {
            $jawabanMap[(int)$row['soal_id']] = [
                'jawaban_pg' => $row['jawaban_pg'],
                'jawaban_essay' => $row['jawaban_essay'],
                'is_ragu' => (int)$row['is_ragu']
            ];
        }

        return [
            'success' => true,
            'ujian' => [
                'id' => (int)$ujian['id'],
                'judul' => $ujian['judul'],
                'nama_mapel' => $ujian['nama_mapel'],
                'nama_guru' => $ujian['nama_guru'],
                'durasi_menit' => (int)$ujian['durasi_menit'],
                'kkm' => (float)$ujian['kkm']
            ],
            'sesi' => [
                'id' => (int)$sesi['id'],
                'sisa_detik' => (int)$sesi['sisa_detik'],
                'status' => $sesi['status']
            ],
            'soal' => $sanitizedSoal,
            'jawaban_tersimpan' => $jawabanMap
        ];
    }

    public function saveJawaban(int $ujian_siswa_id, int $soal_id, ?string $jawaban_pg, ?string $jawaban_essay, int $is_ragu = 0, ?int $sisa_detik = null): bool
    {
        // 1. Cek sesi
        $stmt = $this->db->prepare("SELECT status FROM lms_ujian_siswa WHERE id = ?");
        $stmt->execute([$ujian_siswa_id]);
        $status = $stmt->fetchColumn();
        if ($status !== 'sedang_mengerjakan') {
            return false;
        }

        // Cek kunci soal untuk menghitung is_benar jika PG
        $stmt = $this->db->prepare("SELECT tipe_soal, kunci_jawaban, bobot_nilai FROM lms_soal WHERE id = ?");
        $stmt->execute([$soal_id]);
        $soal = $stmt->fetch();

        $isBenar = 0;
        $nilaiButir = 0;

        if ($soal && $soal['tipe_soal'] === 'pilihan_ganda') {
            if (!empty($jawaban_pg) && strtoupper($jawaban_pg) === strtoupper($soal['kunci_jawaban'])) {
                $isBenar = 1;
                $nilaiButir = (float)$soal['bobot_nilai'];
            }
        }

        // 2. Simpan atau perbarui lms_ujian_jawaban
        $stmt = $this->db->prepare("
            INSERT INTO lms_ujian_jawaban (ujian_siswa_id, soal_id, jawaban_pg, jawaban_essay, is_ragu, is_benar, nilai_butir)
            VALUES (:us_id, :soal_id, :jawaban_pg, :jawaban_essay, :is_ragu, :is_benar, :nilai_butir)
            ON DUPLICATE KEY UPDATE
                jawaban_pg = VALUES(jawaban_pg),
                jawaban_essay = VALUES(jawaban_essay),
                is_ragu = VALUES(is_ragu),
                is_benar = VALUES(is_benar),
                nilai_butir = VALUES(nilai_butir),
                updated_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            'us_id' => $ujian_siswa_id,
            'soal_id' => $soal_id,
            'jawaban_pg' => $jawaban_pg ? strtoupper(trim($jawaban_pg)) : null,
            'jawaban_essay' => $jawaban_essay ? trim($jawaban_essay) : null,
            'is_ragu' => $is_ragu,
            'is_benar' => $isBenar,
            'nilai_butir' => $nilaiButir
        ]);

        // 3. Update sisa detik jika dikirim dari countdown client
        if ($sisa_detik !== null) {
            $stmt = $this->db->prepare("UPDATE lms_ujian_siswa SET sisa_detik = ? WHERE id = ?");
            $stmt->execute([max(0, $sisa_detik), $ujian_siswa_id]);
        }

        return true;
    }

    public function submitUjianSiswa(int $ujian_siswa_id, bool $isTimeout = false): array
    {
        $statusFinal = $isTimeout ? 'waktu_habis' : 'selesai';

        $stmt = $this->db->prepare("
            UPDATE lms_ujian_siswa
            SET status = ?, waktu_selesai = NOW(), sisa_detik = 0
            WHERE id = ?
        ");
        $stmt->execute([$statusFinal, $ujian_siswa_id]);

        // Hitung nilai akhir otomatis
        $this->recalculateNilaiAkhir($ujian_siswa_id);

        $stmt = $this->db->prepare("SELECT * FROM lms_ujian_siswa WHERE id = ?");
        $stmt->execute([$ujian_siswa_id]);
        $res = $stmt->fetch();

        return [
            'success' => true,
            'status' => $statusFinal,
            'nilai_akhir' => (float)($res['nilai_akhir'] ?? 0),
            'nilai_pg' => (float)($res['nilai_pg'] ?? 0),
            'jumlah_benar' => (int)($res['jumlah_benar'] ?? 0),
            'jumlah_salah' => (int)($res['jumlah_salah'] ?? 0)
        ];
    }
}

<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class NilaiSiswa
{
    /**
     * Tentukan Predikat dan Deskripsi Asesmen berdasarkan Nilai Akhir
     */
    public static function getPredikatDanDeskripsi(float $na): array
    {
        if ($na >= 86.0) {
            $predikat = 'A';
            $deskripsi = 'Menunjukkan penguasaan kompetensi yang sangat optimal dan melampaui kriteria ketuntasan.';
        } elseif ($na >= 71.0) {
            $predikat = 'B';
            $deskripsi = 'Menunjukkan penguasaan kompetensi yang baik dan telah mencapai kriteria ketuntasan.';
        } elseif ($na >= 56.0) {
            $predikat = 'C';
            $deskripsi = 'Menunjukkan penguasaan kompetensi yang cukup, namun perlu bimbingan dan penguatan materi.';
        } else {
            $predikat = 'D';
            $deskripsi = 'Belum mencapai kriteria ketuntasan minimal, memerlukan remedial intensif.';
        }

        return [
            'predikat' => $predikat,
            'capaian_kompetensi' => $deskripsi
        ];
    }

    /**
     * Kalkulasi Nilai Rapor Semester Berjalan dari Komponen KBM:
     * Tugas/PR, Ulangan Harian (UH), UTS (Tengah Semester), dan UAS (Akhir Semester)
     * 
     * Formulasi Akademik Standar:
     * Rata-rata Harian = (Tugas + UH) / 2
     * Nilai Rapor Jadi (NA) = ((2 * Rata-rata Harian) + UTS + UAS) / 4
     */
    public static function hitungNilaiRaporKBM(float $tugas, float $uh, float $uts, float $uas): array
    {
        // Hitung rata-rata harian
        $tugasAda = $tugas > 0;
        $uhAda = $uh > 0;

        if ($tugasAda && $uhAda) {
            $rataHarian = ($tugas + $uh) / 2;
        } elseif ($tugasAda) {
            $rataHarian = $tugas;
        } elseif ($uhAda) {
            $rataHarian = $uh;
        } else {
            $rataHarian = 0.0;
        }

        // Hitung Nilai Rapor Akhir
        if ($rataHarian > 0 && $uts > 0 && $uas > 0) {
            $na = round(((2 * $rataHarian) + $uts + $uas) / 4, 2);
        } elseif ($rataHarian > 0 && ($uts > 0 || $uas > 0)) {
            $bobotHarian = 2 * $rataHarian;
            $bobotLain = ($uts > 0 ? $uts : 0) + ($uas > 0 ? $uas : 0);
            $pembagi = 2 + ($uts > 0 ? 1 : 0) + ($uas > 0 ? 1 : 0);
            $na = round(($bobotHarian + $bobotLain) / $pembagi, 2);
        } elseif ($rataHarian > 0) {
            $na = round($rataHarian, 2);
        } elseif ($uts > 0 || $uas > 0) {
            $na = round((($uts > 0 ? $uts : 0) + ($uas > 0 ? $uas : 0)) / (($uts > 0 ? 1 : 0) + ($uas > 0 ? 1 : 0)), 2);
        } else {
            $na = 0.0;
        }

        $info = self::getPredikatDanDeskripsi($na);

        return [
            'rata_harian' => round($rataHarian, 2),
            'nilai_akhir' => $na,
            'predikat' => $info['predikat'],
            'capaian_kompetensi' => $info['capaian_kompetensi']
        ];
    }

    /**
     * Method kompatibilitas untuk form Permendikbud / Sumatif Materi + SAS
     */
    public static function hitungNilaiAkhir(float $sumatifMateri, float $sas, float $formatif = 0.0): array
    {
        if ($sas > 0) {
            $na = round(($sumatifMateri * 0.60) + ($sas * 0.40), 2);
        } else {
            $na = round($sumatifMateri > 0 ? $sumatifMateri : $formatif, 2);
        }

        $info = self::getPredikatDanDeskripsi($na);

        return [
            'nilai_akhir' => $na,
            'predikat' => $info['predikat'],
            'capaian_kompetensi' => $info['capaian_kompetensi']
        ];
    }

    /**
     * Simpan Nilai Detail Semester Berjalan (Guru / Admin KBM)
     */
    public static function saveNilaiKBM(
        int $siswaId,
        int $mapelId,
        int $semesterKe,
        float $tugas,
        float $uh,
        float $uts,
        float $uas,
        ?int $tapelId = null,
        ?string $capaianKustom = null
    ): bool {
        $db = Database::getConnection();
        $calc = self::hitungNilaiRaporKBM($tugas, $uh, $uts, $uas);

        $formatif = $calc['rata_harian'];
        $sumatifMateri = $uts > 0 ? $uts : $calc['rata_harian'];
        $sas = $uas;
        $na = $calc['nilai_akhir'];
        $predikat = $calc['predikat'];
        $capaian = !empty($capaianKustom) ? $capaianKustom : $calc['capaian_kompetensi'];

        $stmt = $db->prepare("
            INSERT INTO `nilai_siswa` 
            (`siswa_id`, `mapel_id`, `semester_ke`, `tahun_pelajaran_id`, 
             `nilai_tugas`, `nilai_uh`, `nilai_uts`, `nilai_uas`,
             `nilai_formatif`, `nilai_sumatif_materi`, `nilai_sas`, `nilai_akhir`, `predikat`, `capaian_kompetensi`, `is_manual`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
            ON DUPLICATE KEY UPDATE 
                `tahun_pelajaran_id` = VALUES(`tahun_pelajaran_id`),
                `nilai_tugas` = VALUES(`nilai_tugas`),
                `nilai_uh` = VALUES(`nilai_uh`),
                `nilai_uts` = VALUES(`nilai_uts`),
                `nilai_uas` = VALUES(`nilai_uas`),
                `nilai_formatif` = VALUES(`nilai_formatif`),
                `nilai_sumatif_materi` = VALUES(`nilai_sumatif_materi`),
                `nilai_sas` = VALUES(`nilai_sas`),
                `nilai_akhir` = VALUES(`nilai_akhir`),
                `predikat` = VALUES(`predikat`),
                `capaian_kompetensi` = VALUES(`capaian_kompetensi`),
                `is_manual` = 0
        ");
        return $stmt->execute([
            $siswaId, $mapelId, $semesterKe, $tapelId,
            $tugas, $uh, $uts, $uas,
            $formatif, $sumatifMateri, $sas, $na, $predikat, $capaian
        ]);
    }

    /**
     * Backward-compatible saveNilai
     */
    public static function saveNilai(
        int $siswaId,
        int $mapelId,
        int $semesterKe,
        float $formatif,
        float $sumatifMateri,
        float $sas,
        ?int $tapelId = null,
        ?string $capaianKustom = null
    ): bool {
        return self::saveNilaiKBM(
            $siswaId, $mapelId, $semesterKe,
            $formatif, $formatif, $sumatifMateri, $sas,
            $tapelId, $capaianKustom
        );
    }

    /**
     * Simpan Nilai Manual Hasil Rapor Semester Lalu (Admin TU di Buku Induk)
     */
    public static function saveNilaiManualRaport(
        int $siswaId,
        int $semesterKe,
        array $mapelNilai,
        ?int $tapelId = null
    ): int {
        $db = Database::getConnection();
        $savedCount = 0;

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

        foreach ($mapelNilai as $mapelId => $item) {
            $na = is_array($item) ? (float)($item['nilai_akhir'] ?? 0) : (float)$item;
            if ($na <= 0) continue;

            $capaian = is_array($item) ? trim($item['capaian'] ?? '') : '';
            $info = self::getPredikatDanDeskripsi($na);
            $predikat = $info['predikat'];
            if (empty($capaian)) {
                $capaian = $info['capaian_kompetensi'];
            }

            $stmt->execute([
                $siswaId, (int)$mapelId, $semesterKe, $tapelId,
                $na, $na, $na, $na,
                $na, $na, $na, $na, $predikat, $capaian
            ]);
            $savedCount++;
        }

        return $savedCount;
    }

    /**
     * Ambil seluruh nilai siswa untuk 1 semester tertentu
     */
    public static function getNilaiBySiswaSemester(int $siswaId, int $semesterKe): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `nilai_siswa` WHERE `siswa_id` = ? AND `semester_ke` = ?");
        $stmt->execute([$siswaId, $semesterKe]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil Nilai Kelas dan Mapel untuk Penilaian Semester Berjalan
     */
    public static function getNilaiKelasMapel(int $kelasId, int $mapelId, int $semesterKe): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT s.id as siswa_id, s.nisn, s.nama_siswa,
                   ns.id as nilai_id, ns.nilai_tugas, ns.nilai_uh, ns.nilai_uts, ns.nilai_uas,
                   ns.nilai_formatif, ns.nilai_sumatif_materi, ns.nilai_sas, ns.nilai_akhir, 
                   ns.predikat, ns.capaian_kompetensi, ns.is_manual
            FROM `siswa` s
            LEFT JOIN `nilai_siswa` ns ON s.id = ns.siswa_id AND ns.mapel_id = ? AND ns.semester_ke = ?
            WHERE s.kelas_id = ?
            ORDER BY s.nama_siswa ASC
        ");
        $stmt->execute([$mapelId, $semesterKe, $kelasId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil Riwayat Nilai Lengkap Siswa untuk Buku Induk (Semua Mapel Semester 1 - 6)
     */
    public static function getRiwayatNilaiSiswaSemuaSemester(int $siswaId): array
    {
        $db = Database::getConnection();
        
        // Ambil mapel yang pernah di-plot untuk kelas siswa ini (sekarang & historis), 
        // atau yang memang sudah ada nilainya secara manual
        $stmtMapel = $db->prepare("
            SELECT DISTINCT m.*
            FROM mata_pelajaran m
            WHERE m.id IN (
                SELECT mapel_id FROM kelas_mapel WHERE kelas_id IN (
                    SELECT kelas_id FROM siswa WHERE id = :sid1
                    UNION 
                    SELECT kelas_id FROM riwayat_kelas_siswa WHERE siswa_id = :sid2
                )
            ) OR m.id IN (
                SELECT mapel_id FROM nilai_siswa WHERE siswa_id = :sid3
            )
            ORDER BY FIELD(m.kelompok, 'Umum', 'Kejuruan', 'Muatan Lokal', 'Pilihan'), m.nama_mapel ASC
        ");
        $stmtMapel->execute([':sid1' => $siswaId, ':sid2' => $siswaId, ':sid3' => $siswaId]);
        $allMapel = $stmtMapel->fetchAll();

        $stmt = $db->prepare("
            SELECT `mapel_id`, `semester_ke`, `nilai_tugas`, `nilai_uh`, `nilai_uts`, `nilai_uas`, 
                   `nilai_akhir`, `predikat`, `capaian_kompetensi`, `is_manual`
            FROM `nilai_siswa`
            WHERE `siswa_id` = ?
        ");
        $stmt->execute([$siswaId]);
        $rows = $stmt->fetchAll();

        $matrix = [];
        foreach ($rows as $r) {
            $matrix[$r['mapel_id']][$r['semester_ke']] = [
                'na' => (float)$r['nilai_akhir'],
                'nilai_akhir' => (float)$r['nilai_akhir'],
                'predikat' => $r['predikat'],
                'tugas' => (float)$r['nilai_tugas'],
                'uh' => (float)$r['nilai_uh'],
                'uts' => (float)$r['nilai_uts'],
                'uas' => (float)$r['nilai_uas'],
                'is_manual' => (int)$r['is_manual']
            ];
        }

        $daftarMapel = [];
        $semesterTotals = [1 => 0.0, 2 => 0.0, 3 => 0.0, 4 => 0.0, 5 => 0.0, 6 => 0.0];
        $semesterCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];

        foreach ($allMapel as $m) {
            $mid = $m['id'];
            $semValues = [];
            $sumMapel = 0.0;
            $countMapel = 0;

            for ($sem = 1; $sem <= 6; $sem++) {
                $item = $matrix[$mid][$sem] ?? null;
                $semValues[$sem] = $item;
                if ($item !== null && $item['na'] > 0) {
                    $sumMapel += $item['na'];
                    $countMapel++;
                    $semesterTotals[$sem] += $item['na'];
                    $semesterCounts[$sem]++;
                }
            }

            $rataMapel = $countMapel > 0 ? round($sumMapel / $countMapel, 2) : 0.0;

            $daftarMapel[] = [
                'mapel_id' => $mid,
                'kode_mapel' => $m['kode_mapel'],
                'nama_mapel' => $m['nama_mapel'],
                'kelompok' => $m['kelompok'],
                'semesters' => $semValues,
                'rata_rata' => $rataMapel,
                'rata_mapel' => $rataMapel
            ];
        }

        // Hitung rata-rata per semester
        $semesterAverages = [];
        $sumSemAverages = 0.0;
        $countSemActive = 0;

        for ($sem = 1; $sem <= 6; $sem++) {
            $avg = $semesterCounts[$sem] > 0 ? round($semesterTotals[$sem] / $semesterCounts[$sem], 2) : 0.0;
            $semesterAverages[$sem] = $avg;
            if ($avg > 0) {
                $sumSemAverages += $avg;
                $countSemActive++;
            }
        }

        $rataRataKumulatif = $countSemActive > 0 ? round($sumSemAverages / $countSemActive, 2) : 0.0;
        $totalNilaiTerisi = array_sum($semesterCounts);

        return [
            'daftar_mapel' => $daftarMapel,
            'mapel_matrix' => $daftarMapel,
            'semester_averages' => $semesterAverages,
            'rata_semester' => $semesterAverages,
            'semester_counts' => $semesterCounts,
            'rata_rata_kumulatif' => $rataRataKumulatif,
            'rata_kumulatif' => $rataRataKumulatif,
            'total_nilai_terisi' => $totalNilaiTerisi
        ];
    }

    /**
     * Transkrip Kumulatif Lengkap Semester 1-6
     */
    public static function getTranskripLengkap(int $siswaId): array
    {
        $riwayat = self::getRiwayatNilaiSiswaSemuaSemester($siswaId);
        $transkrip = [];

        foreach ($riwayat['daftar_mapel'] as $m) {
            $semNA = [];
            for ($sem = 1; $sem <= 6; $sem++) {
                $semNA[$sem] = isset($m['semesters'][$sem]['na']) ? $m['semesters'][$sem]['na'] : 0.0;
            }

            $transkrip[] = [
                'mapel' => ['id' => $m['mapel_id'], 'nama_mapel' => $m['nama_mapel'], 'kelompok' => $m['kelompok']],
                'nama_mapel' => $m['nama_mapel'],
                'kelompok' => $m['kelompok'],
                'semester' => $m['semesters'],
                'semesters' => $semNA,
                'rata_rata' => $m['rata_rata']
            ];
        }

        return [
            'transkrip' => $transkrip,
            'rata_rata_kumulatif' => $riwayat['rata_rata_kumulatif'],
            'ipk_kumulatif' => $riwayat['rata_rata_kumulatif'],
            'semester_averages' => $riwayat['semester_averages']
        ];
    }

    /**
     * Format Khusus Transkrip Ijazah SMK Sesuai Juknis Kemendikbudristek
     * (Rata-rata Rapor Semester 1-5 atau 1-6 + Nilai Ujian Sekolah -> Nilai Ijazah)
     */
    public static function getTranskripIjazah(int $siswaId): array
    {
        $riwayat = self::getRiwayatNilaiSiswaSemuaSemester($siswaId);
        $allMapel = MataPelajaran::getAll();

        $rowsIjazah = [];
        $totalNilaiRapor = 0.0;
        $totalNilaiUjian = 0.0;
        $totalNilaiIjazah = 0.0;
        $countMapel = 0;

        foreach ($riwayat['daftar_mapel'] as $m) {
            $rataRapor = $m['rata_rata'];
            
            // Jika ada nilai rapor
            if ($rataRapor > 0) {
                // Nilai Ujian Sekolah (default seimbang dengan rapor jika belum ada ujian khusus)
                $nilaiUjian = $rataRapor; 
                // Nilai Ijazah: 60% Rata-rata Rapor + 40% Ujian Sekolah
                $nilaiIjazah = round((0.60 * $rataRapor) + (0.40 * $nilaiUjian), 2);

                $totalNilaiRapor += $rataRapor;
                $totalNilaiUjian += $nilaiUjian;
                $totalNilaiIjazah += $nilaiIjazah;
                $countMapel++;
            } else {
                $nilaiUjian = 0.0;
                $nilaiIjazah = 0.0;
            }

            $rowsIjazah[] = [
                'nama_mapel' => $m['nama_mapel'],
                'kode_mapel' => $m['kode_mapel'],
                'kelompok' => $m['kelompok'],
                'rata_rapor' => $rataRapor,
                'nilai_ujian' => $nilaiUjian,
                'nilai_ijazah' => $nilaiIjazah
            ];
        }

        $rataRaporTotal = $countMapel > 0 ? round($totalNilaiRapor / $countMapel, 2) : 0.0;
        $rataUjianTotal = $countMapel > 0 ? round($totalNilaiUjian / $countMapel, 2) : 0.0;
        $rataIjazahTotal = $countMapel > 0 ? round($totalNilaiIjazah / $countMapel, 2) : 0.0;

        return [
            'daftar_mapel' => $rowsIjazah,
            'rata_rapor_total' => $rataRaporTotal,
            'rata_ujian_total' => $rataUjianTotal,
            'rata_ijazah_total' => $rataIjazahTotal,
            'count_mapel' => $countMapel
        ];
    }
}

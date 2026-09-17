<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class PresensiMapel
{
    /**
     * Menyimpan batch presensi siswa pada sesi mengajar
     * $presensiData format: [siswa_id => ['status' => 'HADIR'|'SAKIT'|'IZIN'|'ALPHA', 'catatan' => '...']]
     */
    public static function saveBatch(int $sesiMengajarId, array $presensiData): bool
    {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $sql = "INSERT INTO presensi_mapel_siswa (sesi_mengajar_id, siswa_id, status, catatan)
                    VALUES (:sesi_id, :siswa_id, :status, :catatan)
                    ON DUPLICATE KEY UPDATE status = VALUES(status), catatan = VALUES(catatan)";
            $stmt = $db->prepare($sql);

            foreach ($presensiData as $siswaId => $item) {
                $status = in_array($item['status'] ?? '', ['HADIR', 'SAKIT', 'IZIN', 'ALPHA']) ? $item['status'] : 'HADIR';
                $catatan = $item['catatan'] ?? null;

                $stmt->execute([
                    ':sesi_id' => $sesiMengajarId,
                    ':siswa_id' => (int)$siswaId,
                    ':status' => $status,
                    ':catatan' => $catatan,
                ]);
            }

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Mengambil presensi siswa untuk sesi mengajar tertentu
     */
    public static function getBySesi(int $sesiMengajarId, int $kelasId): array
    {
        $db = Database::getConnection();
        $sql = "SELECT s.id as siswa_id, s.nama_siswa, s.nisn, s.barcode_code, s.foto, s.jenis_kelamin,
                       pm.id as mapel_presensi_id,
                       COALESCE(pm.status, 'HADIR') as status,
                       pm.catatan
                FROM siswa s
                LEFT JOIN presensi_mapel_siswa pm ON pm.siswa_id = s.id AND pm.sesi_mengajar_id = :sesi_id
                WHERE s.kelas_id = :kid
                ORDER BY s.nama_siswa ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':sesi_id' => $sesiMengajarId, ':kid' => $kelasId]);
        return $stmt->fetchAll();
    }

    /**
     * Evaluasi Ketuntasan Mapel Harian Siswa:
     * Jika pada hari tersebut seorang siswa memiliki minimal 1 (satu) catatan tidak hadir
     * (Sakit, Izin, atau Alpha) pada salah satu sesi mapel, status rekapitulasi mapel harian
     * siswa tersebut otomatis dinyatakan TIDAK HADIR / TIDAK TUNTAS.
     */
    public static function getKetuntasanHarian(string $tanggal, ?int $kelasId = null): array
    {
        $db = Database::getConnection();
        $conditions = ["s.id IS NOT NULL"];
        $params = [':tgl' => $tanggal];

        if ($kelasId) {
            $conditions[] = "s.kelas_id = :kid";
            $params[':kid'] = $kelasId;
        }

        $where = implode(" AND ", $conditions);

        $sql = "SELECT s.id as siswa_id, s.nama_siswa, s.nisn, s.foto, k.nama_kelas,
                       COUNT(pm.id) as total_sesi_tercatat,
                       SUM(CASE WHEN pm.status = 'HADIR' THEN 1 ELSE 0 END) as count_hadir,
                       SUM(CASE WHEN pm.status = 'SAKIT' THEN 1 ELSE 0 END) as count_sakit,
                       SUM(CASE WHEN pm.status = 'IZIN' THEN 1 ELSE 0 END) as count_izin,
                       SUM(CASE WHEN pm.status = 'ALPHA' THEN 1 ELSE 0 END) as count_alpha,
                       CASE 
                           WHEN COUNT(pm.id) = 0 THEN 'BELUM_ADA_SESI'
                           WHEN SUM(CASE WHEN pm.status IN ('SAKIT', 'IZIN', 'ALPHA') THEN 1 ELSE 0 END) > 0 
                               THEN 'TIDAK TUNTAS / TIDAK HADIR'
                           ELSE 'TUNTAS / HADIR LENGKAP'
                       END as status_ketuntasan
                FROM siswa s
                JOIN kelas k ON k.id = s.kelas_id
                LEFT JOIN (
                    SELECT pm_sub.id, pm_sub.siswa_id, pm_sub.status, pm_sub.catatan
                    FROM presensi_mapel_siswa pm_sub
                    JOIN sesi_mengajar_guru sm_sub ON sm_sub.id = pm_sub.sesi_mengajar_id
                    WHERE sm_sub.tanggal = :tgl
                ) pm ON pm.siswa_id = s.id
                WHERE {$where}
                GROUP BY s.id, k.nama_kelas
                ORDER BY k.nama_kelas ASC, s.nama_siswa ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Rincian Lengkap Pelajaran Hari Ini & Status Masuk/Tidak Masuk Siswa Per Mapel
     */
    public static function getRincianMapelHarian(string $tanggal, ?int $kelasId = null): array
    {
        $db = Database::getConnection();
        $hariIndo = \App\Helpers\TimeHelper::getDayName($tanggal);

        // 1. Ambil seluruh jadwal mapel pada hari ini
        $jadwalConditions = ["j.hari = :hari"];
        $jadwalParams = [':hari' => $hariIndo, ':tgl' => $tanggal];

        if ($kelasId) {
            $jadwalConditions[] = "j.kelas_id = :kid";
            $jadwalParams[':kid'] = $kelasId;
        }

        $whereJadwal = implode(" AND ", $jadwalConditions);

        $sqlJadwal = "SELECT j.id as jadwal_id, j.kelas_id, j.nama_mapel, j.jam_mulai, j.jam_selesai, j.jumlah_jp,
                             k.nama_kelas, k.tingkat, k.jurusan,
                             g.nama_lengkap as nama_guru, g.nik_nip,
                             sm.id as sesi_id, sm.waktu_checkin, sm.waktu_checkout, sm.catatan_guru
                      FROM jadwal_pelajaran j
                      JOIN kelas k ON k.id = j.kelas_id
                      JOIN guru g ON g.id = j.guru_id
                      LEFT JOIN sesi_mengajar_guru sm ON sm.jadwal_id = j.id AND sm.tanggal = :tgl
                      WHERE {$whereJadwal}
                      ORDER BY k.nama_kelas ASC, j.jam_mulai ASC";

        $stmtJadwal = $db->prepare($sqlJadwal);
        $stmtJadwal->execute($jadwalParams);
        $jadwalRows = $stmtJadwal->fetchAll(PDO::FETCH_ASSOC);

        $jadwalByKelas = [];
        $jadwalList = [];
        foreach ($jadwalRows as $j) {
            if (empty($j['sesi_id'])) {
                $j['status_sesi'] = 'BELUM_MULAI';
            } elseif (!empty($j['waktu_checkout'])) {
                $j['status_sesi'] = 'SELESAI';
            } else {
                $j['status_sesi'] = 'SEDANG_BERLANGSUNG';
            }
            $jadwalByKelas[$j['kelas_id']][] = $j;
            $jadwalList[] = $j;
        }

        // 2. Ambil catatan presensi siswa pada sesi mengajar hari ini
        $sqlAtt = "SELECT pm.sesi_mengajar_id, pm.siswa_id, pm.status, pm.catatan, sm.jadwal_id
                   FROM presensi_mapel_siswa pm
                   JOIN sesi_mengajar_guru sm ON sm.id = pm.sesi_mengajar_id
                   WHERE sm.tanggal = :tgl";
        $stmtAtt = $db->prepare($sqlAtt);
        $stmtAtt->execute([':tgl' => $tanggal]);
        $attRows = $stmtAtt->fetchAll(PDO::FETCH_ASSOC);

        $attBySiswaAndJadwal = [];
        foreach ($attRows as $a) {
            $attBySiswaAndJadwal[$a['siswa_id']][$a['jadwal_id']] = $a;
        }

        // 3. Ambil daftar siswa
        $siswaConditions = ["s.id IS NOT NULL"];
        $siswaParams = [];
        if ($kelasId) {
            $siswaConditions[] = "s.kelas_id = :kid";
            $siswaParams[':kid'] = $kelasId;
        }
        $whereSiswa = implode(" AND ", $siswaConditions);

        $sqlSiswa = "SELECT s.id as siswa_id, s.nama_siswa, s.nisn, s.foto, s.kelas_id, k.nama_kelas
                     FROM siswa s
                     JOIN kelas k ON k.id = s.kelas_id
                     WHERE {$whereSiswa}
                     ORDER BY k.nama_kelas ASC, s.nama_siswa ASC";
        $stmtSiswa = $db->prepare($sqlSiswa);
        $stmtSiswa->execute($siswaParams);
        $siswaRows = $stmtSiswa->fetchAll(PDO::FETCH_ASSOC);

        // 4. Petakan rincian mata pelajaran hari ini untuk setiap siswa
        $siswaList = [];
        foreach ($siswaRows as $s) {
            $kid = (int)$s['kelas_id'];
            $jadwals = $jadwalByKelas[$kid] ?? [];

            $mapelList = [];
            $countHadir = 0;
            $countSakit = 0;
            $countIzin = 0;
            $countAlpha = 0;
            $countSesiBerjalan = 0;

            foreach ($jadwals as $j) {
                $jid = (int)$j['jadwal_id'];
                $att = $attBySiswaAndJadwal[$s['siswa_id']][$jid] ?? null;

                $item = [
                    'jadwal_id' => $jid,
                    'nama_mapel' => $j['nama_mapel'],
                    'jam_mulai' => $j['jam_mulai'],
                    'jam_selesai' => $j['jam_selesai'],
                    'nama_guru' => $j['nama_guru'],
                    'jumlah_jp' => $j['jumlah_jp'],
                    'sesi_id' => $j['sesi_id'],
                    'status_sesi' => $j['status_sesi'],
                ];

                if (!empty($j['sesi_id'])) {
                    $countSesiBerjalan++;
                    if ($att) {
                        $status = $att['status'];
                        $item['catatan'] = $att['catatan'];
                    } else {
                        $status = 'HADIR';
                        $item['catatan'] = null;
                    }

                    $item['status_kehadiran'] = $status;
                    if ($status === 'HADIR') {
                        $countHadir++;
                        $item['is_masuk'] = true;
                        $item['label_masuk'] = 'Masuk (Hadir)';
                        $item['badge_theme'] = 'emerald';
                    } elseif ($status === 'SAKIT') {
                        $countSakit++;
                        $item['is_masuk'] = false;
                        $item['label_masuk'] = 'Tidak Masuk (Sakit)';
                        $item['badge_theme'] = 'amber';
                    } elseif ($status === 'IZIN') {
                        $countIzin++;
                        $item['is_masuk'] = false;
                        $item['label_masuk'] = 'Tidak Masuk (Izin)';
                        $item['badge_theme'] = 'sky';
                    } elseif ($status === 'ALPHA') {
                        $countAlpha++;
                        $item['is_masuk'] = false;
                        $item['label_masuk'] = 'Tidak Masuk (Alpa)';
                        $item['badge_theme'] = 'rose';
                    }
                } else {
                    $item['status_kehadiran'] = 'BELUM_MULAI';
                    $item['is_masuk'] = null;
                    $item['label_masuk'] = 'Belum Dimulai';
                    $item['badge_theme'] = 'slate';
                    $item['catatan'] = null;
                }

                $mapelList[] = $item;
            }

            if ($countSesiBerjalan === 0) {
                $statusKetuntasan = 'BELUM_ADA_SESI';
            } elseif (($countSakit + $countIzin + $countAlpha) > 0) {
                $statusKetuntasan = 'TIDAK TUNTAS / TIDAK HADIR';
            } else {
                $statusKetuntasan = 'TUNTAS / HADIR LENGKAP';
            }

            $s['mapel_list'] = $mapelList;
            $s['summary'] = [
                'total_jadwal' => count($jadwals),
                'total_sesi_berjalan' => $countSesiBerjalan,
                'count_hadir' => $countHadir,
                'count_sakit' => $countSakit,
                'count_izin' => $countIzin,
                'count_alpha' => $countAlpha,
                'status_ketuntasan' => $statusKetuntasan,
            ];

            $siswaList[] = $s;
        }

        return [
            'hari' => $hariIndo,
            'tanggal' => $tanggal,
            'jadwal_list' => $jadwalList,
            'siswa_list' => $siswaList,
        ];
    }
}

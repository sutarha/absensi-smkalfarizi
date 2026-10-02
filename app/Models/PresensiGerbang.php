<?php
namespace App\Models;

use App\Config\Database;
use App\Helpers\WhatsappHelper;
use PDO;

class PresensiGerbang
{
    /**
     * Mencatat Tap-in Datang Siswa
     */
    public static function recordTapIn(int $siswaId, ?string $waktu = null): array
    {
        $db = Database::getConnection();
        $now = $waktu ?: date('Y-m-d H:i:s');
        $today = date('Y-m-d', strtotime($now));
        
        $config = KonfigurasiSekolah::get();
        $jamMasukMulai = $config['jam_gerbang_masuk_mulai'] ?? '06:00:00';
        $jamSekarang = date('H:i:s', strtotime($now));

        if ($jamSekarang < $jamMasukMulai) {
            return [
                'success' => false,
                'is_repeat' => false,
                'message' => "Gerbang Masuk belum dibuka! (Buka: " . substr($jamMasukMulai, 0, 5) . ")"
            ];
        }

        // Cek apakah sudah ada baris presensi hari ini
        $stmt = $db->prepare("SELECT * FROM presensi_gerbang_siswa WHERE siswa_id = :sid AND tanggal = :tgl LIMIT 1");
        $stmt->execute([':sid' => $siswaId, ':tgl' => $today]);
        $existing = $stmt->fetch();

        $jamDatang = date('H:i:s', strtotime($now));
        $newStatus = 'HADIR';
        $ket = 'Tap-In Berhasil';

        if ($jamDatang > '08:00:00') {
            $newStatus = 'ALPHA';
            $ket = 'Gugur Alpha (Datang jam ' . substr($jamDatang, 0, 5) . ', lewat dari 08:00)';
        } elseif ($jamDatang > '06:30:00') {
            $newStatus = 'TERLAMBAT';
            $ket = 'Terlambat (Datang jam ' . substr($jamDatang, 0, 5) . ')';
        }

        if ($existing) {
            // Sudah pernah tap-in sebelumnya?
            if (!empty($existing['waktu_datang'])) {
                return [
                    'success' => true,
                    'is_repeat' => true,
                    'message' => 'Siswa sudah melakukan Tap-In sebelumnya pada jam ' . date('H:i', strtotime($existing['waktu_datang'])),
                    'data' => $existing
                ];
            }

            // Update waktu datang
            $upStmt = $db->prepare("UPDATE presensi_gerbang_siswa SET waktu_datang = :waktu, status_kehadiran = :st, keterangan = :ket WHERE id = :id");
            $upStmt->execute([':waktu' => $now, ':st' => $newStatus, ':ket' => $ket, ':id' => $existing['id']]);
        } else {
            // Insert baru
            $inStmt = $db->prepare("INSERT INTO presensi_gerbang_siswa (siswa_id, tanggal, waktu_datang, status_kehadiran, keterangan) 
                                    VALUES (:sid, :tgl, :waktu, :st, :ket)");
            $inStmt->execute([':sid' => $siswaId, ':tgl' => $today, ':waktu' => $now, ':st' => $newStatus, ':ket' => $ket]);
        }

        // WA Notification (Jika ada no_hp_ortu)
        $siswaStmt = $db->prepare("SELECT nama_siswa, no_hp_ortu FROM siswa WHERE id = ?");
        $siswaStmt->execute([$siswaId]);
        $siswa = $siswaStmt->fetch();
        if ($siswa && !empty($siswa['no_hp_ortu'])) {
            $timeStr = date('H:i', strtotime($now));
            $msg = "INFO SEKOLAH:\nBapak/Ibu, anak Anda *{$siswa['nama_siswa']}* telah tiba di sekolah pada pukul {$timeStr} WIB.";
            if ($newStatus === 'TERLAMBAT') {
                $msg .= "\nStatus: Terlambat.";
            } elseif ($newStatus === 'ALPHA') {
                $msg .= "\nStatus: Alpha (Terlambat melewati jam 08:00).";
            }
            WhatsappHelper::sendMessage($siswa['no_hp_ortu'], $msg);
        }

        return [
            'success' => true,
            'is_repeat' => false,
            'type' => 'DATANG',
            'time' => date('H:i:s', strtotime($now)),
            'message' => 'Tap-In Masuk Berhasil dicatat!'
        ];
    }

    /**
     * Mencatat Tap-out Pulang Siswa
     */
    public static function recordTapOut(int $siswaId, ?string $waktu = null): array
    {
        $db = Database::getConnection();
        $now = $waktu ?: date('Y-m-d H:i:s');
        $today = date('Y-m-d', strtotime($now));

        $stmt = $db->prepare("SELECT * FROM presensi_gerbang_siswa WHERE siswa_id = :sid AND tanggal = :tgl LIMIT 1");
        $stmt->execute([':sid' => $siswaId, ':tgl' => $today]);
        $existing = $stmt->fetch();

        $config = KonfigurasiSekolah::get();
        $isFriday = (date('N', strtotime($now)) == 5);
        
        $jamPulangMulai = $isFriday ? ($config['jam_gerbang_pulang_mulai_jumat'] ?? '11:30:00') : ($config['jam_gerbang_pulang_mulai'] ?? '14:00:00');
        $jamPulangSelesai = $isFriday ? ($config['jam_gerbang_pulang_selesai_jumat'] ?? '13:30:00') : ($config['jam_gerbang_pulang_selesai'] ?? '16:00:00');
        $jamSekarang = date('H:i:s', strtotime($now));

        if ($jamSekarang < $jamPulangMulai) {
            return [
                'success' => false,
                'is_repeat' => false,
                'message' => "Gerbang Pulang belum dibuka! (Buka: " . substr($jamPulangMulai, 0, 5) . ")"
            ];
        }

        if ($existing) {
            if (!empty($existing['waktu_pulang'])) {
                return [
                    'success' => true,
                    'is_repeat' => true,
                    'message' => 'Siswa sudah melakukan Tap-Out kepulangan pada jam ' . date('H:i', strtotime($existing['waktu_pulang'])),
                    'data' => $existing
                ];
            }

            // Jika ada waktu_datang, evaluasi statusnya (06:30 dan 08:00)
            if (!empty($existing['waktu_datang'])) {
                $jamDatang = date('H:i:s', strtotime($existing['waktu_datang']));
                if ($jamDatang > '08:00:00') {
                    $newStatus = 'ALPHA';
                    $ket = 'Gugur Alpha (Datang jam ' . substr($jamDatang, 0, 5) . ', lewat dari 08:00)';
                } elseif ($jamDatang > '06:30:00') {
                    $newStatus = 'TERLAMBAT';
                    $ket = 'Terlambat (Datang jam ' . substr($jamDatang, 0, 5) . ')';
                } else {
                    $newStatus = 'HADIR';
                    $ket = 'Hadir (Tepat Waktu)';
                }
            } else {
                $newStatus = 'ALPHA';
                $ket = 'Gugur Alpha (Tidak melakukan Tap-In Pagi)';
            }
            $upStmt = $db->prepare("UPDATE presensi_gerbang_siswa SET waktu_pulang = :waktu, status_kehadiran = :st, keterangan = :ket WHERE id = :id");
            $upStmt->execute([':waktu' => $now, ':st' => $newStatus, ':ket' => $ket, ':id' => $existing['id']]);
        } else {
            // Tidak ada tap-in, langsung tap-out -> status tetap ALPHA
            $inStmt = $db->prepare("INSERT INTO presensi_gerbang_siswa (siswa_id, tanggal, waktu_pulang, status_kehadiran, keterangan) 
                                    VALUES (:sid, :tgl, :waktu, 'ALPHA', 'Gugur Alpha (Tidak melakukan Tap-In Pagi)')");
            $inStmt->execute([':sid' => $siswaId, ':tgl' => $today, ':waktu' => $now]);
        }

        // WA Notification (Jika ada no_hp_ortu)
        $siswaStmt = $db->prepare("SELECT nama_siswa, no_hp_ortu FROM siswa WHERE id = ?");
        $siswaStmt->execute([$siswaId]);
        $siswa = $siswaStmt->fetch();
        if ($siswa && !empty($siswa['no_hp_ortu'])) {
            $timeStr = date('H:i', strtotime($now));
            $msg = "INFO SEKOLAH:\nBapak/Ibu, anak Anda *{$siswa['nama_siswa']}* telah melakukan Tap-Out pulang pada pukul {$timeStr} WIB. Hati-hati di jalan.";
            WhatsappHelper::sendMessage($siswa['no_hp_ortu'], $msg);
        }

        return [
            'success' => true,
            'is_repeat' => false,
            'type' => 'PULANG',
            'time' => date('H:i:s', strtotime($now)),
            'message' => 'Tap-Out Pulang Berhasil! Status kehadiran terverifikasi.'
        ];
    }

    /**
     * Evaluasi Integritas Harian Siswa:
     * Kunci status menjadi ALPHA bagi siswa yang hanya tap datang atau hanya tap pulang
     */
    public static function runDailyEvaluation(?string $tanggal = null): int
    {
        $db = Database::getConnection();
        $tgl = $tanggal ?: date('Y-m-d');

        $sql = "SELECT s.id as siswa_id, pg.id as presensi_id, pg.waktu_datang, pg.waktu_pulang, pg.status_kehadiran, pg.is_terkunci 
                FROM siswa s 
                LEFT JOIN presensi_gerbang_siswa pg ON pg.siswa_id = s.id AND pg.tanggal = :tgl 
                WHERE s.status = 'AKTIF'";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([':tgl' => $tgl]);
        $siswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ketuntasan = \App\Models\PresensiMapel::getKetuntasanHarian($tgl);
        $mapelSummary = [];
        foreach ($ketuntasan as $k) {
            $mapelSummary[$k['siswa_id']] = $k;
        }

        $affected = 0;
        $db->beginTransaction();
        try {
            $insStmt = $db->prepare("INSERT INTO presensi_gerbang_siswa (siswa_id, tanggal, status_kehadiran, keterangan, is_terkunci, dikunci_oleh, dikunci_at) VALUES (:sid, :tgl, :st, :ket, :lock, :oleh, :at)");
            $updStmt = $db->prepare("UPDATE presensi_gerbang_siswa SET status_kehadiran = :st, keterangan = :ket, is_terkunci = :lock, waktu_pulang = :wp, dikunci_oleh = :oleh, dikunci_at = :at WHERE id = :id");

            foreach ($siswaList as $s) {
                $sid = $s['siswa_id'];
                $sum = $mapelSummary[$sid] ?? ['total_sesi_tercatat' => 0, 'count_hadir' => 0, 'count_sakit' => 0, 'count_izin' => 0, 'count_alpha' => 0];

                // Lewati jika sudah dikunci (manual oleh admin atau dari eval sebelumnya)
                if (!empty($s['is_terkunci'])) {
                    continue;
                }

                $presensiId = $s['presensi_id'];
                $waktuDatang = $s['waktu_datang'];
                $waktuPulang = $s['waktu_pulang'];
                $statusSekarang = $s['status_kehadiran'];

                $newStatus = null;
                $newKet = null;
                $newLock = 0;
                $newWp = $waktuPulang;
                $dikunciOleh = null;
                $dikunciAt = null;

                if (!$presensiId || !$waktuDatang) {
                    // TIDAK TAP-IN
                    if ($sum['count_sakit'] > 0) {
                        $newStatus = 'SAKIT';
                        $newKet = 'Sakit (Berdasarkan Laporan KBM)';
                    } elseif ($sum['count_izin'] > 0) {
                        $newStatus = 'IZIN';
                        $newKet = 'Izin (Berdasarkan Laporan KBM)';
                    } else {
                        $newStatus = 'ALPHA';
                        $newKet = 'Gugur Alpha (Tidak Absen Gerbang)';
                        $newLock = 1;
                        $dikunciOleh = 'Sistem Auto-Eval';
                        $dikunciAt = date('Y-m-d H:i:s');
                    }
                } else {
                    // ADA TAP-IN
                    if (!$waktuPulang) {
                        // TIDAK TAP-OUT
                        if ($sum['count_alpha'] > 0) {
                            $newStatus = 'ALPHA';
                            $newKet = 'Gugur Alpha (Tidak Tap-Out & Ada Sesi KBM Alpha)';
                            $newLock = 1;
                            $dikunciOleh = 'Sistem Auto-Eval';
                            $dikunciAt = date('Y-m-d H:i:s');
                        } elseif ($sum['total_sesi_tercatat'] > 0 && $sum['count_hadir'] == $sum['total_sesi_tercatat']) {
                            // Semua KBM hadir -> Auto-checkout
                            $newStatus = $statusSekarang; // Pertahankan HADIR/TERLAMBAT
                            $newKet = 'Hadir (Auto-Checkout KBM Lengkap)';
                            $newWp = date('Y-m-d 17:00:00'); // Set default tap-out sore
                        } else {
                            $newStatus = 'ALPHA';
                            $newKet = 'Gugur Alpha (Tidak Tap-Out & Sesi KBM Tidak Lengkap)';
                            $newLock = 1;
                            $dikunciOleh = 'Sistem Auto-Eval';
                            $dikunciAt = date('Y-m-d H:i:s');
                        }
                    }
                }

                if ($newStatus) {
                    if (!$presensiId) {
                        $insStmt->execute([
                            ':sid' => $sid,
                            ':tgl' => $tgl,
                            ':st' => $newStatus,
                            ':ket' => $newKet,
                            ':lock' => $newLock,
                            ':oleh' => $dikunciOleh,
                            ':at' => $dikunciAt
                        ]);
                    } else {
                        $updStmt->execute([
                            ':st' => $newStatus,
                            ':ket' => $newKet,
                            ':lock' => $newLock,
                            ':wp' => $newWp,
                            ':oleh' => $dikunciOleh,
                            ':at' => $dikunciAt,
                            ':id' => $presensiId
                        ]);
                    }
                    $affected++;

                    if ($newStatus === 'ALPHA') {
                        $stmtSiswa = $db->prepare("SELECT nama_siswa, no_hp_ortu FROM siswa WHERE id = :sid");
                        $stmtSiswa->execute([':sid' => $sid]);
                        $siswaRow = $stmtSiswa->fetch();
                        if ($siswaRow && !empty($siswaRow['no_hp_ortu'])) {
                            $msg = "INFO SEKOLAH:\nBapak/Ibu, anak Anda *{$siswaRow['nama_siswa']}* tidak tercatat hadir di sekolah pada {$tgl} (Status: ALPHA).\nKeterangan: {$newKet}";
                            WhatsappHelper::sendMessage($siswaRow['no_hp_ortu'], $msg);
                        }
                    }
                }
            }
            $db->commit();
            return $affected;
        } catch (\Exception $e) {
            $db->rollBack();
            return 0;
        }
    }

    /**
     * Mengambil rekapitulasi presensi gerbang berdasarkan tanggal
     */
    public static function getByDate(string $tanggal, ?int $kelasId = null, bool $filterBelumTapOut = false): array
    {
        $db = Database::getConnection();
        $conditions = ["s.id IS NOT NULL"];
        $params = [':tgl' => $tanggal];

        if ($kelasId) {
            $conditions[] = "s.kelas_id = :kid";
            $params[':kid'] = $kelasId;
        }

        if ($filterBelumTapOut) {
            $conditions[] = "pg.waktu_datang IS NOT NULL AND pg.waktu_pulang IS NULL";
        }

        $where = implode(" AND ", $conditions);

        $sql = "SELECT s.id as siswa_id, s.nisn, s.barcode_code, s.nama_siswa, s.foto, s.jenis_kelamin,
                       k.nama_kelas, k.tingkat, k.jurusan,
                       pg.id as presensi_id, pg.tanggal, pg.waktu_datang, pg.waktu_pulang, 
                       COALESCE(pg.status_kehadiran, 'BELUM_PRESENSI') as status_kehadiran,
                       pg.keterangan, pg.is_terkunci, pg.dikunci_oleh, pg.dikunci_at
                FROM siswa s
                JOIN kelas k ON k.id = s.kelas_id
                LEFT JOIN presensi_gerbang_siswa pg ON pg.siswa_id = s.id AND pg.tanggal = :tgl
                WHERE {$where}
                ORDER BY k.nama_kelas ASC, s.nama_siswa ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $gerbangData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Auto-evaluasi: Jika siswa belum absen gerbang, otomatis jadi ALPHA. 
        // KECUALI: jika semua sesi KBM yang sudah berjalan menyatakan dia SAKIT atau IZIN.
        $ketuntasan = \App\Models\PresensiMapel::getKetuntasanHarian($tanggal, $kelasId);
        $mapelSummary = [];
        foreach ($ketuntasan as $k) {
            $mapelSummary[$k['siswa_id']] = $k;
        }

        foreach ($gerbangData as &$g) {
            $sid = $g['siswa_id'];
            $sum = $mapelSummary[$sid] ?? ['total_sesi_tercatat' => 0, 'count_hadir' => 0, 'count_sakit' => 0, 'count_izin' => 0, 'count_alpha' => 0];

            if ($g['status_kehadiran'] === 'BELUM_PRESENSI') {
                $newStatus = 'ALPHA';
                $ket = 'Gugur Alpha (Tidak Absen Gerbang)';
                $isTerkunci = 0; // Virtual status should not be locked, otherwise unlock action fails

                if ($sum['total_sesi_tercatat'] > 0) {
                    if ($sum['count_sakit'] == $sum['total_sesi_tercatat']) {
                        $newStatus = 'SAKIT';
                        $ket = 'Sakit (Berdasarkan Laporan KBM)';
                        $isTerkunci = 0;
                    } elseif ($sum['count_izin'] == $sum['total_sesi_tercatat']) {
                        $newStatus = 'IZIN';
                        $ket = 'Izin (Berdasarkan Laporan KBM)';
                        $isTerkunci = 0;
                    } elseif ($sum['count_alpha'] > 0) {
                        $ket = 'Gugur Alpha (Tidak Absen Gerbang & Ada KBM Alpha)';
                    }
                }
                
                $g['status_kehadiran'] = $newStatus;
                $g['is_terkunci'] = $isTerkunci;
                if (empty($g['keterangan'])) {
                    $g['keterangan'] = $ket;
                }
            } elseif ($g['waktu_datang'] && !$g['waktu_pulang'] && $g['is_terkunci'] != 1) {
                // Ada tap-in, tidak ada tap-out (visual only logic, runDailyEvaluation persists it)
                if ($sum['total_sesi_tercatat'] > 0 && $sum['count_hadir'] == $sum['total_sesi_tercatat']) {
                    $g['waktu_pulang'] = 'AUTO'; // Just a marker
                    $g['keterangan'] = 'Hadir (Auto-Checkout KBM Lengkap)';
                } else {
                    $g['status_kehadiran'] = 'ALPHA';
                    $g['is_terkunci'] = 0; // Virtual status should not be locked
                    $g['keterangan'] = 'Gugur Alpha (Tidak Tap-Out & Sesi KBM Tidak Lengkap)';
                }
            }
        }
        unset($g);

        return $gerbangData;
    }

    /**
     * Mengambil daftar aktivitas scan kartu siswa terbaru hari ini (Live Feed)
     */
    public static function getRecentScans(string $tanggal, int $limit = 15): array
    {
        $db = Database::getConnection();
        $sql = "SELECT s.id as siswa_id, s.nama_siswa, s.nisn, s.foto, s.jenis_kelamin, k.nama_kelas,
                       pg.waktu_datang, pg.waktu_pulang, pg.status_kehadiran,
                       GREATEST(COALESCE(pg.waktu_datang, '1970-01-01 00:00:00'), COALESCE(pg.waktu_pulang, '1970-01-01 00:00:00')) as latest_scan_time,
                       CASE 
                           WHEN pg.waktu_pulang IS NOT NULL AND pg.waktu_pulang >= COALESCE(pg.waktu_datang, '1970-01-01 00:00:00') THEN 'PULANG'
                           ELSE 'DATANG'
                       END as latest_direction
                FROM presensi_gerbang_siswa pg
                JOIN siswa s ON s.id = pg.siswa_id
                JOIN kelas k ON k.id = s.kelas_id
                WHERE pg.tanggal = :tgl AND (pg.waktu_datang IS NOT NULL OR pg.waktu_pulang IS NOT NULL)
                ORDER BY latest_scan_time DESC
                LIMIT :lim";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':tgl', $tanggal);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

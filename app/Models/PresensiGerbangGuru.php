<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class PresensiGerbangGuru
{
    /**
     * Mendapatkan data presensi gerbang guru untuk tanggal tertentu (default hari ini)
     */
    public static function getToday(int $guruId, ?string $tanggal = null): ?array
    {
        $db = Database::getConnection();
        $tgl = $tanggal ?: date('Y-m-d');

        $stmt = $db->prepare("SELECT * FROM presensi_gerbang_guru WHERE guru_id = :gid AND tanggal = :tgl LIMIT 1");
        $stmt->execute([':gid' => $guruId, ':tgl' => $tgl]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Mencatat Tap Datang / Pengajuan Non-Hadir (Tugas Luar, Izin, Sakit)
     */
    public static function recordTapDatang(
        int $guruId,
        string $statusPilihan,
        ?float $lat,
        ?float $lng,
        float $distance = 0.0,
        int $menitTelat = 0,
        ?string $keterangan = null,
        ?string $waktu = null
    ): array {
        $db = Database::getConnection();
        $now = $waktu ?: date('Y-m-d H:i:s');
        $today = date('Y-m-d', strtotime($now));

        $statusPilihan = strtoupper(trim($statusPilihan));
        if (!in_array($statusPilihan, ['HADIR', 'TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
            $statusPilihan = 'HADIR';
        }

        $existing = self::getToday($guruId, $today);

        if ($existing) {
            // Sudah pernah mengisi sebelumnya
            if (!empty($existing['waktu_datang']) || in_array($existing['status_kehadiran'], ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
                $jam = !empty($existing['waktu_datang']) ? date('H:i', strtotime($existing['waktu_datang'])) : '-';
                return [
                    'success' => true,
                    'is_repeat' => true,
                    'message' => "Anda sudah mencatat presensi hari ini pada pukul {$jam} WIB dengan status [{$existing['status_kehadiran']}].",
                    'data' => $existing,
                ];
            }

            // Update baris yang sudah ada
            if (in_array($statusPilihan, ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
                $stmt = $db->prepare("
                    UPDATE presensi_gerbang_guru 
                    SET waktu_datang = :waktu,
                        status_kehadiran = :st,
                        keterangan = :ket,
                        lat_datang = NULL,
                        long_datang = NULL,
                        jarak_datang_meter = 0
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':waktu' => $now,
                    ':st' => $statusPilihan,
                    ':ket' => $keterangan ?: "Pengajuan {$statusPilihan}",
                    ':id' => $existing['id']
                ]);
            } else {
                // HADIR
                $statusKehadiran = ($menitTelat > 0) ? 'TERLAMBAT' : 'HADIR';
                $stmt = $db->prepare("
                    UPDATE presensi_gerbang_guru 
                    SET waktu_datang = :waktu,
                        status_kehadiran = :st,
                        lat_datang = :lat,
                        long_datang = :lng,
                        jarak_datang_meter = :dist,
                        menit_terlambat_datang = :telat,
                        keterangan = :ket
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':waktu' => $now,
                    ':st' => $statusKehadiran,
                    ':lat' => $lat,
                    ':lng' => $lng,
                    ':dist' => $distance,
                    ':telat' => $menitTelat,
                    ':ket' => $keterangan,
                    ':id' => $existing['id']
                ]);
            }
        } else {
            // INSERT baru
            if (in_array($statusPilihan, ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
                $stmt = $db->prepare("
                    INSERT INTO presensi_gerbang_guru 
                    (guru_id, tanggal, waktu_datang, status_kehadiran, keterangan)
                    VALUES (:gid, :tgl, :waktu, :st, :ket)
                ");
                $stmt->execute([
                    ':gid' => $guruId,
                    ':tgl' => $today,
                    ':waktu' => $now,
                    ':st' => $statusPilihan,
                    ':ket' => $keterangan ?: "Pengajuan {$statusPilihan}"
                ]);
            } else {
                // HADIR
                $statusKehadiran = ($menitTelat > 0) ? 'TERLAMBAT' : 'HADIR';
                $stmt = $db->prepare("
                    INSERT INTO presensi_gerbang_guru 
                    (guru_id, tanggal, waktu_datang, status_kehadiran, lat_datang, long_datang, jarak_datang_meter, menit_terlambat_datang, keterangan)
                    VALUES (:gid, :tgl, :waktu, :st, :lat, :lng, :dist, :telat, :ket)
                ");
                $stmt->execute([
                    ':gid' => $guruId,
                    ':tgl' => $today,
                    ':waktu' => $now,
                    ':st' => $statusKehadiran,
                    ':lat' => $lat,
                    ':lng' => $lng,
                    ':dist' => $distance,
                    ':telat' => $menitTelat,
                    ':ket' => $keterangan
                ]);
            }
        }

        $fresh = self::getToday($guruId, $today);

        if (in_array($statusPilihan, ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
            $label = ($statusPilihan === 'TUGAS_LUAR') ? 'Tugas Luar / Dinas' : ucfirst(strtolower($statusPilihan));
            return [
                'success' => true,
                'is_repeat' => false,
                'status' => $statusPilihan,
                'requires_tap_out' => false,
                'message' => "Presensi [{$label}] berhasil dicatat! Anda tidak perlu melakukan Tap Pulang hari ini.",
                'data' => $fresh
            ];
        }

        $telatMsg = ($menitTelat > 0) ? " (Terlambat {$menitTelat} menit)" : " (Tepat Waktu)";
        return [
            'success' => true,
            'is_repeat' => false,
            'status' => 'HADIR',
            'requires_tap_out' => true,
            'message' => "Tap Datang berhasil dicatat pukul " . date('H:i', strtotime($now)) . " WIB{$telatMsg}. Jangan lupa lakukan Tap Pulang saat selesai jam kerja!",
            'data' => $fresh
        ];
    }

    /**
     * Mencatat Tap Pulang Guru
     */
    public static function recordTapPulang(
        int $guruId,
        ?float $lat,
        ?float $lng,
        float $distance = 0.0,
        ?string $keterangan = null,
        ?string $waktu = null
    ): array {
        $db = Database::getConnection();
        $now = $waktu ?: date('Y-m-d H:i:s');
        $today = date('Y-m-d', strtotime($now));

        $existing = self::getToday($guruId, $today);

        if (!$existing || empty($existing['waktu_datang'])) {
            return [
                'success' => false,
                'message' => 'Anda belum melakukan Tap Datang hari ini! Silakan lakukan Tap Datang terlebih dahulu sebelum Tap Pulang.',
            ];
        }

        if (in_array($existing['status_kehadiran'], ['TUGAS_LUAR', 'IZIN', 'SAKIT'])) {
            return [
                'success' => true,
                'is_repeat' => true,
                'message' => "Status kehadiran Anda hari ini adalah [{$existing['status_kehadiran']}]. Tidak diperlukan Tap Pulang.",
                'data' => $existing
            ];
        }

        if (!empty($existing['waktu_pulang'])) {
            return [
                'success' => true,
                'is_repeat' => true,
                'message' => 'Anda sudah melakukan Tap Pulang pada jam ' . date('H:i', strtotime($existing['waktu_pulang'])) . ' WIB.',
                'data' => $existing
            ];
        }

        // Status akhir: Jika saat datang terlambat, tetap TERLAMBAT; Jika tepat waktu, HADIR
        $statusFinal = ((int)$existing['menit_terlambat_datang'] > 0) ? 'TERLAMBAT' : 'HADIR';

        $stmt = $db->prepare("
            UPDATE presensi_gerbang_guru 
            SET waktu_pulang = :waktu,
                lat_pulang = :lat,
                long_pulang = :lng,
                jarak_pulang_meter = :dist,
                status_kehadiran = :st,
                keterangan = COALESCE(:ket, keterangan)
            WHERE id = :id
        ");

        $stmt->execute([
            ':waktu' => $now,
            ':lat' => $lat,
            ':lng' => $lng,
            ':dist' => $distance,
            ':st' => $statusFinal,
            ':ket' => $keterangan,
            ':id' => $existing['id']
        ]);

        $fresh = self::getToday($guruId, $today);

        return [
            'success' => true,
            'is_repeat' => false,
            'message' => 'Tap Pulang Berhasil pukul ' . date('H:i', strtotime($now)) . ' WIB! Kehadiran dua arah Anda hari ini telah LENGKAP terverifikasi.',
            'data' => $fresh
        ];
    }

    /**
     * Evaluasi Integritas Harian Guru Sore Hari (Cron Job jam 17:00):
     * Kunci status menjadi ALPHA bagi guru yang hanya tap datang tapi tidak tap pulang.
     * Status TUGAS_LUAR, IZIN, dan SAKIT TIDAK AKAN diubah menjadi ALPHA.
     */
    public static function runDailyEvaluation(?string $tanggal = null): int
    {
        $db = Database::getConnection();
        $tgl = $tanggal ?: date('Y-m-d');

        // Kunci guru yang hanya tap datang tanpa tap pulang
        $sql = "
            UPDATE presensi_gerbang_guru 
            SET status_kehadiran = 'ALPHA', 
                keterangan = CASE 
                    WHEN waktu_datang IS NOT NULL AND waktu_pulang IS NULL THEN 'Gugur Alpha: Tidak melakukan Tap-Out Pulang'
                    WHEN waktu_datang IS NULL AND waktu_pulang IS NOT NULL THEN 'Gugur Alpha: Tidak melakukan Tap-In Masuk'
                    ELSE keterangan 
                END
            WHERE tanggal = :tgl 
              AND status_kehadiran IN ('HADIR', 'TERLAMBAT', 'ALPHA') 
              AND (waktu_datang IS NULL OR waktu_pulang IS NULL)
              AND status_kehadiran NOT IN ('TUGAS_LUAR', 'IZIN', 'SAKIT')
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([':tgl' => $tgl]);
        return $stmt->rowCount();
    }

    /**
     * Mengambil rekap presensi seluruh guru pada tanggal tertentu (Untuk Monitoring Admin)
     */
    public static function getByDate(string $tanggal): array
    {
        $db = Database::getConnection();

        $sql = "
            SELECT 
                g.id as guru_id,
                g.nik_nip,
                g.nama_lengkap,
                g.username,
                g.role,
                g.tugas_tambahan,
                pg.id as presensi_id,
                pg.tanggal,
                pg.waktu_datang,
                pg.waktu_pulang,
                pg.jarak_datang_meter,
                pg.jarak_pulang_meter,
                pg.menit_terlambat_datang,
                COALESCE(pg.status_kehadiran, 'BELUM_PRESENSI') as status_kehadiran,
                pg.keterangan
            FROM guru g
            LEFT JOIN presensi_gerbang_guru pg ON pg.guru_id = g.id AND pg.tanggal = :tgl
            WHERE g.role != 'admin'
            ORDER BY g.nama_lengkap ASC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([':tgl' => $tanggal]);
        return $stmt->fetchAll();
    }

    /**
     * Mengambil riwayat presensi gerbang bulanan untuk seorang guru
     */
    public static function getTeacherMonthlyHistory(int $guruId, int $bulan, int $tahun): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT * FROM presensi_gerbang_guru 
            WHERE guru_id = :gid 
              AND MONTH(tanggal) = :bln 
              AND YEAR(tanggal) = :thn
            ORDER BY tanggal DESC
        ");
        $stmt->execute([
            ':gid' => $guruId,
            ':bln' => $bulan,
            ':thn' => $tahun
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Update Manual untuk Admin/SuperAdmin
     */
    public static function upsertManual(
        int $guruId, 
        string $tanggal, 
        ?string $waktuDatang, 
        ?string $waktuPulang, 
        string $statusKehadiran,
        int $menitTerlambat,
        ?string $keterangan
    ): void {
        $db = Database::getConnection();
        
        $cek = self::getToday($guruId, $tanggal);
        
        if ($cek) {
            $sql = "UPDATE presensi_gerbang_guru 
                    SET waktu_datang = :datang, 
                        waktu_pulang = :pulang, 
                        status_kehadiran = :status,
                        menit_terlambat_datang = :telat,
                        keterangan = :ket
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':datang' => $waktuDatang,
                ':pulang' => $waktuPulang,
                ':status' => $statusKehadiran,
                ':telat' => $menitTerlambat,
                ':ket' => $keterangan,
                ':id' => $cek['id']
            ]);
        } else {
            $sql = "INSERT INTO presensi_gerbang_guru (guru_id, tanggal, waktu_datang, waktu_pulang, status_kehadiran, menit_terlambat_datang, keterangan) 
                    VALUES (:gid, :tgl, :datang, :pulang, :status, :telat, :ket)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':gid' => $guruId,
                ':tgl' => $tanggal,
                ':datang' => $waktuDatang,
                ':pulang' => $waktuPulang,
                ':status' => $statusKehadiran,
                ':telat' => $menitTerlambat,
                ':ket' => $keterangan
            ]);
        }
    }
}

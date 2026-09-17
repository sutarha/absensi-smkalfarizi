<?php
namespace App\Models;

use App\Config\Database;
use App\Helpers\TimeHelper;
use PDO;

class SesiMengajar
{
    public static function getById(int $id): ?array
    {
        $db = Database::getConnection();
        $sql = "SELECT sm.*, j.nama_mapel, j.jumlah_jp, j.jam_mulai, j.jam_selesai,
                       k.nama_kelas, k.tingkat, k.jurusan,
                       g.nama_lengkap as nama_guru, g.nik_nip
                FROM sesi_mengajar_guru sm
                JOIN jadwal_pelajaran j ON j.id = sm.jadwal_id
                JOIN kelas k ON k.id = j.kelas_id
                JOIN guru g ON g.id = sm.guru_id
                WHERE sm.id = :id LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getSessionByJadwalDate(int $jadwalId, string $tanggal): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM sesi_mengajar_guru WHERE jadwal_id = :jid AND tanggal = :tgl LIMIT 1");
        $stmt->execute([':jid' => $jadwalId, ':tgl' => $tanggal]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function startSession(
        int $jadwalId,
        int $guruId,
        string $tanggal,
        string $waktuCheckin,
        ?float $latCheckin,
        ?float $longCheckin,
        float $jarakMeter,
        int $menitTerlambat,
        int $durasiEfektif,
        float $honorDidapat,
        string $statusVerifikasi = 'VALID'
    ): int {
        $db = Database::getConnection();
        $sql = "INSERT INTO sesi_mengajar_guru (
                    jadwal_id, guru_id, tanggal, waktu_checkin,
                    lat_checkin, long_checkin, jarak_meter,
                    menit_terlambat, durasi_efektif_menit, honor_didapat, status_verifikasi
                ) VALUES (
                    :jid, :gid, :tgl, :checkin,
                    :lat, :lng, :jarak,
                    :telat, :durasi, :honor, :verif
                )";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':jid' => $jadwalId,
            ':gid' => $guruId,
            ':tgl' => $tanggal,
            ':checkin' => $waktuCheckin,
            ':lat' => $latCheckin,
            ':lng' => $longCheckin,
            ':jarak' => $jarakMeter,
            ':telat' => $menitTerlambat,
            ':durasi' => $durasiEfektif,
            ':honor' => $honorDidapat,
            ':verif' => $statusVerifikasi,
        ]);

        return (int)$db->lastInsertId();
    }

    public static function finishSession(int $sesiId, ?string $waktuCheckout = null, ?string $catatan = null): bool
    {
        $db = Database::getConnection();
        $now = $waktuCheckout ?: date('Y-m-d H:i:s');
        $sql = "UPDATE sesi_mengajar_guru SET waktu_checkout = :checkout, catatan_guru = :catatan WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':checkout' => $now,
            ':catatan' => $catatan,
            ':id' => $sesiId
        ]);
    }

    /**
     * Mengambil daftar jadwal hari ini untuk guru beserta status sesi mengajar (Check-in, Selesai, Belum)
     */
    public static function getTodayScheduleWithSession(int $guruId, string $tanggal, string $hariIndo): array
    {
        $db = Database::getConnection();
        $sql = "SELECT j.*, k.nama_kelas, k.tingkat, k.jurusan,
                       sm.id as sesi_id, sm.waktu_checkin, sm.waktu_checkout, sm.menit_terlambat,
                       sm.durasi_efektif_menit, sm.honor_didapat, sm.status_verifikasi, sm.jarak_meter
                FROM jadwal_pelajaran j
                JOIN kelas k ON k.id = j.kelas_id
                LEFT JOIN sesi_mengajar_guru sm ON sm.jadwal_id = j.id AND sm.tanggal = :tgl
                WHERE j.guru_id = :gid AND j.hari = :hari
                ORDER BY j.jam_mulai ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':tgl' => $tanggal, ':gid' => $guruId, ':hari' => $hariIndo]);
        return $stmt->fetchAll();
    }

    /**
     * Riwayat mengajar guru (Buku Saku Honor)
     */
    public static function getTeacherHistory(int $guruId, ?string $bulan = null, ?string $tahun = null): array
    {
        $db = Database::getConnection();
        $conditions = ["sm.guru_id = :gid"];
        $params = [':gid' => $guruId];

        if ($bulan && $tahun) {
            $conditions[] = "MONTH(sm.tanggal) = :bln AND YEAR(sm.tanggal) = :thn";
            $params[':bln'] = (int)$bulan;
            $params[':thn'] = (int)$tahun;
        }

        $where = implode(" AND ", $conditions);

        $sql = "SELECT sm.*, j.nama_mapel, j.jumlah_jp, j.jam_mulai, j.jam_selesai,
                       k.nama_kelas
                FROM sesi_mengajar_guru sm
                JOIN jadwal_pelajaran j ON j.id = sm.jadwal_id
                JOIN kelas k ON k.id = j.kelas_id
                WHERE {$where}
                ORDER BY sm.tanggal DESC, sm.waktu_checkin DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Monitoring kehadiran guru live hari ini untuk Super Admin / TU
     */
    public static function getLiveMonitoring(?string $tanggal = null): array
    {
        $db = Database::getConnection();
        $tgl = $tanggal ?: date('Y-m-d');
        $hariIndo = TimeHelper::getDayName($tgl);

        $sql = "SELECT j.id as jadwal_id, j.nama_mapel, j.jumlah_jp, j.jam_mulai, j.jam_selesai,
                       k.nama_kelas, g.id as guru_id, g.nama_lengkap as nama_guru, g.nik_nip,
                       sm.id as sesi_id, sm.waktu_checkin, sm.waktu_checkout, sm.menit_terlambat,
                       sm.durasi_efektif_menit, sm.honor_didapat, sm.status_verifikasi, sm.jarak_meter,
                       sm.lat_checkin, sm.long_checkin
                FROM jadwal_pelajaran j
                JOIN kelas k ON k.id = j.kelas_id
                JOIN guru g ON g.id = j.guru_id
                LEFT JOIN sesi_mengajar_guru sm ON sm.jadwal_id = j.id AND sm.tanggal = :tgl
                WHERE j.hari = :hari
                ORDER BY j.jam_mulai ASC, k.nama_kelas ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([':tgl' => $tgl, ':hari' => $hariIndo]);
        return $stmt->fetchAll();
    }

    public static function upsertManual(int $jadwalId, int $guruId, string $tanggal, ?string $checkin, ?string $checkout, int $menitTerlambat): void
    {
        $db = Database::getConnection();
        
        // Kalkulasi Honor Didapat berdasarkan konfigurasi & jadwal
        $configStmt = $db->query("SELECT * FROM konfigurasi_sekolah LIMIT 1");
        $config = $configStmt->fetch();
        $honorPerJp = (float)($config['honor_per_jp'] ?? 0);
        $dendaPerMenit = (float)($config['denda_per_menit'] ?? 0);

        $jadwalStmt = $db->prepare("SELECT jumlah_jp FROM jadwal_pelajaran WHERE id = ?");
        $jadwalStmt->execute([$jadwalId]);
        $jadwal = $jadwalStmt->fetch();
        $jumlahJp = (int)($jadwal['jumlah_jp'] ?? 0);

        // Jika waktu_checkin null (dikosongkan), maka honor_didapat 0 (dianggap alpha/tidak hadir KBM)
        $honorDidapat = 0;
        if ($checkin !== null) {
            $plafonHonor = $jumlahJp * $honorPerJp;
            $totalDenda = $menitTerlambat * $dendaPerMenit;
            $honorDidapat = max(0, $plafonHonor - $totalDenda);
        }

        $cek = self::getSessionByJadwalDate($jadwalId, $tanggal);
        if ($cek) {
            $sql = "UPDATE sesi_mengajar_guru 
                    SET waktu_checkin = :in, waktu_checkout = :out, menit_terlambat = :telat, honor_didapat = :honor
                    WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':in' => $checkin,
                ':out' => $checkout,
                ':telat' => $menitTerlambat,
                ':honor' => $honorDidapat,
                ':id' => $cek['id']
            ]);
        } else {
            $sql = "INSERT INTO sesi_mengajar_guru (jadwal_id, guru_id, tanggal, waktu_checkin, waktu_checkout, menit_terlambat, honor_didapat, status_verifikasi) 
                    VALUES (:jid, :gid, :tgl, :in, :out, :telat, :honor, 'VALID')";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':jid' => $jadwalId,
                ':gid' => $guruId,
                ':tgl' => $tanggal,
                ':in' => $checkin,
                ':out' => $checkout,
                ':telat' => $menitTerlambat,
                ':honor' => $honorDidapat
            ]);
        }
    }
}

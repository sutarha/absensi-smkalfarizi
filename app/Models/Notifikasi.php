<?php
// app/Models/Notifikasi.php
declare(strict_types=1);

namespace App\Models;

use App\Config\Database;
use PDO;

class Notifikasi
{
    /**
     * Broadcast notifikasi dari Admin ke Guru, Siswa, atau Keduanya.
     * 
     * @param array $data [
     *    'judul'           => string,
     *    'pesan'           => string,
     *    'tipe'            => 'pengumuman'|'penting'|'akademik'|'kegiatan'|'info',
     *    'target_role'     => 'semua'|'guru'|'siswa',
     *    'target_kelas_id' => int|null,
     *    'target_guru_id'  => int|null,
     *    'target_siswa_id' => int|null,
     *    'link_url'        => string|null,
     *    'sender_nama'     => string
     * ]
     * @return int ID notifikasi broadcast yang dibuat
     */
    public static function broadcast(array $data): int
    {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $judul         = trim($data['judul'] ?? '');
            $pesan         = trim($data['pesan'] ?? '');
            $tipe          = trim($data['tipe'] ?? 'pengumuman');
            $targetRole    = $data['target_role'] ?? 'semua';
            $targetKelasId = !empty($data['target_kelas_id']) ? (int)$data['target_kelas_id'] : null;
            $targetGuruId  = !empty($data['target_guru_id']) ? (int)$data['target_guru_id'] : null;
            $targetSiswaId = !empty($data['target_siswa_id']) ? (int)$data['target_siswa_id'] : null;
            $linkUrl       = !empty($data['link_url']) ? trim($data['link_url']) : null;
            $senderNama    = !empty($data['sender_nama']) ? trim($data['sender_nama']) : 'Admin SMK';

            // 1. Simpan master broadcast
            $stmt = $db->prepare("
                INSERT INTO notifikasi_broadcast 
                    (judul, pesan, tipe, target_role, target_kelas_id, target_guru_id, target_siswa_id, link_url, sender_nama, total_penerima)
                VALUES 
                    (:judul, :pesan, :tipe, :target_role, :target_kelas_id, :target_guru_id, :target_siswa_id, :link_url, :sender_nama, 0)
            ");
            $stmt->execute([
                ':judul'           => $judul,
                ':pesan'           => $pesan,
                ':tipe'            => $tipe,
                ':target_role'     => $targetRole,
                ':target_kelas_id' => $targetKelasId,
                ':target_guru_id'  => $targetGuruId,
                ':target_siswa_id' => $targetSiswaId,
                ':link_url'        => $linkUrl,
                ':sender_nama'     => $senderNama,
            ]);
            $broadcastId = (int)$db->lastInsertId();
            $totalPenerima = 0;

            // 2. Distribusi ke Guru jika targetRole == 'semua' atau 'guru'
            if ($targetRole === 'semua' || $targetRole === 'guru') {
                if ($targetGuruId) {
                    $stmtGuru = $db->prepare("
                        INSERT INTO notifikasi_guru (broadcast_id, guru_id, judul, pesan, tipe, link_url, is_read)
                        VALUES (:b_id, :guru_id, :judul, :pesan, :tipe, :link, 0)
                    ");
                    $stmtGuru->execute([
                        ':b_id'    => $broadcastId,
                        ':guru_id' => $targetGuruId,
                        ':judul'   => $judul,
                        ':pesan'   => $pesan,
                        ':tipe'    => $tipe,
                        ':link'    => $linkUrl,
                    ]);
                    $totalPenerima++;
                } else {
                    $allGuru = $db->query("SELECT id FROM guru")->fetchAll(PDO::FETCH_COLUMN);
                    if (!empty($allGuru)) {
                        $stmtGuru = $db->prepare("
                            INSERT INTO notifikasi_guru (broadcast_id, guru_id, judul, pesan, tipe, link_url, is_read)
                            VALUES (:b_id, :guru_id, :judul, :pesan, :tipe, :link, 0)
                        ");
                        foreach ($allGuru as $gid) {
                            $stmtGuru->execute([
                                ':b_id'    => $broadcastId,
                                ':guru_id' => (int)$gid,
                                ':judul'   => $judul,
                                ':pesan'   => $pesan,
                                ':tipe'    => $tipe,
                                ':link'    => $linkUrl,
                            ]);
                            $totalPenerima++;
                        }
                    }
                }
            }

            // 3. Distribusi ke Siswa jika targetRole == 'semua' atau 'siswa'
            if ($targetRole === 'semua' || $targetRole === 'siswa') {
                if ($targetSiswaId) {
                    $stmtSiswa = $db->prepare("
                        INSERT INTO notifikasi_siswa (broadcast_id, siswa_id, judul, pesan, tipe, link_url, is_read)
                        VALUES (:b_id, :siswa_id, :judul, :pesan, :tipe, :link, 0)
                    ");
                    $stmtSiswa->execute([
                        ':b_id'     => $broadcastId,
                        ':siswa_id' => $targetSiswaId,
                        ':judul'    => $judul,
                        ':pesan'    => $pesan,
                        ':tipe'     => $tipe,
                        ':link'     => $linkUrl,
                    ]);
                    $totalPenerima++;
                } elseif ($targetKelasId) {
                    $siswaKelas = $db->prepare("SELECT id FROM siswa WHERE kelas_id = :kid");
                    $siswaKelas->execute([':kid' => $targetKelasId]);
                    $listSiswa = $siswaKelas->fetchAll(PDO::FETCH_COLUMN);
                    if (!empty($listSiswa)) {
                        $stmtSiswa = $db->prepare("
                            INSERT INTO notifikasi_siswa (broadcast_id, siswa_id, judul, pesan, tipe, link_url, is_read)
                            VALUES (:b_id, :siswa_id, :judul, :pesan, :tipe, :link, 0)
                        ");
                        foreach ($listSiswa as $sid) {
                            $stmtSiswa->execute([
                                ':b_id'     => $broadcastId,
                                ':siswa_id' => (int)$sid,
                                ':judul'    => $judul,
                                ':pesan'    => $pesan,
                                ':tipe'     => $tipe,
                                ':link'     => $linkUrl,
                            ]);
                            $totalPenerima++;
                        }
                    }
                } else {
                    $allSiswa = $db->query("SELECT id FROM siswa")->fetchAll(PDO::FETCH_COLUMN);
                    if (!empty($allSiswa)) {
                        $stmtSiswa = $db->prepare("
                            INSERT INTO notifikasi_siswa (broadcast_id, siswa_id, judul, pesan, tipe, link_url, is_read)
                            VALUES (:b_id, :siswa_id, :judul, :pesan, :tipe, :link, 0)
                        ");
                        foreach ($allSiswa as $sid) {
                            $stmtSiswa->execute([
                                ':b_id'     => $broadcastId,
                                ':siswa_id' => (int)$sid,
                                ':judul'    => $judul,
                                ':pesan'    => $pesan,
                                ':tipe'     => $tipe,
                                ':link'     => $linkUrl,
                            ]);
                            $totalPenerima++;
                        }
                    }
                }
            }

            // Update total penerima di master broadcast
            $update = $db->prepare("UPDATE notifikasi_broadcast SET total_penerima = :tot WHERE id = :id");
            $update->execute([':tot' => $totalPenerima, ':id' => $broadcastId]);

            $db->commit();
            return $broadcastId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Ambil daftar broadcast untuk Admin
     */
    public static function getAllBroadcast(int $limit = 50): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT nb.*, k.nama_kelas, g.nama_lengkap AS target_guru_nama, s.nama_siswa AS target_siswa_nama
            FROM notifikasi_broadcast nb
            LEFT JOIN kelas k ON nb.target_kelas_id = k.id
            LEFT JOIN guru g ON nb.target_guru_id = g.id
            LEFT JOIN siswa s ON nb.target_siswa_id = s.id
            ORDER BY nb.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Hapus broadcast beserta notifikasi anak yang belum/sudah terkirim
     */
    public static function deleteBroadcast(int $id): bool
    {
        $db = Database::getConnection();
        $db->beginTransaction();
        try {
            $db->prepare("DELETE FROM notifikasi_guru WHERE broadcast_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM notifikasi_siswa WHERE broadcast_id = :id")->execute([':id' => $id]);
            $db->prepare("DELETE FROM notifikasi_broadcast WHERE id = :id")->execute([':id' => $id]);
            $db->commit();
            return true;
        } catch (\Throwable $e) {
            $db->rollBack();
            return false;
        }
    }

    /**
     * Ambil statistik ringkas untuk Admin
     */
    public static function getAdminStats(): array
    {
        $db = Database::getConnection();
        $totalBroadcast = (int)$db->query("SELECT COUNT(*) FROM notifikasi_broadcast")->fetchColumn();
        $totalGuruNotif = (int)$db->query("SELECT COUNT(*) FROM notifikasi_guru")->fetchColumn();
        $totalSiswaNotif = (int)$db->query("SELECT COUNT(*) FROM notifikasi_siswa")->fetchColumn();
        
        return [
            'total_broadcast' => $totalBroadcast,
            'total_guru_notif' => $totalGuruNotif,
            'total_siswa_notif' => $totalSiswaNotif,
        ];
    }

    // ==========================================
    // METODE UNTUK GURU
    // ==========================================

    public static function getForGuru(int $guruId, int $limit = 30): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ng.*, COALESCE(nb.sender_nama, 'Admin SMK') AS pengirim
            FROM notifikasi_guru ng
            LEFT JOIN notifikasi_broadcast nb ON ng.broadcast_id = nb.id
            WHERE ng.guru_id = :gid
            ORDER BY ng.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':gid', $guruId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnreadCountForGuru(int $guruId): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifikasi_guru WHERE guru_id = :gid AND is_read = 0");
        $stmt->execute([':gid' => $guruId]);
        return (int)$stmt->fetchColumn();
    }

    public static function markAsReadForGuru(int $notifId, int $guruId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifikasi_guru SET is_read = 1 WHERE id = :id AND guru_id = :gid");
        return $stmt->execute([':id' => $notifId, ':gid' => $guruId]);
    }

    public static function markAllAsReadForGuru(int $guruId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifikasi_guru SET is_read = 1 WHERE guru_id = :gid AND is_read = 0");
        return $stmt->execute([':gid' => $guruId]);
    }

    // ==========================================
    // METODE UNTUK SISWA
    // ==========================================

    public static function getForSiswa(int $siswaId, int $limit = 30): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT ns.*, COALESCE(nb.sender_nama, 'Admin SMK') AS pengirim
            FROM notifikasi_siswa ns
            LEFT JOIN notifikasi_broadcast nb ON ns.broadcast_id = nb.id
            WHERE ns.siswa_id = :sid
            ORDER BY ns.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':sid', $siswaId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUnreadCountForSiswa(int $siswaId): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifikasi_siswa WHERE siswa_id = :sid AND is_read = 0");
        $stmt->execute([':sid' => $siswaId]);
        return (int)$stmt->fetchColumn();
    }

    public static function markAsReadForSiswa(int $notifId, int $siswaId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifikasi_siswa SET is_read = 1 WHERE id = :id AND siswa_id = :sid");
        return $stmt->execute([':id' => $notifId, ':sid' => $siswaId]);
    }

    public static function markAllAsReadForSiswa(int $siswaId): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE notifikasi_siswa SET is_read = 1 WHERE siswa_id = :sid AND is_read = 0");
        return $stmt->execute([':sid' => $siswaId]);
    }
}

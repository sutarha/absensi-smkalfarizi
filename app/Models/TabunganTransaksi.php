<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class TabunganTransaksi
{
    public static function getByTabunganSiswa(int $tabunganSiswaId): array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT tt.*, g.nama_lengkap as nama_pencatat 
                              FROM tabungan_transaksi tt
                              JOIN guru g ON tt.dicatat_guru_id = g.id
                              WHERE tt.tabungan_siswa_id = :ts_id
                              ORDER BY tt.tanggal DESC, tt.created_at DESC");
        $stmt->execute([':ts_id' => $tabunganSiswaId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $db->beginTransaction();
        
        try {
            // 1. Catat transaksi
            $sql = "INSERT INTO tabungan_transaksi (tabungan_siswa_id, jumlah, tanggal, dicatat_guru_id, catatan) 
                    VALUES (:ts_id, :jumlah, :tanggal, :guru_id, :catatan)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':ts_id' => $data['tabungan_siswa_id'],
                ':jumlah' => (float)$data['jumlah'],
                ':tanggal' => $data['tanggal'] ?? date('Y-m-d'),
                ':guru_id' => (int)$data['dicatat_guru_id'],
                ':catatan' => $data['catatan'] ?? null,
            ]);
            $transaksiId = (int)$db->lastInsertId();

            // 2. Update total terkumpul di tabungan_siswa
            $stmtUpdate = $db->prepare("UPDATE tabungan_siswa SET total_terkumpul = total_terkumpul + :jumlah WHERE id = :ts_id");
            $stmtUpdate->execute([
                ':jumlah' => (float)$data['jumlah'],
                ':ts_id' => $data['tabungan_siswa_id']
            ]);

            // Cek apakah lunas
            $stmtCheck = $db->prepare("SELECT ts.total_terkumpul, tp.target_nominal 
                                       FROM tabungan_siswa ts
                                       JOIN tabungan_program tp ON ts.program_id = tp.id
                                       WHERE ts.id = :ts_id");
            $stmtCheck->execute([':ts_id' => $data['tabungan_siswa_id']]);
            $info = $stmtCheck->fetch();
            
            if ($info && $info['total_terkumpul'] >= $info['target_nominal']) {
                $stmtLunas = $db->prepare("UPDATE tabungan_siswa SET status = 'lunas' WHERE id = :ts_id");
                $stmtLunas->execute([':ts_id' => $data['tabungan_siswa_id']]);
            }

            $db->commit();
            return $transaksiId;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}

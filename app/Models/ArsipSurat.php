<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class ArsipSurat
{
    public static function getAll(?string $jenisSurat = null, int $limit = 50, int $offset = 0): array
    {
        $db = Database::getConnection();
        $sql = "
            SELECT a.*, 
                   s.nama_siswa, s.nisn, k.nama_kelas,
                   g.nama_lengkap as nama_guru, g.nik_nip
            FROM `arsip_surat` a
            LEFT JOIN `siswa` s ON a.siswa_id = s.id
            LEFT JOIN `kelas` k ON s.kelas_id = k.id
            LEFT JOIN `guru` g ON a.guru_id = g.id
            WHERE 1=1
        ";
        $params = [];

        if ($jenisSurat) {
            $sql .= " AND a.jenis_surat = ?";
            $params[] = $jenisSurat;
        }

        $sql .= " ORDER BY a.tanggal_surat DESC, a.id DESC LIMIT {$limit} OFFSET {$offset}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT a.*, 
                   s.nama_siswa, s.nisn, s.jenis_kelamin, k.nama_kelas, k.tingkat, k.jurusan,
                   b.nis, b.nik, b.tempat_lahir, b.tanggal_lahir, b.alamat_jalan, b.nama_ayah, b.nama_ibu,
                   g.nama_lengkap as nama_guru, g.nik_nip, g.tugas_tambahan, g.no_hp as no_hp_guru
            FROM `arsip_surat` a
            LEFT JOIN `siswa` s ON a.siswa_id = s.id
            LEFT JOIN `kelas` k ON s.kelas_id = k.id
            LEFT JOIN `buku_induk_siswa` b ON s.id = b.siswa_id
            LEFT JOIN `guru` g ON a.guru_id = g.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function getNextUrutan(string $jenisSurat, int $tahun): int
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM `arsip_surat` 
            WHERE YEAR(`tanggal_surat`) = ?
        ");
        $stmt->execute([$tahun]);
        $count = (int)$stmt->fetchColumn() + 1;

        while (true) {
            $checkNo = str_pad((string)$count, 3, '0', STR_PAD_LEFT);
            $checkStmt = $db->prepare("SELECT id FROM `arsip_surat` WHERE `nomor_surat` LIKE ?");
            $checkStmt->execute(["%/{$checkNo}/%/{$tahun}%"]);
            if (!$checkStmt->fetch()) {
                break;
            }
            $count++;
        }

        return $count;
    }

    public static function create(array $data): int
    {
        $db = Database::getConnection();
        $fields = [
            'nomor_surat', 'jenis_surat', 'penerima_tipe', 'siswa_id', 'guru_id',
            'perihal', 'keperluan', 'tanggal_surat',
            'dasar_penugasan', 'tempat_berangkat', 'tempat_tujuan', 'instansi_tujuan', 'pejabat_tujuan',
            'tanggal_berangkat', 'tanggal_kembali', 'lama_hari', 'alat_angkut', 'beban_anggaran', 'pengikut',
            'pejabat_penandatangan', 'created_by'
        ];

        $cols = [];
        $placeholders = [];
        $vals = [];

        foreach ($fields as $f) {
            if (array_key_exists($f, $data)) {
                $cols[] = "`$f`";
                $placeholders[] = '?';
                $vals[] = $data[$f];
            }
        }

        $sql = "INSERT INTO `arsip_surat` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        $stmt->execute($vals);
        return (int)$db->lastInsertId();
    }

    public static function delete(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM `arsip_surat` WHERE `id` = ?");
        return $stmt->execute([$id]);
    }
}

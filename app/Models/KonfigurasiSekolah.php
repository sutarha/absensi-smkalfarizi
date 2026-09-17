<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class KonfigurasiSekolah
{
    public static function get(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM konfigurasi_sekolah ORDER BY id ASC LIMIT 1");
        $config = $stmt->fetch();

        if (!$config) {
            // Fallback default
            return [
                'id' => 1,
                'nama_sekolah' => 'SMK AL-FARIZI',
                'alamat_sekolah' => 'Jl. Terusan Al-Farizi No. 45',
                'kepala_sekolah' => 'Drs. H. Ahmad Farizi, M.Pd.',
                'bendahara_tu' => 'Siti Aminah, S.E.',
                'latitude_pusat' => -6.91746400,
                'longitude_pusat' => 107.61912300,
                'radius_meter' => 50,
                'honor_per_jp' => 5000.00,
                'durasi_jp_menit' => 40,
                'denda_per_menit' => 125.00,
                'toleransi_h_minus' => 5,
                'jam_gerbang_masuk_mulai' => '06:30:00',
                'jam_gerbang_masuk_selesai' => '06:30:00',
                'jam_guru_masuk_selesai' => '06:30:00',
                'jam_gerbang_pulang_mulai' => '13:00:00',
                'jam_guru_pulang_mulai' => '13:00:00',
                'jam_gerbang_pulang_selesai' => '16:30:00',
            ];
        }

        return $config;
    }

    public static function updateConfig(array $data): bool
    {
        $db = Database::getConnection();
        $firstId = $db->query("SELECT id FROM `konfigurasi_sekolah` ORDER BY id ASC LIMIT 1")->fetchColumn();
        $targetId = $firstId ? (int)$firstId : (int)($data['id'] ?? 1);

        $setClause = "
            nama_sekolah = :nama_sekolah,
            alamat_sekolah = :alamat_sekolah,
            kepala_sekolah = :kepala_sekolah,
            bendahara_tu = :bendahara_tu,
            latitude_pusat = :latitude_pusat,
            longitude_pusat = :longitude_pusat,
            radius_meter = :radius_meter,
            honor_per_jp = :honor_per_jp,
            durasi_jp_menit = :durasi_jp_menit,
            denda_per_menit = :denda_per_menit,
            toleransi_h_minus = :toleransi_h_minus,
            jam_guru_masuk_selesai = :jam_guru_masuk_selesai,
            jam_guru_pulang_mulai = :jam_guru_pulang_mulai";

        if (isset($data['logo_kop'])) {
            $setClause .= ", logo_kop = :logo_kop";
        }

        $sql = "UPDATE konfigurasi_sekolah SET {$setClause} WHERE id = :id";

        $stmt = $db->prepare($sql);
        $params = [
            ':nama_sekolah' => $data['nama_sekolah'],
            ':alamat_sekolah' => $data['alamat_sekolah'] ?? '',
            ':kepala_sekolah' => $data['kepala_sekolah'],
            ':bendahara_tu' => $data['bendahara_tu'],
            ':latitude_pusat' => $data['latitude_pusat'],
            ':longitude_pusat' => $data['longitude_pusat'],
            ':radius_meter' => (int)$data['radius_meter'],
            ':honor_per_jp' => (float)$data['honor_per_jp'],
            ':durasi_jp_menit' => (int)$data['durasi_jp_menit'],
            ':denda_per_menit' => (float)$data['denda_per_menit'],
            ':toleransi_h_minus' => (int)($data['toleransi_h_minus'] ?? 5),
            ':jam_guru_masuk_selesai' => $data['jam_guru_masuk_selesai'] ?? '06:30:00',
            ':jam_guru_pulang_mulai' => $data['jam_guru_pulang_mulai'] ?? '13:00:00',
            ':id' => $targetId,
        ];
        
        if (isset($data['logo_kop'])) {
            $params[':logo_kop'] = $data['logo_kop'];
        }

        return $stmt->execute($params);
    }

    public static function updateKopConfig(array $data): bool
    {
        $db = Database::getConnection();
        $fields = ['nama_sekolah', 'alamat_sekolah', 'npsn', 'nss', 'akreditasi', 'email_sekolah', 'website_sekolah', 'kepala_sekolah'];
        if (!empty($data['logo_kop'])) {
            $fields[] = 'logo_kop';
        }

        $sets = [];
        $params = [];
        foreach ($fields as $f) {
            $sets[] = "`$f` = :$f";
            $params[":$f"] = $data[$f] ?? '';
        }

        $sql = "UPDATE `konfigurasi_sekolah` SET " . implode(', ', $sets) . " WHERE id = 1";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
}

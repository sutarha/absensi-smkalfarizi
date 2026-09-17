<?php
// database/migration_presensi_gerbang_guru.php
declare(strict_types=1);

require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

try {
    $db = Database::getConnection();
    echo "Terhubung ke database MySQL...\n";

    echo "1. Membuat tabel `presensi_gerbang_guru`...\n";
    $sql = "
        CREATE TABLE IF NOT EXISTS `presensi_gerbang_guru` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `guru_id` INT NOT NULL,
            `tanggal` DATE NOT NULL,
            `waktu_datang` DATETIME NULL,
            `waktu_pulang` DATETIME NULL,
            `metode_datang` ENUM('GPS_MANDIRI', 'MANUAL') DEFAULT 'GPS_MANDIRI',
            `metode_pulang` ENUM('GPS_MANDIRI', 'MANUAL') DEFAULT 'GPS_MANDIRI',
            `lat_datang` DECIMAL(10, 8) NULL,
            `long_datang` DECIMAL(11, 8) NULL,
            `jarak_datang_meter` DECIMAL(10, 2) DEFAULT 0.00,
            `lat_pulang` DECIMAL(10, 8) NULL,
            `long_pulang` DECIMAL(11, 8) NULL,
            `jarak_pulang_meter` DECIMAL(10, 2) DEFAULT 0.00,
            `menit_terlambat_datang` INT DEFAULT 0,
            `status_kehadiran` ENUM('HADIR', 'TERLAMBAT', 'TUGAS_LUAR', 'IZIN', 'SAKIT', 'ALPHA') NOT NULL DEFAULT 'ALPHA',
            `keterangan` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `unq_guru_tanggal` UNIQUE (`guru_id`, `tanggal`),
            CONSTRAINT `fk_gerbang_guru_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $db->exec($sql);
    echo "   + Tabel `presensi_gerbang_guru` berhasil dibuat / sudah ada.\n";

    echo "Migrasi presensi gerbang guru selesai dengan sukses!\n";
} catch (Throwable $e) {
    echo "Gagal migrasi: " . $e->getMessage() . "\n";
    exit(1);
}

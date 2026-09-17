<?php
// database/migration_notifikasi.php
declare(strict_types=1);

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) require_once $file;
});

try {
    $db = \App\Config\Database::getConnection();
    echo "Starting Notifikasi Migration...\n";

    // 1. Tabel notifikasi_broadcast
    $db->exec("
        CREATE TABLE IF NOT EXISTS `notifikasi_broadcast` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `judul` VARCHAR(255) NOT NULL,
            `pesan` TEXT NOT NULL,
            `tipe` VARCHAR(50) NOT NULL DEFAULT 'pengumuman',
            `target_role` ENUM('semua','guru','siswa') NOT NULL DEFAULT 'semua',
            `target_kelas_id` INT NULL,
            `target_guru_id` INT NULL,
            `target_siswa_id` INT NULL,
            `link_url` VARCHAR(255) NULL,
            `sender_nama` VARCHAR(100) NOT NULL DEFAULT 'Admin SMK',
            `total_penerima` INT NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`target_role`),
            INDEX (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel `notifikasi_broadcast` siap.\n";

    // 2. Tabel notifikasi_guru
    $db->exec("
        CREATE TABLE IF NOT EXISTS `notifikasi_guru` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `broadcast_id` INT NULL,
            `guru_id` INT NOT NULL,
            `judul` VARCHAR(255) NOT NULL,
            `pesan` TEXT NOT NULL,
            `tipe` VARCHAR(50) NOT NULL DEFAULT 'pengumuman',
            `link_url` VARCHAR(255) NULL,
            `is_read` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`guru_id`),
            INDEX (`is_read`),
            INDEX (`broadcast_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "✓ Tabel `notifikasi_guru` siap.\n";

    // 3. Modifikasi kolom notifikasi_siswa jika belum ada
    $cols = $db->query("SHOW COLUMNS FROM `notifikasi_siswa`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('broadcast_id', $cols, true)) {
        $db->exec("ALTER TABLE `notifikasi_siswa` ADD COLUMN `broadcast_id` INT NULL AFTER `id`");
        echo "✓ Kolom `broadcast_id` ditambahkan ke `notifikasi_siswa`.\n";
    }
    if (!in_array('link_url', $cols, true)) {
        $db->exec("ALTER TABLE `notifikasi_siswa` ADD COLUMN `link_url` VARCHAR(255) NULL AFTER `tipe`");
        echo "✓ Kolom `link_url` ditambahkan ke `notifikasi_siswa`.\n";
    }
    // Ganti tipe enum jadi varchar agar lebih fleksibel
    $db->exec("ALTER TABLE `notifikasi_siswa` MODIFY COLUMN `tipe` VARCHAR(50) NOT NULL DEFAULT 'pengumuman'");
    echo "✓ Tipe kolom `notifikasi_siswa.tipe` diperbarui menjadi VARCHAR(50).\n";

    echo "Migration Notifikasi SELESAI DENGAN SUKSES!\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

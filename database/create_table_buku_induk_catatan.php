<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

// PSR-4 Autoloader Sederhana
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

try {
    $db = Database::getConnection();
    
    echo "Creating table buku_induk_catatan...\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `buku_induk_catatan` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `siswa_id` INT NOT NULL,
        `jenis` ENUM('PKL', 'EKSTRAKURIKULER', 'INTEGRITAS') NOT NULL,
        `nama_kegiatan` VARCHAR(255) NOT NULL,
        `mitra_instansi` VARCHAR(255) NULL,
        `nilai_predikat` VARCHAR(50) NULL,
        `keterangan` TEXT NULL,
        `semester_ke` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT `fk_buku_catatan_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $db->exec($sql);
    echo "Table created successfully.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

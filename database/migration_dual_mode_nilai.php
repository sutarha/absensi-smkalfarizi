<?php
// database/migration_dual_mode_nilai.php - Add detailed grading components & manual flag

declare(strict_types=1);

require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

$db = Database::getConnection();

echo "Memeriksa dan memperbarui kolom tabel `nilai_siswa`...\n";

// Cek kolom yang ada
$stmt = $db->query("SHOW COLUMNS FROM `nilai_siswa`");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

$columnsToAdd = [
    'nilai_tugas' => "DECIMAL(5, 2) DEFAULT 0.00 AFTER `tahun_pelajaran_id`",
    'nilai_uh' => "DECIMAL(5, 2) DEFAULT 0.00 AFTER `nilai_tugas`",
    'nilai_uts' => "DECIMAL(5, 2) DEFAULT 0.00 AFTER `nilai_uh`",
    'nilai_uas' => "DECIMAL(5, 2) DEFAULT 0.00 AFTER `nilai_uts`",
    'is_manual' => "TINYINT(1) DEFAULT 0 AFTER `capaian_kompetensi`"
];

foreach ($columnsToAdd as $col => $definition) {
    if (!in_array($col, $columns, true)) {
        echo "Menambahkan kolom `{$col}`...\n";
        $db->exec("ALTER TABLE `nilai_siswa` ADD COLUMN `{$col}` {$definition}");
    } else {
        echo "Kolom `{$col}` sudah ada.\n";
    }
}

echo "Migrasi `nilai_siswa` selesai!\n";

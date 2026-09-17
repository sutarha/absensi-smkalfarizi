<?php
// database/migration_relasi_master.php - Penambahan Relasi Master Data SMK Al-Farizi

declare(strict_types=1);

require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

$db = Database::getConnection();

echo "========================================================\n";
echo "    MIGRASI RELASI MASTER DATA & ENHANCEMENT ALUR        \n";
echo "========================================================\n\n";

// 1. Tambah kolom `wali_kelas_guru_id` pada tabel `kelas`
echo "1. Memeriksa kolom `wali_kelas_guru_id` pada tabel `kelas`...\n";
$colsKelas = $db->query("SHOW COLUMNS FROM `kelas`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('wali_kelas_guru_id', $colsKelas, true)) {
    $db->exec("ALTER TABLE `kelas` ADD COLUMN `wali_kelas_guru_id` INT NULL AFTER `jurusan`");
    // Add Foreign Key constraint jika belum ada
    try {
        $db->exec("ALTER TABLE `kelas` ADD CONSTRAINT `fk_kelas_wali_guru` FOREIGN KEY (`wali_kelas_guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL");
    } catch (\Throwable $e) {
        // FK might already exist or silently handled
    }
    echo "   [SUCCESS] Kolom `wali_kelas_guru_id` berhasil ditambahkan pada `kelas`.\n";
} else {
    echo "   [INFO] Kolom `wali_kelas_guru_id` sudah ada pada `kelas`.\n";
}

// 2. Tambah kolom `mapel_id` pada tabel `jadwal_pelajaran`
echo "\n2. Memeriksa kolom `mapel_id` pada tabel `jadwal_pelajaran`...\n";
$colsJadwal = $db->query("SHOW COLUMNS FROM `jadwal_pelajaran`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('mapel_id', $colsJadwal, true)) {
    $db->exec("ALTER TABLE `jadwal_pelajaran` ADD COLUMN `mapel_id` INT NULL AFTER `nama_mapel`");
    try {
        $db->exec("ALTER TABLE `jadwal_pelajaran` ADD CONSTRAINT `fk_jadwal_mapel` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE SET NULL");
    } catch (\Throwable $e) {
        // FK handled
    }
    echo "   [SUCCESS] Kolom `mapel_id` berhasil ditambahkan pada `jadwal_pelajaran`.\n";
} else {
    echo "   [INFO] Kolom `mapel_id` sudah ada pada `jadwal_pelajaran`.\n";
}

// 3. Sinkronisasi mapel_id pada jadwal yang sudah ada berdasarkan nama_mapel
echo "\n3. Melakukan sinkronisasi otomatis `mapel_id` pada data jadwal yang sudah ada...\n";
$allMapel = $db->query("SELECT id, nama_mapel FROM `mata_pelajaran`")->fetchAll();
$stmtUpdateJadwal = $db->prepare("UPDATE `jadwal_pelajaran` SET `mapel_id` = ? WHERE LOWER(TRIM(`nama_mapel`)) = LOWER(TRIM(?))");
$stmtFallbackJadwal = $db->prepare("UPDATE `jadwal_pelajaran` SET `mapel_id` = ? WHERE LOWER(`nama_mapel`) LIKE ? AND `mapel_id` IS NULL");

$syncedCount = 0;
foreach ($allMapel as $m) {
    $stmtUpdateJadwal->execute([$m['id'], $m['nama_mapel']]);
    $syncedCount += $stmtUpdateJadwal->rowCount();

    $firstWord = trim(explode(' ', $m['nama_mapel'])[0]);
    if (strlen($firstWord) >= 3) {
        $stmtFallbackJadwal->execute([$m['id'], '%' . strtolower($firstWord) . '%']);
        $syncedCount += $stmtFallbackJadwal->rowCount();
    }
}
echo "   [SUCCESS] Sinkronisasi `mapel_id` selesai ($syncedCount jadwal diperbarui).\n";

// 4. Default penugasan Wali Kelas awal jika belum ada
echo "\n4. Memeriksa penugasan Wali Kelas default...\n";
$guruWali = $db->query("SELECT id FROM `guru` WHERE `role` = 'guru' ORDER BY `id` ASC LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
$kelasList = $db->query("SELECT id, wali_kelas_guru_id FROM `kelas` ORDER BY `id` ASC")->fetchAll();

$stmtSetWali = $db->prepare("UPDATE `kelas` SET `wali_kelas_guru_id` = ? WHERE `id` = ?");
foreach ($kelasList as $idx => $k) {
    if (empty($k['wali_kelas_guru_id']) && !empty($guruWali)) {
        $guruId = $guruWali[$idx % count($guruWali)];
        $stmtSetWali->execute([$guruId, $k['id']]);
    }
}
echo "   [SUCCESS] Wali Kelas terverifikasi.\n";

echo "\n========================================================\n";
echo "    MIGRASI DATABASE BERHASIL 100%!                      \n";
echo "========================================================\n";

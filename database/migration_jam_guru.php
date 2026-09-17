<?php
// database/migration_jam_guru.php
declare(strict_types=1);

require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

try {
    $db = Database::getConnection();
    echo "Terhubung ke database MySQL...\n";

    // Cek kolom di konfigurasi_sekolah
    $cols = $db->query("SHOW COLUMNS FROM `konfigurasi_sekolah`")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('jam_guru_masuk_selesai', $cols)) {
        $db->exec("ALTER TABLE `konfigurasi_sekolah` ADD COLUMN `jam_guru_masuk_selesai` TIME NOT NULL DEFAULT '06:30:00' AFTER `jam_gerbang_masuk_selesai`");
        echo "   + Kolom `jam_guru_masuk_selesai` berhasil ditambahkan.\n";
    }

    if (!in_array('jam_guru_pulang_mulai', $cols)) {
        $db->exec("ALTER TABLE `konfigurasi_sekolah` ADD COLUMN `jam_guru_pulang_mulai` TIME NOT NULL DEFAULT '13:00:00' AFTER `jam_gerbang_pulang_mulai`");
        echo "   + Kolom `jam_guru_pulang_mulai` berhasil ditambahkan.\n";
    }

    // Set nilai konfigurasi: Jam datang batas 06:30, Jam pulang mulai 13:00
    $db->exec("
        UPDATE `konfigurasi_sekolah` 
        SET `jam_guru_masuk_selesai` = '06:30:00',
            `jam_guru_pulang_mulai` = '13:00:00',
            `jam_gerbang_masuk_selesai` = '06:30:00',
            `jam_gerbang_pulang_mulai` = '13:00:00'
        WHERE id = 1
    ");
    echo "   + Nilai jam_guru_masuk_selesai diset ke 06:30:00 dan jam_guru_pulang_mulai diset ke 13:00:00.\n";

    echo "Migrasi jam operasional guru selesai!\n";
} catch (Throwable $e) {
    echo "Gagal migrasi: " . $e->getMessage() . "\n";
    exit(1);
}

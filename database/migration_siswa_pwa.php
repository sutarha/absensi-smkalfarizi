<?php
// database/migration_siswa_pwa.php
// Fase 1A: Menambahkan kolom auth siswa + tabel tabungan cicilan
declare(strict_types=1);

require_once __DIR__ . '/../app/Config/App.php';
require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\App;
use App\Config\Database;

try {
    App::init();
    $db = Database::getConnection();
    echo "✓ Terhubung ke database MySQL...\n";

    // =========================================================
    // 1. Tambah Kolom Auth ke Tabel Siswa
    // =========================================================
    echo "\n1. Menambahkan kolom auth ke tabel `siswa`...\n";
    $existingCols = $db->query("SHOW COLUMNS FROM `siswa`")->fetchAll(PDO::FETCH_COLUMN);

    $siswaCols = [
        'tanggal_lahir' => "DATE NULL COMMENT 'Digunakan sebagai password login PWA'",
        'password_pwa'  => "VARCHAR(255) NULL COMMENT 'Hash bcrypt opsional, override tgl lahir'",
        'last_login_pwa'=> "DATETIME NULL",
        'fcm_token'     => "VARCHAR(512) NULL COMMENT 'Firebase Cloud Messaging token untuk push notif'",
        'no_hp_ortu'    => "VARCHAR(20) NULL COMMENT 'No HP orang tua/wali'",
        'alamat'        => "TEXT NULL",
    ];

    foreach ($siswaCols as $col => $def) {
        if (!in_array($col, $existingCols)) {
            $db->exec("ALTER TABLE `siswa` ADD COLUMN `$col` $def");
            echo "   + Kolom `$col` ditambahkan.\n";
        } else {
            echo "   - Kolom `$col` sudah ada, dilewati.\n";
        }
    }

    // =========================================================
    // 2. Tabel Petugas Tabungan (guru yang ditunjuk admin)
    // =========================================================
    echo "\n2. Membuat tabel `tabungan_petugas`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `tabungan_petugas` (
            `id`       INT AUTO_INCREMENT PRIMARY KEY,
            `guru_id`  INT NOT NULL COMMENT 'FK ke tabel guru',
            `kelas_id` INT NULL COMMENT 'NULL = semua kelas',
            `is_aktif` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_guru_kelas` (`guru_id`, `kelas_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `tabungan_petugas` siap.\n";

    // =========================================================
    // 3. Tabel Program Tabungan
    // =========================================================
    echo "\n3. Membuat tabel `tabungan_program`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `tabungan_program` (
            `id`              INT AUTO_INCREMENT PRIMARY KEY,
            `nama_program`    VARCHAR(200) NOT NULL,
            `deskripsi`       TEXT NULL,
            `target_nominal`  DECIMAL(12,2) NOT NULL DEFAULT 0,
            `target_tanggal`  DATE NULL,
            `kelas_id`        INT NULL COMMENT 'NULL = semua kelas',
            `foto`            VARCHAR(255) NULL,
            `status`          ENUM('aktif','selesai','dibatalkan') NOT NULL DEFAULT 'aktif',
            `created_by_guru` INT NULL,
            `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `tabungan_program` siap.\n";

    // =========================================================
    // 4. Tabel Tabungan per Siswa per Program
    // =========================================================
    echo "\n4. Membuat tabel `tabungan_siswa`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `tabungan_siswa` (
            `id`               INT AUTO_INCREMENT PRIMARY KEY,
            `siswa_id`         INT NOT NULL,
            `program_id`       INT NOT NULL,
            `total_terkumpul`  DECIMAL(12,2) NOT NULL DEFAULT 0,
            `status`           ENUM('berjalan','lunas','mundur') NOT NULL DEFAULT 'berjalan',
            `created_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_siswa_program` (`siswa_id`, `program_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `tabungan_siswa` siap.\n";

    // =========================================================
    // 5. Tabel Transaksi Setoran Tabungan
    // =========================================================
    echo "\n5. Membuat tabel `tabungan_transaksi`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `tabungan_transaksi` (
            `id`                INT AUTO_INCREMENT PRIMARY KEY,
            `tabungan_siswa_id` INT NOT NULL,
            `jumlah`            DECIMAL(12,2) NOT NULL,
            `tanggal`           DATE NOT NULL,
            `dicatat_guru_id`   INT NOT NULL COMMENT 'Guru petugas yang input',
            `catatan`           VARCHAR(255) NULL,
            `created_at`        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `tabungan_transaksi` siap.\n";

    // =========================================================
    // 6. Tabel LMS Materi & Tugas
    // =========================================================
    echo "\n6. Membuat tabel `lms_materi`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `lms_materi` (
            `id`          INT AUTO_INCREMENT PRIMARY KEY,
            `judul`       VARCHAR(255) NOT NULL,
            `deskripsi`   TEXT NULL,
            `konten`      LONGTEXT NULL COMMENT 'Rich text HTML',
            `file_path`   VARCHAR(512) NULL,
            `file_name`   VARCHAR(255) NULL,
            `tipe`        ENUM('materi','tugas','pengumuman') NOT NULL DEFAULT 'materi',
            `mapel_id`    INT NULL,
            `kelas_id`    INT NULL,
            `guru_id`     INT NOT NULL,
            `deadline`    DATETIME NULL,
            `is_published` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `lms_materi` siap.\n";

    // =========================================================
    // 7. Tabel Submission Tugas Siswa
    // =========================================================
    echo "\n7. Membuat tabel `lms_submission`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `lms_submission` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `materi_id`     INT NOT NULL,
            `siswa_id`      INT NOT NULL,
            `file_path`     VARCHAR(512) NULL,
            `file_name`     VARCHAR(255) NULL,
            `jawaban`       TEXT NULL,
            `nilai`         DECIMAL(5,2) NULL,
            `catatan_guru`  TEXT NULL,
            `submitted_at`  TIMESTAMP NULL,
            `dinilai_at`    TIMESTAMP NULL,
            UNIQUE KEY `uk_sub_siswa` (`materi_id`, `siswa_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `lms_submission` siap.\n";

    // =========================================================
    // 8. Tabel Diskusi Forum
    // =========================================================
    echo "\n8. Membuat tabel `diskusi_thread`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `diskusi_thread` (
            `id`             INT AUTO_INCREMENT PRIMARY KEY,
            `judul`          VARCHAR(255) NOT NULL,
            `konten`         TEXT NOT NULL,
            `tipe_pengirim`  ENUM('siswa','guru') NOT NULL,
            `pengirim_id`    INT NOT NULL,
            `kelas_id`       INT NULL,
            `mapel_id`       INT NULL,
            `is_pinned`      TINYINT(1) NOT NULL DEFAULT 0,
            `is_closed`      TINYINT(1) NOT NULL DEFAULT 0,
            `reply_count`    INT NOT NULL DEFAULT 0,
            `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `diskusi_thread` siap.\n";

    echo "\n9. Membuat tabel `diskusi_reply`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `diskusi_reply` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `thread_id`     INT NOT NULL,
            `konten`        TEXT NOT NULL,
            `tipe_pengirim` ENUM('siswa','guru') NOT NULL,
            `pengirim_id`   INT NOT NULL,
            `is_best`       TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Jawaban terbaik ditandai guru',
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `diskusi_reply` siap.\n";

    // =========================================================
    // 10. Tabel Notifikasi In-App
    // =========================================================
    echo "\n10. Membuat tabel `notifikasi_siswa`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `notifikasi_siswa` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `siswa_id`   INT NOT NULL,
            `judul`      VARCHAR(255) NOT NULL,
            `pesan`      TEXT NOT NULL,
            `tipe`       ENUM('absensi','nilai','tabungan','lms','diskusi','pengumuman') NOT NULL DEFAULT 'pengumuman',
            `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "   ✓ Tabel `notifikasi_siswa` siap.\n";

    echo "\n========================================\n";
    echo "✅  Migration PWA Siswa SELESAI!\n";
    echo "========================================\n";
    echo "\nTabel yang dibuat/dimodifikasi:\n";
    echo "  - siswa (+ 6 kolom baru)\n";
    echo "  - tabungan_petugas (BARU)\n";
    echo "  - tabungan_program (BARU)\n";
    echo "  - tabungan_siswa   (BARU)\n";
    echo "  - tabungan_transaksi (BARU)\n";
    echo "  - lms_materi       (BARU)\n";
    echo "  - lms_submission   (BARU)\n";
    echo "  - diskusi_thread   (BARU)\n";
    echo "  - diskusi_reply    (BARU)\n";
    echo "  - notifikasi_siswa (BARU)\n";

} catch (Throwable $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File : " . $e->getFile() . " (line " . $e->getLine() . ")\n";
    exit(1);
}

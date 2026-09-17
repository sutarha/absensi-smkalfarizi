<?php
require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

try {
    $db = Database::getConnection();

    // 1. Create lms_materi table
    $db->exec("
    CREATE TABLE IF NOT EXISTS lms_materi (
        id INT PRIMARY KEY AUTO_INCREMENT,
        judul VARCHAR(255) NOT NULL,
        deskripsi TEXT,
        tipe ENUM('materi', 'tugas') NOT NULL DEFAULT 'materi',
        file_path VARCHAR(512) NULL,
        kelas_id INT NOT NULL,
        mapel_id INT NOT NULL,
        guru_id INT NOT NULL,
        deadline DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Tabel lms_materi berhasil dibuat.\n";

    // 2. Create lms_submission table
    $db->exec("
    CREATE TABLE IF NOT EXISTS lms_submission (
        id INT PRIMARY KEY AUTO_INCREMENT,
        materi_id INT NOT NULL,
        siswa_id INT NOT NULL,
        file_path VARCHAR(512) NULL,
        jawaban_teks TEXT NULL,
        nilai DECIMAL(5,2) NULL,
        catatan_guru TEXT NULL,
        submitted_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        dinilai_at TIMESTAMP NULL,
        FOREIGN KEY (materi_id) REFERENCES lms_materi(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Tabel lms_submission berhasil dibuat.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

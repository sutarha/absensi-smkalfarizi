<?php
require __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

try {
    $pdo = Database::getConnection();
    
    // Matikan foreign key check sementara
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Tabel nilai_semester
    $pdo->exec("CREATE TABLE IF NOT EXISTS nilai_semester (
        id INT AUTO_INCREMENT PRIMARY KEY,
        siswa_id INT NOT NULL,
        mapel_id INT NOT NULL,
        guru_id INT NOT NULL,
        semester_ke INT NOT NULL COMMENT '1 sampai 6',
        nilai_tugas DECIMAL(5,2) DEFAULT 0,
        nilai_uh DECIMAL(5,2) DEFAULT 0,
        nilai_uts DECIMAL(5,2) DEFAULT 0,
        nilai_uas DECIMAL(5,2) DEFAULT 0,
        nilai_akhir DECIMAL(5,2) DEFAULT 0,
        keterangan TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
        FOREIGN KEY (mapel_id) REFERENCES mata_pelajaran(id) ON DELETE CASCADE,
        FOREIGN KEY (guru_id) REFERENCES guru(id) ON DELETE CASCADE,
        UNIQUE KEY(siswa_id, mapel_id, semester_ke)
    ) ENGINE=InnoDB;");

    // Kembalikan foreign key check
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Tabel Nilai (nilai_semester) berhasil dibuat/diverifikasi.\n";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

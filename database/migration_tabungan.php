<?php
require __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

try {
    $pdo = Database::getConnection();
    
    // Matikan foreign key check sementara
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    $pdo->exec("DROP TABLE IF EXISTS tabungan_transaksi;");
    $pdo->exec("DROP TABLE IF EXISTS tabungan_siswa;");
    $pdo->exec("DROP TABLE IF EXISTS tabungan_petugas;");
    $pdo->exec("DROP TABLE IF EXISTS tabungan_program;");

    // Tabel tabungan_program (misal: "Beli Kaos Olahraga", "Study Tour")
    $pdo->exec("CREATE TABLE IF NOT EXISTS tabungan_program (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_program VARCHAR(100) NOT NULL,
        deskripsi TEXT,
        target_nominal DECIMAL(12,2) NOT NULL DEFAULT 0,
        target_tanggal DATE,
        kelas_id INT NULL COMMENT 'Jika NULL, berlaku untuk semua kelas',
        created_by_guru INT NULL,
        pengelola_id INT NULL COMMENT 'Guru pengelola utama',
        asisten_pengelola_id INT NULL COMMENT 'Asisten pengelola (opsional)',
        status ENUM('aktif','selesai','batal') DEFAULT 'aktif',
        foto VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (kelas_id) REFERENCES kelas(id) ON DELETE SET NULL,
        FOREIGN KEY (created_by_guru) REFERENCES guru(id) ON DELETE SET NULL,
        FOREIGN KEY (pengelola_id) REFERENCES guru(id) ON DELETE SET NULL,
        FOREIGN KEY (asisten_pengelola_id) REFERENCES guru(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");

    // Tabel tabungan_siswa
    $pdo->exec("CREATE TABLE IF NOT EXISTS tabungan_siswa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        siswa_id INT NOT NULL,
        program_id INT NOT NULL,
        total_terkumpul DECIMAL(12,2) NOT NULL DEFAULT 0,
        status ENUM('berjalan','lunas') DEFAULT 'berjalan',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (siswa_id) REFERENCES siswa(id) ON DELETE CASCADE,
        FOREIGN KEY (program_id) REFERENCES tabungan_program(id) ON DELETE CASCADE,
        UNIQUE KEY(siswa_id, program_id)
    ) ENGINE=InnoDB;");

    // Tabel tabungan_transaksi (sesuai Model TabunganTransaksi.php)
    $pdo->exec("CREATE TABLE IF NOT EXISTS tabungan_transaksi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tabungan_siswa_id INT NOT NULL,
        jumlah DECIMAL(12,2) NOT NULL,
        tanggal DATE,
        dicatat_guru_id INT NOT NULL COMMENT 'ID dari tabel guru (petugas)',
        catatan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (tabungan_siswa_id) REFERENCES tabungan_siswa(id) ON DELETE CASCADE,
        FOREIGN KEY (dicatat_guru_id) REFERENCES guru(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // Kembalikan foreign key check
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Tabel Tabungan (Recreated) berhasil dibuat/diperbarui.\n";
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

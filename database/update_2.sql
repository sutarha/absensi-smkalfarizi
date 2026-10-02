-- Update 2 (Penambahan Kolom Kunci Status Presensi & Tabel Pengajuan Izin Siswa)

-- 1. Menambahkan kolom untuk status terkunci di presensi gerbang
ALTER TABLE `presensi_gerbang_siswa` 
ADD COLUMN `is_terkunci` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN `dikunci_oleh` VARCHAR(255) NULL,
ADD COLUMN `dikunci_at` DATETIME NULL;

-- 2. Membuat tabel baru untuk pengajuan izin/sakit siswa secara mandiri (PWA)
CREATE TABLE IF NOT EXISTS `pengajuan_izin_siswa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `siswa_id` INT NOT NULL,
    `tanggal_mulai` DATE NOT NULL,
    `tanggal_selesai` DATE NOT NULL,
    `jenis_izin` ENUM('SAKIT', 'IZIN', 'TUGAS_LUAR') NOT NULL,
    `alasan` TEXT NOT NULL,
    `file_lampiran` VARCHAR(255) NULL,
    `status` ENUM('MENUNGGU', 'DISETUJUI', 'DITOLAK') NOT NULL DEFAULT 'MENUNGGU',
    `disetujui_oleh` VARCHAR(100) NULL,
    `disetujui_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

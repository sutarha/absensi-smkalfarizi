ALTER TABLE `presensi_gerbang_siswa` MODIFY COLUMN `status_kehadiran` ENUM('HADIR', 'ALPHA', 'SAKIT', 'IZIN', 'TUGAS_LUAR') NOT NULL DEFAULT 'ALPHA';

ALTER TABLE `konfigurasi_sekolah` 
ADD COLUMN `jam_gerbang_pulang_mulai_jumat` TIME NOT NULL DEFAULT '11:30:00' AFTER `jam_gerbang_pulang_selesai`,
ADD COLUMN `jam_gerbang_pulang_selesai_jumat` TIME NOT NULL DEFAULT '13:30:00' AFTER `jam_gerbang_pulang_mulai_jumat`;

CREATE TABLE IF NOT EXISTS `hari_libur` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tanggal` DATE NOT NULL UNIQUE,
    `keterangan` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

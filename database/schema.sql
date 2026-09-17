-- Skema Database: Sistem Informasi Presensi Cerdas & Penggajian Terintegrasi
-- Sekolah: SMK Al-Farizi
-- Target Database: MySQL 8.0+ / MariaDB 10.4+

CREATE DATABASE IF NOT EXISTS `db_presensi_smkalfarizi` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `db_presensi_smkalfarizi`;

-- 1. Tabel Konfigurasi Sekolah & Geofence
CREATE TABLE IF NOT EXISTS `konfigurasi_sekolah` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_sekolah` VARCHAR(150) NOT NULL DEFAULT 'SMK AL-FARIZI',
    `alamat_sekolah` TEXT NULL,
    `kepala_sekolah` VARCHAR(150) NOT NULL DEFAULT 'Drs. H. Ahmad Farizi, M.Pd.',
    `bendahara_tu` VARCHAR(150) NOT NULL DEFAULT 'Siti Aminah, S.E.',
    `latitude_pusat` DECIMAL(10, 8) NOT NULL DEFAULT -6.91746400,
    `longitude_pusat` DECIMAL(11, 8) NOT NULL DEFAULT 107.61912300,
    `radius_meter` INT NOT NULL DEFAULT 50,
    `honor_per_jp` DECIMAL(10, 2) NOT NULL DEFAULT 5000.00,
    `durasi_jp_menit` INT NOT NULL DEFAULT 40,
    `denda_per_menit` DECIMAL(10, 2) NOT NULL DEFAULT 125.00,
    `toleransi_h_minus` INT NOT NULL DEFAULT 5,
    `jam_gerbang_masuk_mulai` TIME NOT NULL DEFAULT '06:30:00',
    `jam_gerbang_masuk_selesai` TIME NOT NULL DEFAULT '07:30:00',
    `jam_gerbang_pulang_mulai` TIME NOT NULL DEFAULT '14:00:00',
    `jam_gerbang_pulang_selesai` TIME NOT NULL DEFAULT '16:00:00',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Master Kelas
CREATE TABLE IF NOT EXISTS `kelas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama_kelas` VARCHAR(50) NOT NULL UNIQUE,
    `tingkat` VARCHAR(10) NOT NULL,
    `jurusan` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Master Guru & Pengguna Sistem
CREATE TABLE IF NOT EXISTS `guru` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nik_nip` VARCHAR(50) NOT NULL UNIQUE,
    `nama_lengkap` VARCHAR(150) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'kepala_sekolah', 'wakasek_kurikulum', 'bendahara', 'guru', 'piket') NOT NULL DEFAULT 'guru',
    `tugas_tambahan` VARCHAR(100) NULL,
    `tunjangan_tugas` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `no_hp` VARCHAR(20) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Master Siswa & Barcode
CREATE TABLE IF NOT EXISTS `siswa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nisn` VARCHAR(20) NOT NULL UNIQUE,
    `barcode_code` VARCHAR(50) NOT NULL UNIQUE,
    `nama_siswa` VARCHAR(150) NOT NULL,
    `kelas_id` INT NULL,
    `jenis_kelamin` ENUM('L', 'P') NOT NULL DEFAULT 'L',
    `foto` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_siswa_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Jadwal Pelajaran (KBM)
CREATE TABLE IF NOT EXISTS `jadwal_pelajaran` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `hari` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    `kelas_id` INT NOT NULL,
    `guru_id` INT NOT NULL,
    `nama_mapel` VARCHAR(100) NOT NULL,
    `jumlah_jp` INT NOT NULL DEFAULT 2,
    `jam_mulai` TIME NOT NULL,
    `jam_selesai` TIME NOT NULL,
    CONSTRAINT `fk_jadwal_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jadwal_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Presensi Gerbang Siswa (Integritas Dua Arah Tap-in & Tap-out)
CREATE TABLE IF NOT EXISTS `presensi_gerbang_siswa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `siswa_id` INT NOT NULL,
    `tanggal` DATE NOT NULL,
    `waktu_datang` DATETIME NULL,
    `waktu_pulang` DATETIME NULL,
    `status_kehadiran` ENUM('HADIR', 'ALPHA') NOT NULL DEFAULT 'ALPHA',
    `keterangan` VARCHAR(255) NULL,
    CONSTRAINT `unq_siswa_hari` UNIQUE (`siswa_id`, `tanggal`),
    CONSTRAINT `fk_gerbang_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Sesi Mengajar Guru & Realisasi Honor (GPS & Denda Telat)
CREATE TABLE IF NOT EXISTS `sesi_mengajar_guru` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `jadwal_id` INT NOT NULL,
    `guru_id` INT NOT NULL,
    `tanggal` DATE NOT NULL,
    `waktu_checkin` DATETIME NOT NULL,
    `waktu_checkout` DATETIME NULL,
    `lat_checkin` DECIMAL(10, 8) NULL,
    `long_checkin` DECIMAL(11, 8) NULL,
    `jarak_meter` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `menit_terlambat` INT NOT NULL DEFAULT 0,
    `durasi_efektif_menit` INT NOT NULL DEFAULT 0,
    `honor_didapat` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `status_verifikasi` ENUM('VALID', 'DITOLAK_GPS') NOT NULL DEFAULT 'VALID',
    `catatan_guru` TEXT NULL,
    CONSTRAINT `unq_sesi_jadwal_tanggal` UNIQUE (`jadwal_id`, `tanggal`),
    CONSTRAINT `fk_sesi_jadwal` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_pelajaran` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sesi_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Detail Presensi Siswa per Sesi Mapel
CREATE TABLE IF NOT EXISTS `presensi_mapel_siswa` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sesi_mengajar_id` INT NOT NULL,
    `siswa_id` INT NOT NULL,
    `status` ENUM('HADIR', 'SAKIT', 'IZIN', 'ALPHA') NOT NULL DEFAULT 'HADIR',
    `catatan` VARCHAR(255) NULL,
    CONSTRAINT `unq_sesi_siswa` UNIQUE (`sesi_mengajar_id`, `siswa_id`),
    CONSTRAINT `fk_mapel_sesi` FOREIGN KEY (`sesi_mengajar_id`) REFERENCES `sesi_mengajar_guru` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mapel_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

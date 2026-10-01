<?php
/**
 * Migration M005 — Sprint 6: Status siswa (alumni, pindah) + log kenaikan kelas
 * UP  : Tambah kolom status siswa + buat tabel log_kenaikan_kelas
 * DOWN: Hapus tabel + kolom (rollback)
 */

return [
    'description' => 'Tambah status siswa (AKTIF/ALUMNI/PINDAH/KELUAR) dan tabel log kenaikan kelas (Sprint 6)',
    'up' => "
        ALTER TABLE `siswa`
            ADD COLUMN `status` ENUM('AKTIF','ALUMNI','PINDAH','KELUAR') NOT NULL DEFAULT 'AKTIF'
                COMMENT 'Status siswa saat ini'
                AFTER `kelas_id`;

        CREATE TABLE IF NOT EXISTS `log_kenaikan_kelas` (
            `id`              INT AUTO_INCREMENT PRIMARY KEY,
            `siswa_id`        INT NOT NULL,
            `kelas_asal_id`   INT NULL    COMMENT 'NULL jika siswa baru masuk',
            `kelas_tujuan_id` INT NULL    COMMENT 'NULL jika siswa lulus/keluar',
            `tapel_asal_id`   INT NOT NULL,
            `tapel_baru_id`   INT NOT NULL,
            `jenis`           ENUM('NAIK','TINGGAL','LULUS','PINDAH','KELUAR') NOT NULL DEFAULT 'NAIK',
            `catatan`         TEXT NULL,
            `diproses_oleh`   INT NOT NULL COMMENT 'guru_id (admin) yang mengeksekusi',
            `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_log_kk_siswa`     FOREIGN KEY (`siswa_id`)     REFERENCES `siswa`(`id`)           ON DELETE CASCADE,
            CONSTRAINT `fk_log_kk_tapel`     FOREIGN KEY (`tapel_asal_id`) REFERENCES `tahun_pelajaran`(`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_log_kk_tapel_baru` FOREIGN KEY (`tapel_baru_id`) REFERENCES `tahun_pelajaran`(`id`) ON DELETE RESTRICT,
            CONSTRAINT `fk_log_kk_admin`     FOREIGN KEY (`diproses_oleh`) REFERENCES `guru`(`id`)           ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='Riwayat kenaikan kelas / kelulusan siswa setiap tahun ajaran';
    ",
    'down' => "
        DROP TABLE IF EXISTS `log_kenaikan_kelas`;
        ALTER TABLE `siswa` DROP COLUMN IF EXISTS `status`;
    ",
];

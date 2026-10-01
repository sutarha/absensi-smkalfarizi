<?php
/**
 * Migration M003 — Sprint 2: Tabel pengajuan izin siswa
 * UP  : Buat tabel baru
 * DOWN: Drop tabel (rollback)
 */

return [
    'description' => 'Buat tabel pengajuan_izin_siswa untuk fitur izin dari PWA (Sprint 2)',
    'up' => "
        CREATE TABLE IF NOT EXISTS `pengajuan_izin_siswa` (
            `id`              INT AUTO_INCREMENT PRIMARY KEY,
            `siswa_id`        INT NOT NULL,
            `tanggal_mulai`   DATE NOT NULL,
            `tanggal_selesai` DATE NOT NULL,
            `jenis_izin`      ENUM('SAKIT','IZIN') NOT NULL DEFAULT 'IZIN',
            `alasan`          TEXT NOT NULL,
            `file_lampiran`   VARCHAR(255) NULL COMMENT 'Path surat dokter / lampiran',
            `status`          ENUM('MENUNGGU','DISETUJUI','DITOLAK') NOT NULL DEFAULT 'MENUNGGU',
            `diproses_oleh`   INT NULL COMMENT 'guru_id yang menyetujui/menolak',
            `diproses_at`     TIMESTAMP NULL,
            `catatan_guru`    TEXT NULL,
            `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_izin_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_izin_diproses` FOREIGN KEY (`diproses_oleh`) REFERENCES `guru`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
          COMMENT='Pengajuan izin/sakit siswa dari dashboard PWA';
    ",
    'down' => "
        DROP TABLE IF EXISTS `pengajuan_izin_siswa`;
    ",
];

<?php
/**
 * Migration M002 — Sprint 1: Kunci status absensi siswa
 * UP  : Tambah kolom is_terkunci, dikunci_oleh, dikunci_at
 * DOWN: Hapus kolom (rollback)
 */

return [
    'description' => 'Tambah kolom kunci status absensi siswa (Sprint 1)',
    'up' => "
        ALTER TABLE `presensi_gerbang_siswa`
            ADD COLUMN `is_terkunci` TINYINT(1) NOT NULL DEFAULT 0
                COMMENT '1 = status dikunci oleh admin, tidak bisa berubah otomatis'
                AFTER `status_kehadiran`,
            ADD COLUMN `dikunci_oleh` VARCHAR(100) NULL
                COMMENT 'Username admin yang mengunci'
                AFTER `is_terkunci`,
            ADD COLUMN `dikunci_at` TIMESTAMP NULL
                COMMENT 'Waktu penguncian status'
                AFTER `dikunci_oleh`;
    ",
    'down' => "
        ALTER TABLE `presensi_gerbang_siswa`
            DROP COLUMN IF EXISTS `is_terkunci`,
            DROP COLUMN IF EXISTS `dikunci_oleh`,
            DROP COLUMN IF EXISTS `dikunci_at`;
    ",
];

<?php
/**
 * Migration M001 — Tambah kolom gambar_kop_surat ke konfigurasi_sekolah
 * UP  : Tambah kolom
 * DOWN: Hapus kolom (rollback)
 */

return [
    'description' => 'Tambah kolom gambar_kop_surat untuk upload header surat (terpisah dari logo)',
    'up' => "
        ALTER TABLE `konfigurasi_sekolah`
            ADD COLUMN `gambar_kop_surat` VARCHAR(255) NULL COMMENT 'Path gambar kop surat utuh (header)'
            AFTER `logo_kop`;
    ",
    'down' => "
        ALTER TABLE `konfigurasi_sekolah`
            DROP COLUMN IF EXISTS `gambar_kop_surat`;
    ",
];

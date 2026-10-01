<?php
/**
 * Migration M004 — Sprint 3: Pengaturan notifikasi WA di konfigurasi_sekolah
 * UP  : Tambah kolom fonnte_token + toggle notifikasi
 * DOWN: Hapus kolom (rollback)
 */

return [
    'description' => 'Tambah pengaturan notifikasi WhatsApp (Fonnte) ke konfigurasi_sekolah (Sprint 3)',
    'up' => "
        ALTER TABLE `konfigurasi_sekolah`
            ADD COLUMN `fonnte_token`      VARCHAR(255) NULL
                COMMENT 'API Token Fonnte untuk notif WhatsApp'
                AFTER `updated_at`,
            ADD COLUMN `wa_notif_aktif`    TINYINT(1) NOT NULL DEFAULT 0
                COMMENT 'Master switch notifikasi WA (0=off, 1=on)'
                AFTER `fonnte_token`,
            ADD COLUMN `wa_notif_tap_in`   TINYINT(1) NOT NULL DEFAULT 1
                COMMENT 'Notif saat siswa tap-in gerbang'
                AFTER `wa_notif_aktif`,
            ADD COLUMN `wa_notif_alpha`    TINYINT(1) NOT NULL DEFAULT 1
                COMMENT 'Notif saat siswa alpha tanpa keterangan'
                AFTER `wa_notif_tap_in`,
            ADD COLUMN `wa_notif_izin`     TINYINT(1) NOT NULL DEFAULT 1
                COMMENT 'Notif saat izin disetujui/ditolak'
                AFTER `wa_notif_alpha`,
            ADD COLUMN `wa_notif_bulanan`  TINYINT(1) NOT NULL DEFAULT 1
                COMMENT 'Notif rekap bulanan ke orang tua'
                AFTER `wa_notif_izin`;
    ",
    'down' => "
        ALTER TABLE `konfigurasi_sekolah`
            DROP COLUMN IF EXISTS `fonnte_token`,
            DROP COLUMN IF EXISTS `wa_notif_aktif`,
            DROP COLUMN IF EXISTS `wa_notif_tap_in`,
            DROP COLUMN IF EXISTS `wa_notif_alpha`,
            DROP COLUMN IF EXISTS `wa_notif_izin`,
            DROP COLUMN IF EXISTS `wa_notif_bulanan`;
    ",
];

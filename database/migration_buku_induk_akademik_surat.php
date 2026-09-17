<?php
// database/migration_buku_induk_akademik_surat.php
declare(strict_types=1);

require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

try {
    $db = Database::getConnection();
    echo "Terhubung ke database MySQL...\n";

    // 1. Modifikasi tabel konfigurasi_sekolah untuk KOP Surat resmi
    echo "1. Memeriksa kolom KOP pada konfigurasi_sekolah...\n";
    $cols = $db->query("SHOW COLUMNS FROM `konfigurasi_sekolah`")->fetchAll(PDO::FETCH_COLUMN);
    
    $kopCols = [
        'npsn' => "VARCHAR(20) NOT NULL DEFAULT '69912345'",
        'nss' => "VARCHAR(20) NOT NULL DEFAULT '402020202020'",
        'akreditasi' => "VARCHAR(10) NOT NULL DEFAULT 'A (Unggul)'",
        'email_sekolah' => "VARCHAR(100) NOT NULL DEFAULT 'info@smkalfarizi.sch.id'",
        'website_sekolah' => "VARCHAR(100) NOT NULL DEFAULT 'www.smkalfarizi.sch.id'",
        'logo_kop' => "VARCHAR(255) NULL"
    ];

    foreach ($kopCols as $col => $def) {
        if (!in_array($col, $cols)) {
            $db->exec("ALTER TABLE `konfigurasi_sekolah` ADD COLUMN `$col` $def AFTER `alamat_sekolah`");
            echo "   + Kolom `$col` berhasil ditambahkan ke `konfigurasi_sekolah`.\n";
        }
    }

    // 2. Master Tahun Pelajaran
    echo "2. Membuat tabel `tahun_pelajaran`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `tahun_pelajaran` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `tahun_ajaran` VARCHAR(20) NOT NULL,
            `semester` ENUM('Ganjil', 'Genap') NOT NULL,
            `is_aktif` TINYINT(1) NOT NULL DEFAULT 0,
            `tanggal_mulai` DATE NULL,
            `tanggal_selesai` DATE NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `unq_tapel_sem` UNIQUE (`tahun_ajaran`, `semester`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 3. Riwayat Penempatan Kelas Siswa per Tahun Pelajaran
    echo "3. Membuat tabel `riwayat_kelas_siswa`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `riwayat_kelas_siswa` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `siswa_id` INT NOT NULL,
            `kelas_id` INT NOT NULL,
            `tahun_pelajaran_id` INT NOT NULL,
            `status_kenaikan` ENUM('BARU', 'NAIK', 'TINGGAL', 'LULUS', 'MUTASI') NOT NULL DEFAULT 'BARU',
            `catatan` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `unq_rks` UNIQUE (`siswa_id`, `tahun_pelajaran_id`),
            CONSTRAINT `fk_rks_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_rks_kelas` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_rks_tapel` FOREIGN KEY (`tahun_pelajaran_id`) REFERENCES `tahun_pelajaran` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 4. Buku Induk Siswa Lengkap (Standar Dapodik)
    echo "4. Membuat tabel `buku_induk_siswa`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `buku_induk_siswa` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `siswa_id` INT NOT NULL UNIQUE,
            `nis` VARCHAR(30) NULL,
            `nik` VARCHAR(20) NULL,
            `no_kk` VARCHAR(20) NULL,
            `no_akta_lahir` VARCHAR(50) NULL,
            `tempat_lahir` VARCHAR(100) NULL,
            `tanggal_lahir` DATE NULL,
            `agama` VARCHAR(30) NULL DEFAULT 'Islam',
            `kewarganegaraan` VARCHAR(30) DEFAULT 'WNI',
            `anak_ke` INT DEFAULT 1,
            `jumlah_saudara` INT DEFAULT 0,
            -- Alamat
            `alamat_jalan` TEXT NULL,
            `rt` VARCHAR(5) NULL,
            `rw` VARCHAR(5) NULL,
            `dusun_kelurahan` VARCHAR(100) NULL,
            `kecamatan` VARCHAR(100) NULL,
            `kabupaten_kota` VARCHAR(100) NULL,
            `provinsi` VARCHAR(100) NULL,
            `kode_pos` VARCHAR(10) NULL,
            `tinggal_bersama` VARCHAR(50) DEFAULT 'Orang Tua',
            `transportasi` VARCHAR(50) DEFAULT 'Sepeda Motor',
            -- Data Orang Tua
            `nama_ayah` VARCHAR(150) NULL,
            `nik_ayah` VARCHAR(20) NULL,
            `tahun_lahir_ayah` VARCHAR(4) NULL,
            `pendidikan_ayah` VARCHAR(50) NULL,
            `pekerjaan_ayah` VARCHAR(100) NULL,
            `penghasilan_ayah` VARCHAR(50) NULL,
            `nama_ibu` VARCHAR(150) NULL,
            `nik_ibu` VARCHAR(20) NULL,
            `tahun_lahir_ibu` VARCHAR(4) NULL,
            `pendidikan_ibu` VARCHAR(50) NULL,
            `pekerjaan_ibu` VARCHAR(100) NULL,
            `penghasilan_ibu` VARCHAR(50) NULL,
            `nama_wali` VARCHAR(150) NULL,
            `nik_wali` VARCHAR(20) NULL,
            `pekerjaan_wali` VARCHAR(100) NULL,
            `no_hp_ortu` VARCHAR(20) NULL,
            -- Registrasi & Riwayat
            `sekolah_asal` VARCHAR(150) NULL,
            `no_ijazah_smp` VARCHAR(50) NULL,
            `no_skhun_smp` VARCHAR(50) NULL,
            `tanggal_masuk` DATE NULL,
            `status_siswa` ENUM('AKTIF', 'LULUS', 'MUTASI', 'KELUAR', 'MENINGGAL') NOT NULL DEFAULT 'AKTIF',
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `fk_buku_induk_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 5. Master Mata Pelajaran Kurikulum
    echo "5. Membuat tabel `mata_pelajaran`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `mata_pelajaran` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `kode_mapel` VARCHAR(30) NOT NULL UNIQUE,
            `nama_mapel` VARCHAR(150) NOT NULL,
            `kelompok` ENUM('Umum', 'Kejuruan', 'Muatan Lokal', 'Pilihan') NOT NULL DEFAULT 'Umum',
            `tingkat` VARCHAR(50) NOT NULL DEFAULT 'SEMUA'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 6. Tabel Nilai Siswa (Semester 1 s/d 6)
    echo "6. Membuat tabel `nilai_siswa`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `nilai_siswa` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `siswa_id` INT NOT NULL,
            `mapel_id` INT NOT NULL,
            `semester_ke` TINYINT NOT NULL,
            `tahun_pelajaran_id` INT NULL,
            `nilai_formatif` DECIMAL(5, 2) DEFAULT 0.00,
            `nilai_sumatif_materi` DECIMAL(5, 2) DEFAULT 0.00,
            `nilai_sas` DECIMAL(5, 2) DEFAULT 0.00,
            `nilai_akhir` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
            `predikat` ENUM('A', 'B', 'C', 'D') NOT NULL DEFAULT 'B',
            `capaian_kompetensi` TEXT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT `unq_siswa_mapel_semester` UNIQUE (`siswa_id`, `mapel_id`, `semester_ke`),
            CONSTRAINT `fk_nilai_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_nilai_mapel` FOREIGN KEY (`mapel_id`) REFERENCES `mata_pelajaran` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 7. Tabel Arsip Surat Keluar Otomatis (Termasuk SPPD & Surat Tugas)
    echo "7. Membuat tabel `arsip_surat`...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS `arsip_surat` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nomor_surat` VARCHAR(100) NOT NULL UNIQUE,
            `jenis_surat` ENUM('SISWA_AKTIF', 'BERKELAKUAN_BAIK', 'IZIN_PKL', 'PANGGILAN_ORTU', 'SKL', 'SPPD', 'SURAT_TUGAS', 'LAINNYA') NOT NULL,
            `penerima_tipe` ENUM('SISWA', 'GURU', 'PEGAWAI') NOT NULL DEFAULT 'SISWA',
            `siswa_id` INT NULL,
            `guru_id` INT NULL,
            `perihal` VARCHAR(255) NOT NULL,
            `keperluan` TEXT NULL,
            `tanggal_surat` DATE NOT NULL,
            -- Field Khusus SPPD & Surat Tugas Dinas
            `dasar_penugasan` TEXT NULL,
            `tempat_berangkat` VARCHAR(150) DEFAULT 'SMK AL-FARIZI',
            `tempat_tujuan` VARCHAR(150) NULL,
            `instansi_tujuan` VARCHAR(150) NULL,
            `pejabat_tujuan` VARCHAR(150) NULL,
            `tanggal_berangkat` DATE NULL,
            `tanggal_kembali` DATE NULL,
            `lama_hari` INT DEFAULT 1,
            `alat_angkut` VARCHAR(50) DEFAULT 'Kendaraan Dinas / Umum',
            `beban_anggaran` VARCHAR(100) DEFAULT 'Dana BOS SMK Al-Farizi',
            `pengikut` TEXT NULL,
            `pejabat_penandatangan` VARCHAR(150) NOT NULL,
            `file_path` VARCHAR(255) NULL,
            `created_by` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT `fk_surat_siswa` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_surat_guru` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // 8. Seeding Default Data
    echo "8. Menyiapkan Data Awal (Seeder)...\n";
    
    // Tahun Pelajaran Awal
    $tapels = [
        ['tahun_ajaran' => '2024/2025', 'semester' => 'Ganjil', 'is_aktif' => 0],
        ['tahun_ajaran' => '2024/2025', 'semester' => 'Genap', 'is_aktif' => 0],
        ['tahun_ajaran' => '2025/2026', 'semester' => 'Ganjil', 'is_aktif' => 1],
        ['tahun_ajaran' => '2025/2026', 'semester' => 'Genap', 'is_aktif' => 0],
        ['tahun_ajaran' => '2026/2027', 'semester' => 'Ganjil', 'is_aktif' => 0],
    ];

    $stmtTapel = $db->prepare("INSERT IGNORE INTO `tahun_pelajaran` (`tahun_ajaran`, `semester`, `is_aktif`) VALUES (?, ?, ?)");
    foreach ($tapels as $t) {
        $stmtTapel->execute([$t['tahun_ajaran'], $t['semester'], $t['is_aktif']]);
    }

    // Ambil ID tapel aktif
    $activeTapelId = (int)$db->query("SELECT id FROM `tahun_pelajaran` WHERE `is_aktif` = 1 LIMIT 1")->fetchColumn();

    // Master Mapel Standar SMK (Kurikulum Merdeka & K13)
    $mapels = [
        ['PAI', 'Pendidikan Agama Islam dan Budi Pekerti', 'Umum', 'SEMUA'],
        ['PPKN', 'Pendidikan Pancasila dan Kewarganegaraan', 'Umum', 'SEMUA'],
        ['BINDO', 'Bahasa Indonesia', 'Umum', 'SEMUA'],
        ['MTK', 'Matematika', 'Umum', 'SEMUA'],
        ['SEJ', 'Sejarah Indonesia', 'Umum', 'X'],
        ['BING', 'Bahasa Inggris', 'Umum', 'SEMUA'],
        ['PJOK', 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'Umum', 'X'],
        ['IPAS', 'Projek Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'Umum', 'X'],
        ['INF', 'Informatika', 'Umum', 'X'],
        ['DASAR_KEJURUAN', 'Dasar-Dasar Program Keahlian', 'Kejuruan', 'X'],
        ['KONSENTRASI', 'Konsentrasi Keahlian (PPLG / TKJ)', 'Kejuruan', 'XI'],
        ['PKK', 'Produk Kreatif dan Kewirausahaan (PKK)', 'Kejuruan', 'XII'],
        ['PKL', 'Praktik Kerja Lapangan (PKL)', 'Kejuruan', 'XII'],
        ['MULOK_SUNDA', 'Bahasa dan Seni Daerah (Sunda)', 'Muatan Lokal', 'SEMUA'],
    ];

    $stmtMapel = $db->prepare("INSERT IGNORE INTO `mata_pelajaran` (`kode_mapel`, `nama_mapel`, `kelompok`, `tingkat`) VALUES (?, ?, ?, ?)");
    foreach ($mapels as $m) {
        $stmtMapel->execute([$m[0], $m[1], $m[2], $m[3]]);
    }

    // Inisialisasi Buku Induk & Riwayat Kelas untuk siswa yang sudah ada
    $allSiswa = $db->query("SELECT s.id, s.nisn, s.nama_siswa, s.kelas_id, s.jenis_kelamin FROM `siswa` s")->fetchAll(PDO::FETCH_ASSOC);
    $stmtBuku = $db->prepare("
        INSERT IGNORE INTO `buku_induk_siswa` 
        (`siswa_id`, `nis`, `nik`, `tempat_lahir`, `tanggal_lahir`, `alamat_jalan`, `nama_ayah`, `nama_ibu`, `sekolah_asal`, `tanggal_masuk`, `status_siswa`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'AKTIF')
    ");

    $stmtRks = $db->prepare("
        INSERT IGNORE INTO `riwayat_kelas_siswa` 
        (`siswa_id`, `kelas_id`, `tahun_pelajaran_id`, `status_kenaikan`, `catatan`) 
        VALUES (?, ?, ?, 'BARU', 'Inisialisasi Data Awal')
    ");

    foreach ($allSiswa as $s) {
        $nis = 'AF-' . substr($s['nisn'], -4);
        $nik = '3204' . str_pad((string)$s['id'], 12, '0', STR_PAD_LEFT);
        $tglLahir = '2008-05-15';
        $stmtBuku->execute([
            $s['id'],
            $nis,
            $nik,
            'Bandung',
            $tglLahir,
            'Jl. Raya Al-Farizi No. ' . $s['id'] . ', RT 01 / RW 02',
            'Bapak ' . $s['nama_siswa'],
            'Ibu ' . $s['nama_siswa'],
            'SMP Negeri 1 Bojongsoang',
            '2024-07-15'
        ]);

        if ($activeTapelId) {
            $stmtRks->execute([$s['id'], $s['kelas_id'], $activeTapelId]);
        }
    }

    echo "✅ MIGRASI DAN SEEDING DATA SELESAI DENGAN SUKSES!\n";

} catch (\Throwable $e) {
    echo "❌ ERROR MIGRASI: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

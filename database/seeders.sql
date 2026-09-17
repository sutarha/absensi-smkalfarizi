USE `db_presensi_smkalfarizi`;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Konfigurasi Awal Sekolah
INSERT INTO `konfigurasi_sekolah` (
    `id`, `nama_sekolah`, `alamat_sekolah`, `kepala_sekolah`, `bendahara_tu`,
    `latitude_pusat`, `longitude_pusat`, `radius_meter`, `honor_per_jp`,
    `durasi_jp_menit`, `denda_per_menit`, `toleransi_h_minus`,
    `jam_gerbang_masuk_mulai`, `jam_gerbang_masuk_selesai`,
    `jam_gerbang_pulang_mulai`, `jam_gerbang_pulang_selesai`
) VALUES (
    1,
    'SMK AL-FARIZI',
    'Jl. Pendidikan No. 45, Terusan Al-Farizi, Jawa Barat',
    'Drs. H. Ahmad Farizi, M.Pd.',
    'Siti Aminah, S.E.',
    -6.91746400,
    107.61912300,
    100, -- Default 100m toleransi
    5000.00,
    40,
    125.00,
    5,
    '06:30:00',
    '07:30:00',
    '14:00:00',
    '16:30:00'
) ON DUPLICATE KEY UPDATE `nama_sekolah` = VALUES(`nama_sekolah`);

-- 2. Master Kelas
INSERT INTO `kelas` (`id`, `nama_kelas`, `tingkat`, `jurusan`) VALUES
(1, 'X PPLG 1', 'X', 'Pengembangan Perangkat Lunak dan GIM'),
(2, 'XI PPLG 1', 'XI', 'Pengembangan Perangkat Lunak dan GIM'),
(3, 'XII RPL 1', 'XII', 'Rekayasa Perangkat Lunak'),
(4, 'X TJKT 1', 'X', 'Teknik Jaringan Komputer dan Telekomunikasi')
ON DUPLICATE KEY UPDATE `nama_kelas` = VALUES(`nama_kelas`);

-- 3. Master Guru (Password default: 'admin123' untuk admin, 'guru123' untuk guru, 'piket123' untuk piket)
-- Hash bcrypt PHP:
-- admin123: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (standard test secret/hash)
-- atau gunakan hash teruji
INSERT INTO `guru` (`id`, `nik_nip`, `nama_lengkap`, `username`, `password`, `role`, `tugas_tambahan`, `tunjangan_tugas`, `no_hp`) VALUES
(1, '197505122000031001', 'Dra. Hj. Nurhayati, M.M. (TU)', 'admin', '$2y$10$wT0XkO07r/c1w3KkYx7k/e5fPqv3tO3v1V.J4G9x4p6l/z2B6e6K.', 'admin', 'Kepala Tata Usaha', 500000.00, '081234567890'),
(2, '198501102010011001', 'Budi Santoso, S.Kom.', 'guru1', '$2y$10$wT0XkO07r/c1w3KkYx7k/e5fPqv3tO3v1V.J4G9x4p6l/z2B6e6K.', 'guru', 'Wali Kelas X PPLG 1', 300000.00, '081298765432'),
(3, '198803152012022002', 'Dewi Lestari, M.Pd.', 'guru2', '$2y$10$wT0XkO07r/c1w3KkYx7k/e5fPqv3tO3v1V.J4G9x4p6l/z2B6e6K.', 'guru', 'Pembina Pramuka', 200000.00, '081311223344'),
(4, '199207212018011005', 'Rizky Ramadhan, S.T.', 'guru3', '$2y$10$wT0XkO07r/c1w3KkYx7k/e5fPqv3tO3v1V.J4G9x4p6l/z2B6e6K.', 'guru', 'Kepala Bengkel RPL', 250000.00, '081377889900'),
(5, '199005202015031003', 'Ahmad Fauzi, S.Pd. (Piket)', 'piket', '$2y$10$wT0XkO07r/c1w3KkYx7k/e5fPqv3tO3v1V.J4G9x4p6l/z2B6e6K.', 'piket', 'Koordinator Guru Piket', 150000.00, '085612345678')
ON DUPLICATE KEY UPDATE `nama_lengkap` = VALUES(`nama_lengkap`);

-- 4. Master Siswa (Lengkap dengan NISN dan Barcode Code 128)
INSERT INTO `siswa` (`id`, `nisn`, `barcode_code`, `nama_siswa`, `kelas_id`, `jenis_kelamin`, `foto`) VALUES
(1, '0071234501', 'ALF-0071234501', 'Aditya Pratama', 1, 'L', 'uploads/siswa/default_m.png'),
(2, '0071234502', 'ALF-0071234502', 'Anisa Rahmawati', 1, 'P', 'uploads/siswa/default_f.png'),
(3, '0071234503', 'ALF-0071234503', 'Bagus Setiawan', 1, 'L', 'uploads/siswa/default_m.png'),
(4, '0071234504', 'ALF-0071234504', 'Cantika Putri', 1, 'P', 'uploads/siswa/default_f.png'),
(5, '0071234505', 'ALF-0071234505', 'Dimas Anggara', 1, 'L', 'uploads/siswa/default_m.png'),
(6, '0071234506', 'ALF-0071234506', 'Fajar Ramadhan', 1, 'L', 'uploads/siswa/default_m.png'),
(7, '0071234507', 'ALF-0071234507', 'Gita Gutawa Putri', 1, 'P', 'uploads/siswa/default_f.png'),
(8, '0071234508', 'ALF-0071234508', 'Hafizh Al-Farisi', 1, 'L', 'uploads/siswa/default_m.png'),
(9, '0071234509', 'ALF-0071234509', 'Intan Permatasari', 2, 'P', 'uploads/siswa/default_f.png'),
(10, '0071234510', 'ALF-0071234510', 'Joko Susilo', 2, 'L', 'uploads/siswa/default_m.png')
ON DUPLICATE KEY UPDATE `nama_siswa` = VALUES(`nama_siswa`);

-- 5. Jadwal Pelajaran (KBM)
-- Format Hari: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu
INSERT INTO `jadwal_pelajaran` (`id`, `hari`, `kelas_id`, `guru_id`, `nama_mapel`, `jumlah_jp`, `jam_mulai`, `jam_selesai`) VALUES
(1, 'Senin', 1, 2, 'Pemodelan Perangkat Lunak (RPL)', 3, '07:30:00', '09:30:00'),
(2, 'Senin', 1, 3, 'Basis Data Relasional (MySQL)', 2, '09:45:00', '11:05:00'),
(3, 'Senin', 2, 4, 'Pemrograman Web & Mobile', 4, '11:05:00', '14:25:00'),
(4, 'Selasa', 1, 2, 'Pemrograman Berorientasi Objek', 3, '07:30:00', '09:30:00'),
(5, 'Selasa', 2, 3, 'Kreatif dan Kewirausahaan (PKK)', 2, '09:45:00', '11:05:00'),
(6, 'Rabu', 1, 4, 'Pemrograman Web (Backend PHP)', 4, '07:30:00', '10:10:00'),
(7, 'Kamis', 1, 2, 'Cloud Computing & VPS Deployment', 2, '07:30:00', '08:50:00'),
(8, 'Jumat', 1, 3, 'Pendidikan Agama & Budi Pekerti', 2, '07:30:00', '08:50:00'),
(9, 'Sabtu', 1, 2, 'Proyek Pengembangan Game & App', 3, '08:00:00', '10:00:00'),
-- Jadwal fleksibel untuk simulasi hari ini sepanjang jam operasional
(10, 'Senin', 1, 2, 'Workshop Praktik Industri', 2, '13:00:00', '14:20:00'),
(11, 'Selasa', 1, 2, 'Workshop Praktik Industri', 2, '13:00:00', '14:20:00'),
(12, 'Rabu', 1, 2, 'Workshop Praktik Industri', 2, '13:00:00', '14:20:00'),
(13, 'Kamis', 1, 2, 'Workshop Praktik Industri', 2, '13:00:00', '14:20:00'),
(14, 'Jumat', 1, 2, 'Workshop Praktik Industri', 2, '13:00:00', '14:20:00'),
(15, 'Sabtu', 1, 2, 'Workshop Praktik Industri', 2, '13:00:00', '14:20:00')
ON DUPLICATE KEY UPDATE `nama_mapel` = VALUES(`nama_mapel`);

SET FOREIGN_KEY_CHECKS = 1;

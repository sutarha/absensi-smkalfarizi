# Sistem Informasi Presensi Cerdas & Penggajian Terintegrasi (SMK AL-FARIZI)

Aplikasi web modern berbasis **Mobile-First Responsive / Progressive Web App (PWA)** untuk Guru & Piket, serta **Desktop-Optimized** untuk Super Admin / Tata Usaha (TU). Sistem ini mengintegrasikan seluruh mata rantai pencatatan kehadiran dan proses belajar mengajar (KBM) dengan database **MySQL / MariaDB**.

---

## 🌟 Fitur Utama & Core Business Logic

1. **Disiplin KBM Guru & Penalti Keterlambatan Per Menit**:
   - $1\text{ JP} = 40\text{ menit}$. Tarif dasar honor: $\text{Rp } 5.000/\text{JP} \implies \text{Rp } 125/\text{menit}$.
   - Tombol check-in otomatis terkunci dan terbuka tepat **5 menit sebelum jam mulai jadwal** ($T_{\text{mulai}} - 5\text{ menit}$).
   - Validasi koordinat GPS Geofencing (radius toleransi sekolah). Check-in di luar radius otomatis ditolak.
   - Perhitungan keterlambatan riil:
     - Tepat Waktu: $0\text{ menit}$ terlambat, plafon honor penuh 100%.
     - Terlambat: Denda $=\Delta t_{\text{terlambat}} \times \text{Rp } 125$. Honor sesi didapat $= \text{Durasi Efektif} \times \text{Rp } 125$.
2. **Integritas Kehadiran Siswa Dua Arah (Gerbang Tap-In & Tap-Out)**:
   - Siswa wajib memindai kartu barcode saat tiba (Pagi 06.30 - 07.30) dan kepulangan (Sore 14.00 - 16.00).
   - **Aturan Integritas**: Jika siswa hanya tap datang tanpa tap pulang (atau sebaliknya), sistem/cron job otomatis mengunci status harian siswa menjadi **ALPHA**.
3. **Ketuntasan Mapel Harian Siswa**:
   - Guru mapel mencatat presensi siswa di kelas (Hadir, Sakit, Izin, Alpha).
   - Jika siswa memiliki minimal 1 (satu) catatan tidak hadir (Sakit, Izin, Alpha) pada salah satu sesi mapel hari tersebut, rekapitulasi mapel harian otomatis dinyatakan **TIDAK HADIR / TIDAK TUNTAS**.
4. **Modul Guru Piket (Mobile Web-Cam Scanner & Audio Beep)**:
   - Pemindai barcode berbasis browser kamera belakang (tanpa aplikasi native).
   - **Web Audio API**: Suara nada frekuensi tinggi (BEEP) instan seketika barcode terdeteksi tanpa delay audio eksternal.
   - Pop-up kartu siswa hijau selama 1.5 detik sebelum siap memindai siswa berikutnya.
5. **Master Barcode Generator & Cetak Kartu Siswa**:
   - Auto-generate kode barcode Code 128 (SVG tajam).
   - Template siap cetak format A4 portrait (8-10 kartu pelajar per lembar lengkap dengan foto dan identitas).
6. **Otomatisasi Penggajian & Cetak Slip Gaji Resmi PDF**:
   - Perhitungan otomatis rekapitulasi bulanan: $(\text{Total JP Valid} \times \text{Honor Riil Menit}) - \text{Denda Telat} + \text{Tunjangan Tugas Tambahan (Wali Kelas/Pembina)}$.
   - Cetak slip gaji per guru resmi dengan Kop Surat SMK Al-Farizi, rincian potongan telat, dan kolom tanda tangan TU/Kepala Sekolah.

---

## 🔑 Akun Demo Default

| Role | Username | Password | Keterangan & Tugas Tambahan |
|---|---|---|---|
| **Super Admin (TU)** | `admin` | `admin123` | Kepala Tata Usaha (Full Master Data, GPS & Payroll) |
| **Guru Mapel 1** | `guru1` | `guru123` | Budi Santoso, S.Kom. (Wali Kelas X PPLG 1 - Tunjangan Rp 300.000) |
| **Guru Mapel 2** | `guru2` | `guru123` | Dewi Lestari, M.Pd. (Pembina Pramuka - Tunjangan Rp 200.000) |
| **Guru Mapel 3** | `guru3` | `guru123` | Rizky Ramadhan, S.T. (Kepala Bengkel RPL - Tunjangan Rp 250.000) |
| **Guru Piket** | `piket` | `piket123` | Ahmad Fauzi, S.Pd. (Scanner Kamera Gerbang & Audio Beep) |

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Prasyarat Sistem
- **PHP**: Versi 8.2 atau lebih baru (ekstensi `pdo_mysql`, `mbstring`, `gd`, `fileinfo` aktif).
- **MySQL / MariaDB**: Server MySQL aktif di port `3306` (contoh: via Laragon, XAMPP, atau MySQL service).

### 2. Inisialisasi Database
Jalankan script migrasi otomatis untuk membuat database `db_presensi_smkalfarizi` dan memasukkan data seeder awal:
```bash
php database/migrate.php
```

### 3. Menjalankan Server Lokal
Jalankan server built-in PHP pada folder `public`:
```bash
php -S 127.0.0.1:8000 -t public
```
Akses aplikasi melalui browser: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**.

---

## 🧪 Menjalankan Pengujian Otomatis (Test Suite)

Jalankan pengujian unit test dan validasi seluruh formulasi matematika serta aturan bisnis PRD:
```bash
php tests/system_test.php
```
Pengujian mencakup:
- Formulasi denda keterlambatan Rp 125/menit dan plafon honor.
- Jendela tombol H-5 menit.
- Validasi jarak GPS Haversine.
- Generator Barcode Code 128 SVG.
- Integritas gerbang dua arah & auto-drop ALPHA.
- Generator slip gaji PDF resmi.

Jalankan pengujian HTTP web server & otentikasi:
```bash
php tests/http_test.php
```

---

## 🌐 Panduan Deployment Server

### Opsi A: Cloud VPS (Ubuntu + Nginx / Apache)
1. Clone repositori ke direktori web server (misal `/var/www/smkalfarizi`).
2. Konfigurasi Nginx Server Block:
   ```nginx
   server {
       listen 80;
       server_name presensi.smkalfarizi.sch.id;
       root /var/www/smkalfarizi/public;
       index index.php index.html;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
       }
   }
   ```
3. Pasang sertifikat SSL gratis via Certbot (Wajib untuk HTTPS agar kamera & GPS browser berfungsi):
   ```bash
   sudo certbot --nginx -d presensi.smkalfarizi.sch.id
   ```
4. Pasang Cron Job sore hari untuk evaluasi otomatis siswa yang tidak tap-out:
   ```bash
   crontab -e
   # Tambahkan baris berikut (dieksekusi setiap hari Senin-Sabtu jam 17:00):
   0 17 * * 1-6 php /var/www/smkalfarizi/cron/evaluasi_harian.php >> /var/log/presensi_cron.log 2>&1
   ```

### Opsi B: Standard cPanel / DirectAdmin Hosting
1. Upload file proyek ke cPanel. Arahkan **Document Root** domain/subdomain ke folder `public_html/public` (atau extract isi `public` ke root `public_html` dan sesuaikan path helper).
2. Buat database MySQL di menu cPanel *MySQL Database Wizard*, lalu impor file `database/schema.sql` dan `database/seeders.sql` via phpMyAdmin.
3. Pastikan SSL/HTTPS berstatus aktif (AutoSSL cPanel).
4. Atur Cron Job di menu cPanel *Cron Jobs*:
   - Waktu: `0 17 * * 1-6`
   - Command: `php /home/username/public_html/cron/evaluasi_harian.php`

---

## 📁 Struktur Direktori Proyek

```
Absensi_smk_smkalfarizi/
├── app/
│   ├── Config/
│   │   ├── Database.php          # Koneksi Singleton PDO MySQL
│   │   └── App.php               # Konfigurasi aplikasi & zona waktu WIB
│   ├── Controllers/
│   │   ├── AuthController.php    # Autentikasi multi-role (Admin, Guru, Piket)
│   │   ├── AdminController.php   # Master CRUD, Geofence Map, Barcode Card Generator
│   │   ├── GuruController.php    # Dashboard PWA Guru, GPS Check-in H-5, Presensi Mapel, Dompet Honor
│   │   ├── PiketController.php   # Scanner Kamera Web, Audio Beep Oscillator, Tap-in/Tap-out
│   │   └── PayrollController.php # Perhitungan Penggajian & Cetak Slip Gaji PDF Resmi
│   ├── Models/
│   │   ├── KonfigurasiSekolah.php
│   │   ├── Guru.php
│   │   ├── Siswa.php
│   │   ├── Kelas.php
│   │   ├── JadwalPelajaran.php
│   │   ├── PresensiGerbang.php
│   │   ├── SesiMengajar.php
│   │   └── PresensiMapel.php
│   ├── Helpers/
│   │   ├── GeolocationHelper.php # Formula Haversine jarak koordinat GPS meter
│   │   ├── BarcodeHelper.php     # Generator Barcode Code 128 SVG
│   │   ├── PdfHelper.php         # Slip gaji resmi kop sekolah & template kartu A4
│   │   └── TimeHelper.php        # Formatter waktu & kalkulasi denda keterlambatan
│   └── Views/
│       ├── auth/login.php
│       ├── admin/                # Dashboard, Geofence Map, Master Data, Monitoring
│       ├── guru/                 # Dashboard KBM, Presensi Siswa, Dompet Honor
│       ├── piket/                # Scanner Barcode Kamera & Riwayat Gerbang
│       └── payroll/              # Rekapitulasi Gaji & Cetak Slip
├── cron/
│   └── evaluasi_harian.php       # CLI script evaluasi gugur ALPHA jam pulang
├── database/
│   ├── schema.sql                # DDL Database MySQL
│   ├── seeders.sql               # Data seeder awal SMK Al-Farizi
│   └── migrate.php               # Script migrasi & hashing password
├── public/
│   ├── index.php                 # Front Controller / Router
│   ├── manifest.json             # PWA Manifest SMK Al-Farizi
│   ├── sw.js                     # Service Worker PWA
│   ├── css/app.css               # Desain modern responsif mobile-first & desktop
│   └── js/scanner.js             # Barcode engine & Web Audio API beep sound
├── tests/
│   ├── system_test.php           # Unit test formulasi matematika & aturan bisnis PRD
│   └── http_test.php             # Integration test HTTP web server
└── README.md
```

<?php
// cron/evaluasi_harian.php - Script CLI / Cron Job Evaluasi Integritas Harian Siswa
// Eksekusi setiap sore hari (misal jam 17:00) via cPanel Cron Job atau VPS Systemd/Crontab:
// 0 17 * * 1-6 php /path/to/Absensi_smk_smkalfarizi/cron/evaluasi_harian.php

declare(strict_types=1);

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Config/App.php';
require_once __DIR__ . '/../app/Models/PresensiGerbang.php';
require_once __DIR__ . '/../app/Models/PresensiGerbangGuru.php';

use App\Config\App;
use App\Models\PresensiGerbang;
use App\Models\PresensiGerbangGuru;
use App\Models\SesiMengajar;
use App\Helpers\TimeHelper;

App::init();

$today = date('Y-m-d');
echo "[" . date('Y-m-d H:i:s') . "] Memulai evaluasi integritas presensi gerbang dua arah untuk tanggal: {$today} ...\n";

if (!TimeHelper::isWorkingDay($today)) {
    echo "[" . date('Y-m-d H:i:s') . "] Hari Libur / Akhir Pekan. Evaluasi dilewati.\n";
    exit(0);
}

$affectedSiswa = PresensiGerbang::runDailyEvaluation($today);
$affectedGuru = PresensiGerbangGuru::runDailyEvaluation($today);
$affectedKbm = SesiMengajar::runAutoCheckout();

echo "[" . date('Y-m-d H:i:s') . "] Evaluasi selesai.\n";
echo " - Siswa: Sebanyak {$affectedSiswa} data siswa yang tidak lengkap tap-in/tap-out dikunci menjadi ALPHA.\n";
echo " - Guru : Sebanyak {$affectedGuru} data guru yang tidak lengkap tap-out dikunci menjadi ALPHA (Tugas Luar/Izin/Sakit tetap aman).\n";
echo " - KBM  : Sebanyak {$affectedKbm} sesi mengajar guru di-checkout otomatis.\n";

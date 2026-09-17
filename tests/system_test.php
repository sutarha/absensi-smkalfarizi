<?php
// tests/system_test.php - Pengujian Otomatis Seluruh Aturan Bisnis PRD SMK Al-Farizi

declare(strict_types=1);

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Config/App.php';
require_once __DIR__ . '/../app/Helpers/GeolocationHelper.php';
require_once __DIR__ . '/../app/Helpers/TimeHelper.php';
require_once __DIR__ . '/../app/Helpers/BarcodeHelper.php';
require_once __DIR__ . '/../app/Helpers/PdfHelper.php';
require_once __DIR__ . '/../app/Models/KonfigurasiSekolah.php';
require_once __DIR__ . '/../app/Models/Guru.php';
require_once __DIR__ . '/../app/Models/Siswa.php';
require_once __DIR__ . '/../app/Models/Kelas.php';
require_once __DIR__ . '/../app/Models/JadwalPelajaran.php';
require_once __DIR__ . '/../app/Models/PresensiGerbang.php';
require_once __DIR__ . '/../app/Models/SesiMengajar.php';
require_once __DIR__ . '/../app/Models/PresensiMapel.php';

use App\Config\App;
use App\Helpers\GeolocationHelper;
use App\Helpers\TimeHelper;
use App\Helpers\BarcodeHelper;
use App\Helpers\PdfHelper;
use App\Models\KonfigurasiSekolah;
use App\Models\PresensiGerbang;
use App\Models\SesiMengajar;
use App\Models\Siswa;

App::init();

$passed = 0;
$failed = 0;

function assertCondition(bool $condition, string $testName): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $failed++;
    }
}

echo "=================================================================\n";
echo "PENGUJIAN SISTEM INFORMASI PRESENSI & PENGGAJIAN SMK AL-FARIZI\n";
echo "=================================================================\n\n";

// 1. PENGUJIAN FORMULA MATEMATIKA HONOR & DENDA KETERLAMBATAN
echo "1. PENGUJIAN FORMULASI HONOR GURU & PENALTI KETERLAMBATAN:\n";
// Skenario A: Tepat Waktu (2 JP = 80 mnt, Mulai 07:30, Checkin 07:28)
$calcOnTime = TimeHelper::calculateHonor(2, '07:30:00', '2026-09-07 07:28:00', 5000.0, 40, 125.0);
assertCondition($calcOnTime['menit_terlambat'] === 0, "Checkin sebelum jam mulai -> Menit terlambat = 0");
assertCondition($calcOnTime['potongan_denda'] === 0.0, "Checkin tepat waktu -> Potongan denda = Rp 0");
assertCondition($calcOnTime['honor_didapat'] === 10000.0, "Checkin tepat waktu -> Honor penuh 100% (Rp 10.000)");

// Skenario B: Terlambat 12 Menit (2 JP, Mulai 07:30, Checkin 07:42)
$calcLate = TimeHelper::calculateHonor(2, '07:30:00', '2026-09-07 07:42:00', 5000.0, 40, 125.0);
assertCondition($calcLate['menit_terlambat'] === 12, "Checkin jam 07:42 untuk jadwal 07:30 -> Menit telat = 12");
assertCondition($calcLate['potongan_denda'] === 1500.0, "Denda telat 12 menit x Rp 125 = Rp 1.500");
assertCondition($calcLate['durasi_efektif_menit'] === 68, "Durasi efektif 80 - 12 = 68 menit");
assertCondition($calcLate['honor_didapat'] === 8500.0, "Honor riil didapat = 68 menit x Rp 125 = Rp 8.500");

// Skenario C: Terlambat melebihi durasi total (2 JP = 80 mnt, Telat 90 mnt)
$calcExtreme = TimeHelper::calculateHonor(2, '07:30:00', '2026-09-07 09:00:00', 5000.0, 40, 125.0);
assertCondition($calcExtreme['durasi_efektif_menit'] === 0, "Telat > durasi total -> Durasi efektif = 0");
assertCondition($calcExtreme['honor_didapat'] === 0.0, "Telat > durasi total -> Honor didapat = Rp 0");

echo "\n2. PENGUJIAN JENDELA AKTIVASI TOMBOL PRESENSI (H-5 MENIT):\n";
// Skenario: Jadwal jam 08:00
$wLocked = TimeHelper::checkActivationWindow('08:00:00', 5, '07:50:00');
assertCondition($wLocked['can_checkin'] === false && $wLocked['status'] === 'LOCKED', "Pukul 07:50 (H-10) -> Tombol Terkunci (Locked)");

$wUnlocked = TimeHelper::checkActivationWindow('08:00:00', 5, '07:55:00');
assertCondition($wUnlocked['can_checkin'] === true && $wUnlocked['status'] === 'OPEN', "Pukul 07:55 (H-5) -> Tombol Terbuka (Unlocked)");

$wLate = TimeHelper::checkActivationWindow('08:00:00', 5, '08:10:00', '09:20:00');
assertCondition($wLate['can_checkin'] === true && $wLate['is_late'] === true, "Pukul 08:10 -> Tombol Terbuka & Berstatus Terlambat");

$wPastEnd = TimeHelper::checkActivationWindow('08:00:00', 5, '09:25:00', '09:20:00');
assertCondition($wPastEnd['can_checkin'] === false && $wPastEnd['status'] === 'CLOSED' && $wPastEnd['is_closed'] === true, "Pukul 09:25 (Lewat jam mengajar 09:20) -> Absen Ditutup & Dianggap Tidak Hadir");

echo "\n3. PENGUJIAN VALIDASI JARAK GPS GEOFENCING (HAVERSINE):\n";
$schoolLat = -6.917464;
$schoolLng = 107.619123;
$radius = 50;

// Titik di dalam radius sekolah (~10 meter)
$nearLat = -6.917500;
$nearLng = 107.619150;
$geoInside = GeolocationHelper::isWithinRadius($nearLat, $nearLng, $schoolLat, $schoolLng, $radius);
assertCondition($geoInside['is_valid'] === true, "Koordinat jarak ~10m -> Diterima dalam radius 50m");

// Titik di luar radius sekolah (~250 meter)
$farLat = -6.919500;
$farLng = 107.620000;
$geoOutside = GeolocationHelper::isWithinRadius($farLat, $farLng, $schoolLat, $schoolLng, $radius);
assertCondition($geoOutside['is_valid'] === false, "Koordinat jarak > 200m -> Ditolak di luar radius");

echo "\n4. PENGUJIAN GENERATOR BARCODE KOTAK (QR CODE 2D) & CODE 128 (SVG):\n";
$qrSvg = BarcodeHelper::getQrCodeSvg('ALF-0071234501');
assertCondition(strpos($qrSvg, '<svg') !== false && strpos($qrSvg, '</svg>') !== false, "Barcode Kotak (QR Code) SVG valid dihasilkan");
$qrUri = BarcodeHelper::getQrCodeDataUri('ALF-0071234501');
assertCondition(strpos($qrUri, 'data:image/svg+xml;base64,') === 0, "Barcode Kotak Data URI Base64 valid untuk disematkan pada tag img kartu");
$barcodeSvg = BarcodeHelper::getBarcodeSvg('ALF-0071234501');
assertCondition(strpos($barcodeSvg, '<svg') !== false && strpos($barcodeSvg, '</svg>') !== false, "Barcode 1D Code 128 SVG tetap kompatibel");
$barcodeUri = BarcodeHelper::getBarcodeDataUri('ALF-0071234501');
assertCondition(strpos($barcodeUri, 'data:image/svg+xml;base64,') === 0, "Barcode 1D Data URI Base64 valid");

echo "\n5. PENGUJIAN PRESENSI GERBANG DUA ARAH & ATURAN INTEGRITAS:\n";
$testDate = '2026-09-08';

$allSiswa = Siswa::getAll();
$s1Id = !empty($allSiswa[0]['id']) ? (int)$allSiswa[0]['id'] : 1;
$s2Id = !empty($allSiswa[1]['id']) ? (int)$allSiswa[1]['id'] : 2;

// Siswa 1: Tap datang dan Tap pulang lengkap -> HADIR
PresensiGerbang::recordTapIn($s1Id, "{$testDate} 07:10:00");
$resOut = PresensiGerbang::recordTapOut($s1Id, "{$testDate} 14:15:00");
$list1 = PresensiGerbang::getByDate($testDate);
$s1Data = null;
foreach ($list1 as $row) {
    if ((int)$row['siswa_id'] === $s1Id) $s1Data = $row;
}
assertCondition($s1Data && $s1Data['status_kehadiran'] === 'HADIR', "Siswa tap-in & tap-out lengkap -> Status HADIR");

// Siswa 2: Hanya tap datang, TIDAK tap pulang
PresensiGerbang::recordTapIn($s2Id, "{$testDate} 07:12:00");
// Jalankan evaluasi integritas harian gerbang sore hari
$evalCount = PresensiGerbang::runDailyEvaluation($testDate);
$list2 = PresensiGerbang::getByDate($testDate);
$s2Data = null;
foreach ($list2 as $row) {
    if ((int)$row['siswa_id'] === $s2Id) $s2Data = $row;
}
assertCondition($s2Data && $s2Data['status_kehadiran'] === 'ALPHA', "Siswa hanya tap datang tanpa tap pulang -> Otomatis gugur menjadi ALPHA");

echo "\n6. PENGUJIAN CETAK SLIP GAJI PDF RESMI DENGAN KOP SEKOLAH:\n";
$config = KonfigurasiSekolah::get();
$mockGuru = [
    'id' => 2,
    'nik_nip' => '198501102010011001',
    'nama_lengkap' => 'Budi Santoso, S.Kom.',
    'tugas_tambahan' => 'Wali Kelas X PPLG 1',
    'tunjangan_tugas' => 300000.0,
];
$mockSesi = [
    [
        'tanggal' => '2026-09-01',
        'nama_mapel' => 'Pemodelan Perangkat Lunak',
        'nama_kelas' => 'X PPLG 1',
        'jumlah_jp' => 3,
        'waktu_checkin' => '2026-09-01 07:30:00',
        'menit_terlambat' => 0,
        'honor_didapat' => 15000.0,
    ],
    [
        'tanggal' => '2026-09-02',
        'nama_mapel' => 'Pemrograman Berorientasi Objek',
        'nama_kelas' => 'X PPLG 1',
        'jumlah_jp' => 3,
        'waktu_checkin' => '2026-09-02 07:38:00',
        'menit_terlambat' => 8,
        'honor_didapat' => 14000.0,
    ]
];

$slipHtml = PdfHelper::renderSlipGajiHtml([
    'guru' => $mockGuru,
    'config' => $config,
    'bulan' => 9,
    'tahun' => 2026,
    'sesi_list' => $mockSesi,
]);

assertCondition(strpos($slipHtml, 'SLIP HONORARIUM MENGAJAR & TUNJANGAN GURU') !== false, "Dokumen Slip Gaji memiliki judul resmi");
assertCondition(strpos($slipHtml, 'Budi Santoso, S.Kom.') !== false, "Nama guru tercetak pada slip gaji");
assertCondition(strpos($slipHtml, 'Wali Kelas X PPLG 1') !== false, "Tugas tambahan dan tunjangannya terhitung");
assertCondition(strpos($slipHtml, 'window.print()') !== false, "Fitur siap cetak / simpan PDF tersedia");

echo "\n=================================================================\n";
echo "HASIL PENGUJIAN SISTEM: {$passed} LULUS, {$failed} GAGAL\n";
echo "=================================================================\n";

if ($failed > 0) {
    exit(1);
}

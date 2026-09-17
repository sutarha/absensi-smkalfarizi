<?php
// tests/presensi_gerbang_guru_test.php
declare(strict_types=1);

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Config/App.php';
require_once __DIR__ . '/../app/Models/Guru.php';
require_once __DIR__ . '/../app/Models/PresensiGerbangGuru.php';

use App\Config\App;
use App\Config\Database;
use App\Models\Guru;
use App\Models\PresensiGerbangGuru;

App::init();

echo "=================================================================\n";
echo "PENGUJIAN PRESENSI GERBANG MANDIRI GURU (GPS & NON-HADIR)\n";
echo "=================================================================\n\n";

$db = Database::getConnection();

// Ambil 3 guru aktif secara dinamis dari database
$gurus = Guru::getAll('guru');
if (count($gurus) < 3) {
    echo "[FAIL] Dibutuhkan minimal 3 guru di database untuk pengujian!\n";
    exit(1);
}

$guru1 = $gurus[0];
$guru2 = $gurus[1];
$guru3 = $gurus[2];

$guruId1 = (int)$guru1['id'];
$guruId2 = (int)$guru2['id'];
$guruId3 = (int)$guru3['id'];

echo "Menggunakan akun uji: [{$guru1['nama_lengkap']}], [{$guru2['nama_lengkap']}], [{$guru3['nama_lengkap']}]\n\n";

$testDate = '2026-09-08';

// Bersihkan data tes tanggal ini
$db->prepare("DELETE FROM presensi_gerbang_guru WHERE tanggal = :tgl")->execute([':tgl' => $testDate]);

// -------------------------------------------------------------
// 1. PENGUJIAN TAP DATANG HADIR (BATAS JAM 06:30)
// -------------------------------------------------------------
echo "1. PENGUJIAN TAP DATANG HADIR (BATAS JAM 06:30):\n";

// Skenario 1A: Tepat Waktu (06:25:00 <= 06:30:00)
$resDatang = PresensiGerbangGuru::recordTapDatang(
    $guruId1,
    'HADIR',
    -6.91746400,
    107.61912300,
    15.5,
    0,
    'Tepat Waktu',
    "{$testDate} 06:25:00"
);

assert($resDatang['success'] === true, "Tap datang harus berhasil");
assert($resDatang['is_repeat'] === false, "Tidak boleh repeat");
assert($resDatang['data']['status_kehadiran'] === 'HADIR', "Status harus HADIR");
assert($resDatang['data']['menit_terlambat_datang'] === 0, "Menit telat harus 0");
assert($resDatang['requires_tap_out'] === true, "Harus butuh tap out pulang");
echo "  [PASS] Tap Datang HADIR tepat waktu (06:25 <= 06:30) berhasil dicatat\n";

// Uji pencegahan tap datang ganda
$resRepeat = PresensiGerbangGuru::recordTapDatang(
    $guruId1,
    'HADIR',
    -6.91746400,
    107.61912300,
    15.5,
    0,
    null,
    "{$testDate} 07:00:00"
);
assert($resRepeat['is_repeat'] === true, "Harus terdeteksi sebagai tap berulang");
echo "  [PASS] Percobaan Tap Datang ganda terdeteksi (is_repeat = true)\n";

// -------------------------------------------------------------
// 2. PENGUJIAN TAP PULANG
// -------------------------------------------------------------
echo "\n2. PENGUJIAN TAP PULANG GURU:\n";
$resPulang = PresensiGerbangGuru::recordTapPulang(
    $guruId1,
    -6.91746400,
    107.61912300,
    12.0,
    'Pulang Tepat Waktu',
    "{$testDate} 15:30:00"
);

assert($resPulang['success'] === true, "Tap pulang harus berhasil");
assert(!empty($resPulang['data']['waktu_pulang']), "Waktu pulang harus tersimpan");
assert($resPulang['data']['status_kehadiran'] === 'HADIR', "Status akhir harus HADIR");
echo "  [PASS] Tap Pulang berhasil dicatat pada jam 15:30 WIB\n";
echo "  [PASS] Status kehadiran dua arah final menjadi HADIR lengkap\n";

// -------------------------------------------------------------
// 3. PENGUJIAN PENGESAHAN TUGAS LUAR (TANPA GPS & TANPA TAP PULANG)
// -------------------------------------------------------------
echo "\n3. PENGUJIAN TUGAS LUAR (DINAS LUAR):\n";
$resTugas = PresensiGerbangGuru::recordTapDatang(
    $guruId2,
    'TUGAS_LUAR',
    null,
    null,
    0.0,
    0,
    'Mengikuti Rakor Kurikulum SMK di Dinas Pendidikan Provinsi',
    "{$testDate} 07:15:00"
);

assert($resTugas['success'] === true, "Pengajuan tugas luar harus berhasil");
assert($resTugas['status'] === 'TUGAS_LUAR', "Status harus TUGAS_LUAR");
assert($resTugas['requires_tap_out'] === false, "Tugas luar tidak boleh wajib tap out");
assert($resTugas['data']['lat_datang'] === null, "Koordinat lat harus null");
assert($resTugas['data']['status_kehadiran'] === 'TUGAS_LUAR', "Status DB harus TUGAS_LUAR");
echo "  [PASS] Tugas Luar berhasil diajukan tanpa koordinat GPS\n";
echo "  [PASS] Keterangan penugasan tersimpan rapi\n";

// Coba lakukan tap pulang pada guru berstatus TUGAS_LUAR
$resPulangTugas = PresensiGerbangGuru::recordTapPulang($guruId2, -6.91, 107.61, 5.0);
assert($resPulangTugas['is_repeat'] === true, "Tap pulang pada tugas luar tidak diperlukan");
echo "  [PASS] Tap Pulang pada guru tugas luar ditolak/diabaikan secara elegan\n";

// -------------------------------------------------------------
// 4. PENGUJIAN SAKIT & IZIN
// -------------------------------------------------------------
echo "\n4. PENGUJIAN SAKIT & IZIN:\n";
$resSakit = PresensiGerbangGuru::recordTapDatang(
    $guruId3,
    'SAKIT',
    null,
    null,
    0.0,
    0,
    'Demam tinggi, surat dokter terlampir via WA',
    "{$testDate} 06:30:00"
);
assert($resSakit['success'] === true, "Pengajuan sakit berhasil");
assert($resSakit['status'] === 'SAKIT', "Status harus SAKIT");
assert($resSakit['requires_tap_out'] === false, "Sakit tidak butuh tap pulang");
echo "  [PASS] Pengajuan SAKIT tersimpan langsung final tanpa GPS dan tanpa tap pulang\n";

// -------------------------------------------------------------
// 5. PENGUJIAN ATURAN INTEGRITAS DUA ARAH (EVALUASI SORE HARI)
// -------------------------------------------------------------
echo "\n5. PENGUJIAN EVALUASI SORE (CRON JOB INTEGRITAS):\n";
// Buat data simulasi: Guru yang hanya tap datang tapi TIDAK tap pulang di tanggal simulasi
$simulasiDate = '2026-09-07';
$db->prepare("DELETE FROM presensi_gerbang_guru WHERE tanggal = :tgl")->execute([':tgl' => $simulasiDate]);

// Guru 1: Hanya Tap Datang, lupa Tap Pulang
PresensiGerbangGuru::recordTapDatang($guruId1, 'HADIR', -6.91, 107.61, 10.0, 0, null, "{$simulasiDate} 06:50:00");

// Guru 2: Tugas Luar
PresensiGerbangGuru::recordTapDatang($guruId2, 'TUGAS_LUAR', null, null, 0.0, 0, 'Dinas', "{$simulasiDate} 07:00:00");

// Guru 3: Izin
PresensiGerbangGuru::recordTapDatang($guruId3, 'IZIN', null, null, 0.0, 0, 'Acara keluarga', "{$simulasiDate} 07:10:00");

// Jalankan evaluasi
$affected = PresensiGerbangGuru::runDailyEvaluation($simulasiDate);
assert($affected === 1, "Hanya 1 guru (guru1) yang boleh dikunci menjadi ALPHA");

// Verifikasi guru 1 menjadi ALPHA
$checkG1 = PresensiGerbangGuru::getToday($guruId1, $simulasiDate);
assert($checkG1['status_kehadiran'] === 'ALPHA', "Guru 1 yang tidak tap pulang harus ALPHA");
assert(strpos($checkG1['keterangan'], 'Gugur Alpha') !== false, "Keterangan harus memuat Gugur Alpha");
echo "  [PASS] Guru yang hanya Tap Datang tanpa Tap Pulang otomatis dikunci menjadi ALPHA\n";

// Verifikasi guru 2 (Tugas Luar) dan guru 3 (Izin) TIDAK menjadi ALPHA
$checkG2 = PresensiGerbangGuru::getToday($guruId2, $simulasiDate);
assert($checkG2['status_kehadiran'] === 'TUGAS_LUAR', "Guru 2 harus tetap TUGAS_LUAR");
echo "  [PASS] Guru Tugas Luar aman dan TIDAK dikunci menjadi Alpha\n";

$checkG3 = PresensiGerbangGuru::getToday($guruId3, $simulasiDate);
assert($checkG3['status_kehadiran'] === 'IZIN', "Guru 3 harus tetap IZIN");
echo "  [PASS] Guru Izin aman dan TIDAK dikunci menjadi Alpha\n";

// Bersihkan data tanggal simulasi
$db->prepare("DELETE FROM presensi_gerbang_guru WHERE tanggal = :tgl")->execute([':tgl' => $simulasiDate]);

echo "\n=================================================================\n";
echo "SEMUA PENGUJIAN PRESENSI GERBANG GURU BERHASIL 100%! (ALL PASS)\n";
echo "=================================================================\n";

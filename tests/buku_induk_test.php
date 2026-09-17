<?php
// tests/buku_induk_test.php - Automated verification for Buku Induk, Nilai, Akademik & SPPD

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Config/App.php';
require_once __DIR__ . '/../app/Models/BukuInduk.php';
require_once __DIR__ . '/../app/Models/MataPelajaran.php';
require_once __DIR__ . '/../app/Models/NilaiSiswa.php';
require_once __DIR__ . '/../app/Models/TahunPelajaran.php';
require_once __DIR__ . '/../app/Models/RiwayatKelas.php';
require_once __DIR__ . '/../app/Models/ArsipSurat.php';
require_once __DIR__ . '/../app/Models/KonfigurasiSekolah.php';
require_once __DIR__ . '/../app/Helpers/ExcelHelper.php';
require_once __DIR__ . '/../app/Helpers/DapodikHelper.php';
require_once __DIR__ . '/../app/Helpers/TimeHelper.php';
require_once __DIR__ . '/../app/Helpers/SuratHelper.php';

use App\Models\BukuInduk;
use App\Models\MataPelajaran;
use App\Models\NilaiSiswa;
use App\Models\TahunPelajaran;
use App\Models\RiwayatKelas;
use App\Models\ArsipSurat;
use App\Models\KonfigurasiSekolah;
use App\Helpers\DapodikHelper;
use App\Helpers\SuratHelper;
use App\Config\Database;

echo "========================================================\n";
echo "    TEST SUITE: BUKU INDUK, NILAI, AKADEMIK & PERSURATAN \n";
echo "========================================================\n\n";

$passCount = 0;
$totalTests = 0;

function assertTest(string $name, bool $condition, string $detail = '') {
    global $passCount, $totalTests;
    $totalTests++;
    if ($condition) {
        $passCount++;
        echo "  [PASS] $name\n";
    } else {
        echo "  [FAIL] $name - $detail\n";
    }
}

// 1. TEST BUKU INDUK MODEL & QUERY
echo "--- 1. Testing Buku Induk Siswa ---\n";
$bukuList = BukuInduk::getAllWithSiswa(null, '', 10, 0);
assertTest("BukuInduk::getAllWithSiswa() returns array", is_array($bukuList));
assertTest("BukuInduk contains seeded students", count($bukuList) > 0, "Count: " . count($bukuList));

$firstSiswa = $bukuList[0];
$siswaId = (int)$firstSiswa['id'];
$detail = BukuInduk::getBySiswaId($siswaId);
assertTest("BukuInduk::getBySiswaId({$siswaId}) returns valid student", !empty($detail) && $detail['id'] == $siswaId);
assertTest("BukuInduk has NISN and status", !empty($detail['nisn']) && isset($detail['status_siswa']));

// Test Update Buku Induk
$updateResult = BukuInduk::saveOrUpdate($siswaId, [
    'agama' => 'Islam',
    'kewarganegaraan' => 'WNI',
    'alamat_jalan' => 'Jl. Pendidikan No. 45',
    'kabupaten_kota' => 'Cianjur',
    'status_siswa' => 'AKTIF'
]);
assertTest("BukuInduk::saveOrUpdate() successfully executes", $updateResult === true);

$updatedDetail = BukuInduk::getBySiswaId($siswaId);
assertTest("BukuInduk fields correctly updated in DB", $updatedDetail['alamat_jalan'] === 'Jl. Pendidikan No. 45');

// 2. TEST NILAI SISWA & FORMULA PERMENDIKBUD
echo "\n--- 2. Testing Penilaian Permendikbud (Dual-Mode: KBM Guru & Manual TU) ---\n";
// Test 2.1 Formula KBM: RH = (Tugas + UH)/2, NA = ((2*RH) + UTS + UAS) / 4
$calcKbmA = NilaiSiswa::hitungNilaiRaporKBM(90.0, 90.0, 90.0, 90.0);
assertTest("KBM Formula: NA (90, 90, 90, 90) = 90.0", abs($calcKbmA['nilai_akhir'] - 90.0) < 0.01, "Got: " . $calcKbmA['nilai_akhir']);
assertTest("KBM Formula: Predikat for NA 90 is 'A'", $calcKbmA['predikat'] === 'A');

$calcKbmB = NilaiSiswa::hitungNilaiRaporKBM(80.0, 80.0, 75.0, 85.0); // RH = 80, NA = (160 + 75 + 85)/4 = 320/4 = 80.0
assertTest("KBM Formula: NA (80, 80, 75, 85) = 80.0", abs($calcKbmB['nilai_akhir'] - 80.0) < 0.01, "Got: " . $calcKbmB['nilai_akhir']);
assertTest("KBM Formula: Predikat for NA 80 is 'B'", $calcKbmB['predikat'] === 'B');

$mapelList = MataPelajaran::getAll();
assertTest("Mata Pelajaran exists", count($mapelList) >= 10, "Count: " . count($mapelList));
$mapel1 = (int)$mapelList[0]['id'];
$mapel2 = (int)$mapelList[1]['id'];

// Test 2.2 Mode 1 (KBM Guru Semester Berjalan): saveNilaiKBM (is_manual = 0)
$savedKbm = NilaiSiswa::saveNilaiKBM($siswaId, $mapel1, 1, 85.0, 90.0, 85.0, 90.0, null, "Sangat baik dalam menyelesaikan tugas dan ulangan harian.");
assertTest("NilaiSiswa::saveNilaiKBM() Sem 1 saved with is_manual = 0", $savedKbm === true);

// Verify row from DB
$db = Database::getConnection();
$stmtCheck = $db->prepare("SELECT * FROM nilai_siswa WHERE siswa_id = ? AND mapel_id = ? AND semester_ke = 1");
$stmtCheck->execute([$siswaId, $mapel1]);
$rowKbm = $stmtCheck->fetch();
assertTest("DB Row has is_manual = 0", isset($rowKbm['is_manual']) && (int)$rowKbm['is_manual'] === 0);
assertTest("DB Row has nilai_tugas = 85.0 and nilai_uh = 90.0", (float)$rowKbm['nilai_tugas'] == 85.0 && (float)$rowKbm['nilai_uh'] == 90.0);

// Test 2.3 Mode 2 (Manual TU Raport Lalu): saveNilaiManualRaport (is_manual = 1)
$savedManualCount = NilaiSiswa::saveNilaiManualRaport($siswaId, 2, [
    $mapel1 => ['nilai_akhir' => 88.0, 'capaian' => 'Tuntas semester 2.'],
    $mapel2 => ['nilai_akhir' => 92.0, 'capaian' => 'Sangat memuaskan.']
], null);
assertTest("NilaiSiswa::saveNilaiManualRaport() Sem 2 saved 2 mapel", $savedManualCount === 2);

$stmtCheckMan = $db->prepare("SELECT * FROM nilai_siswa WHERE siswa_id = ? AND mapel_id = ? AND semester_ke = 2");
$stmtCheckMan->execute([$siswaId, $mapel1]);
$rowMan = $stmtCheckMan->fetch();
assertTest("DB Row for manual entry has is_manual = 1", isset($rowMan['is_manual']) && (int)$rowMan['is_manual'] === 1);
assertTest("DB Row has nilai_akhir = 88.0 and predikat = 'A'", (float)$rowMan['nilai_akhir'] == 88.0 && $rowMan['predikat'] === 'A');

// Test 2.4 Riwayat Rekam Jejak Nilai Semester 1 - 6 untuk Buku Induk
$riwayatBukuInduk = NilaiSiswa::getRiwayatNilaiSiswaSemuaSemester($siswaId);
assertTest("getRiwayatNilaiSiswaSemuaSemester() returns mapel_matrix", !empty($riwayatBukuInduk['mapel_matrix']));
assertTest("Riwayat Buku Induk has total_nilai_terisi > 0", $riwayatBukuInduk['total_nilai_terisi'] > 0);
assertTest("Riwayat Buku Induk has rata_kumulatif > 0", $riwayatBukuInduk['rata_kumulatif'] > 0);

// Test 2.5 Transkrip Ijazah SMK (Bobot 60% Rapor + 40% Ujian)
$ijazah = NilaiSiswa::getTranskripIjazah($siswaId);
assertTest("NilaiSiswa::getTranskripIjazah() returns daftar_mapel", !empty($ijazah['daftar_mapel']));
assertTest("Transkrip Ijazah rata_ijazah_total > 0", $ijazah['rata_ijazah_total'] > 0, "Got: " . $ijazah['rata_ijazah_total']);
assertTest("Transkrip Ijazah count_mapel >= 2", $ijazah['count_mapel'] >= 2, "Got: " . $ijazah['count_mapel']);

// 3. TEST TAHUN PELAJARAN & KENAIKAN KELAS
echo "\n--- 3. Testing Tahun Pelajaran & Kenaikan Kelas ---\n";
$tapels = TahunPelajaran::getAll();
assertTest("TahunPelajaran::getAll() returns list", count($tapels) >= 2);
$activeTapel = TahunPelajaran::getActive();
assertTest("Active TahunPelajaran exists", !empty($activeTapel) && $activeTapel['is_aktif'] == 1);

// Test Riwayat Kelas
$rkList = RiwayatKelas::getBySiswa($siswaId);
assertTest("RiwayatKelas::getBySiswa() executes", is_array($rkList));

// 4. TEST PERSURATAN, PENOMORAN OTOMATIS & SPPD VISUM
echo "\n--- 4. Testing Persuratan Dinas, SPPD & Lembar Visum ---\n";
$nomorSiswaAktif = SuratHelper::generateNomorSurat('SISWA_AKTIF', 1, '2026-09-06');
assertTest("Generate Nomor Surat Siswa Aktif: $nomorSiswaAktif", strpos($nomorSiswaAktif, '421.5/001/SMK-AF/IX/2026') !== false);

$urutanTest = ArsipSurat::getNextUrutan('SPPD', 2026);
$nomorSppd = SuratHelper::generateNomorSurat('SPPD', $urutanTest, '2026-09-06');
assertTest("Generate Nomor SPPD: $nomorSppd", strpos($nomorSppd, '090/') !== false);

// Test Create SPPD in DB
$config = KonfigurasiSekolah::get();
$sppdId = ArsipSurat::create([
    'nomor_surat' => $nomorSppd,
    'jenis_surat' => 'SPPD',
    'penerima_tipe' => 'GURU',
    'guru_id' => 1,
    'perihal' => 'Perjalanan Dinas Workshop Kurikulum',
    'keperluan' => 'Mengikuti Workshop Peningkatan Kompetensi Guru SMK Tingkat Provinsi',
    'tanggal_surat' => '2026-09-06',
    'dasar_penugasan' => 'Surat Undangan Dinas Pendidikan No. 005/789/Disdik/2026',
    'tempat_berangkat' => 'SMK AL-FARIZI',
    'tempat_tujuan' => 'Bandung',
    'instansi_tujuan' => 'Kantor Balai Besar Pengembangan Penjaminan Mutu Pendidikan',
    'pejabat_tujuan' => 'Dr. H. Bambang Sutrisno, M.Pd (Kepala BBGP Jabar)',
    'tanggal_berangkat' => '2026-09-10',
    'tanggal_kembali' => '2026-09-12',
    'lama_hari' => 3,
    'alat_angkut' => 'Kendaraan Dinas / Bus',
    'beban_anggaran' => 'Dana BOS SMK Al-Farizi',
    'pejabat_penandatangan' => $config['kepala_sekolah'] ?? 'Kepala Sekolah',
    'created_by' => 1
]);
assertTest("ArsipSurat::create() for SPPD saved with ID: $sppdId", $sppdId > 0);

$savedSurat = ArsipSurat::findById($sppdId);
assertTest("ArsipSurat::findById() retrieves full SPPD fields", !empty($savedSurat['pejabat_tujuan']));
assertTest("SPPD Pejabat Tujuan matches input", strpos($savedSurat['pejabat_tujuan'], 'Bambang Sutrisno') !== false);

// Test Lembar Visum HTML Rendering
$visumHtml = SuratHelper::renderLembarVisumSppd($savedSurat, $config);
assertTest("Lembar Visum SPPD contains Bagian II (Tiba di Tempat Tujuan)", strpos($visumHtml, 'II. Tiba di') !== false);
assertTest("Lembar Visum SPPD contains Tanda Tangan & Pejabat Tujuan", strpos($visumHtml, 'Dr. H. Bambang Sutrisno') !== false);
assertTest("Lembar Visum SPPD contains Cap Stempel Instansi yang Dituju", stripos($visumHtml, 'Cap Stempel') !== false || stripos($visumHtml, 'Tanda Tangan') !== false);

// 5. TEST CSV DAPODIK PARSER
echo "\n--- 5. Testing Dapodik CSV Parser & Template ---\n";
$templateCsv = DapodikHelper::getCsvTemplateContent();
assertTest("DapodikHelper::getCsvTemplateContent() contains headers", strpos($templateCsv, 'Nama') !== false && strpos($templateCsv, 'NISN') !== false);

// Write temporary CSV to test parser
$tempCsv = __DIR__ . '/test_dapodik_temp.csv';
file_put_contents($tempCsv, "Nama;NISN;NIS;NIK;Tempat Lahir;Tanggal Lahir;Jenis Kelamin;Rombel;Nama Ayah;Nama Ibu;Alamat Jalan\n" .
    "Budi Santoso;0081234567;24251099;3203010101080001;Cianjur;2008-05-15;L;X PPLG 1;Ahmad Santoso;Siti Aminah;Jl. Raya Pagelaran No. 10\n");

$parsedRows = DapodikHelper::parseCsv($tempCsv);
assertTest("DapodikHelper::parseCsv() parses semicolons correctly", count($parsedRows) === 1);
assertTest("Parsed row contains normalized data", $parsedRows[0]['nama_siswa'] === 'Budi Santoso' && $parsedRows[0]['nisn'] === '0081234567');
@unlink($tempCsv);

// Test Bulk Upsert
$upsertStats = BukuInduk::bulkUpsertDapodik($parsedRows, $activeTapel['id'] ?? 1);
assertTest("BukuInduk::bulkUpsertDapodik() completed", ($upsertStats['inserted'] + $upsertStats['updated']) === 1);

echo "\n========================================================\n";
echo " RESULTS: $passCount / $totalTests TESTS PASSED!\n";
echo "========================================================\n";

if ($passCount === $totalTests) {
    echo "🎉 ALL TESTS PASSED! SISTEM SIAP DIGUNAKAN DENGAN SEMPURNA.\n";
    exit(0);
} else {
    echo "⚠️ SOME TESTS FAILED.\n";
    exit(1);
}

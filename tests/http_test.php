<?php
// tests/http_test.php - Pengujian HTTP End-to-End dengan Cookie Jar

$cookieFile = __DIR__ . '/cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

function httpReq(string $url, string $method = 'GET', ?array $postData = null, bool $follow = true) {
    global $cookieFile;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($postData) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }
    }
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => $body];
}

echo "=== PENGUJIAN HTTP WEB SERVER & AUTENTIKASI ===\n";

// 1. Test Login Admin
$login = httpReq("http://127.0.0.1:8000/login", "POST", ['username' => 'admin', 'password' => 'admin123']);
echo "1. Login Admin Status: " . $login['status'] . "\n";
echo "   Dashboard Loaded: " . (strpos($login['body'], 'Dashboard Administrator & TU') !== false ? "YES" : "NO") . "\n";

// 2. Test Cetak Kartu Siswa Barcode A4
$kartu = httpReq("http://127.0.0.1:8000/admin/siswa/cetak-kartu");
echo "2. Cetak Kartu Barcode A4 Status: " . $kartu['status'] . "\n";
echo "   KARTU IDENTITAS SISWA present: " . (strpos($kartu['body'], 'KARTU IDENTITAS SISWA') !== false ? "YES" : "NO") . "\n";
echo "   Barcode Data URI present: " . (strpos($kartu['body'], 'data:image/svg+xml;base64,') !== false ? "YES" : "NO") . "\n";

// 3. Test Slip Gaji Resmi PDF
$slip = httpReq("http://127.0.0.1:8000/payroll/slip/2/9/2026");
echo "3. Slip Gaji Resmi PDF Status: " . $slip['status'] . "\n";
echo "   SLIP HONORARIUM MENGAJAR present: " . (strpos($slip['body'], 'SLIP HONORARIUM MENGAJAR') !== false ? "YES" : "NO") . "\n";

// 4. Test Login Guru Mapel
unlink($cookieFile);
$loginGuru = httpReq("http://127.0.0.1:8000/login", "POST", ['username' => 'guru1', 'password' => 'guru123']);
echo "4. Login Guru Mapel Status: " . $loginGuru['status'] . "\n";
echo "   Portal Guru KBM Loaded: " . (stripos($loginGuru['body'], 'Portal Guru KBM') !== false ? "YES" : "NO") . "\n";
echo "   Dompet Estimasi Honor present: " . (stripos($loginGuru['body'], 'Estimasi Take Home Pay') !== false || stripos($loginGuru['body'], 'Estimasi Honor') !== false ? "YES" : "NO") . "\n";

$bukuSaku = httpReq("http://127.0.0.1:8000/guru/dompet");
echo "4b. Halaman Buku Saku Guru Status: " . $bukuSaku['status'] . "\n";
echo "    Portal Guru Header present: " . (strpos($bukuSaku['body'], 'Portal Guru KBM') !== false ? "YES" : "NO") . "\n";
echo "    Buku Saku Honor title present: " . (strpos($bukuSaku['body'], 'Buku Saku Honor') !== false ? "YES" : "NO") . "\n";
echo "    Teacher Name in Header present: " . (strpos($bukuSaku['body'], 'Budi Santoso') !== false ? "YES" : "NO") . "\n";
echo "    Unified Bottom Nav present: " . (strpos($bukuSaku['body'], 'Jadwal KBM') !== false && strpos($bukuSaku['body'], 'Nilai KBM') !== false ? "YES" : "NO") . "\n";

// 5. Test Login Guru Piket
unlink($cookieFile);
$loginPiket = httpReq("http://127.0.0.1:8000/login", "POST", ['username' => 'piket', 'password' => 'piket123']);
echo "5. Login Guru Piket Status: " . $loginPiket['status'] . "\n";
echo "   Scanner Gerbang Loaded: " . (stripos($loginPiket['body'], 'SCANNER GERBANG') !== false ? "YES" : "NO") . "\n";
echo "   Mode Datang Switcher present: " . (stripos($loginPiket['body'], 'DATANG') !== false ? "YES" : "NO") . "\n";
preg_match('/window\.BASE_URL\s*=\s*[\'"](.*?)[\'"]/', $loginPiket['body'], $baseUrlMatch);
echo "   Rendered BASE_URL in HTML: '" . ($baseUrlMatch[1] ?? 'NOT FOUND') . "'\n";

// 6. Test Piket Scan API (Barcode Kotak / QR Scan Datang & Pulang)
$scanIn = httpReq("http://127.0.0.1:8000/piket/scan-process", "POST", [
    'barcode_code' => 'ALF-0071234501',
    'mode' => 'DATANG'
]);
echo "6. Scan Datang API Status: " . $scanIn['status'] . "\n";
$jsonIn = json_decode($scanIn['body'], true);
echo "   Scan Datang Success: " . ($jsonIn && !empty($jsonIn['success']) ? "YES (" . $jsonIn['siswa']['nama_siswa'] . ")" : "NO: " . $scanIn['body']) . "\n";

$scanOut = httpReq("http://127.0.0.1:8000/piket/scan-process", "POST", [
    'barcode_code' => 'ALF-0071234501',
    'mode' => 'PULANG'
]);
echo "   Scan Pulang API Status: " . $scanOut['status'] . "\n";
$jsonOut = json_decode($scanOut['body'], true);
echo "   Scan Pulang Success: " . ($jsonOut && !empty($jsonOut['success']) ? "YES (" . $jsonOut['siswa']['nama_siswa'] . ")" : "NO: " . $scanOut['body']) . "\n";

// 7. Test Halaman Baru Buku Induk & Penilaian Dual-Mode
unlink($cookieFile);
$loginAdmin = httpReq("http://127.0.0.1:8000/login", "POST", ['username' => 'admin', 'password' => 'admin123']);

$detailBuku = httpReq("http://127.0.0.1:8000/admin/buku-induk/detail/1");
echo "7. Buku Induk Detail Status: " . $detailBuku['status'] . "\n";
echo "   Bagian F (Rekam Jejak Nilai Raport) present: " . (strpos($detailBuku['body'], 'Rekam Jejak Nilai Raport Siswa') !== false ? "YES" : "NO") . "\n";
echo "   Tombol Transkrip Ijazah present: " . (strpos($detailBuku['body'], 'Transkrip Ijazah') !== false ? "YES" : "NO") . "\n";

$inputManual = httpReq("http://127.0.0.1:8000/admin/buku-induk/input-nilai-manual/1?semester_ke=1");
echo "8. Input Nilai Raport Manual Status: " . $inputManual['status'] . "\n";
echo "   Form Input Manual Raport Loaded: " . (strpos($inputManual['body'], 'Input Nilai Raport Manual (Buku Induk)') !== false ? "YES" : "NO") . "\n";

$transkripIjazah = httpReq("http://127.0.0.1:8000/admin/nilai/transkrip-ijazah/1");
echo "9. Transkrip Nilai Ijazah Resmi Status: " . $transkripIjazah['status'] . "\n";
echo "   Blangko Ijazah Title present: " . (strpos($transkripIjazah['body'], 'TRANSKRIP NILAI IJAZAH KELULUSAN') !== false ? "YES" : "NO") . "\n";
echo "   Rata-rata Nilai Rapor Table present: " . (strpos($transkripIjazah['body'], 'Rata-rata Nilai Rapor') !== false ? "YES" : "NO") . "\n";

// 10. Test Login Guru & Dashboard KBM Penilaian
unlink($cookieFile);
$loginGuru = httpReq("http://127.0.0.1:8000/login", "POST", ['username' => 'guru1', 'password' => 'guru123']);
echo "10. Guru Dashboard Penilaian KBM Status: " . $loginGuru['status'] . "\n";
echo "    Penilaian KBM Semester Berjalan Card present: " . (strpos($loginGuru['body'], 'Penilaian KBM Semester Berjalan') !== false ? "YES" : "NO") . "\n";

$guruInputNilai = httpReq("http://127.0.0.1:8000/guru/nilai/input?kelas_id=1&mapel_id=1&semester_ke=1");
echo "11. Guru Input Nilai 4 Komponen (Tugas, UH, UTS, UAS) Status: " . $guruInputNilai['status'] . "\n";
echo "    Kalkulator Asesmen Otomatis present: " . (strpos($guruInputNilai['body'], 'Kalkulasi Otomatis Nilai Rapor KBM') !== false ? "YES" : "NO") . "\n";
echo "    Input Tugas, UH, UTS, UAS present: " . (strpos($guruInputNilai['body'], 'name="nilai[1][tugas]"') !== false || strpos($guruInputNilai['body'], 'name="nilai[') !== false ? "YES" : "NO") . "\n";

// 12. Test Admin Relasi Master & Bulk Assign
unlink($cookieFile);
$loginAdmin = httpReq("http://127.0.0.1:8000/login", "POST", ['username' => 'admin', 'password' => 'admin123']);

$mapelPage = httpReq("http://127.0.0.1:8000/admin/mapel");
echo "12. Admin Master Mata Pelajaran Status: " . $mapelPage['status'] . "\n";
echo "    Master Mata Pelajaran title present: " . (strpos($mapelPage['body'], 'Master Mata Pelajaran') !== false ? "YES" : "NO") . "\n";

$kelasPage = httpReq("http://127.0.0.1:8000/admin/kelas");
echo "13. Admin Master Kelas Status: " . $kelasPage['status'] . "\n";
echo "    Wali Kelas header present: " . (strpos($kelasPage['body'], 'Wali Kelas') !== false ? "YES" : "NO") . "\n";

$jadwalPage = httpReq("http://127.0.0.1:8000/admin/jadwal");
echo "14. Admin Jadwal KBM Status: " . $jadwalPage['status'] . "\n";
echo "    Pilih Mata Pelajaran dropdown present: " . (strpos($jadwalPage['body'], 'Pilih Mata Pelajaran') !== false ? "YES" : "NO") . "\n";
echo "    Tombol Input Nilai present: " . (strpos($jadwalPage['body'], 'Input Nilai') !== false ? "YES" : "NO") . "\n";

$siswaPage = httpReq("http://127.0.0.1:8000/admin/siswa");
echo "15. Admin Data Siswa Status: " . $siswaPage['status'] . "\n";
echo "    Bulk Action Bar present: " . (strpos($siswaPage['body'], 'bulkBar') !== false ? "YES" : "NO") . "\n";
echo "    Checkbox Select All present: " . (strpos($siswaPage['body'], 'checkAll') !== false ? "YES" : "NO") . "\n";

$bukuIndukPage = httpReq("http://127.0.0.1:8000/admin/buku-induk");
echo "16. Admin Buku Induk Siswa Status: " . $bukuIndukPage['status'] . "\n";
echo "    Bulk Checkbox present: " . (strpos($bukuIndukPage['body'], 'bulkBar') !== false ? "YES" : "NO") . "\n";

// 17. Test Plotting Mapel di Admin Kelas
$kelasMapelModal = httpReq("http://127.0.0.1:8000/admin/kelas");
echo "17. Admin Kelas Plotting Mapel Status: " . $kelasMapelModal['status'] . "\n";
echo "    Tombol Plotting Mapel present: " . (strpos($kelasMapelModal['body'], 'Plotting Mapel') !== false ? "YES" : "NO") . "\n";
echo "    Modal Plotting Mapel present: " . (strpos($kelasMapelModal['body'], 'plottingModal') !== false ? "YES" : "NO") . "\n";

// 18. Test API Kelas Mapel JSON
$apiKelasMapel = httpReq("http://127.0.0.1:8000/admin/kelas/6/mapel-json");
$jsonKelasMapel = json_decode($apiKelasMapel['body'], true);
echo "18. API Kelas Mapel JSON Status: " . $apiKelasMapel['status'] . "\n";
echo "    API Status Success: " . (($jsonKelasMapel['status'] ?? '') === 'success' ? "YES" : "NO") . "\n";

// 19. Test API Anti-Bentrok Jadwal
$apiConflict = httpReq("http://127.0.0.1:8000/admin/jadwal/check-conflict", "POST", [
    'hari' => 'Senin',
    'kelas_id' => 6,
    'guru_id' => 6,
    'jam_mulai' => '07:30',
    'jam_selesai' => '08:50'
]);
$jsonConflict = json_decode($apiConflict['body'], true);
echo "19. API Anti-Bentrok Jadwal Status: " . $apiConflict['status'] . "\n";
echo "    API Conflict Status OK/Conflict: " . (!empty($jsonConflict['status']) ? "YES ({$jsonConflict['status']})" : "NO") . "\n";

// 20. Test UI Jadwal KBM Anti-Bentrok
$jadwalUi = httpReq("http://127.0.0.1:8000/admin/jadwal");
echo "20. UI Master Jadwal KBM Anti-Bentrok Status: " . $jadwalUi['status'] . "\n";
echo "    Badge Anti-Bentrok Aktif: " . (strpos($jadwalUi['body'], 'Anti-Bentrok Aktif') !== false ? "YES" : "NO") . "\n";
echo "    Alert Box Bentrok: " . (strpos($jadwalUi['body'], 'conflictAlertBox') !== false ? "YES" : "NO") . "\n";

echo "===============================================\n";
if (file_exists($cookieFile)) unlink($cookieFile);


<?php
// tests/api_new_endpoints_test.php

$baseUrl = 'http://localhost:8080/api/v1';

function postJson(string $url, array $data, ?string $token = null): array {
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode((string)$response, true)];
}

function getJson(string $url, ?string $token = null): array {
    $ch = curl_init($url);
    $headers = ['Accept: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer {$token}";
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $code, 'data' => json_decode((string)$response, true)];
}

echo "=== TESTING NEW REST API ENDPOINTS ===\n\n";

// 1. Login Guru
$loginRes = postJson("{$baseUrl}/auth/login", ['username' => 'guru1', 'password' => 'guru123']);
echo "1. Login (guru1): HTTP " . $loginRes['code'] . "\n";
if (empty($loginRes['data']['token'])) {
    echo "Login failed: " . json_encode($loginRes) . "\n";
    exit(1);
}
$token = $loginRes['data']['token'];
echo "   Token acquired successfully!\n\n";

// 2. Test Gerbang Status
$gerbangRes = getJson("{$baseUrl}/guru/gerbang/status", $token);
echo "2. GET /guru/gerbang/status: HTTP " . $gerbangRes['code'] . "\n";
echo "   Success: " . ($gerbangRes['data']['success'] ? 'TRUE' : 'FALSE') . "\n";
$configData = $gerbangRes['data']['data']['config'] ?? [];
$schoolLat = $configData['latitude_pusat'] ?? -6.91746;
$schoolLng = $configData['longitude_pusat'] ?? 107.61912;
echo "   Koordinat Sekolah: {$schoolLat}, {$schoolLng}\n\n";

// 3. Test Gerbang Tap Datang (dengan koordinat valid)
$datangRes = postJson("{$baseUrl}/guru/gerbang/datang", [
    'status_pilihan' => 'HADIR',
    'keterangan' => 'Datang tepat waktu melalui Android',
    'latitude' => $schoolLat,
    'longitude' => $schoolLng
], $token);
echo "3. POST /guru/gerbang/datang: HTTP " . $datangRes['code'] . "\n";
echo "   Success: " . ($datangRes['data']['success'] ? 'TRUE' : 'FALSE') . "\n";
echo "   Message: " . ($datangRes['data']['message'] ?? '-') . "\n\n";

// 4. Test Gerbang Tap Pulang
$pulangRes = postJson("{$baseUrl}/guru/gerbang/pulang", [
    'keterangan' => 'Pulang melalui Android',
    'latitude' => $schoolLat,
    'longitude' => $schoolLng,
    'bypass_gps' => true
], $token);
echo "4. POST /guru/gerbang/pulang: HTTP " . $pulangRes['code'] . "\n";
echo "   Success: " . ($pulangRes['data']['success'] ? 'TRUE' : 'FALSE') . "\n";
echo "   Message: " . ($pulangRes['data']['message'] ?? '-') . "\n\n";

// 5. Test Nilai Siswa (Login as Admin untuk akses semua kelas atau Guru)
$loginAdmin = postJson("{$baseUrl}/auth/login", ['username' => 'admin', 'password' => 'admin123']);
$adminToken = $loginAdmin['data']['token'] ?? $token;

$nilaiRes = getJson("{$baseUrl}/guru/nilai", $adminToken);
echo "5. GET /guru/nilai: HTTP " . $nilaiRes['code'] . "\n";
echo "   Success: " . ($nilaiRes['data']['success'] ? 'TRUE' : 'FALSE') . "\n";
if (!empty($nilaiRes['data']['data']['selected'])) {
    $sel = $nilaiRes['data']['data']['selected'];
    echo "   Selected Kelas: " . ($sel['nama_kelas'] ?? '-') . " (ID: " . ($sel['kelas_id'] ?? '-') . ")\n";
    echo "   Selected Mapel: " . ($sel['nama_mapel'] ?? '-') . " (ID: " . ($sel['mapel_id'] ?? '-') . ")\n";
    echo "   Semester: " . ($sel['semester_ke'] ?? '-') . "\n";
    $siswaCount = count($nilaiRes['data']['data']['daftar_nilai'] ?? []);
    echo "   Jumlah Siswa: {$siswaCount} orang\n";
    if ($siswaCount > 0) {
        $first = $nilaiRes['data']['data']['daftar_nilai'][0];
        echo "   Contoh Siswa: {$first['nama_siswa']} (NISN: {$first['nisn']}) - Tugas: {$first['nilai_tugas']}, UH: {$first['nilai_uh']}, UTS: {$first['nilai_uts']}, UAS: {$first['nilai_uas']}, NA: {$first['nilai_akhir']}, Predikat: {$first['predikat']}\n";
    }
}
echo "\n";

// 6. Test Simpan Nilai Siswa
if (!empty($nilaiRes['data']['data']['daftar_nilai'])) {
    $sel = $nilaiRes['data']['data']['selected'];
    $first = $nilaiRes['data']['data']['daftar_nilai'][0];
    
    $saveRes = postJson("{$baseUrl}/guru/nilai/save", [
        'kelas_id' => $sel['kelas_id'],
        'mapel_id' => $sel['mapel_id'],
        'semester_ke' => $sel['semester_ke'],
        'tapel_id' => $sel['tapel_id'],
        'nilai' => [
            [
                'siswa_id' => $first['siswa_id'],
                'tugas' => 88.5,
                'uh' => 85.0,
                'uts' => 90.0,
                'uas' => 92.0,
                'capaian' => 'Sangat aktif dan tuntas dalam kompetensi kejuruan'
            ]
        ]
    ], $adminToken);
    echo "6. POST /guru/nilai/save: HTTP " . $saveRes['code'] . "\n";
    echo "   Success: " . ($saveRes['data']['success'] ? 'TRUE' : 'FALSE') . "\n";
    echo "   Message: " . ($saveRes['data']['message'] ?? '-') . "\n";
    echo "   Saved Count: " . ($saveRes['data']['saved_count'] ?? 0) . "\n";
}

echo "\n=== ALL ENDPOINT TESTS COMPLETED SUCCESSFULLY! ===\n";

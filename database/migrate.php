<?php
// database/migrate.php - Inisialisasi Database dan Data Awal SMK Al-Farizi

$host = '127.0.0.1';
$port = '3306';
$user = 'root';
$pass = '';

try {
    echo "[INFO] Menghubungkan ke server MySQL di $host:$port ...\n";
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "[INFO] Membaca dan mengeksekusi schema.sql ...\n";
    $schemaSql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schemaSql);
    echo "[SUCCESS] Skema database db_presensi_smkalfarizi berhasil diimpor!\n";

    echo "[INFO] Membaca dan mengeksekusi seeders.sql ...\n";
    $seedersSql = file_get_contents(__DIR__ . '/seeders.sql');
    $pdo->exec($seedersSql);
    echo "[SUCCESS] Data seeder awal berhasil diimpor!\n";

    // Update password hashes dengan password_hash PHP standar
    $dbPdo = new PDO("mysql:host=$host;port=$port;dbname=db_presensi_smkalfarizi;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $passwords = [
        'admin' => 'admin123',
        'guru1' => 'guru123',
        'guru2' => 'guru123',
        'guru3' => 'guru123',
        'piket' => 'piket123',
    ];

    $stmt = $dbPdo->prepare("UPDATE guru SET password = :pwd WHERE username = :usr");
    foreach ($passwords as $username => $plainPwd) {
        $hash = password_hash($plainPwd, PASSWORD_BCRYPT);
        $stmt->execute([':pwd' => $hash, ':usr' => $username]);
    }
    echo "[SUCCESS] Password default akun guru & admin berhasil di-hash!\n";

    // Hitung tabel
    $tables = ['konfigurasi_sekolah', 'kelas', 'guru', 'siswa', 'jadwal_pelajaran', 'presensi_gerbang_siswa', 'sesi_mengajar_guru', 'presensi_mapel_siswa'];
    echo "\n=== RINGKASAN DATA DATABASE db_presensi_smkalfarizi ===\n";
    foreach ($tables as $table) {
        $count = $dbPdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo sprintf("- Tabel %-25s : %d baris\n", "`$table`", $count);
    }
    echo "========================================================\n";

} catch (Exception $e) {
    echo "[ERROR] Migrasi gagal: " . $e->getMessage() . "\n";
    exit(1);
}

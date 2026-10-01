<?php
/**
 * database/migrator.php — Migration Runner dengan dukungan Rollback
 * 
 * CARA PAKAI (via terminal hosting / SSH / cPanel Terminal):
 * 
 *   php database/migrator.php status          → Lihat status semua migration
 *   php database/migrator.php up              → Jalankan SEMUA migration yang belum dijalankan
 *   php database/migrator.php up m001         → Jalankan migration tertentu saja
 *   php database/migrator.php down m001       → ROLLBACK (batalkan) migration tertentu
 *   php database/migrator.php down            → Rollback migration TERAKHIR saja
 * 
 * CATATAN PENTING:
 *   - Selalu backup database sebelum menjalankan migration di production!
 *   - Rollback (down) hanya menghapus kolom/tabel yang ditambahkan, DATA tidak hilang.
 *   - Urutan migration: m001 → m002 → m003 → ... (berdasarkan nama file)
 */

declare(strict_types=1);

// ===== 1. LOAD KONFIGURASI =====
$envPath = __DIR__ . '/../.env';
$dbConfig = ['DB_HOST' => '127.0.0.1', 'DB_PORT' => '3306', 'DB_DATABASE' => 'db_presensi_smkalfarizi', 'DB_USERNAME' => 'root', 'DB_PASSWORD' => ''];

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $dbConfig[trim($k)] = trim($v);
    }
}

// ===== 2. KONEKSI DATABASE =====
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['DB_HOST']};port={$dbConfig['DB_PORT']};dbname={$dbConfig['DB_DATABASE']};charset=utf8mb4",
        $dbConfig['DB_USERNAME'],
        $dbConfig['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (\PDOException $e) {
    die("[ERROR] Gagal konek ke database: " . $e->getMessage() . "\n");
}

// ===== 3. BUAT TABEL TRACKING MIGRATION (jika belum ada) =====
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `migrations` (
        `id`          INT AUTO_INCREMENT PRIMARY KEY,
        `migration`   VARCHAR(100) NOT NULL UNIQUE,
        `description` TEXT NULL,
        `batch`       INT NOT NULL DEFAULT 1,
        `run_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ===== 4. FUNGSI HELPER =====
function loadMigrations(): array {
    $dir = __DIR__ . '/migrations';
    if (!is_dir($dir)) return [];
    $files = glob($dir . '/m*.php');
    sort($files);
    return $files;
}

function getMigrationName(string $file): string {
    return pathinfo($file, PATHINFO_FILENAME); // e.g. "m001_gambar_kop_surat"
}

function getRanMigrations(PDO $pdo): array {
    return $pdo->query("SELECT `migration`, `batch` FROM `migrations` ORDER BY `id` ASC")
               ->fetchAll(PDO::FETCH_KEY_PAIR);
}

function printLine(string $msg, string $type = 'INFO'): void {
    $colors = ['INFO' => "\033[36m", 'SUCCESS' => "\033[32m", 'ERROR' => "\033[31m", 'WARN' => "\033[33m", 'RESET' => "\033[0m"];
    echo ($colors[$type] ?? '') . "[{$type}] " . ($colors['RESET']) . $msg . "\n";
}

function runSql(PDO $pdo, string $sql, string $label): bool {
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (empty($stmt)) continue;
        try {
            $pdo->exec($stmt . ';');
        } catch (\PDOException $e) {
            // Abaikan error "Duplicate column name" saat re-run (idempotent)
            if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                strpos($e->getMessage(), 'already exists') !== false) {
                printLine("  Kolom/tabel sudah ada, dilewati (idempotent): {$label}", 'WARN');
                continue;
            }
            printLine("Gagal eksekusi SQL di [{$label}]: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    return true;
}

// ===== 5. COMMAND PARSER =====
$command = $argv[1] ?? 'status';
$target  = $argv[2] ?? null; // misal: "m001"

$allFiles  = loadMigrations();
$ranMigrations = getRanMigrations($pdo);
$currentBatch  = $ranMigrations ? max(array_values($ranMigrations)) : 0;

// ===================================================================
// COMMAND: status
// ===================================================================
if ($command === 'status') {
    printLine("======= STATUS MIGRATION =======", 'INFO');
    if (empty($allFiles)) {
        printLine("Tidak ada file migration di database/migrations/", 'WARN');
        exit(0);
    }
    foreach ($allFiles as $file) {
        $name = getMigrationName($file);
        $migration = require $file;
        $status = isset($ranMigrations[$name]) ? '✅ Sudah jalan (batch #' . $ranMigrations[$name] . ')' : '⏳ Belum dijalankan';
        echo "  {$name}  →  {$status}\n";
        echo "     Keterangan: " . ($migration['description'] ?? '-') . "\n\n";
    }
    exit(0);
}

// ===================================================================
// COMMAND: up (jalankan migration)
// ===================================================================
if ($command === 'up') {
    $newBatch = $currentBatch + 1;
    $ran = 0;

    foreach ($allFiles as $file) {
        $name = getMigrationName($file);

        // Jika target tertentu, skip yang lain
        if ($target && stripos($name, $target) === false) continue;
        // Jika sudah dijalankan, skip
        if (isset($ranMigrations[$name]) && !$target) continue;
        if (isset($ranMigrations[$name]) && $target) {
            printLine("Migration [{$name}] sudah pernah dijalankan. Gunakan 'down' untuk rollback dulu.", 'WARN');
            continue;
        }

        $migration = require $file;
        printLine("Menjalankan migration: {$name} ...", 'INFO');

        if (runSql($pdo, $migration['up'], $name)) {
            $stmt = $pdo->prepare("INSERT INTO `migrations` (`migration`, `description`, `batch`) VALUES (?, ?, ?)");
            $stmt->execute([$name, $migration['description'] ?? '', $newBatch]);
            printLine("Berhasil: {$name}", 'SUCCESS');
            $ran++;
        } else {
            printLine("GAGAL di migration: {$name}. Proses dihentikan.", 'ERROR');
            exit(1);
        }
    }

    if ($ran === 0) {
        printLine("Tidak ada migration baru yang perlu dijalankan.", 'INFO');
    } else {
        printLine("Total {$ran} migration berhasil dijalankan (batch #{$newBatch}).", 'SUCCESS');
    }
    exit(0);
}

// ===================================================================
// COMMAND: down (rollback)
// ===================================================================
if ($command === 'down') {
    if (empty($ranMigrations)) {
        printLine("Tidak ada migration yang sudah dijalankan. Tidak ada yang perlu di-rollback.", 'WARN');
        exit(0);
    }

    // Tentukan apa yang di-rollback
    $toRollback = [];

    if ($target) {
        // Rollback target tertentu
        foreach ($allFiles as $file) {
            $name = getMigrationName($file);
            if (stripos($name, $target) !== false && isset($ranMigrations[$name])) {
                $toRollback[] = $file;
            }
        }
        if (empty($toRollback)) {
            printLine("Migration [{$target}] tidak ditemukan atau belum pernah dijalankan.", 'WARN');
            exit(1);
        }
    } else {
        // Rollback batch terakhir saja
        $lastBatch = max(array_values($ranMigrations));
        $lastBatchNames = array_keys(array_filter($ranMigrations, fn($b) => $b === $lastBatch));
        foreach (array_reverse($allFiles) as $file) { // reverse agar urutan terbalik
            if (in_array(getMigrationName($file), $lastBatchNames)) {
                $toRollback[] = $file;
            }
        }
    }

    $rolled = 0;
    foreach (array_reverse($toRollback) as $file) {
        $name = getMigrationName($file);
        $migration = require $file;

        printLine("Rolling back: {$name} ...", 'WARN');

        if (!isset($migration['down']) || empty(trim($migration['down']))) {
            printLine("Migration [{$name}] tidak memiliki script 'down'. Dilewati.", 'WARN');
            continue;
        }

        if (runSql($pdo, $migration['down'], "ROLLBACK:{$name}")) {
            $pdo->prepare("DELETE FROM `migrations` WHERE `migration` = ?")->execute([$name]);
            printLine("Rollback berhasil: {$name}", 'SUCCESS');
            $rolled++;
        } else {
            printLine("GAGAL rollback: {$name}.", 'ERROR');
            exit(1);
        }
    }

    printLine("Total {$rolled} migration berhasil di-rollback.", 'SUCCESS');
    exit(0);
}

printLine("Perintah tidak dikenal: '{$command}'. Gunakan: status | up | up m001 | down | down m001", 'ERROR');
exit(1);

<?php
// cron/backup_db.php
// Eksekusi tiap minggu via cron (misal: 0 0 * * 0 php /path/to/backup_db.php)

declare(strict_types=1);

require_once __DIR__ . '/../app/Config/App.php';

use App\Config\App;

App::init();

$backupDir = __DIR__ . '/../database/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$date = date('Ymd_His');
$filename = $backupDir . '/backup_' . $date . '.sql';

$env = parse_ini_file(__DIR__ . '/../.env');
$host = $env['DB_HOST'] ?? '127.0.0.1';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$name = $env['DB_NAME'] ?? 'absensi_db';
$port = $env['DB_PORT'] ?? '3306';

// Build command
$command = "mysqldump --host={$host} --port={$port} --user={$user}";
if (!empty($pass)) {
    $command .= " --password={$pass}";
}
$command .= " {$name} > {$filename}";

echo "[" . date('Y-m-d H:i:s') . "] Memulai backup database...\n";
exec($command, $output, $result);

if ($result === 0) {
    echo "[" . date('Y-m-d H:i:s') . "] Backup berhasil: {$filename}\n";
    
    // Optional: Bersihkan backup lama (contoh: simpan hanya 5 terbaru)
    $files = glob($backupDir . '/backup_*.sql');
    if (count($files) > 5) {
        rsort($files);
        $toDelete = array_slice($files, 5);
        foreach ($toDelete as $f) {
            unlink($f);
            echo " - File backup lama dihapus: {$f}\n";
        }
    }
} else {
    echo "[" . date('Y-m-d H:i:s') . "] Backup gagal dengan kode error: {$result}\n";
}

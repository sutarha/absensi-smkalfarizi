<?php
namespace App\Controllers;

use App\Config\App;
use App\Config\Database;
use App\Controllers\AuthController;
use App\Models\KonfigurasiSekolah;
use PDO;

class BackupController
{
    private function authAdmin(): array
    {
        $user = AuthController::checkAuth();
        if (!$user || !in_array($user['role'], ['admin', 'kepala_sekolah'])) {
            App::redirect(App::baseUrl('login'));
            exit;
        }
        return $user;
    }

    public function index(): void
    {
        $user = $this->authAdmin();
        $config = KonfigurasiSekolah::get();

        require __DIR__ . '/../Views/admin/backup.php';
    }

    public function download(): void
    {
        $this->authAdmin();

        $db = Database::getConnection();
        
        $tables = [];
        $stmt = $db->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sqlScript = "-- Database Backup for SMK Al-Farizi\n";
        $sqlScript .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n";
        $sqlScript .= "-- --------------------------------------------------------\n\n";
        $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Drop table statement
            $sqlScript .= "DROP TABLE IF EXISTS `$table`;\n";

            // Create table statement
            $stmt = $db->query("SHOW CREATE TABLE `$table`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            $sqlScript .= $row[1] . ";\n\n";

            // Insert rows
            $stmt = $db->query("SELECT * FROM `$table`");
            $rowCount = $stmt->rowCount();

            if ($rowCount > 0) {
                $sqlScript .= "INSERT INTO `$table` VALUES ";
                $rowsData = [];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $rowData = [];
                    foreach ($row as $val) {
                        if (is_null($val)) {
                            $rowData[] = "NULL";
                        } else {
                            $val = addslashes($val);
                            $val = str_replace("\n", "\\n", $val);
                            $val = str_replace("\r", "\\r", $val);
                            $rowData[] = "'" . $val . "'";
                        }
                    }
                    $rowsData[] = "(" . implode(', ', $rowData) . ")";
                }
                $sqlScript .= implode(",\n", $rowsData) . ";\n\n";
            }
        }
        $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_db_smkalfarizi_' . date('Ymd_His') . '.sql';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sqlScript));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        echo $sqlScript;
        exit;
    }
}

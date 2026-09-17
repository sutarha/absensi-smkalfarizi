<?php

namespace App\Helpers;

class AuditLog
{
    private static string $logFile = __DIR__ . '/../../storage/logs/audit.log';

    public static function log(string $action, string $description, ?string $user = null): void
    {
        $dir = dirname(self::$logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $user = $user ?? ($_SESSION['user']['username'] ?? 'System');
        
        $logEntry = sprintf(
            "[%s] [IP: %s] [User: %s] [Action: %s] - %s\n",
            $timestamp,
            $ip,
            $user,
            $action,
            $description
        );

        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
}

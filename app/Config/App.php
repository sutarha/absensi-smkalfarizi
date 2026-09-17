<?php
namespace App\Config;

class App
{
    public const APP_NAME = 'SI Presensi Cerdas & Penggajian Terintegrasi';
    public const SCHOOL_NAME = 'SMK AL-FARIZI';
    public const TIMEZONE = 'Asia/Jakarta';

    public static function init(): void
    {
        self::loadEnv();
        date_default_timezone_set(self::TIMEZONE);
        
        // Error Reporting based on Environment
        $env = getenv('APP_ENV') ?: 'production';
        $debug = getenv('APP_DEBUG') ?: 'false';
        
        if (strtolower($env) === 'production' && strtolower($debug) !== 'true') {
            ini_set('display_errors', '0');
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
        } else {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        }

        if (session_status() === PHP_SESSION_NONE) {
            $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
            
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    private static function loadEnv(): void
    {
        $path = __DIR__ . '/../../.env';
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    public static function isDevMode(): bool
    {
        $env = getenv('APP_ENV') ?: 'production';
        $debug = getenv('APP_DEBUG') ?: 'false';
        return in_array(strtolower($env), ['local', 'dev', 'development', 'testing']) || strtolower($debug) === 'true';
    }

    public static function baseUrl(string $path = ''): string
    {
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base = rtrim($scriptName, '/');
        $path = ltrim($path, '/');
        if ($path !== '') {
            return "{$base}/{$path}";
        }
        return $base ? "{$base}" : '';
    }

    public static function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

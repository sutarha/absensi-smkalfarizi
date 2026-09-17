<?php
namespace App\Controllers\Api;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

/**
 * ApiController - Base Class untuk semua REST API Endpoint
 * 
 * Menyediakan:
 *  - JWT Authentication middleware
 *  - CORS headers (untuk Android/mobile client)
 *  - JSON response helpers
 *  - Input parsing (JSON body)
 * 
 * ROLLBACK: Hapus seluruh folder app/Controllers/Api/ untuk kembali ke semula
 */
class ApiController
{
    protected const JWT_ALGO = 'HS256';
    protected const TOKEN_TTL = 86400 * 7; // 7 hari

    /**
     * Kirim response JSON dan stop eksekusi
     */
    protected static function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Baca body request sebagai JSON (untuk POST dengan body JSON)
     */
    protected static function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Ambil JWT secret dari environment variable
     */
    protected static function getJwtSecret(): string
    {
        $secret = getenv('JWT_SECRET') ?: 'smk_alfarizi_secret_key_2024_xK9pL3mN7qR1vT5wY8uZ';
        return $secret;
    }

    /**
     * Buat JWT token baru untuk user
     */
    protected static function createToken(array $userPayload): string
    {
        $now = time();
        $payload = [
            'iss' => 'smk-alfarizi-api',
            'iat' => $now,
            'exp' => $now + self::TOKEN_TTL,
            'uid' => $userPayload['id'],
            'username' => $userPayload['username'],
            'role' => $userPayload['role'],
            'nama' => $userPayload['nama_lengkap'],
        ];
        return JWT::encode($payload, self::getJwtSecret(), self::JWT_ALGO);
    }

    /**
     * Verifikasi dan decode JWT dari Authorization header
     * Return payload jika valid, atau langsung kirim 401 dan exit
     */
    protected static function requireAuth(?array $allowedRoles = null): object
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
            self::json(['success' => false, 'message' => 'Token autentikasi tidak ditemukan. Harap login terlebih dahulu.'], 401);
        }

        $token = substr($authHeader, 7);

        try {
            $payload = JWT::decode($token, new Key(self::getJwtSecret(), self::JWT_ALGO));
        } catch (ExpiredException $e) {
            self::json(['success' => false, 'message' => 'Sesi login telah berakhir. Silakan login kembali.', 'code' => 'TOKEN_EXPIRED'], 401);
        } catch (SignatureInvalidException $e) {
            self::json(['success' => false, 'message' => 'Token tidak valid.', 'code' => 'TOKEN_INVALID'], 401);
        } catch (\Throwable $e) {
            self::json(['success' => false, 'message' => 'Autentikasi gagal: ' . $e->getMessage()], 401);
        }

        // Cek role jika diperlukan
        if ($allowedRoles !== null && !in_array($payload->role, $allowedRoles)) {
            self::json(['success' => false, 'message' => 'Akses ditolak. Role Anda tidak memiliki izin untuk fitur ini.'], 403);
        }

        return $payload;
    }

    /**
     * Kirim CORS headers — wajib untuk Android/mobile http client
     */
    public static function setCorsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    /**
     * Pastikan method request sesuai, atau kirim 405
     */
    protected static function requireMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            self::json([
                'success' => false,
                'message' => "Method {$_SERVER['REQUEST_METHOD']} tidak diizinkan untuk endpoint ini. Gunakan {$method}."
            ], 405);
        }
    }
}

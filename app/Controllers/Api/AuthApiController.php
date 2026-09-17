<?php
namespace App\Controllers\Api;

use App\Config\Database;
use App\Models\Guru;

/**
 * AuthApiController - Endpoint Login & Logout via JWT
 * 
 * POST /api/v1/auth/login
 * POST /api/v1/auth/logout
 * 
 * ROLLBACK: Hapus file ini + blok di index.php
 */
class AuthApiController extends ApiController
{
    /**
     * POST /api/v1/auth/login
     * 
     * Request body (JSON atau form-data):
     * { "username": "...", "password": "..." }
     * 
     * Response sukses:
     * { "success": true, "token": "...", "user": {...}, "expires_in": 604800 }
     */
    public function login(): void
    {
        self::requireMethod('POST');

        // Terima input dari JSON body atau form POST
        $body = self::jsonBody();
        $username = trim($body['username'] ?? $_POST['username'] ?? '');
        $password  = $body['password']  ?? $_POST['password']  ?? '';

        if (empty($username) || empty($password)) {
            self::json(['success' => false, 'message' => 'Username dan password wajib diisi.'], 422);
        }

        $user = Guru::findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            self::json(['success' => false, 'message' => 'Username atau password salah.'], 401);
        }

        // Hapus password dari payload sebelum dikembalikan
        $userPayload = $user;
        unset($userPayload['password']);

        $token = self::createToken($userPayload);

        self::json([
            'success'    => true,
            'message'    => 'Login berhasil. Selamat datang, ' . $user['nama_lengkap'] . '!',
            'token'      => $token,
            'expires_in' => self::TOKEN_TTL,
            'token_type' => 'Bearer',
            'user'       => [
                'id'             => (int)$user['id'],
                'nik_nip'        => $user['nik_nip'],
                'nama_lengkap'   => $user['nama_lengkap'],
                'username'       => $user['username'],
                'role'           => $user['role'],
                'tugas_tambahan' => $user['tugas_tambahan'] ?? null,
                'tunjangan_tugas'=> (float)($user['tunjangan_tugas'] ?? 0),
                'no_hp'          => $user['no_hp'] ?? null,
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * 
     * Karena JWT stateless, logout di sisi server hanya konfirmasi.
     * Client wajib menghapus token dari storage-nya.
     * 
     * Headers: Authorization: Bearer {token}
     */
    public function logout(): void
    {
        self::requireMethod('POST');
        // Verifikasi token masih valid sebelum konfirmasi logout
        self::requireAuth();

        self::json([
            'success' => true,
            'message' => 'Logout berhasil. Silakan hapus token dari perangkat Anda.',
        ]);
    }

    /**
     * GET /api/v1/auth/me
     * Ambil data user dari token (untuk validasi sesi aktif di Android)
     */
    public function me(): void
    {
        self::requireMethod('GET');
        $payload = self::requireAuth();

        // Ambil data terbaru dari database
        $user = Guru::findByUsername($payload->username);
        if (!$user) {
            self::json(['success' => false, 'message' => 'User tidak ditemukan.'], 404);
        }
        unset($user['password']);

        self::json([
            'success' => true,
            'user'    => [
                'id'             => (int)$user['id'],
                'nik_nip'        => $user['nik_nip'],
                'nama_lengkap'   => $user['nama_lengkap'],
                'username'       => $user['username'],
                'role'           => $user['role'],
                'tugas_tambahan' => $user['tugas_tambahan'] ?? null,
                'tunjangan_tugas'=> (float)($user['tunjangan_tugas'] ?? 0),
                'no_hp'          => $user['no_hp'] ?? null,
            ],
        ]);
    }
}

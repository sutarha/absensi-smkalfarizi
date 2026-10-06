<?php
namespace App\Controllers;

use App\Config\App;
use App\Models\Guru;

class AuthController
{
    public static function checkAuth(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function requireLogin(): array
    {
        $user = self::checkAuth();
        if (!$user) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                App::json(['success' => false, 'message' => 'Sesi login telah berakhir. Silakan muat ulang halaman dan login kembali.'], 401);
            }
            App::redirect(App::baseUrl('login'));
        }
        return $user;
    }

    public static function requireRole(array $allowedRoles): array
    {
        if (in_array('admin', $allowedRoles) && !in_array('super_admin', $allowedRoles)) {
            $allowedRoles[] = 'super_admin';
        }
        $user = self::requireLogin();
        if (!in_array($user['role'], $allowedRoles)) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

            if ($isAjax) {
                App::json(['success' => false, 'message' => 'Akses ditolak: role Anda tidak memiliki wewenang fitur ini.'], 403);
            }
            http_response_code(403);
            die("Akses ditolak. Halaman ini hanya untuk hak akses: " . implode(', ', $allowedRoles));
        }
        return $user;
    }

    public function loginView(): void
    {
        $user = self::checkAuth();
        if ($user) {
            $this->redirectByRole($user['role']);
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);
        
        $config = \App\Models\KonfigurasiSekolah::get();

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function doLogin(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Username dan password wajib diisi!';
            App::redirect(App::baseUrl('login'));
        }

        // ===== ANTI BRUTE-FORCE: Rate Limiting Login =====
        $attempts   = $_SESSION['login_attempts']   ?? 0;
        $lastAttempt = $_SESSION['login_last_attempt'] ?? 0;
        $lockoutTime = 5 * 60; // 5 menit

        if ($attempts >= 5 && (time() - $lastAttempt) < $lockoutTime) {
            $sisaDetik = $lockoutTime - (time() - $lastAttempt);
            $_SESSION['login_error'] = 'Terlalu banyak percobaan login. Coba lagi dalam ' . ceil($sisaDetik / 60) . ' menit.';
            App::redirect(App::baseUrl('login'));
            return;
        }
        // =================================================

        $user = Guru::findByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            // Check Siswa (username = NISN, password = Tanggal Lahir)
            $siswa = \App\Models\Siswa::findByNisnAndTanggalLahir($username, $password);
            
            if ($siswa) {
                session_regenerate_id(true);
                $siswa['role'] = 'siswa';
                $siswa['nama_lengkap'] = $siswa['nama_siswa'];
                $_SESSION['user'] = $siswa;
                unset($_SESSION['login_attempts'], $_SESSION['login_last_attempt']);
                \App\Helpers\AuditLog::log('LOGIN_SUCCESS', "Siswa login: {$username}", $username);
                $this->redirectByRole('siswa');
                return;
            }

            // Tambah counter percobaan gagal
            $_SESSION['login_attempts']    = ($attempts + 1);
            $_SESSION['login_last_attempt'] = time();
            \App\Helpers\AuditLog::log('LOGIN_FAILED', "Percobaan login gagal untuk username: {$username}", $username);
            $_SESSION['login_error'] = 'Username atau password salah. Silakan coba lagi.';
            App::redirect(App::baseUrl('login'));
        }

        // Login berhasil - Cegah Session Fixation
        session_regenerate_id(true);
        unset($user['password']);
        unset($_SESSION['login_attempts'], $_SESSION['login_last_attempt']);
        $_SESSION['user'] = $user;
        $_SESSION['last_activity'] = time();
        \App\Helpers\AuditLog::log('LOGIN_SUCCESS', "Login berhasil: {$username} (role: {$user['role']})", $username);

        $this->redirectByRole($user['role']);
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        App::redirect(App::baseUrl('login'));
    }

    private function redirectByRole(string $role): void
    {
        switch ($role) {
            case 'super_admin':
            case 'admin':
                App::redirect(App::baseUrl('admin/dashboard'));
                break;
            case 'piket':
                App::redirect(App::baseUrl('piket/scanner'));
                break;
            case 'siswa':
                // Redirect to PWA shell untuk siswa
                App::redirect(App::baseUrl('siswa'));
                break;
            case 'kepala_sekolah':
            case 'wakasek_kurikulum':
            case 'bendahara':
            case 'guru':
            default:
                // Opsi B: Default ke Panel Guru
                App::redirect(App::baseUrl('guru/dashboard'));
                break;
        }
    }
}

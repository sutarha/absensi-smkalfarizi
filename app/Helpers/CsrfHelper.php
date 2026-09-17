<?php
namespace App\Helpers;

class CsrfHelper
{
    private const SESSION_KEY = 'csrf_token';

    /**
     * Generate or retrieve the current CSRF token.
     */
    public static function getToken(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            try {
                $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
            } catch (\Exception $e) {
                // Fallback if random_bytes fails
                $_SESSION[self::SESSION_KEY] = bin2hex(openssl_random_pseudo_bytes(32));
            }
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Get the HTML input field for the CSRF token.
     */
    public static function getTokenInput(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Validate the provided token against the session token.
     */
    public static function validateToken(?string $token): bool
    {
        if (empty($_SESSION[self::SESSION_KEY]) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * Clear the CSRF token (useful on logout or after sensitive actions)
     */
    public static function clearToken(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}

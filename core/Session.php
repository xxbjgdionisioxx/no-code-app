<?php

namespace Core;

/**
 * Session — Wrapper around PHP native sessions.
 *
 * Provides a clean static interface with configurable lifetime and cookie settings.
 */
class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(SESSION_NAME);

        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),  // HTTPS-only if available
            'httponly' => true,                        // Deny JS access to cookie
            'samesite' => 'Lax',                       // CSRF mitigation
        ]);

        session_start();
        self::$started = true;

        // Regenerate session ID periodically to prevent fixation attacks
        if (!isset($_SESSION['_last_regen'])) {
            $_SESSION['_last_regen'] = time();
        } elseif (time() - $_SESSION['_last_regen'] > 900) {
            // Regenerate every 15 minutes
            session_regenerate_id(true);
            $_SESSION['_last_regen'] = time();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Retrieve and immediately remove a flash value.
     * Returns the stored value (or default), then clears the key.
     */
    public static function flash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $value;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
        self::$started = false;
    }

    /**
     * Store all form input so it can be re-populated after a redirect.
     */
    public static function old(array $input): void
    {
        $_SESSION['_old_input'] = $input;
    }

    /**
     * Retrieve a previously stored form value (cleared after first access).
     */
    public static function getOld(string $key, mixed $default = null): mixed
    {
        $old = $_SESSION['_old_input'] ?? [];
        unset($_SESSION['_old_input'][$key]);
        return $old[$key] ?? $default;
    }
}

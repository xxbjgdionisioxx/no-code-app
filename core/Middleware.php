<?php

namespace Core;

/**
 * Middleware — Authentication and permission middleware runner.
 *
 * Controllers call these guards to protect actions.
 * The RBAC-specific permission guard lives in RbacEngine.
 */
class Middleware
{
    /**
     * Ensure the request comes from an authenticated user.
     * Redirects to /login with a flash message if not.
     */
    public static function auth(bool $showFlash = true): array
    {
        Session::start();
        $user = Session::get('user');

        if (!$user || empty($user['id'])) {
            if ($showFlash) {
                Session::set('flash', ['type' => 'error', 'message' => 'Please log in to continue.']);
            }
            header('Location: ' . APP_URL . '/login');
            exit;
        }

        return $user;
    }

    /**
     * Ensure the authenticated user is a platform admin.
     */
    public static function admin(): array
    {
        $user = self::auth();

        if (empty($user['is_admin'])) {
            http_response_code(403);
            require BASE_PATH . '/views/errors/403.php';
            exit;
        }

        return $user;
    }

    /**
     * Throttle repeated requests (simple session-based rate limiter).
     *
     * @param string $key      Unique throttle key (e.g., 'login_attempt')
     * @param int    $max      Max attempts allowed
     * @param int    $window   Time window in seconds
     */
    public static function throttle(string $key, int $max = 10, int $window = 60): void
    {
        $sessionKey = '_throttle_' . $key;
        $data = Session::get($sessionKey, ['count' => 0, 'reset_at' => time() + $window]);

        if (time() > $data['reset_at']) {
            // Window expired — reset counter
            $data = ['count' => 0, 'reset_at' => time() + $window];
        }

        $data['count']++;
        Session::set($sessionKey, $data);

        if ($data['count'] > $max) {
            http_response_code(429);
            die('<h1>429 — Too Many Requests</h1><p>Please wait before trying again.</p>');
        }
    }

    /**
     * Validate CSRF token from the current POST request.
     * Dies with 419 if invalid.
     */
    public static function csrf(): void
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        CSRF::validate($token);
    }
}

<?php

namespace Core;

/**
 * CSRF — Token generation and validation.
 *
 * Tokens are stored in session and verified on each mutating request.
 * Each token has a one-hour expiry.
 */
class CSRF
{
    /**
     * Generate (or retrieve existing) CSRF token.
     * Token is tied to the current session.
     */
    public static function token(): string
    {
        if (!Session::has('_csrf_token') || self::isExpired()) {
            $_SESSION['_csrf_token']         = bin2hex(random_bytes(32));
            $_SESSION['_csrf_token_expires'] = time() + CSRF_EXPIRY;
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Render a hidden HTML input carrying the CSRF token.
     * Usage: <?= CSRF::field() ?> inside every form.
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        $name  = CSRF_TOKEN_NAME;
        return "<input type=\"hidden\" name=\"{$name}\" value=\"{$token}\">";
    }

    /**
     * Validate a submitted token against the session-stored one.
     * Throws an exception on mismatch — front controller renders 419.
     */
    public static function validate(string $submittedToken): void
    {
        $sessionToken = Session::get('_csrf_token', '');

        if (
            empty($submittedToken) ||
            empty($sessionToken)   ||
            !hash_equals($sessionToken, $submittedToken) ||
            self::isExpired()
        ) {
            http_response_code(419);
            die('<h1>419 — Page Expired</h1><p>Your CSRF token has expired. Please go back and try again.</p>');
        }
    }

    private static function isExpired(): bool
    {
        $expires = Session::get('_csrf_token_expires', 0);
        return time() > $expires;
    }
}

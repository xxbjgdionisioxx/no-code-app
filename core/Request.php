<?php

namespace Core;

/**
 * Request — HTTP request abstraction.
 *
 * Wraps superglobals with safe accessor methods and
 * provides route parameter injection by the router.
 */
class Request
{
    private array $routeParams = [];

    // ── HTTP Verb & URI ──────────────────────────────────────

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        // Strip query string and base path prefix
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?');   // remove ?query
        $uri = rtrim($uri, '/') ?: '/';

        // If app lives in a subdirectory, strip the subfolder prefix
        // Example: /no-code-app/apps → /apps
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }

        return $uri;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    // ── Input Accessors ──────────────────────────────────────

    /**
     * Get a POST value, with optional default.
     * Does NOT sanitize — sanitization is FieldEngine's responsibility.
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get all POST data.
     */
    public function postAll(): array
    {
        return $_POST;
    }

    /**
     * Get a GET/query string value.
     */
    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get a route parameter injected by the router.
     * e.g., {appId} from /apps/{appId}/modules
     */
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /**
     * Get uploaded file info from $_FILES.
     */
    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Get all uploaded files.
     */
    public function files(): array
    {
        return $_FILES;
    }

    // ── Headers & Meta ───────────────────────────────────────

    public function header(string $name, string $default = ''): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? $default;
    }

    public function ip(): string
    {
        // Check proxy headers for real IP (basic version)
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function bearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }

    // ── JSON body (for API requests) ─────────────────────────

    public function json(): array
    {
        $body = file_get_contents('php://input');
        return json_decode($body, true) ?? [];
    }
}

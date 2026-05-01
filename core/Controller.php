<?php

namespace Core;

/**
 * Base Controller — shared functionality for all controllers.
 *
 * Provides view rendering, redirects, JSON responses,
 * flash message management, and auth guards.
 */
abstract class Controller
{
    protected \PDO $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    // ── View Rendering ───────────────────────────────────────

    /**
     * Render a view file inside a layout.
     *
     * @param string $view      Dot-notation path relative to views/ (e.g., 'records.index')
     * @param array  $data      Variables extracted into view scope
     * @param string $layout    Layout template name (without .php)
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Make data available as variables in view scope
        extract($data, EXTR_SKIP);

        // Capture view content
        ob_start();
        $viewFile = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Render inside layout
        $layoutFile = BASE_PATH . '/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout not found: {$layoutFile}");
        }
        require $layoutFile;
    }

    /**
     * Render a view without any layout (for AJAX partials).
     */
    protected function partial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = BASE_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        require $viewFile;
    }

    // ── JSON Response ────────────────────────────────────────

    /**
     * Output a JSON response and terminate.
     */
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Convenience JSON error response.
     */
    protected function jsonError(string $message, int $status = 400): void
    {
        $this->json(['success' => false, 'error' => $message], $status);
    }

    // ── Redirects ────────────────────────────────────────────

    /**
     * Redirect to a URL (relative or absolute).
     */
    protected function redirect(string $url): void
    {
        // Append cache buster to forcefully break browser cache on redirects
        $separator = str_contains($url, '?') ? '&' : '?';
        $url .= $separator . '_t=' . time();
        
        header('Location: ' . APP_URL . $url);
        exit;
    }

    protected function redirectBack(string $fallback = '/'): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? APP_URL . $fallback;
        header('Location: ' . $ref);
        exit;
    }

    // ── Flash Messages ───────────────────────────────────────

    protected function flash(string $type, string $message): void
    {
        Session::set('flash', ['type' => $type, 'message' => $message]);
    }

    protected function flashSuccess(string $message): void
    {
        $this->flash('success', $message);
    }

    protected function flashError(string $message): void
    {
        $this->flash('error', $message);
    }

    // ── Auth Guards ──────────────────────────────────────────

    /**
     * Require authenticated user — redirect to login if not.
     */
    protected function requireAuth(bool $showFlash = true): array
    {
        $user = Session::get('user');
        if (!$user) {
            if ($showFlash) {
                $this->flash('error', 'Please log in to continue.');
            }
            $this->redirect('/login');
        }
        return $user;
    }

    /**
     * Require admin — show 403 if not.
     */
    protected function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if (empty($user['is_admin'])) {
            http_response_code(403);
            require BASE_PATH . '/views/errors/403.php';
            exit;
        }
        return $user;
    }

    // ── CSRF ────────────────────────────────────────────────

    protected function validateCsrf(Request $request): void
    {
        CSRF::validate($request->post(CSRF_TOKEN_NAME, ''));
    }

    // ── Utility ──────────────────────────────────────────────

    /**
     * Generate a URL-safe slug from a string.
     */
    protected function slugify(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s_-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '_', $text);
        return $text;
    }
}

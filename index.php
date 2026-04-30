<?php
/**
 * Front Controller — AppForge No-Code Platform
 *
 * Single entry point for all HTTP requests.
 * Bootstraps config, autoloader, session, and routes the request.
 */

// ── Bootstrap ───────────────────────────────────────────────
ob_start();
file_put_contents(__DIR__.'/request.log', date('Y-m-d H:i:s') . ' | ' . $_SERVER['REQUEST_METHOD'] . ' ' . ($_SERVER['REQUEST_URI'] ?? '') . "\n", FILE_APPEND);

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

// PSR-4 style autoloader — loads classes from their namespace-mapped directories
spl_autoload_register(function (string $class): void {
    // Map namespace prefixes to directories
    $prefixes = [
        'Core\\'        => __DIR__ . '/core/',
        'Engine\\'      => __DIR__ . '/engine/',
        'Controllers\\' => __DIR__ . '/controllers/',
        'Models\\'      => __DIR__ . '/models/',
        'Plugins\\'     => __DIR__ . '/plugins/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $relative = substr($class, strlen($prefix));
            $file = $dir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

// ── Session ─────────────────────────────────────────────────
use Core\Session;
use Core\Router;
use Core\Request;

Session::start();

// Prevent browser caching for dynamic pages (fixes delete redirect cache issues)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// ── Plugin Manager (boots all registered plugins) ───────────
use Plugins\PluginManager;
$pluginManager = new PluginManager();
$pluginManager->register(new \Plugins\AuditLog\AuditLogPlugin(getDB()));
$pluginManager->boot();

// ── Router ──────────────────────────────────────────────────
$router = new Router($pluginManager);
require_once __DIR__ . '/config/routes.php';

// ── Dispatch ─────────────────────────────────────────────────
$request = new Request();

try {
    $router->dispatch($request);
} catch (\Exception $e) {
    // Generic error fallback
    file_put_contents(__DIR__.'/error.log', date('Y-m-d H:i:s') . ' | ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
    http_response_code(500);
    if (APP_ENV === 'development') {
        echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:2rem;font-family:monospace;">';
        echo '<strong style="color:#f38ba8">Error:</strong> ' . htmlspecialchars($e->getMessage()) . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        echo '<h1>Internal Server Error</h1><p>Please try again later.</p>';
    }
}

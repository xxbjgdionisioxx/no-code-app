<?php
require 'config/app.php';
require 'config/database.php';
require 'core/Request.php';
require 'core/Session.php';

spl_autoload_register(function ($class) {
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

// Mock request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/apps/1/dashboard/widgets/1/delete';
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Bypass auth by setting session manually
\Core\Session::start();
\Core\Session::set('user', ['id' => 1, 'is_admin' => 1]);

$request = new \Core\Request();

// Re-initialize Router
$pluginManager = new \Plugins\PluginManager();
$router = new \Core\Router($pluginManager);
require 'config/routes.php';

try {
    $router->dispatch($request);
    echo "Dispatched successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

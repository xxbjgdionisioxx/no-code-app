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

$db = getDB();

$engine = new \Engine\DashboardEngine($db);
$widget = $engine->getWidget(1);

if ($widget) {
    echo "Widget 1 found.\n";
    try {
        $data = $engine->compute($widget);
        print_r($data);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Widget 1 not found.\n";
}

$recEngine = new \Engine\RecordEngine($db, new \Engine\FieldEngine($db), new \Plugins\PluginManager());
try {
    $recEngine->deleteRecord(1);
    echo "Record 1 deleted.\n";
} catch (Exception $e) {
    echo "Record Delete Error: " . $e->getMessage() . "\n";
}

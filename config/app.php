<?php
/**
 * Application-level constants and global settings.
 */

// Base URL — auto-detected from server, or override manually
define('APP_NAME',    'Modulyn');
define('APP_VERSION', '1.0.0');
if (php_sapi_name() === 'cli') {
    define('APP_URL', 'http://localhost/no-code-app');
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = dirname($_SERVER['SCRIPT_NAME']);
    $dir = ($dir === '/' || $dir === '\\') ? '' : str_replace('\\', '/', $dir);
    define('APP_URL', $protocol . $host . $dir);
}
define('APP_ENV',     'development');   // development | production

// Filesystem paths
define('BASE_PATH',    dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/storage/uploads');
define('PUBLIC_PATH',  BASE_PATH . '/public');

// Session
define('SESSION_NAME',     'appforge_session');
define('SESSION_LIFETIME', 7200);  // 2 hours

// File upload limits
define('UPLOAD_MAX_SIZE',       10 * 1024 * 1024);  // 10 MB
define('UPLOAD_ALLOWED_TYPES',  ['image/jpeg','image/png','image/gif','image/webp',
                                  'application/pdf','text/csv',
                                  'application/vnd.ms-excel',
                                  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);

// Pagination
define('DEFAULT_PAGE_SIZE', 25);

// CSRF
define('CSRF_TOKEN_NAME', '_csrf_token');
define('CSRF_EXPIRY',     3600);  // 1 hour

// Error display (turn off in production)
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

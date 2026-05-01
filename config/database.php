<?php
/**
 * Database Configuration & PDO Connection Factory
 *
 * Returns a singleton PDO instance configured for MySQL with:
 *  - UTF-8 charset
 *  - Exception error mode
 *  - Emulated prepares disabled (real prepared statements)
 *  - Fetch mode: associative arrays
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'no_code_app');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a shared PDO connection.
 * Throws PDOException on connection failure — caught by front controller.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // Use real prepared statements
            PDO::ATTR_STRINGIFY_FETCHES  => false,   // Keep native PHP types
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}

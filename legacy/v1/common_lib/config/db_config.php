<?php
/**
 * Database Configuration
 *
 * Database connection settings for the application.
 */

// Database credentials
// Database connection
if (! defined('DB_HOST')) {
    define('DB_HOST', env('DB_HOST', '127.0.0.1'));
}

if (! defined('DB_USER')) {
    define('DB_USER', env('DB_USERNAME', 'root'));
}

if (! defined('DB_PASS')) {
    define('DB_PASS', env('DB_PASSWORD', ''));
}

if (! defined('DB_NAME')) {
    define('DB_NAME', env('DB_DATABASE', 'tapsndr_db'));
}

// Other settings
if (! defined('SESSION_LIFETIME')) {
    define('SESSION_LIFETIME', 86400);
}

// Database charset and collation
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Database table prefix (optional)
define('DB_PREFIX', 'ts_');

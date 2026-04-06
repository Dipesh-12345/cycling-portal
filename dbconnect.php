<?php
/**
 * dbconnect.php — shared database configuration
 * Provides $servername, $username, $password, $database
 * for use with PDO connections throughout the app.
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);      // Set to 1 when using HTTPS
    ini_set('session.gc_maxlifetime', 1800);
    session_set_cookie_params(1800);
}

$servername = 'localhost';
$username   = 'root';
$password   = '';
$database   = 'cycling';

// Allow overriding via environment variables (useful for Docker/hosting)
$servername = getenv('DB_HOST') ?: $servername;
$username   = getenv('DB_USER') ?: $username;
$password   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : $password;
$database   = getenv('DB_NAME') ?: $database;

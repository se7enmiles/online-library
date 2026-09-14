<?php
// BookLoop — configuration and connection.
// Every page starts with: require 'config.php';

// ---------------------------------------------------------------- environment
// 'dev' while you are building, 'prod' on a real server.
// In dev PHP shows every error on screen; in prod it shows none and logs instead.
const ENVIRONMENT = 'dev';

if (ENVIRONMENT === 'dev') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

// ------------------------------------------------------------------- database
// MAMP defaults. On Windows/XAMPP use port 3306 and password ''.
$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'bookloop';
$dbUser = 'root';
$dbPass = 'root';

$dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // show errors, don't hide them
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows as ['title' => ...]
    ]);
} catch (PDOException $e) {
    // Never print a database message to a visitor: it can reveal usernames and paths.
    error_log('Database connection failed: ' . $e->getMessage());

    http_response_code(503);
    exit(ENVIRONMENT === 'dev'
        ? 'Database connection failed: ' . $e->getMessage()
        : 'BookLoop is temporarily unavailable. Please try again shortly.');
}

// --------------------------------------------------------------------- pieces
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/book-helpers.php';
require_once __DIR__ . '/includes/shelf-helpers.php';
require_once __DIR__ . '/includes/reservation-helpers.php';
require_once __DIR__ . '/includes/exchange-helpers.php';
require_once __DIR__ . '/includes/http.php';
require_once __DIR__ . '/includes/book-api.php';

// Your own API key, if you have one. Optional on purpose: the site works
// without it, only the AI features switch off.
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

require_once __DIR__ . '/includes/ai.php';

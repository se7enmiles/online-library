<?php
// BookLoop — database connection
// Every page that needs the database will do: require 'config.php';

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
    die('Database connection failed: ' . $e->getMessage());
}

// Sessions and login helpers (Session 2)
require_once __DIR__ . '/includes/auth.php';

// Book validation and ownership helpers (Session 3)
require_once __DIR__ . '/includes/book-helpers.php';

// Personal library helpers (Session 4)
require_once __DIR__ . '/includes/shelf-helpers.php';

// Reservations and borrowing (Session 5)
require_once __DIR__ . '/includes/reservation-helpers.php';

// Book exchange (Session 6)
require_once __DIR__ . '/includes/exchange-helpers.php';

// Talking to other services (Session 7)
require_once __DIR__ . '/includes/http.php';
require_once __DIR__ . '/includes/book-api.php';

// Your own API key, if you have one. The file is optional on purpose:
// the site works without it, only the AI features switch off.
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

require_once __DIR__ . '/includes/ai.php';

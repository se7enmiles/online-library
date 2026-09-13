<?php
// BookLoop — session + login helpers
// Loaded by config.php, so every page has these functions available.

// Start the PHP session once, and only if it isn't running already.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Is somebody logged in right now?
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * The logged-in user's row, or null for a guest.
 * The row is loaded from the database once per request.
 */
function currentUser(): ?array
{
    global $pdo;
    static $user = null;

    if (!isLoggedIn()) {
        return null;
    }
    if ($user === null) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;

        // The account was deleted while the session was still alive.
        if ($user === null) {
            session_destroy();
        }
    }
    return $user;
}

/**
 * Put this at the top of any page only logged-in users may see.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Store a one-off message, shown on the next page the user opens.
 */
function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Read the flash message and remove it, so it only appears once.
 */
function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

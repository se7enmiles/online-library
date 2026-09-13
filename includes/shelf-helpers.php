<?php
// BookLoop — helpers for the personal library ("shelves").
// Loaded by config.php.

// The three shelves, in reading order. Used for the buttons, the labels and validation.
const SHELVES = [
    'want'     => '❤️ Want to Read',
    'reading'  => '📖 Currently Reading',
    'finished' => '✅ Finished',
];

/**
 * Which shelf has the logged-in user put this book on? null = none.
 */
function shelfStatus(int $bookId): ?string
{
    global $pdo;

    if (!isLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT status FROM user_books WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$_SESSION['user_id'], $bookId]);

    $status = $stmt->fetchColumn();

    return $status !== false ? $status : null;
}

/**
 * Turn 'want' into '❤️ Want to Read'.
 */
function shelfLabel(?string $status): string
{
    return SHELVES[$status] ?? '';
}

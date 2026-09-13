<?php
require 'config.php';

// Changing a shelf changes data, so this page only accepts POST.
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$bookId = (int) ($_POST['book_id'] ?? 0);
$status = $_POST['status'] ?? '';
$userId = $_SESSION['user_id'];

// Where should we send the user back to? Default: the book's own page.
$back = $_POST['back'] ?? ('book.php?id=' . $bookId);

// 1. The book has to exist.
$stmt = $pdo->prepare('SELECT id FROM books WHERE id = ?');
$stmt->execute([$bookId]);

if (!$stmt->fetch()) {
    setFlash('That book does not exist.', 'warning');
    header('Location: index.php');
    exit;
}

// 2. "remove" takes the book off the shelf entirely.
if ($status === 'remove') {
    $stmt = $pdo->prepare('DELETE FROM user_books WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$userId, $bookId]);

    setFlash('Removed from your library.', 'info');
    header('Location: ' . $back);
    exit;
}

// 3. Anything else must be one of the three real shelves.
if (!array_key_exists($status, SHELVES)) {
    setFlash('Unknown shelf.', 'danger');
    header('Location: ' . $back);
    exit;
}

// 4. Insert, or move the book if it is already on a shelf.
//    ON DUPLICATE KEY UPDATE relies on the UNIQUE (user_id, book_id) index.
$stmt = $pdo->prepare(
    'INSERT INTO user_books (user_id, book_id, status)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE status = VALUES(status)'
);
$stmt->execute([$userId, $bookId, $status]);

setFlash('Moved to ' . SHELVES[$status] . '.');
header('Location: ' . $back);
exit;

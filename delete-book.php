<?php
require 'config.php';

requireLogin();

// A delete must never happen from a plain link — only from a submitted form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

// Load the book so we can check the owner and use the title in the message.
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('That book does not exist.', 'warning');
    header('Location: index.php');
    exit;
}

if ((int) $book['owner_id'] !== (int) $_SESSION['user_id']) {
    setFlash('You can only delete books you added yourself.', 'danger');
    header('Location: book.php?id=' . $id);
    exit;
}

// owner_id in the WHERE clause as well — a second lock on the same door.
$stmt = $pdo->prepare('DELETE FROM books WHERE id = ? AND owner_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);

setFlash('“' . $book['title'] . '” was deleted.', 'info');
header('Location: my-books.php');
exit;

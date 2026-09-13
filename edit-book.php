<?php
require 'config.php';

requireLogin();

// 1. Which book?
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('That book does not exist.', 'warning');
    header('Location: index.php');
    exit;
}

// 2. Is it yours? Nobody may edit somebody else's book.
if ((int) $book['owner_id'] !== (int) $_SESSION['user_id']) {
    setFlash('You can only edit books you added yourself.', 'danger');
    header('Location: book.php?id=' . $id);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Keep the id, replace the fields with what was submitted.
    $book = array_merge($book, [
        'title'       => trim($_POST['title'] ?? ''),
        'author'      => trim($_POST['author'] ?? ''),
        'genre'       => trim($_POST['genre'] ?? ''),
        'year'        => trim($_POST['year'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ]);

    $errors = validateBook($book);

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE books
                SET title = ?, author = ?, genre = ?, year = ?, description = ?
              WHERE id = ? AND owner_id = ?'
        );
        $stmt->execute([
            $book['title'],
            $book['author'],
            $book['genre'],
            $book['year'] !== '' ? (int) $book['year'] : null,
            $book['description'] !== '' ? $book['description'] : null,
            $id,
            $_SESSION['user_id'],
        ]);

        setFlash('“' . $book['title'] . '” was updated.');
        header('Location: book.php?id=' . $id);
        exit;
    }
}

$pageTitle = 'Edit ' . $book['title'];
require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="h3 mb-4">Edit book</h1>

        <?php require 'includes/book-form.php'; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

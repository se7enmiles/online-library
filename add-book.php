<?php
require 'config.php';

// Only logged-in users may add books.
requireLogin();

$errors = [];
$book = ['title' => '', 'author' => '', 'genre' => '', 'year' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Collect
    $book = [
        'title'       => trim($_POST['title'] ?? ''),
        'author'      => trim($_POST['author'] ?? ''),
        'genre'       => trim($_POST['genre'] ?? ''),
        'year'        => trim($_POST['year'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ];

    // 2. Validate
    $errors = validateBook($book);

    // 3. Save
    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO books (owner_id, title, author, genre, year, description)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $_SESSION['user_id'],
            $book['title'],
            $book['author'],
            $book['genre'],
            $book['year'] !== '' ? (int) $book['year'] : null,
            $book['description'] !== '' ? $book['description'] : null,
        ]);

        setFlash('“' . $book['title'] . '” was added to the catalog.');
        header('Location: book.php?id=' . $pdo->lastInsertId());
        exit;
    }
}

$pageTitle = 'Add a book';
require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="h3 mb-4">Add a book</h1>

        <?php require 'includes/book-form.php'; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

<?php
require 'config.php';

// Only logged-in users may add books.
requireLogin();

$errors = [];

// The fields start empty, or pre-filled from the online search
// (add-book.php?title=...&author=...).
$book = [
    'title'       => trim($_GET['title']  ?? ''),
    'author'      => trim($_GET['author'] ?? ''),
    'genre'       => trim($_GET['genre']  ?? ''),
    'year'        => trim($_GET['year']   ?? ''),
    'description' => '',
];

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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Add a book</h1>
            <a href="api-search.php" class="btn btn-sm btn-outline-primary">🔎 Find it online</a>
        </div>

        <?php require 'includes/book-form.php'; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

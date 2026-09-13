<?php
require 'config.php';

// 1. Read the id from the URL: book.php?id=3
$id = (int) ($_GET['id'] ?? 0);

// 2. Load that one book with a prepared statement (safe against SQL injection)
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch();

// 3. No book with that id? Show a friendly message and stop.
if (!$book) {
    $pageTitle = 'Book not found';
    require 'includes/header.php';
    echo '<div class="alert alert-warning">That book does not exist.</div>';
    echo '<a href="index.php" class="btn btn-secondary">Back to catalog</a>';
    require 'includes/footer.php';
    exit;
}

$pageTitle = $book['title'];
require 'includes/header.php';
?>

<a href="index.php" class="text-decoration-none">&larr; Back to catalog</a>

<div class="row mt-3 g-4">
    <div class="col-md-4">
        <div class="book-cover rounded" style="height:320px;font-size:5rem;">
            <?= htmlspecialchars(mb_substr($book['title'], 0, 1)) ?>
        </div>
    </div>
    <div class="col-md-8">
        <h1 class="h2"><?= htmlspecialchars($book['title']) ?></h1>
        <p class="lead text-muted mb-2">by <?= htmlspecialchars($book['author']) ?></p>
        <p>
            <span class="badge text-bg-secondary"><?= htmlspecialchars($book['genre']) ?></span>
            <?php if ($book['year']): ?>
                <span class="badge text-bg-light border"><?= $book['year'] ?></span>
            <?php endif; ?>
        </p>
        <p><?= nl2br(htmlspecialchars($book['description'] ?? '')) ?></p>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

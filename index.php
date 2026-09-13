<?php
require 'config.php';

$pageTitle = 'Books';

// 1. Ask the database for all books, newest first
$stmt  = $pdo->query('SELECT * FROM books ORDER BY created_at DESC');
$books = $stmt->fetchAll();

require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Book catalog</h1>
    <span class="text-muted"><?= count($books) ?> books</span>
</div>

<div class="row g-4">
    <?php foreach ($books as $book): ?>
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="card book-card h-100">
                <div class="book-cover">
                    <?= htmlspecialchars(mb_substr($book['title'], 0, 1)) ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                    <p class="card-text text-muted mb-2"><?= htmlspecialchars($book['author']) ?></p>
                    <span class="badge text-bg-secondary"><?= htmlspecialchars($book['genre']) ?></span>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="book.php?id=<?= $book['id'] ?>" class="btn btn-outline-primary btn-sm">View book</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require 'includes/footer.php'; ?>

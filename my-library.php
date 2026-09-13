<?php
require 'config.php';

requireLogin();

// One query for all three shelves. The JOIN turns book_id into the whole book.
$stmt = $pdo->prepare(
    'SELECT books.*, user_books.status, user_books.created_at AS shelved_at
       FROM user_books
       JOIN books ON books.id = user_books.book_id
      WHERE user_books.user_id = ?
      ORDER BY user_books.created_at DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$rows = $stmt->fetchAll();

// Sort the rows into three lists, one per shelf.
$shelves = ['want' => [], 'reading' => [], 'finished' => []];

foreach ($rows as $row) {
    $shelves[$row['status']][] = $row;
}

$pageTitle = 'My library';
require 'includes/header.php';
?>

<h1 class="h3 mb-4">My library</h1>

<?php if (!$rows): ?>

    <div class="text-center text-muted py-5">
        <p class="mb-3">Your library is empty.</p>
        <a href="index.php" class="btn btn-outline-primary">Browse the catalog</a>
    </div>

<?php else: ?>

    <ul class="nav nav-tabs mb-4" role="tablist">
        <?php $first = true; ?>
        <?php foreach (SHELVES as $value => $label): ?>
            <li class="nav-item">
                <button class="nav-link <?= $first ? 'active' : '' ?>" data-bs-toggle="tab"
                        data-bs-target="#tab-<?= $value ?>" type="button">
                    <?= $label ?>
                    <span class="badge text-bg-secondary ms-1"><?= count($shelves[$value]) ?></span>
                </button>
            </li>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content">
        <?php $first = true; ?>
        <?php foreach (SHELVES as $value => $label): ?>
            <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="tab-<?= $value ?>">

                <?php if (!$shelves[$value]): ?>
                    <p class="text-muted py-4">Nothing on this shelf yet.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($shelves[$value] as $book): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <a href="book.php?id=<?= $book['id'] ?>" class="fw-semibold text-decoration-none">
                                        <?= htmlspecialchars($book['title']) ?>
                                    </a>
                                    <div class="text-muted small"><?= htmlspecialchars($book['author']) ?></div>
                                </div>

                                <?php
                                $shelfBook = $book;
                                $shelfBack = 'my-library.php';
                                require 'includes/shelf-buttons.php';
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
            <?php $first = false; ?>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>

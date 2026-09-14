<?php
require 'config.php';

// 1. Read the id from the URL: book.php?id=3
$id = (int) ($_GET['id'] ?? 0);

// 2. Load the book. The JOIN brings the owner's name along with the book row.
$stmt = $pdo->prepare(
    'SELECT books.*, users.name AS owner_name
       FROM books
       LEFT JOIN users ON users.id = books.owner_id
      WHERE books.id = ?'
);
$stmt->execute([$id]);
$book = $stmt->fetch();

// 3. No book with that id? Show a friendly message and stop.
if (!$book) {
    http_response_code(404);
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

        <?php if ($book['for_exchange']): ?>
            <p class="mb-2">
                <span class="badge text-bg-info">🔄 Offered for exchange</span>
                <?php if (isLoggedIn() && !ownsBook($book) && canBeExchanged($book)): ?>
                    <a href="propose-exchange.php?book_id=<?= $book['id'] ?>" class="ms-2">Offer a swap</a>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php
        $resBook = $book;
        require 'includes/reservation-panel.php';
        ?>

        <?php if (isLoggedIn()): ?>
            <div class="my-3">
                <div class="text-muted small mb-1">My library</div>
                <?php
                $shelfBook = $book;
                require 'includes/shelf-buttons.php';
                ?>
            </div>
        <?php endif; ?>

        <?php if ($book['owner_name']): ?>
            <p class="text-muted small">Added by <?= htmlspecialchars($book['owner_name']) ?></p>
        <?php endif; ?>

        <?php if (ownsBook($book)): ?>
            <hr>
            <a href="edit-book.php?id=<?= $book['id'] ?>" class="btn btn-outline-secondary">Edit</a>

            <form method="post" action="delete-book.php" class="d-inline"
                  onsubmit="return confirm('Delete this book?');">
                <input type="hidden" name="id" value="<?= $book['id'] ?>">
                <button type="submit" class="btn btn-outline-danger">Delete</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

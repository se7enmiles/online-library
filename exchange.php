<?php
require 'config.php';

// Books other people are offering for exchange.
$sql = 'SELECT books.*, users.name AS owner_name
          FROM books
          JOIN users ON users.id = books.owner_id
         WHERE books.for_exchange = 1';

$params = [];

if (isLoggedIn()) {
    $sql .= ' AND books.owner_id <> ?';
    $params[] = $_SESSION['user_id'];
}

$sql .= ' ORDER BY books.title';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offers = $stmt->fetchAll();

$pageTitle = 'Exchange';
require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Books offered for exchange</h1>
    <?php if (isLoggedIn()): ?>
        <a href="my-exchanges.php" class="btn btn-outline-primary btn-sm">My exchanges</a>
    <?php endif; ?>
</div>

<?php if (!$offers): ?>

    <div class="alert alert-light border text-center py-4">
        <p class="mb-1">Nobody is offering a book for exchange right now.</p>
        <?php if (isLoggedIn()): ?>
            <a href="my-books.php">Offer one of yours</a>
        <?php endif; ?>
    </div>

<?php else: ?>

    <div class="row g-4">
        <?php foreach ($offers as $offer): ?>
            <?php $free = canBeExchanged($offer); ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($offer['title']) ?></h5>
                        <p class="card-text text-muted mb-2"><?= htmlspecialchars($offer['author']) ?></p>
                        <span class="badge text-bg-secondary"><?= htmlspecialchars($offer['genre']) ?></span>
                        <p class="text-muted small mt-2 mb-0">
                            Owned by <?= htmlspecialchars($offer['owner_name']) ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <?php if (!isLoggedIn()): ?>
                            <a href="login.php" class="btn btn-sm btn-outline-primary">Log in to offer a swap</a>
                        <?php elseif ($free): ?>
                            <a href="propose-exchange.php?book_id=<?= $offer['id'] ?>"
                               class="btn btn-sm btn-primary">Offer a swap</a>
                        <?php else: ?>
                            <span class="badge text-bg-light border">Currently unavailable</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>

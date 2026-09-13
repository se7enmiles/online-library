<?php
require 'config.php';

$pageTitle = 'Books';

// 1. Read the filters from the URL: index.php?q=hobbit&genre=Fantasy&sort=title
$q     = trim($_GET['q'] ?? '');
$genre = trim($_GET['genre'] ?? '');
$sort  = $_GET['sort'] ?? 'newest';

// 2. Build the WHERE clause piece by piece.
//    Each condition gets a placeholder, and its value goes into $params in the same order.
$where  = [];
$params = [];

if ($q !== '') {
    $where[]  = '(books.title LIKE ? OR books.author LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}

if ($genre !== '') {
    $where[]  = 'books.genre = ?';
    $params[] = $genre;
}

// 3. ORDER BY can never use a placeholder, so we pick from a fixed list instead.
$sortOptions = [
    'newest' => 'books.created_at DESC',
    'title'  => 'books.title ASC',
    'author' => 'books.author ASC',
    'year'   => 'books.year DESC',
];
$orderBy = $sortOptions[$sort] ?? $sortOptions['newest'];

// 4. Glue the query together.
$sql = 'SELECT books.*, users.name AS owner_name
          FROM books
          LEFT JOIN users ON users.id = books.owner_id';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY ' . $orderBy;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

// 5. The genre dropdown is built from the genres that actually exist.
$genres = $pdo->query('SELECT DISTINCT genre FROM books ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);

require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Book catalog</h1>
    <?php if (isLoggedIn()): ?>
        <a href="add-book.php" class="btn btn-primary btn-sm">Add a book</a>
    <?php endif; ?>
</div>

<!-- The search form uses GET, so the filters end up in the URL and can be shared -->
<form method="get" action="index.php" class="row g-2 mb-4">
    <div class="col-md-5">
        <input class="form-control" type="search" name="q" placeholder="Title or author…"
               value="<?= htmlspecialchars($q) ?>">
    </div>
    <div class="col-md-3">
        <select class="form-select" name="genre">
            <option value="">All genres</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?= htmlspecialchars($g) ?>" <?= $g === $genre ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select class="form-select" name="sort">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="title"  <?= $sort === 'title'  ? 'selected' : '' ?>>Title A–Z</option>
            <option value="author" <?= $sort === 'author' ? 'selected' : '' ?>>Author A–Z</option>
            <option value="year"   <?= $sort === 'year'   ? 'selected' : '' ?>>Newest first</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button class="btn btn-outline-primary" type="submit">Search</button>
    </div>
</form>

<p class="text-muted">
    <?= count($books) ?> <?= count($books) === 1 ? 'book' : 'books' ?>
    <?php if ($q !== '' || $genre !== ''): ?>
        found · <a href="index.php">clear filters</a>
    <?php endif; ?>
</p>

<?php if (!$books): ?>

    <div class="alert alert-light border text-center py-4">
        <p class="mb-1">Nothing matched your search.</p>
        <a href="index.php">Show all books</a>
    </div>

<?php else: ?>

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
                        <?php if ($book['year']): ?>
                            <span class="badge text-bg-light border"><?= $book['year'] ?></span>
                        <?php endif; ?>

                        <?php $shelf = shelfStatus((int) $book['id']); ?>
                        <?php if ($shelf): ?>
                            <div class="mt-2"><span class="badge text-bg-success"><?= shelfLabel($shelf) ?></span></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="book.php?id=<?= $book['id'] ?>" class="btn btn-outline-primary btn-sm">View book</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>

<?php
require 'config.php';

$pageTitle = 'Books';

// 1. Read the filters from the URL: index.php?q=hobbit&genre=Fantasy&sort=title
$q     = trim($_GET['q'] ?? '');
$genre = trim($_GET['genre'] ?? '');
$sort  = $_GET['sort'] ?? 'newest';

// 2. Build the WHERE clause piece by piece.
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

// 3. ORDER BY can never use a placeholder, so we pick from a fixed list.
$sortOptions = [
    'newest' => 'books.created_at DESC',
    'oldest' => 'books.created_at ASC',
    'title'  => 'books.title ASC',
    'author' => 'books.author ASC',
    'year'   => 'books.year DESC',
];
$orderBy = $sortOptions[$sort] ?? $sortOptions['newest'];

// 4. One query brings everything the cards need:
//    the book, its owner, the viewer's shelf, and any open reservation.
//    Before, each card ran two extra queries of its own.
$userId = isLoggedIn() ? (int) $_SESSION['user_id'] : 0;

$sql = "SELECT books.*,
               users.name          AS owner_name,
               user_books.status   AS shelf_status,
               reservations.status AS loan_status
          FROM books
          LEFT JOIN users ON users.id = books.owner_id
          LEFT JOIN user_books
                 ON user_books.book_id = books.id
                AND user_books.user_id = ?
          LEFT JOIN reservations
                 ON reservations.book_id = books.id
                AND reservations.status IN ('reserved', 'borrowed')";

$countParams = $params;
array_unshift($params, $userId);   // the JOIN's placeholder comes first

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

// 5. Pagination: show 9 books per page.
const PER_PAGE = 9;

$countSql = 'SELECT COUNT(*) FROM books' . ($where ? ' WHERE ' . implode(' AND ', $where) : '');
$stmt = $pdo->prepare($countSql);
$stmt->execute($countParams);
$total = (int) $stmt->fetchColumn();

$pages = max(1, (int) ceil($total / PER_PAGE));
$page  = min($pages, max(1, (int) ($_GET['page'] ?? 1)));

// LIMIT and OFFSET are numbers we control, so they are safe to write in directly.
$sql .= ' ORDER BY ' . $orderBy . ' LIMIT ' . PER_PAGE . ' OFFSET ' . (($page - 1) * PER_PAGE);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

$genres = $pdo->query('SELECT DISTINCT genre FROM books ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);

require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Book catalog</h1>
    <?php if (isLoggedIn()): ?>
        <a href="add-book.php" class="btn btn-primary btn-sm">Add a book</a>
    <?php endif; ?>
</div>

<form method="get" action="index.php" class="row g-2 mb-4">
    <div class="col-md-5">
        <label class="visually-hidden" for="q">Search</label>
        <input class="form-control" type="search" id="q" name="q" placeholder="Title or author…"
               value="<?= htmlspecialchars($q) ?>">
    </div>
    <div class="col-md-3">
        <label class="visually-hidden" for="genre">Genre</label>
        <select class="form-select" id="genre" name="genre">
            <option value="">All genres</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?= htmlspecialchars($g) ?>" <?= $g === $genre ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="visually-hidden" for="sort">Sort</label>
        <select class="form-select" id="sort" name="sort">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Oldest</option>
            <option value="title"  <?= $sort === 'title'  ? 'selected' : '' ?>>Title A–Z</option>
            <option value="author" <?= $sort === 'author' ? 'selected' : '' ?>>Author A–Z</option>
            <option value="year"   <?= $sort === 'year'   ? 'selected' : '' ?>>Publication year</option>
        </select>
    </div>
    <div class="col-md-2 d-grid">
        <button class="btn btn-outline-primary" type="submit">Search</button>
    </div>
</form>

<p class="text-muted">
    <?= $total ?> <?= $total === 1 ? 'book' : 'books' ?>
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
                        <h2 class="h6 card-title"><?= htmlspecialchars($book['title']) ?></h2>
                        <p class="card-text text-muted mb-2"><?= htmlspecialchars($book['author']) ?></p>

                        <span class="badge text-bg-secondary"><?= htmlspecialchars($book['genre']) ?></span>
                        <?php if ($book['year']): ?>
                            <span class="badge text-bg-light border"><?= $book['year'] ?></span>
                        <?php endif; ?>
                        <?= stateBadge($book['loan_status'] ?? 'available') ?>

                        <?php if ($book['shelf_status']): ?>
                            <div class="mt-2">
                                <span class="badge text-bg-success"><?= shelfLabel($book['shelf_status']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <a href="book.php?id=<?= $book['id'] ?>" class="btn btn-outline-primary btn-sm">
                            View book<span class="visually-hidden">: <?= htmlspecialchars($book['title']) ?></span>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
        <nav class="mt-5" aria-label="Catalog pages">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(
                            array_merge($_GET, ['page' => $p])
                        ) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>

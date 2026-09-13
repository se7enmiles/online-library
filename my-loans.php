<?php
require 'config.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

// 1. Books I have reserved or borrowed from other people.
$stmt = $pdo->prepare(
    "SELECT reservations.*, books.title, books.author, users.name AS owner_name
       FROM reservations
       JOIN books ON books.id = reservations.book_id
       LEFT JOIN users ON users.id = books.owner_id
      WHERE reservations.user_id = ?
        AND reservations.status IN ('reserved', 'borrowed')
      ORDER BY reservations.reserved_at DESC"
);
$stmt->execute([$userId]);
$borrowing = $stmt->fetchAll();

// 2. My books that somebody else has reserved or borrowed.
$stmt = $pdo->prepare(
    "SELECT reservations.*, books.title, books.author, users.name AS borrower_name
       FROM reservations
       JOIN books ON books.id = reservations.book_id
       JOIN users ON users.id = reservations.user_id
      WHERE books.owner_id = ?
        AND reservations.status IN ('reserved', 'borrowed')
      ORDER BY reservations.reserved_at DESC"
);
$stmt->execute([$userId]);
$lending = $stmt->fetchAll();

// 3. Everything that is already finished, both directions.
$stmt = $pdo->prepare(
    "SELECT reservations.*, books.title, users.name AS other_name,
            (reservations.user_id = ?) AS i_borrowed
       FROM reservations
       JOIN books ON books.id = reservations.book_id
       JOIN users ON users.id = reservations.user_id
      WHERE (reservations.user_id = ? OR books.owner_id = ?)
        AND reservations.status IN ('returned', 'cancelled')
      ORDER BY reservations.reserved_at DESC
      LIMIT 20"
);
$stmt->execute([$userId, $userId, $userId]);
$history = $stmt->fetchAll();

$pageTitle = 'Loans';
require 'includes/header.php';
?>

<h1 class="h3 mb-4">Loans</h1>

<div class="row g-4">

    <div class="col-lg-6">
        <h2 class="h5 mb-3">I'm borrowing</h2>

        <?php if (!$borrowing): ?>
            <p class="text-muted">You haven't reserved any books.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($borrowing as $row): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <a href="book.php?id=<?= $row['book_id'] ?>" class="fw-semibold text-decoration-none">
                                    <?= htmlspecialchars($row['title']) ?>
                                </a>
                                <div class="text-muted small">
                                    from <?= htmlspecialchars($row['owner_name'] ?? 'unknown') ?>
                                    <?php if ($row['status'] === 'borrowed'): ?>
                                        · due <?= date('j M Y', strtotime($row['due_date'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <?= stateBadge($row['status']) ?>
                                <?php if (isOverdue($row)): ?>
                                    <span class="badge text-bg-danger">Overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-2">
                            <?php if ($row['status'] === 'reserved'): ?>
                                <?= reservationButton('cancel', 'Cancel', 'outline-secondary', (int) $row['book_id'], 'my-loans.php') ?>
                            <?php else: ?>
                                <?= reservationButton('return', 'Give back', 'outline-success', (int) $row['book_id'], 'my-loans.php') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-6">
        <h2 class="h5 mb-3">I'm lending</h2>

        <?php if (!$lending): ?>
            <p class="text-muted">Nobody has reserved your books yet.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($lending as $row): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <a href="book.php?id=<?= $row['book_id'] ?>" class="fw-semibold text-decoration-none">
                                    <?= htmlspecialchars($row['title']) ?>
                                </a>
                                <div class="text-muted small">
                                    to <?= htmlspecialchars($row['borrower_name']) ?>
                                    <?php if ($row['status'] === 'borrowed'): ?>
                                        · due <?= date('j M Y', strtotime($row['due_date'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <?= stateBadge($row['status']) ?>
                                <?php if (isOverdue($row)): ?>
                                    <span class="badge text-bg-danger">Overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-2">
                            <?php if ($row['status'] === 'reserved'): ?>
                                <?= reservationButton('lend', 'Hand over', 'success', (int) $row['book_id'], 'my-loans.php') ?>
                                <?= reservationButton('cancel', 'Cancel', 'outline-secondary', (int) $row['book_id'], 'my-loans.php') ?>
                            <?php else: ?>
                                <?= reservationButton('return', 'Mark returned', 'outline-success', (int) $row['book_id'], 'my-loans.php') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php if ($history): ?>
    <h2 class="h5 mt-5 mb-3">History</h2>
    <table class="table table-sm">
        <thead>
            <tr><th>Book</th><th>Direction</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
            <?php foreach ($history as $row): ?>
                <tr>
                    <td><a href="book.php?id=<?= $row['book_id'] ?>"><?= htmlspecialchars($row['title']) ?></a></td>
                    <td class="text-muted"><?= $row['i_borrowed'] ? 'Borrowed' : 'Lent to ' . htmlspecialchars($row['other_name']) ?></td>
                    <td><span class="badge text-bg-light border"><?= $row['status'] ?></span></td>
                    <td class="text-muted"><?= date('j M Y', strtotime($row['returned_at'] ?? $row['reserved_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>

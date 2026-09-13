<?php
require 'config.php';

requireLogin();

// Only the books this user added.
$stmt = $pdo->prepare('SELECT * FROM books WHERE owner_id = ? ORDER BY created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$books = $stmt->fetchAll();

$pageTitle = 'My books';
require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My books</h1>
    <a href="add-book.php" class="btn btn-primary">Add a book</a>
</div>

<?php if (!$books): ?>

    <div class="text-center text-muted py-5">
        <p class="mb-3">You haven't added any books yet.</p>
        <a href="add-book.php" class="btn btn-outline-primary">Add your first book</a>
    </div>

<?php else: ?>

    <table class="table align-middle">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Genre</th>
                <th>Year</th>
                <th>Exchange</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><a href="book.php?id=<?= $book['id'] ?>"><?= htmlspecialchars($book['title']) ?></a></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['genre']) ?></td>
                    <td><?= $book['year'] ?: '—' ?></td>
                    <td>
                        <form method="post" action="exchange-action.php" class="d-inline">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                            <input type="hidden" name="back" value="my-books.php">
                            <button type="submit" class="btn btn-sm <?= $book['for_exchange'] ? 'btn-success' : 'btn-outline-secondary' ?>">
                                <?= $book['for_exchange'] ? '🔄 Offered' : 'Offer for exchange' ?>
                            </button>
                        </form>
                    </td>
                    <td class="text-end">
                        <a href="edit-book.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>

                        <form method="post" action="delete-book.php" class="d-inline"
                              onsubmit="return confirm('Delete this book?');">
                            <input type="hidden" name="id" value="<?= $book['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>

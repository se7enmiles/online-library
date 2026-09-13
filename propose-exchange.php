<?php
require 'config.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

// Which book does the user want? It comes from the URL on GET, from the form on POST.
$wantedId = (int) ($_POST['wanted_book_id'] ?? $_GET['book_id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT books.*, users.name AS owner_name
       FROM books JOIN users ON users.id = books.owner_id
      WHERE books.id = ?'
);
$stmt->execute([$wantedId]);
$wanted = $stmt->fetch();

// --- Checks that apply before anything is shown -----------------------------
if (!$wanted) {
    setFlash('That book does not exist.', 'warning');
    header('Location: exchange.php');
    exit;
}
if ((int) $wanted['owner_id'] === $userId) {
    setFlash('That book is already yours.', 'warning');
    header('Location: exchange.php');
    exit;
}
if (!canBeExchanged($wanted)) {
    setFlash('That book is not available for exchange at the moment.', 'warning');
    header('Location: exchange.php');
    exit;
}

// My books that could be offered in return.
$stmt = $pdo->prepare('SELECT * FROM books WHERE owner_id = ? ORDER BY title');
$stmt->execute([$userId]);
$myBooks = array_filter($stmt->fetchAll(), fn($book) => canBeExchanged($book));

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offeredId = (int) ($_POST['offered_book_id'] ?? 0);
    $message   = trim($_POST['message'] ?? '');

    // The offered book must be one of mine, and still exchangeable.
    $offered = null;
    foreach ($myBooks as $book) {
        if ((int) $book['id'] === $offeredId) {
            $offered = $book;
        }
    }

    if (!$offered) {
        $errors[] = 'Choose one of your own books to offer.';
    }
    if (mb_strlen($message) > 300) {
        $errors[] = 'The message is too long (300 characters maximum).';
    }

    // Don't let the same person ask twice for the same book.
    if (!$errors) {
        $stmt = $pdo->prepare(
            "SELECT id FROM exchange_requests
              WHERE from_user_id = ? AND wanted_book_id = ? AND status = 'pending'"
        );
        $stmt->execute([$userId, $wantedId]);

        if ($stmt->fetch()) {
            $errors[] = 'You already have a pending request for this book.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO exchange_requests
                (from_user_id, to_user_id, offered_book_id, wanted_book_id, message)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $wanted['owner_id'],
            $offeredId,
            $wantedId,
            $message !== '' ? $message : null,
        ]);

        setFlash('Your offer was sent to ' . $wanted['owner_name'] . '.');
        header('Location: my-exchanges.php');
        exit;
    }
}

$pageTitle = 'Offer a swap';
require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <h1 class="h3 mb-4">Offer a swap</h1>

        <div class="card mb-4">
            <div class="card-body">
                <div class="text-muted small">You want</div>
                <div class="fw-semibold"><?= htmlspecialchars($wanted['title']) ?></div>
                <div class="text-muted small">
                    <?= htmlspecialchars($wanted['author']) ?> ·
                    owned by <?= htmlspecialchars($wanted['owner_name']) ?>
                </div>
            </div>
        </div>

        <?php if (!$myBooks): ?>

            <div class="alert alert-warning">
                You have no books available to offer. Add a book and mark it
                <strong>for exchange</strong> on <a href="my-books.php">My books</a> first.
            </div>

        <?php else: ?>

            <?php if ($errors): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="wanted_book_id" value="<?= $wanted['id'] ?>">

                <div class="mb-3">
                    <label class="form-label" for="offered_book_id">You give</label>
                    <select class="form-select" id="offered_book_id" name="offered_book_id" required>
                        <option value="">Choose one of your books…</option>
                        <?php foreach ($myBooks as $book): ?>
                            <option value="<?= $book['id'] ?>">
                                <?= htmlspecialchars($book['title']) ?>
                                — <?= htmlspecialchars($book['author']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="message">Message <span class="text-muted">(optional)</span></label>
                    <textarea class="form-control" id="message" name="message" rows="3"
                              maxlength="300" placeholder="Where and when could you meet?"></textarea>
                </div>

                <button class="btn btn-primary" type="submit">Send the offer</button>
                <a href="exchange.php" class="btn btn-link">Cancel</a>
            </form>

        <?php endif; ?>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

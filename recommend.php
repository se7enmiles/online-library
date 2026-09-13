<?php
require 'config.php';

requireLogin();

$taste   = trim($_POST['taste'] ?? '');
$picks   = null;
$books   = [];
$error   = '';

// The catalog we let the AI choose from: every book except the user's own.
$stmt = $pdo->prepare('SELECT id, title, author, genre FROM books WHERE owner_id <> ? ORDER BY id');
$stmt->execute([$_SESSION['user_id']]);
$catalog = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!aiEnabled()) {
        $error = 'No AI key is configured, so recommendations are switched off.';
    } elseif ($taste === '') {
        $error = 'Tell the AI what you like first.';
    } elseif (!$catalog) {
        $error = 'There are no other people’s books in the catalog yet.';
    } else {
        $picks = aiRecommend($taste, $catalog);

        if ($picks === null) {
            $error = 'The AI did not answer properly. Try again.';
        } else {
            // Turn the ids the AI returned into real book rows.
            // Never trust the ids blindly — look each one up.
            foreach ($picks as $pick) {
                $stmt = $pdo->prepare(
                    'SELECT books.*, users.name AS owner_name
                       FROM books LEFT JOIN users ON users.id = books.owner_id
                      WHERE books.id = ?'
                );
                $stmt->execute([(int) ($pick['id'] ?? 0)]);
                $book = $stmt->fetch();

                if ($book) {
                    $book['reason'] = $pick['reason'] ?? '';
                    $books[] = $book;
                }
            }
        }
    }
}

$pageTitle = 'Recommendations';
require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <h1 class="h3 mb-3">✨ Books for you</h1>

        <?php if (!aiEnabled()): ?>
            <div class="alert alert-info">
                This page needs an AI key. Copy <code>config.local.example.php</code> to
                <code>config.local.php</code> and put your key in it.
            </div>
        <?php endif; ?>

        <p class="text-muted">
            Describe what you enjoy reading and the AI will pick from the
            <?= count($catalog) ?> books other members have added.
        </p>

        <form method="post" class="mb-4">
            <div class="mb-3">
                <textarea class="form-control" name="taste" rows="3" maxlength="400"
                          placeholder="I like adventure and fantasy, but nothing too long…"
                          <?= aiEnabled() ? '' : 'disabled' ?>><?= htmlspecialchars($taste) ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit" <?= aiEnabled() ? '' : 'disabled' ?>>
                Ask for recommendations
            </button>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($picks !== null && !$books && !$error): ?>
            <div class="alert alert-light border">
                The AI could not find a good match in the catalog. Try describing
                your taste differently.
            </div>
        <?php endif; ?>

        <?php foreach ($books as $book): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($book['title']) ?></h5>
                    <p class="text-muted small mb-2">
                        <?= htmlspecialchars($book['author']) ?>
                        · <?= htmlspecialchars($book['genre']) ?>
                        · added by <?= htmlspecialchars($book['owner_name'] ?? 'unknown') ?>
                    </p>
                    <p class="mb-3">✨ <?= htmlspecialchars($book['reason']) ?></p>
                    <a href="book.php?id=<?= $book['id'] ?>" class="btn btn-sm btn-outline-primary">
                        View book
                    </a>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>

<?php require 'includes/footer.php'; ?>

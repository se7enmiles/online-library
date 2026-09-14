<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — BookLoop' : 'BookLoop — Discover, Read, Share &amp; Exchange' ?></title>
    <meta name="description" content="BookLoop — discover books, keep a reading list, lend to friends and swap what you have finished.">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='14'>📚</text></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<?php
// Which page are we on? Used to highlight the current navbar link.
$current = basename($_SERVER['SCRIPT_NAME']);

/** Print class="nav-link active" when $page is the page we are on. */
function navLink(string $page, string $current): string
{
    return 'nav-link' . ($page === $current ? ' active" aria-current="page' : '');
}
?>

<a href="#main" class="visually-hidden-focusable btn btn-primary m-2">Skip to content</a>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">📚 BookLoop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#mainNav" aria-controls="mainNav"
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="<?= navLink('index.php', $current) ?>" href="index.php">Books</a></li>
                <li class="nav-item"><a class="<?= navLink('exchange.php', $current) ?>" href="exchange.php">Exchange</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="<?= navLink('my-library.php', $current) ?>" href="my-library.php">My library</a></li>
                    <li class="nav-item"><a class="<?= navLink('my-books.php', $current) ?>" href="my-books.php">My books</a></li>
                    <li class="nav-item"><a class="<?= navLink('my-loans.php', $current) ?>" href="my-loans.php">Loans</a></li>
                    <li class="nav-item"><a class="<?= navLink('my-exchanges.php', $current) ?>" href="my-exchanges.php">Swaps</a></li>
                    <li class="nav-item"><a class="<?= navLink('recommend.php', $current) ?>" href="recommend.php">✨ For you</a></li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="<?= navLink('account.php', $current) ?>" href="account.php">
                            👤 <?= htmlspecialchars(currentUser()['name']) ?>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Log out</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="<?= navLink('login.php', $current) ?>" href="login.php">Log in</a></li>
                    <li class="nav-item"><a class="<?= navLink('register.php', $current) ?>" href="register.php">Create account</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container" id="main">

<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

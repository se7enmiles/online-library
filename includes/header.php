<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? $pageTitle . ' — BookLoop' : 'BookLoop' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">📚 BookLoop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Books</a></li>
            </ul>

            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="account.php">
                            👤 <?= htmlspecialchars(currentUser()['name']) ?>
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Log out</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Log in</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">Create account</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container">

<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
require 'config.php';

// Guests are sent to the login page.
requireLogin();

$user = currentUser();

$pageTitle = 'My account';
require 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <h1 class="h3 mb-4">My account</h1>

        <div class="card">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($user['name']) ?></dd>

                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($user['email']) ?></dd>

                    <dt class="col-sm-4">Member since</dt>
                    <dd class="col-sm-8 mb-0"><?= date('j F Y', strtotime($user['created_at'])) ?></dd>
                </dl>
            </div>
        </div>

        <p class="text-muted mt-4">
            Your personal library, reservations and exchanges will appear here in the next sessions.
        </p>

        <a href="logout.php" class="btn btn-outline-secondary">Log out</a>
    </div>
</div>

<?php require 'includes/footer.php'; ?>

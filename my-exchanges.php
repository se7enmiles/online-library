<?php
require 'config.php';

requireLogin();

$userId = (int) $_SESSION['user_id'];

// Requests other people sent me, still open.
$stmt = $pdo->prepare(
    "SELECT exchange_requests.*,
            offered.title AS offered_title, wanted.title AS wanted_title,
            asker.name AS from_name, owner.name AS to_name
       FROM exchange_requests
       JOIN books offered ON offered.id = exchange_requests.offered_book_id
       JOIN books wanted  ON wanted.id  = exchange_requests.wanted_book_id
       JOIN users asker   ON asker.id   = exchange_requests.from_user_id
       JOIN users owner   ON owner.id   = exchange_requests.to_user_id
      WHERE exchange_requests.to_user_id = ?
        AND exchange_requests.status IN ('pending', 'accepted')
      ORDER BY exchange_requests.created_at DESC"
);
$stmt->execute([$userId]);
$incoming = $stmt->fetchAll();

// Requests I sent, still open.
$stmt = $pdo->prepare(
    "SELECT exchange_requests.*,
            offered.title AS offered_title, wanted.title AS wanted_title,
            asker.name AS from_name, owner.name AS to_name
       FROM exchange_requests
       JOIN books offered ON offered.id = exchange_requests.offered_book_id
       JOIN books wanted  ON wanted.id  = exchange_requests.wanted_book_id
       JOIN users asker   ON asker.id   = exchange_requests.from_user_id
       JOIN users owner   ON owner.id   = exchange_requests.to_user_id
      WHERE exchange_requests.from_user_id = ?
        AND exchange_requests.status IN ('pending', 'accepted')
      ORDER BY exchange_requests.created_at DESC"
);
$stmt->execute([$userId]);
$outgoing = $stmt->fetchAll();

// Everything finished, either direction.
$stmt = $pdo->prepare(
    "SELECT exchange_requests.*,
            offered.title AS offered_title, wanted.title AS wanted_title,
            asker.name AS from_name, owner.name AS to_name
       FROM exchange_requests
       JOIN books offered ON offered.id = exchange_requests.offered_book_id
       JOIN books wanted  ON wanted.id  = exchange_requests.wanted_book_id
       JOIN users asker   ON asker.id   = exchange_requests.from_user_id
       JOIN users owner   ON owner.id   = exchange_requests.to_user_id
      WHERE (exchange_requests.from_user_id = ? OR exchange_requests.to_user_id = ?)
        AND exchange_requests.status IN ('rejected', 'cancelled', 'completed')
      ORDER BY exchange_requests.created_at DESC
      LIMIT 20"
);
$stmt->execute([$userId, $userId]);
$history = $stmt->fetchAll();

$pageTitle = 'My exchanges';
require 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My exchanges</h1>
    <a href="exchange.php" class="btn btn-outline-primary btn-sm">Browse offers</a>
</div>

<div class="row g-4">

    <div class="col-lg-6">
        <h2 class="h5 mb-3">Offers to me</h2>

        <?php if (!$incoming): ?>
            <p class="text-muted">Nobody has offered you a swap yet.</p>
        <?php else: ?>
            <?php foreach ($incoming as $req): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-semibold"><?= htmlspecialchars($req['from_name']) ?> offers</span>
                            <?= exchangeBadge($req['status']) ?>
                        </div>

                        <div class="small">
                            <div class="text-muted">You give</div>
                            <div class="mb-2"><?= htmlspecialchars($req['wanted_title']) ?></div>
                            <div class="text-muted">You get</div>
                            <div><?= htmlspecialchars($req['offered_title']) ?></div>
                        </div>

                        <?php if ($req['message']): ?>
                            <p class="text-muted small fst-italic mt-2 mb-0">
                                “<?= htmlspecialchars($req['message']) ?>”
                            </p>
                        <?php endif; ?>

                        <div class="mt-3">
                            <?php if ($req['status'] === 'pending'): ?>
                                <?= exchangeButton('accept', 'Accept', 'success', (int) $req['id'], 'my-exchanges.php') ?>
                                <?= exchangeButton('reject', 'Reject', 'outline-danger', (int) $req['id'], 'my-exchanges.php') ?>
                            <?php else: ?>
                                <?= exchangeButton('complete', 'Books have been swapped', 'primary', (int) $req['id'], 'my-exchanges.php') ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-6">
        <h2 class="h5 mb-3">Offers I sent</h2>

        <?php if (!$outgoing): ?>
            <p class="text-muted">You haven't offered any swaps.</p>
        <?php else: ?>
            <?php foreach ($outgoing as $req): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="fw-semibold">To <?= htmlspecialchars($req['to_name']) ?></span>
                            <?= exchangeBadge($req['status']) ?>
                        </div>

                        <div class="small">
                            <div class="text-muted">You give</div>
                            <div class="mb-2"><?= htmlspecialchars($req['offered_title']) ?></div>
                            <div class="text-muted">You get</div>
                            <div><?= htmlspecialchars($req['wanted_title']) ?></div>
                        </div>

                        <div class="mt-3">
                            <?php if ($req['status'] === 'accepted'): ?>
                                <?= exchangeButton('complete', 'Books have been swapped', 'primary', (int) $req['id'], 'my-exchanges.php') ?>
                            <?php endif; ?>
                            <?= exchangeButton('cancel', 'Cancel', 'outline-secondary', (int) $req['id'], 'my-exchanges.php') ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php if ($history): ?>
    <h2 class="h5 mt-5 mb-3">History</h2>
    <table class="table table-sm align-middle">
        <thead>
            <tr><th>Between</th><th>Given</th><th>Received</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
            <?php foreach ($history as $req): ?>
                <?php $iAsked = (int) $req['from_user_id'] === $userId; ?>
                <tr>
                    <td class="text-muted"><?= htmlspecialchars($iAsked ? $req['to_name'] : $req['from_name']) ?></td>
                    <td><?= htmlspecialchars($iAsked ? $req['offered_title'] : $req['wanted_title']) ?></td>
                    <td><?= htmlspecialchars($iAsked ? $req['wanted_title'] : $req['offered_title']) ?></td>
                    <td><?= exchangeBadge($req['status']) ?></td>
                    <td class="text-muted"><?= date('j M Y', strtotime($req['completed_at'] ?? $req['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>

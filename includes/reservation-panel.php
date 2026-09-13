<?php
// The availability panel for one book.
// The page must set $resBook (the book row) before requiring this file.
// Optional: $resBack — where to return after an action.

$resState       = bookState((int) $resBook['id']);
$resReservation = openReservation((int) $resBook['id']);
$resIsOwner     = isLoggedIn() && (int) $resBook['owner_id'] === (int) $_SESSION['user_id'];
$resIsBorrower  = $resReservation && isLoggedIn()
                  && (int) $resReservation['user_id'] === (int) $_SESSION['user_id'];
$resBackTo      = $resBack ?? ('book.php?id=' . $resBook['id']);
?>

<div class="card mb-3">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold">Availability</span>
            <?= stateBadge($resState) ?>
        </div>

        <?php if ($resReservation): ?>
            <p class="text-muted small mb-3">
                <?php if ($resState === 'reserved'): ?>
                    Reserved by <?= htmlspecialchars($resReservation['borrower_name']) ?>
                    on <?= date('j M Y', strtotime($resReservation['reserved_at'])) ?>.
                <?php else: ?>
                    Borrowed by <?= htmlspecialchars($resReservation['borrower_name']) ?>,
                    due <?= date('j M Y', strtotime($resReservation['due_date'])) ?>.
                    <?php if (isOverdue($resReservation)): ?>
                        <span class="badge text-bg-danger">Overdue</span>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if (!isLoggedIn()): ?>

            <a href="login.php" class="btn btn-sm btn-outline-primary">Log in to reserve</a>

        <?php else: ?>

            <?php // Anyone but the owner may reserve a free book ?>
            <?php if ($resState === 'available' && !$resIsOwner): ?>
                <?= reservationButton('reserve', 'Reserve this book', 'primary', $resBook['id'], $resBackTo) ?>
            <?php endif; ?>

            <?php // The owner hands the book over ?>
            <?php if ($resState === 'reserved' && $resIsOwner): ?>
                <?= reservationButton('lend', 'Hand over to ' . $resReservation['borrower_name'], 'success', $resBook['id'], $resBackTo) ?>
            <?php endif; ?>

            <?php // Borrower or owner may cancel a reservation that hasn't been picked up ?>
            <?php if ($resState === 'reserved' && ($resIsBorrower || $resIsOwner)): ?>
                <?= reservationButton('cancel', 'Cancel reservation', 'outline-secondary', $resBook['id'], $resBackTo) ?>
            <?php endif; ?>

            <?php // Either side can close a loan ?>
            <?php if ($resState === 'borrowed' && ($resIsBorrower || $resIsOwner)): ?>
                <?= reservationButton('return', 'Mark as returned', 'outline-success', $resBook['id'], $resBackTo) ?>
            <?php endif; ?>

            <?php if ($resState === 'available' && $resIsOwner): ?>
                <p class="text-muted small mb-0">Your book is available for others to reserve.</p>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<?php unset($resBack); ?>

<?php
// Three shelf buttons for one book.
// The page must set $shelfBook (the book row) before requiring this file.
// Optional: $shelfBack — where to return after the change.

$current = shelfStatus((int) $shelfBook['id']);
$backTo  = $shelfBack ?? ('book.php?id=' . $shelfBook['id']);
?>

<div class="btn-group btn-group-sm" role="group">
    <?php foreach (SHELVES as $value => $label): ?>
        <form method="post" action="shelf.php" class="d-inline">
            <input type="hidden" name="book_id" value="<?= $shelfBook['id'] ?>">
            <input type="hidden" name="back" value="<?= htmlspecialchars($backTo) ?>">
            <input type="hidden" name="status" value="<?= $value ?>">
            <button type="submit"
                    class="btn btn-sm <?= $current === $value ? 'btn-success' : 'btn-outline-secondary' ?>">
                <?= $label ?>
            </button>
        </form>
    <?php endforeach; ?>

    <?php if ($current !== null): ?>
        <form method="post" action="shelf.php" class="d-inline">
            <input type="hidden" name="book_id" value="<?= $shelfBook['id'] ?>">
            <input type="hidden" name="back" value="<?= htmlspecialchars($backTo) ?>">
            <input type="hidden" name="status" value="remove">
            <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
        </form>
    <?php endif; ?>
</div>

<?php unset($shelfBack); ?>

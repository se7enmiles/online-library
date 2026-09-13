<?php
// BookLoop — reservations and borrowing.
// Loaded by config.php.

// How long a borrowed book may be kept.
const LOAN_DAYS = 14;

// A reservation is "open" while it is waiting to be picked up or is out on loan.
// 'returned' and 'cancelled' are finished — they no longer block the book.
const OPEN_STATUSES = ['reserved', 'borrowed'];

/**
 * The open reservation for a book, or null when the book is free.
 * Includes the borrower's name, so pages can show who has it.
 */
function openReservation(int $bookId): ?array
{
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT reservations.*, users.name AS borrower_name
           FROM reservations
           JOIN users ON users.id = reservations.user_id
          WHERE reservations.book_id = ?
            AND reservations.status IN ('reserved', 'borrowed')
          ORDER BY reservations.reserved_at DESC
          LIMIT 1"
    );
    $stmt->execute([$bookId]);

    return $stmt->fetch() ?: null;
}

/**
 * 'available', 'reserved' or 'borrowed' — the state of one book.
 */
function bookState(int $bookId): string
{
    $reservation = openReservation($bookId);

    return $reservation === null ? 'available' : $reservation['status'];
}

/**
 * A Bootstrap badge for a state.
 */
function stateBadge(string $state): string
{
    $map = [
        'available' => ['success', 'Available'],
        'reserved'  => ['warning', 'Reserved'],
        'borrowed'  => ['secondary', 'Borrowed'],
    ];

    [$colour, $label] = $map[$state] ?? ['light', $state];

    return '<span class="badge text-bg-' . $colour . '">' . $label . '</span>';
}

/**
 * Is this loan past its due date?
 */
function isOverdue(array $reservation): bool
{
    return $reservation['status'] === 'borrowed'
        && $reservation['due_date'] !== null
        && $reservation['due_date'] < date('Y-m-d');
}

/**
 * One POST button for a reservation action.
 */
function reservationButton(string $action, string $label, string $style, int $bookId, string $back): string
{
    return '<form method="post" action="reserve.php" class="d-inline">'
         . '<input type="hidden" name="action" value="' . $action . '">'
         . '<input type="hidden" name="book_id" value="' . $bookId . '">'
         . '<input type="hidden" name="back" value="' . htmlspecialchars($back) . '">'
         . '<button type="submit" class="btn btn-sm btn-' . $style . '">'
         . htmlspecialchars($label) . '</button></form> ';
}

<?php
// BookLoop — book exchange.
// Loaded by config.php.

// A request is "open" while it is still going somewhere.
const OPEN_EXCHANGE = ['pending', 'accepted'];

/**
 * Can this book take part in an exchange right now?
 * It has to be offered for exchange, not out on loan, and not already promised.
 *
 * Note: several PENDING requests for the same book are fine — they compete,
 * and the owner picks one. Only an ACCEPTED request takes the book off the table.
 */
function canBeExchanged(array $book): bool
{
    return (int) $book['for_exchange'] === 1
        && bookState((int) $book['id']) === 'available'
        && !isPromised((int) $book['id']);
}

/**
 * Is this book already promised in an accepted exchange?
 */
function isPromised(int $bookId): bool
{
    global $pdo;

    $stmt = $pdo->prepare(
        "SELECT id FROM exchange_requests
          WHERE (offered_book_id = ? OR wanted_book_id = ?)
            AND status = 'accepted'
          LIMIT 1"
    );
    $stmt->execute([$bookId, $bookId]);

    return (bool) $stmt->fetch();
}

/**
 * Load one request with both book titles and both user names attached.
 */
function loadExchange(int $id): ?array
{
    global $pdo;

    $stmt = $pdo->prepare(
        'SELECT exchange_requests.*,
                offered.title AS offered_title,
                wanted.title  AS wanted_title,
                asker.name    AS from_name,
                owner.name    AS to_name
           FROM exchange_requests
           JOIN books offered ON offered.id = exchange_requests.offered_book_id
           JOIN books wanted  ON wanted.id  = exchange_requests.wanted_book_id
           JOIN users asker   ON asker.id   = exchange_requests.from_user_id
           JOIN users owner   ON owner.id   = exchange_requests.to_user_id
          WHERE exchange_requests.id = ?'
    );
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

/**
 * A Bootstrap badge for an exchange status.
 */
function exchangeBadge(string $status): string
{
    $map = [
        'pending'   => ['warning', 'Pending'],
        'accepted'  => ['info', 'Accepted'],
        'completed' => ['success', 'Completed'],
        'rejected'  => ['danger', 'Rejected'],
        'cancelled' => ['secondary', 'Cancelled'],
    ];

    [$colour, $label] = $map[$status] ?? ['light', $status];

    return '<span class="badge text-bg-' . $colour . '">' . $label . '</span>';
}

/**
 * One POST button for an exchange action.
 */
function exchangeButton(string $action, string $label, string $style, int $requestId, string $back): string
{
    return '<form method="post" action="exchange-action.php" class="d-inline">'
         . '<input type="hidden" name="action" value="' . $action . '">'
         . '<input type="hidden" name="request_id" value="' . $requestId . '">'
         . '<input type="hidden" name="back" value="' . htmlspecialchars($back) . '">'
         . '<button type="submit" class="btn btn-sm btn-' . $style . '">'
         . htmlspecialchars($label) . '</button></form> ';
}

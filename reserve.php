<?php
require 'config.php';

// Every action here changes data.
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$bookId = (int) ($_POST['book_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];
$back   = $_POST['back'] ?? ('book.php?id=' . $bookId);

// The book has to exist.
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('That book does not exist.', 'warning');
    header('Location: index.php');
    exit;
}

$reservation = openReservation($bookId);
$isOwner     = (int) $book['owner_id'] === $userId;

switch ($action) {

    // ---------------------------------------------------------------- reserve
    case 'reserve':
        if ($isOwner) {
            setFlash('You cannot reserve your own book.', 'danger');
            break;
        }
        if ($reservation !== null) {
            setFlash('Somebody got there first — this book is not available.', 'warning');
            break;
        }

        $stmt = $pdo->prepare('INSERT INTO reservations (book_id, user_id) VALUES (?, ?)');
        $stmt->execute([$bookId, $userId]);

        setFlash('You reserved “' . $book['title'] . '”. The owner will hand it over.');
        break;

    // ----------------------------------------------------------------- cancel
    case 'cancel':
        if ($reservation === null || $reservation['status'] !== 'reserved') {
            setFlash('There is nothing to cancel.', 'warning');
            break;
        }
        // The person who reserved it may cancel; so may the owner.
        if ((int) $reservation['user_id'] !== $userId && !$isOwner) {
            setFlash('That reservation is not yours.', 'danger');
            break;
        }

        $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$reservation['id']]);

        setFlash('The reservation was cancelled.', 'info');
        break;

    // ------------------------------------------------------------------- lend
    case 'lend':
        if (!$isOwner) {
            setFlash('Only the owner can hand over a book.', 'danger');
            break;
        }
        if ($reservation === null || $reservation['status'] !== 'reserved') {
            setFlash('This book has no reservation waiting.', 'warning');
            break;
        }

        $stmt = $pdo->prepare(
            "UPDATE reservations
                SET status = 'borrowed', borrowed_at = NOW(), due_date = ?
              WHERE id = ? AND status = 'reserved'"
        );
        $stmt->execute([date('Y-m-d', strtotime('+' . LOAN_DAYS . ' days')), $reservation['id']]);

        setFlash('Handed over to ' . $reservation['borrower_name']
                 . '. Due back in ' . LOAN_DAYS . ' days.');
        break;

    // ----------------------------------------------------------------- return
    case 'return':
        if ($reservation === null || $reservation['status'] !== 'borrowed') {
            setFlash('This book is not out on loan.', 'warning');
            break;
        }
        // Either side can close a loan: the borrower gives it back, the owner receives it.
        if ((int) $reservation['user_id'] !== $userId && !$isOwner) {
            setFlash('That loan is not yours.', 'danger');
            break;
        }

        $stmt = $pdo->prepare(
            "UPDATE reservations
                SET status = 'returned', returned_at = NOW()
              WHERE id = ? AND status = 'borrowed'"
        );
        $stmt->execute([$reservation['id']]);

        setFlash('“' . $book['title'] . '” is back on the shelf. It is available again.');
        break;

    default:
        setFlash('Unknown action.', 'danger');
}

header('Location: ' . $back);
exit;

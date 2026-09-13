<?php
require 'config.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: exchange.php');
    exit;
}

$action = $_POST['action'] ?? '';
$userId = (int) $_SESSION['user_id'];
$back   = $_POST['back'] ?? 'my-exchanges.php';

// ------------------------------------------------- toggle a book for exchange
if ($action === 'toggle') {
    $bookId = (int) ($_POST['book_id'] ?? 0);

    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();

    if (!$book || (int) $book['owner_id'] !== $userId) {
        setFlash('That is not your book.', 'danger');
        header('Location: ' . $back);
        exit;
    }

    if ($book['for_exchange'] && isPromised($bookId)) {
        setFlash('This book is promised in an accepted exchange.', 'warning');
        header('Location: ' . $back);
        exit;
    }

    $new = $book['for_exchange'] ? 0 : 1;

    $stmt = $pdo->prepare('UPDATE books SET for_exchange = ? WHERE id = ? AND owner_id = ?');
    $stmt->execute([$new, $bookId, $userId]);

    setFlash($new
        ? '“' . $book['title'] . '” is now offered for exchange.'
        : '“' . $book['title'] . '” is no longer offered for exchange.', 'info');

    header('Location: ' . $back);
    exit;
}

// ------------------------------------------- everything else acts on a request
$request = loadExchange((int) ($_POST['request_id'] ?? 0));

if (!$request) {
    setFlash('That request does not exist.', 'warning');
    header('Location: my-exchanges.php');
    exit;
}

$isAsker = (int) $request['from_user_id'] === $userId;
$isOwner = (int) $request['to_user_id']   === $userId;

if (!$isAsker && !$isOwner) {
    setFlash('That request is not yours.', 'danger');
    header('Location: my-exchanges.php');
    exit;
}

switch ($action) {

    // ----------------------------------------------------------------- accept
    case 'accept':
        if (!$isOwner) {
            setFlash('Only the owner of the wanted book can accept.', 'danger');
            break;
        }
        if ($request['status'] !== 'pending') {
            setFlash('This request is no longer pending.', 'warning');
            break;
        }

        $stmt = $pdo->prepare("UPDATE exchange_requests SET status = 'accepted'
                                WHERE id = ? AND status = 'pending'");
        $stmt->execute([$request['id']]);

        // Both books are now promised — turn down every other open request for them.
        $stmt = $pdo->prepare(
            "UPDATE exchange_requests
                SET status = 'rejected'
              WHERE id <> ?
                AND status = 'pending'
                AND (offered_book_id IN (?, ?) OR wanted_book_id IN (?, ?))"
        );
        $stmt->execute([
            $request['id'],
            $request['offered_book_id'], $request['wanted_book_id'],
            $request['offered_book_id'], $request['wanted_book_id'],
        ]);

        setFlash('Accepted. Meet ' . $request['from_name']
                 . ' to swap the books, then mark the exchange complete.');
        break;

    // ----------------------------------------------------------------- reject
    case 'reject':
        if (!$isOwner) {
            setFlash('Only the owner of the wanted book can reject.', 'danger');
            break;
        }
        if ($request['status'] !== 'pending') {
            setFlash('This request is no longer pending.', 'warning');
            break;
        }

        $stmt = $pdo->prepare("UPDATE exchange_requests SET status = 'rejected'
                                WHERE id = ? AND status = 'pending'");
        $stmt->execute([$request['id']]);

        setFlash('Request rejected.', 'info');
        break;

    // ----------------------------------------------------------------- cancel
    case 'cancel':
        if (!$isAsker) {
            setFlash('Only the person who asked can cancel.', 'danger');
            break;
        }
        if (!in_array($request['status'], OPEN_EXCHANGE, true)) {
            setFlash('This request is already closed.', 'warning');
            break;
        }

        $stmt = $pdo->prepare("UPDATE exchange_requests SET status = 'cancelled'
                                WHERE id = ? AND status IN ('pending', 'accepted')");
        $stmt->execute([$request['id']]);

        setFlash('Request cancelled.', 'info');
        break;

    // --------------------------------------------------------------- complete
    case 'complete':
        if ($request['status'] !== 'accepted') {
            setFlash('Only an accepted exchange can be completed.', 'warning');
            break;
        }

        // The two books change hands. Both updates must happen, or neither.
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('UPDATE books SET owner_id = ?, for_exchange = 0 WHERE id = ?');
            $stmt->execute([$request['to_user_id'],   $request['offered_book_id']]);
            $stmt->execute([$request['from_user_id'], $request['wanted_book_id']]);

            $stmt = $pdo->prepare("UPDATE exchange_requests
                                      SET status = 'completed', completed_at = NOW()
                                    WHERE id = ? AND status = 'accepted'");
            $stmt->execute([$request['id']]);

            $pdo->commit();

            setFlash('Exchange complete. “' . $request['wanted_title'] . '” and “'
                     . $request['offered_title'] . '” have new owners.');
        } catch (PDOException $e) {
            $pdo->rollBack();
            setFlash('Something went wrong — nothing was changed.', 'danger');
        }
        break;

    default:
        setFlash('Unknown action.', 'danger');
}

header('Location: ' . $back);
exit;

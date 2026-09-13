<?php
// BookLoop — helpers for working with books.
// Loaded by config.php, so every page can use these.

/**
 * Check one book's fields. Returns an array of messages — empty means valid.
 * Used by add-book.php and edit-book.php, so the rules can never drift apart.
 */
function validateBook(array $book): array
{
    $errors = [];

    if ($book['title'] === '') {
        $errors[] = 'Please enter a title.';
    } elseif (mb_strlen($book['title']) > 200) {
        $errors[] = 'The title is too long (200 characters maximum).';
    }

    if ($book['author'] === '') {
        $errors[] = 'Please enter an author.';
    }

    if ($book['genre'] === '') {
        $errors[] = 'Please enter a genre.';
    }

    // The year is optional, but if it's there it has to make sense.
    if ($book['year'] !== '') {
        if (!ctype_digit((string) $book['year'])) {
            $errors[] = 'The year must be a number.';
        } elseif ($book['year'] < 1450 || $book['year'] > (int) date('Y')) {
            $errors[] = 'The year must be between 1450 and ' . date('Y') . '.';
        }
    }

    return $errors;
}

/**
 * Did the logged-in user add this book?
 */
function ownsBook(array $book): bool
{
    return isLoggedIn() && (int) $book['owner_id'] === (int) $_SESSION['user_id'];
}

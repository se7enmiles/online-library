# BookLoop — Session 3 reference code

Finished state of the project at the end of Session 3 (Book CRUD).

## Run it

1. Copy the `bookloop` folder into your MAMP `htdocs` folder.
2. In phpMyAdmin run the SQL files in order:
   `database.sql` (Session 1) → `database-session-02.sql` (users) →
   `database-session-03.sql` (owner_id).
3. Open http://localhost:8888/bookloop/ and log in.

If your account is not user id 1, change the number in the UPDATE at the
bottom of `database-session-03.sql` before running it.

## New in this session

| File | Purpose |
|------|---------|
| `database-session-03.sql` | Adds `owner_id` to `books` and assigns the sample books |
| `includes/book-helpers.php` | `validateBook()`, `ownsBook()` |
| `includes/book-form.php` | The add/edit form, shared by both pages |
| `add-book.php` | INSERT, owner taken from the session |
| `edit-book.php` | Loads, guards, UPDATE with `WHERE id = ? AND owner_id = ?` |
| `delete-book.php` | POST-only, four guards, DELETE |
| `my-books.php` | The user's own books, with an empty state |

Changed: `config.php` loads the book helpers; `includes/header.php` has the new
navbar links; `index.php` has an "Add a book" button; `book.php` JOINs the owner
and shows Edit/Delete to the owner only.

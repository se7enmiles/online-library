# BookLoop — Session 4 reference code

Finished state of the project at the end of Session 4 (Search & My Library).

## Run it

1. Copy the `bookloop` folder into your MAMP `htdocs` folder.
2. In phpMyAdmin run the SQL files in order:
   `database.sql` → `database-session-02.sql` → `database-session-03.sql` →
   `database-session-04.sql`.
3. Open http://localhost:8888/bookloop/

## New in this session

| File | Purpose |
|------|---------|
| `database-session-04.sql` | The `user_books` join table, with a composite unique key |
| `includes/shelf-helpers.php` | `SHELVES` constant, `shelfStatus()`, `shelfLabel()` |
| `includes/shelf-buttons.php` | The three shelf buttons + Remove, reused on three pages |
| `shelf.php` | POST-only: add, move or remove a book from a shelf |
| `my-library.php` | The three shelves as Bootstrap tabs |

Changed: `index.php` now searches, filters and sorts from GET parameters and
shows a shelf badge per card; `book.php` shows the shelf buttons to logged-in
users; `includes/header.php` has a "My library" link; `config.php` loads the
shelf helpers.

## Search notes

- The search term is matched with `LIKE '%term%'` against title and author.
- `ORDER BY` uses an allow-list (`$sortOptions`), never user input directly.
- Moving a book between shelves uses `ON DUPLICATE KEY UPDATE`, which relies on
  the `UNIQUE (user_id, book_id)` index.

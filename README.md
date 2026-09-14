# BookLoop — final project (end of Session 8)

The finished workshop project: a book library where people keep reading lists,
lend books to each other and swap the ones they have finished.

## Run it

1. Copy the `bookloop` folder into your MAMP `htdocs` folder.
2. In phpMyAdmin run the SQL files **in order**:
   `database.sql` → `database-session-02.sql` → `database-session-03.sql` →
   `database-session-04.sql` → `database-session-05.sql` → `database-session-06.sql`.
3. Open http://localhost:8888/bookloop/ and create an account.

Optional: copy `config.local.example.php` to `config.local.php` and add an AI key
to switch on the ✨ features. Without a key everything else works normally.

## Documents

| File | What it is |
|------|------------|
| `TESTING.md` | ~45 checks to work through before presenting. Use two accounts |
| `DEPLOYMENT.md` | Putting the project on a real host |

## New in this session

| Change | Where |
|--------|-------|
| One query per catalog page instead of 19 | `index.php` (two extra LEFT JOINs) |
| Pagination, 9 books per page | `index.php` |
| `dev` / `prod` error handling | `config.php` (`ENVIRONMENT`) |
| Custom 404 page + correct status codes | `not-found.php`, `.htaccess`, `book.php` |
| Active navbar link, page titles, favicon | `includes/header.php` |
| Skip link, form labels, aria attributes | `includes/header.php`, `index.php` |
| Long titles can't break a card | `assets/style.css` |
| Browser validation switched back on | `register.php`, `includes/book-form.php` |

## The stack

HTML5, CSS3, Bootstrap 5, a little JavaScript (`fetch`), PHP 8 with PDO,
MySQL, the Open Library API and an AI model. Development on MAMP.

## Structure

```
bookloop/
├── assets/style.css
├── includes/
│   ├── header.php, footer.php        page chrome
│   ├── auth.php                      sessions, login helpers, flash messages
│   ├── book-helpers.php              validation, ownership
│   ├── book-form.php                 shared add/edit form (+ AI button)
│   ├── shelf-helpers.php             SHELVES, shelf state
│   ├── shelf-buttons.php             the three shelf buttons
│   ├── reservation-helpers.php       loan state, badges, buttons
│   ├── reservation-panel.php         availability card
│   ├── exchange-helpers.php          exchange rules, badges, buttons
│   ├── http.php                      cURL wrappers
│   ├── book-api.php                  Open Library
│   └── ai.php                        AI prompts and parsing
├── config.php                        environment, PDO, includes
├── config.local.php                  your API key (never shared)
└── *.php                             the pages
```

## Database

| Table | Holds |
|-------|-------|
| `users` | accounts, with hashed passwords |
| `books` | one row per physical book, with an owner |
| `user_books` | which user put which book on which shelf |
| `reservations` | one row per loan, with its whole history |
| `exchange_requests` | one row per swap offer, four foreign keys |

## Conventions used throughout

- Every query with input uses a prepared statement.
- Every action that changes data is a POST, never a link.
- Permission and state checks appear twice: in PHP for the message, and in the
  SQL `WHERE` clause as a second lock.
- A book's availability is derived from `reservations`, never stored on `books`.
- Writes that only make sense together are wrapped in a transaction.
- Anything that can fail (an API, an AI model) degrades instead of breaking.

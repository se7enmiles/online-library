# BookLoop — Session 5 reference code

Finished state of the project at the end of Session 5 (Reservations & borrowing).

## Run it

1. Copy the `bookloop` folder into your MAMP `htdocs` folder.
2. In phpMyAdmin run the SQL files in order:
   `database.sql` → `database-session-02.sql` → `database-session-03.sql` →
   `database-session-04.sql` → `database-session-05.sql`.
3. Open http://localhost:8888/bookloop/

## New in this session

| File | Purpose |
|------|---------|
| `database-session-05.sql` | The `reservations` table |
| `includes/reservation-helpers.php` | `openReservation()`, `bookState()`, `stateBadge()`, `isOverdue()`, `reservationButton()` |
| `includes/reservation-panel.php` | The availability card shown on the book page |
| `reserve.php` | POST-only: reserve, cancel, lend, return |
| `my-loans.php` | Borrowing, lending and history in one page |

Changed: `book.php` shows the availability panel; `index.php` shows a state badge
per card; `includes/header.php` has a "Loans" link; `config.php` loads the
reservation helpers.

## The rules

| Action | Who | Only when | Effect |
|--------|-----|-----------|--------|
| reserve | anyone except the owner | available | new row, `reserved` |
| cancel | the reserver or the owner | reserved | → `cancelled` |
| lend | the owner only | reserved | → `borrowed`, due date = today + `LOAN_DAYS` |
| return | the borrower or the owner | borrowed | → `returned` |

A book's state is derived, never stored: if a row exists with status `reserved`
or `borrowed`, the book is taken; otherwise it is available. Rows are never
deleted — cancelling and returning only change the status, so the history stays.

The loan length is the `LOAN_DAYS` constant in `includes/reservation-helpers.php`.

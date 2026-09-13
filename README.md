# BookLoop — Session 6 reference code

Finished state of the project at the end of Session 6 (Book exchange).

## Run it

1. Copy the `bookloop` folder into your MAMP `htdocs` folder.
2. In phpMyAdmin run the SQL files in order:
   `database.sql` → `database-session-02.sql` → `database-session-03.sql` →
   `database-session-04.sql` → `database-session-05.sql` → `database-session-06.sql`.
3. Open http://localhost:8888/bookloop/

## New in this session

| File | Purpose |
|------|---------|
| `database-session-06.sql` | `books.for_exchange` flag + the `exchange_requests` table |
| `includes/exchange-helpers.php` | `canBeExchanged()`, `isPromised()`, `loadExchange()`, `exchangeBadge()`, `exchangeButton()` |
| `exchange.php` | Browse books other people are offering |
| `propose-exchange.php` | Choose what to offer in return, with a message |
| `exchange-action.php` | POST-only: toggle, accept, reject, cancel, complete |
| `my-exchanges.php` | Offers to me, offers I sent, and history |

Changed: `my-books.php` has an exchange toggle per row; `book.php` shows the
exchange badge and a propose link; `includes/header.php` has "Exchange" and
"Swaps" links; `config.php` loads the exchange helpers.

## The rules

| Action | Who | Only when | Effect |
|--------|-----|-----------|--------|
| toggle | the book's owner | not promised in an accepted deal | `for_exchange` on/off |
| propose | anyone but the owner | the wanted book passes `canBeExchanged()` | new row, `pending` |
| accept | owner of the wanted book | pending | → `accepted`, competing pending requests → `rejected` |
| reject | owner of the wanted book | pending | → `rejected` |
| cancel | the asker | pending or accepted | → `cancelled` |
| complete | either party | accepted | → `completed`, **both books change `owner_id`** |

Several **pending** requests for one book are allowed on purpose — they compete,
and the owner picks one. Only an **accepted** request takes a book off the table.

A book cannot be exchanged while it is reserved or out on loan: `canBeExchanged()`
calls `bookState()` from Session 5.

## The transaction

`complete` changes three rows (two books, one request). They are wrapped in
`beginTransaction()` / `commit()` with a `rollBack()` in the catch, so an
interrupted swap can never leave one book transferred and the other not.

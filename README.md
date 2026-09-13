# BookLoop — Session 2 reference code

Finished state of the project at the end of Session 2 (Users & authentication).

## Run it

1. Copy the `bookloop` folder into your MAMP `htdocs` folder.
2. In phpMyAdmin run `database.sql` first (Session 1: database + books),
   then `database-session-02.sql` (the `users` table).
3. Open http://localhost:8888/bookloop/ and create an account.

## New in this session

| File | Purpose |
|------|---------|
| `database-session-02.sql` | The `users` table, with a unique email |
| `includes/auth.php` | `session_start()`, `isLoggedIn()`, `currentUser()`, `requireLogin()`, flash messages |
| `register.php` | Registration form, validation, `password_hash()` |
| `login.php` | Login form, `password_verify()`, `session_regenerate_id()` |
| `logout.php` | Clears session data, cookie and session file |
| `account.php` | Private page — uses `requireLogin()` |

Changed: `config.php` now requires `includes/auth.php`;
`includes/header.php` shows the user's name or the login links, and prints flash messages.

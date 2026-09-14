# Putting BookLoop on a real server

The project runs on any ordinary PHP + MySQL host. Roughly:

1. **Upload the files** with FTP/SFTP or the host's file manager.
2. **Create a database** in the host's control panel. You will be given a
   database name, a user and a password — they will *not* be `root`/`root`.
3. **Import the SQL files** in order through the host's phpMyAdmin:
   `database.sql` → `database-session-02.sql` → … → `database-session-06.sql`.
4. **Edit `config.php`**: the host's database name, user, password, and port
   `3306` (not MAMP's 8889).
5. **Set `ENVIRONMENT` to `'prod'`** so visitors never see PHP errors.
6. **Upload `config.local.php` separately** if you use the AI features, and make
   sure it is not readable over the web.

## Before you go live

- Passwords are hashed — check the `users` table, no plain text anywhere.
- `ENVIRONMENT` is `'prod'` and no error is printed on a broken page.
- The `.sql` files and `config.local.php` are not downloadable
  (the included `.htaccess` denies them on Apache).
- Every destructive action is a POST, never a link.
- Every query that uses input goes through a prepared statement.

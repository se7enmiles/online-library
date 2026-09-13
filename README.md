# BookLoop — Session 1 reference code

Finished state of the project at the end of Session 1 (Build the foundation).

## Run it

1. Copy the `bookloop` folder into your MAMP `htdocs` folder.
2. Open phpMyAdmin (http://localhost:8888/phpMyAdmin/), go to the SQL tab, paste
   the contents of `database.sql` and click Go. This creates the `bookloop`
   database, the `books` table and six sample books.
3. Open http://localhost:8888/bookloop/

## Settings

`config.php` uses MAMP defaults (port 8889, user root, password root).
On XAMPP/Windows change the port to 3306 and the password to an empty string.

## Files

| File | Purpose |
|------|---------|
| `database.sql` | Creates the database, the `books` table, sample rows |
| `config.php` | PDO connection, `$pdo` used by every page |
| `includes/header.php` | `<head>`, Bootstrap CSS, navbar, opens `<main>` |
| `includes/footer.php` | Closes `<main>`, footer, Bootstrap JS |
| `assets/style.css` | The tiny amount of custom CSS |
| `index.php` | Book catalog (all books as cards) |
| `book.php` | One book, loaded by `?id=` with a prepared statement |

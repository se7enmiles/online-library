-- BookLoop — Session 4
-- The shelf table: which user put which book on which shelf.
-- Run this in phpMyAdmin with the bookloop database selected.

CREATE TABLE user_books (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id    INT UNSIGNED NOT NULL,
  book_id    INT UNSIGNED NOT NULL,
  status     ENUM('want', 'reading', 'finished') NOT NULL DEFAULT 'want',
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY one_shelf_per_book (user_id, book_id)
);

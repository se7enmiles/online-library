-- BookLoop — Session 5
-- Reservations and borrowing. One row per reservation, with a status that changes over time.
-- Run this in phpMyAdmin with the bookloop database selected.

CREATE TABLE reservations (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  book_id     INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NOT NULL,   -- who wants to borrow the book
  status      ENUM('reserved', 'borrowed', 'returned', 'cancelled') NOT NULL DEFAULT 'reserved',
  reserved_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  borrowed_at DATETIME     NULL,
  due_date    DATE         NULL,
  returned_at DATETIME     NULL,
  PRIMARY KEY (id),
  KEY book_status (book_id, status)
);

-- BookLoop — Session 6
-- Book exchange: a request to swap one book for another.
-- Run this in phpMyAdmin with the bookloop database selected.

-- 1. An owner can mark a book as available for exchange.
ALTER TABLE books
  ADD COLUMN for_exchange TINYINT(1) NOT NULL DEFAULT 0 AFTER description;

-- 2. The requests themselves.
CREATE TABLE exchange_requests (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  from_user_id    INT UNSIGNED NOT NULL,   -- who is asking
  to_user_id      INT UNSIGNED NOT NULL,   -- who owns the wanted book
  offered_book_id INT UNSIGNED NOT NULL,   -- what the asker gives
  wanted_book_id  INT UNSIGNED NOT NULL,   -- what the asker wants
  status          ENUM('pending', 'accepted', 'rejected', 'cancelled', 'completed')
                  NOT NULL DEFAULT 'pending',
  message         VARCHAR(300) NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at    DATETIME     NULL,
  PRIMARY KEY (id),
  KEY wanted_status (wanted_book_id, status),
  KEY offered_status (offered_book_id, status)
);

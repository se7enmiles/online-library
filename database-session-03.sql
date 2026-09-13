-- BookLoop — Session 3
-- Every book now belongs to the user who added it.
-- Run this in phpMyAdmin with the bookloop database selected.

ALTER TABLE books
  ADD COLUMN owner_id INT UNSIGNED NULL AFTER id;

-- The six sample books from Session 1 have no owner. Give them to user 1.
UPDATE books SET owner_id = 1 WHERE owner_id IS NULL;

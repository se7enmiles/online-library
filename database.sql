-- BookLoop — Session 1
-- Run this in phpMyAdmin (SQL tab) after creating the "bookloop" database.

CREATE DATABASE IF NOT EXISTS bookloop
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bookloop;

CREATE TABLE books (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title       VARCHAR(200) NOT NULL,
  author      VARCHAR(120) NOT NULL,
  genre       VARCHAR(60)  NOT NULL,
  year        SMALLINT     NULL,
  description TEXT         NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);

INSERT INTO books (title, author, genre, year, description) VALUES
('The Hobbit', 'J.R.R. Tolkien', 'Fantasy', 1937,
 'Bilbo Baggins, a comfortable hobbit, is swept into a quest to reclaim a dwarf kingdom from the dragon Smaug.'),
('Harry Potter and the Philosopher''s Stone', 'J.K. Rowling', 'Fantasy', 1997,
 'An orphan discovers on his eleventh birthday that he is a wizard and leaves for Hogwarts School of Witchcraft and Wizardry.'),
('The Little Prince', 'Antoine de Saint-Exupéry', 'Fable', 1943,
 'A pilot stranded in the desert meets a young prince who has fallen to Earth from a tiny asteroid.'),
('Sapiens', 'Yuval Noah Harari', 'History', 2011,
 'A sweeping history of humankind, from the Stone Age to the present day.'),
('Dune', 'Frank Herbert', 'Science Fiction', 1965,
 'On the desert planet Arrakis, young Paul Atreides becomes entangled in a struggle over the most valuable substance in the universe.'),
('Pride and Prejudice', 'Jane Austen', 'Romance', 1813,
 'Elizabeth Bennet navigates manners, morality and marriage in early 19th-century England.');

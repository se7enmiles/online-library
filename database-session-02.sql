-- BookLoop — Session 2
-- Adds the users table. Run this in phpMyAdmin with the bookloop database selected.

CREATE TABLE users (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name       VARCHAR(80)  NOT NULL,
  email      VARCHAR(160) NOT NULL,
  password   VARCHAR(255) NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_email (email)
);

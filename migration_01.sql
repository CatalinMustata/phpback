ALTER TABLE users ADD COLUMN active tinyint DEFAULT 0;
UPDATE users SET active = true;

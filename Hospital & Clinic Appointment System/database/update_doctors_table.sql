-- Add username and password columns to doctors table
ALTER TABLE doctors ADD COLUMN username VARCHAR(50) UNIQUE AFTER id;
ALTER TABLE doctors ADD COLUMN password VARCHAR(255) AFTER username;

-- Add index for username
ALTER TABLE doctors ADD INDEX idx_username (username);
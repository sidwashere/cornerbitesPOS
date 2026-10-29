-- WallacePOS local database bootstrap (run as MySQL root)
CREATE DATABASE IF NOT EXISTS wpos CHARACTER SET utf8 COLLATE utf8_unicode_ci;
CREATE USER IF NOT EXISTS 'wpos'@'localhost' IDENTIFIED WITH mysql_native_password BY 'wpos';
CREATE USER IF NOT EXISTS 'wpos'@'127.0.0.1' IDENTIFIED WITH mysql_native_password BY 'wpos';
GRANT ALL PRIVILEGES ON wpos.* TO 'wpos'@'localhost';
GRANT ALL PRIVILEGES ON wpos.* TO 'wpos'@'127.0.0.1';
FLUSH PRIVILEGES;

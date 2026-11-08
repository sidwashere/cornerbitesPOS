-- Corner Bites POS — production migration (idempotent, safe to re-run).
-- Run this in phpMyAdmin / MySQL Workbench / DBeaver against the LIVE database
-- (e.g. mllbontz_pos) to install: supplier credit, accounts cash book, category images.
-- Safe on machines that already ran these (CREATE IF NOT EXISTS + guarded alters).
SET NAMES utf8mb4;

-- 1. Extended supplier profile (contact + credit)
DELIMITER $$
DROP PROCEDURE IF EXISTS cb_add_col$$
CREATE PROCEDURE cb_add_col($tbl VARCHAR(64), $col VARCHAR(64), $ddl VARCHAR(512))
BEGIN
  DECLARE n INT DEFAULT 0;
  SELECT COUNT(*) INTO n FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = $tbl AND COLUMN_NAME = $col;
  IF n = 0 THEN
    SET @s = CONCAT('ALTER TABLE `', $tbl, '` ADD COLUMN `', $col, '` ', $ddl);
    PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;

CALL cb_add_col('stored_suppliers','email','VARCHAR(128) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','phone','VARCHAR(66) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','mobile','VARCHAR(66) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','address','VARCHAR(192) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','suburb','VARCHAR(66) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','postcode','VARCHAR(12) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','state','VARCHAR(66) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','country','VARCHAR(66) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','notes','VARCHAR(2048) DEFAULT \'\'');
CALL cb_add_col('stored_suppliers','credit_limit','DECIMAL(12,2) NOT NULL DEFAULT 0');
CALL cb_add_col('stored_suppliers','credit_due_days','INT NOT NULL DEFAULT 7');
CALL cb_add_col('stored_suppliers','credit_enabled','TINYINT NOT NULL DEFAULT 0');

-- 2. Category images (tray + website share one source)
CALL cb_add_col('stored_categories','image','VARCHAR(512) NOT NULL DEFAULT \'\'');
DROP PROCEDURE IF EXISTS cb_add_col;

-- 3. Supplier credit ledger
CREATE TABLE IF NOT EXISTS supplier_purchases (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  supplierid INT NOT NULL,
  ref VARCHAR(128) NOT NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0,
  paid_immediate DECIMAL(12,2) NOT NULL DEFAULT 0,
  balance DECIMAL(12,2) NOT NULL DEFAULT 0,
  status INT NOT NULL DEFAULT 1,
  processdt BIGINT NOT NULL DEFAULT 0,
  duedt BIGINT DEFAULT NULL,
  notes VARCHAR(2048) DEFAULT '',
  items_json TEXT,
  dt DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_sup (supplierid),
  KEY idx_ref (ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS supplier_payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  purchaseid INT NOT NULL,
  method VARCHAR(32) NOT NULL DEFAULT '',
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  processdt BIGINT NOT NULL DEFAULT 0,
  dt DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pay_purchase (purchaseid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Accounts (cash book)
CREATE TABLE IF NOT EXISTS accounts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  type VARCHAR(20) NOT NULL DEFAULT 'cash',
  active TINYINT NOT NULL DEFAULT 1,
  sortorder INT NOT NULL DEFAULT 0,
  dt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_accounts_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS account_movements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  accountid INT UNSIGNED NOT NULL,
  direction ENUM('in','out') NOT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  source VARCHAR(30) NOT NULL DEFAULT 'manual',
  ref VARCHAR(60) NOT NULL DEFAULT '',
  batch VARCHAR(40) NOT NULL DEFAULT '',
  description VARCHAR(255) NOT NULL DEFAULT '',
  processdt BIGINT NOT NULL,
  userid INT NULL,
  supplierid INT UNSIGNED NULL,
  dt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_am_account (accountid, processdt),
  KEY idx_am_source (source, processdt),
  KEY idx_am_supplier (supplierid, processdt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO accounts (name, type, sortorder) VALUES ('Cash (Till)', 'cash', 1), ('M-Pesa', 'mobile', 2), ('Bank', 'bank', 3);

SELECT 'migration complete' AS status,
       (SELECT COUNT(*) FROM supplier_purchases) AS purchases,
       (SELECT COUNT(*) FROM accounts) AS accounts;

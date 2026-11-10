-- Add unit of measurement to stock history (idempotent)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'stock_history' AND COLUMN_NAME = 'unit');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE stock_history ADD COLUMN unit VARCHAR(16) NOT NULL DEFAULT \'Pcs\' AFTER amount;',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

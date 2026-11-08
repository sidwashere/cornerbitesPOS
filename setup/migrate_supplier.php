<?php
// Idempotent supplier migration (extend suppliers + add purchases ledger).
// Run: php setup/migrate_supplier.php
$_SERVER['APP_ROOT'] = "/";
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
$pdo = (new DbConfig())->_db;

// 1. Extend stored_suppliers
$cols = [];
foreach ($pdo->query("SHOW COLUMNS FROM stored_suppliers") as $r) $cols[$r['Field']] = true;
$adds = [
    'email' => "VARCHAR(128) DEFAULT ''",
    'phone' => "VARCHAR(66) DEFAULT ''",
    'mobile' => "VARCHAR(66) DEFAULT ''",
    'address' => "VARCHAR(192) DEFAULT ''",
    'suburb' => "VARCHAR(66) DEFAULT ''",
    'postcode' => "VARCHAR(12) DEFAULT ''",
    'state' => "VARCHAR(66) DEFAULT ''",
    'country' => "VARCHAR(66) DEFAULT ''",
    'notes' => "VARCHAR(2048) DEFAULT ''",
    'credit_limit' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
    'credit_due_days' => "INT NOT NULL DEFAULT 7",
    'credit_enabled' => "TINYINT NOT NULL DEFAULT 0",
];
foreach ($adds as $n => $def) {
    if (!isset($cols[$n])) { $pdo->exec("ALTER TABLE stored_suppliers ADD COLUMN $n $def"); echo "added stored_suppliers.$n\n"; }
    else echo "ok stored_suppliers.$n\n";
}

// 2. Purchases ledger (new tables — required for accurate supplier credit)
$pdo->exec("CREATE TABLE IF NOT EXISTS supplier_purchases (
  id INT AUTO_INCREMENT PRIMARY KEY,
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
  INDEX idx_sup (supplierid), INDEX idx_ref (ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "ok supplier_purchases\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS supplier_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  purchaseid INT NOT NULL,
  method VARCHAR(32) NOT NULL DEFAULT 'mpesa',
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  processdt BIGINT NOT NULL DEFAULT 0,
  notes VARCHAR(255) DEFAULT '',
  dt DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pur (purchaseid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "ok supplier_payments\n";
echo "supplier migration done\n";

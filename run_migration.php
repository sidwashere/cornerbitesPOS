<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);
$root = dirname(__FILE__);
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['APP_ROOT'] = "/";
chdir($root);

require($root . '/library/wpos/config.php');
require_once($root . '/library/autoload.php');
require($root . '/library/wpos/AutoLoader.php');

$dbconf = json_decode(file_get_contents($root . '/library/wpos/.dbconfig.json'), true);
$dsn = "mysql:host={$dbconf['host']};port={$dbconf['port']};dbname={$dbconf['database']}";
$pdo = new PDO($dsn, $dbconf['user'], $dbconf['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

function addCol($pdo, $tbl, $col, $ddl) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$tbl, $col]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("ALTER TABLE `$tbl` ADD COLUMN `$col` $ddl");
        echo "Added $tbl.$col\n";
    } else {
        echo "Exists $tbl.$col\n";
    }
}

$cols = [
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
foreach ($cols as $col => $ddl) {
    try { addCol($pdo, 'stored_suppliers', $col, $ddl); } catch (Exception $e) { echo "ERR stored_suppliers.$col: " . $e->getMessage() . "\n"; }
}

try { addCol($pdo, 'stored_categories', 'image', "VARCHAR(512) NOT NULL DEFAULT ''"); } catch (Exception $e) { echo "ERR stored_categories.image: " . $e->getMessage() . "\n"; }

$pdo->exec("CREATE TABLE IF NOT EXISTS supplier_purchases (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK supplier_purchases\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS supplier_payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  purchaseid INT NOT NULL,
  method VARCHAR(32) NOT NULL DEFAULT '',
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  processdt BIGINT NOT NULL DEFAULT 0,
  dt DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pay_purchase (purchaseid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK supplier_payments\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS accounts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  type VARCHAR(20) NOT NULL DEFAULT 'cash',
  active TINYINT NOT NULL DEFAULT 1,
  sortorder INT NOT NULL DEFAULT 0,
  dt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_accounts_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK accounts\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS account_movements (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
echo "OK account_movements\n";

$pdo->exec("INSERT IGNORE INTO accounts (name, type, sortorder) VALUES ('Cash (Till)', 'cash', 1), ('M-Pesa', 'mobile', 2), ('Bank', 'bank', 3)");
echo "OK seeded accounts\n";

echo "MIGRATION DONE\n";
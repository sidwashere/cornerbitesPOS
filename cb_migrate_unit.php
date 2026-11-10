<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
$pdo = (new DbConfig())->_db;
try {
  $exists = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='stock_history' AND COLUMN_NAME='unit'")->fetchColumn();
  if (!$exists) {
    $pdo->exec("ALTER TABLE stock_history ADD COLUMN unit VARCHAR(16) NOT NULL DEFAULT 'Pcs' AFTER amount");
    echo "ADDED unit column\n";
  } else {
    echo "unit column already exists\n";
  }
  foreach ($pdo->query("SHOW COLUMNS FROM stock_history") as $c) if ($c['Field']==='unit') echo "unit: " . $c['Type'] . "\n";
  echo "DONE\n";
} catch (Exception $e) { echo "ERR: " . $e->getMessage() . "\n"; }

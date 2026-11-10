<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
try {
  $pdo = (new DbConfig())->_db;
  echo "connected\n";
  $exists = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='stock_history' AND COLUMN_NAME='unit'")->fetchColumn();
  echo "exists=" . $exists . "\n";
  if (!$exists) {
    $pdo->exec("ALTER TABLE stock_history ADD COLUMN unit VARCHAR(16) NOT NULL DEFAULT 'Pcs' AFTER amount");
    echo "ADDED\n";
  } else {
    echo "already exists\n";
  }
  foreach ($pdo->query("SHOW COLUMNS FROM stock_history") as $c) if ($c['Field']==='unit') echo "unit: " . $c['Type'] . "\n";
  echo "DONE\n";
} catch (Throwable $e) { echo "ERR: " . get_class($e) . ": " . $e->getMessage() . "\n"; }

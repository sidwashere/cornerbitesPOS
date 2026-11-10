<?php
echo "step1\n";
$_SERVER['APP_ROOT'] = "/";
echo "step2\n";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
echo "step3\n";
$pdo = (new DbConfig())->_db;
echo "step4\n";
$exists = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='stock_history' AND COLUMN_NAME='unit'")->fetchColumn();
echo "exists=" . $exists . "\n";
if (!$exists) {
  $pdo->exec("ALTER TABLE stock_history ADD COLUMN unit VARCHAR(16) NOT NULL DEFAULT 'Pcs' AFTER amount");
  echo "ADDED\n";
} else {
  echo "already exists\n";
}
echo "DONE\n";

<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
$pdo = (new DbConfig())->_db;
try { $pdo->exec("ALTER TABLE stock_history ADD COLUMN IF NOT EXISTS unit VARCHAR(16) NOT NULL DEFAULT 'Pcs' AFTER amount"); echo "OK: unit column ensured\n"; }
catch (Exception $e) { echo "ERR: " . $e->getMessage() . "\n"; }
foreach ($pdo->query("SHOW COLUMNS FROM stock_history") as $c) if ($c['Field']==='unit') echo "unit: " . $c['Type'] . "\n";

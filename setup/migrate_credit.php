<?php
// Idempotent credit migration for existing customers table (no new DB).
// Run: php setup/migrate_credit.php
$_SERVER['APP_ROOT'] = "/";
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
$db = new DbConfig();
$pdo = $db->_db;
$cols = [];
foreach ($pdo->query("SHOW COLUMNS FROM customers") as $r) $cols[$r['Field']] = true;
$adds = [
    'credit_limit' => "DECIMAL(12,2) NOT NULL DEFAULT 0",
    'credit_due_days' => "INT NOT NULL DEFAULT 7",
    'credit_enabled' => "TINYINT NOT NULL DEFAULT 0",
];
foreach ($adds as $name => $def) {
    if (!isset($cols[$name])) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN $name $def");
        echo "added customers.$name\n";
    } else {
        echo "ok customers.$name\n";
    }
}
echo "credit migration done\n";

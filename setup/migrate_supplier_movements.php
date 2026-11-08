<?php
/**
 * Migrate: link account movements to suppliers (for per-supplier movement history).
 * Run: php setup/migrate_supplier_movements.php
 * Idempotent.
 */
$_SERVER['APP_ROOT'] = "/";
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
$pdo = (new DbConfig())->_db;

$cols = [];
foreach ($pdo->query("SHOW COLUMNS FROM account_movements") as $r) $cols[$r['Field']] = true;
if (!isset($cols['supplierid'])) {
    $pdo->exec("ALTER TABLE account_movements ADD COLUMN supplierid INT UNSIGNED NULL, ADD KEY idx_am_supplier (supplierid, processdt)");
    echo "supplierid added\n";
} else {
    echo "already present\n";
}
echo "Done.\n";

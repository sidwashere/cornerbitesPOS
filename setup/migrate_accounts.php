<?php
/**
 * Migrate: Accounts / Cash Book module.
 * Run once: php setup/migrate_accounts.php
 *
 * Tables:
 *  - accounts: money holders (Cash/Till, M-Pesa, Bank, ...). Balance = Σ(in) − Σ(out).
 *  - account_movements: every cash movement (in/out) with source + ref.
 *    Transfers = one OUT + one IN row sharing the same batch ref.
 * Idempotent: safe to re-run.
 */
$_SERVER['APP_ROOT'] = "/";
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
$pdo = (new DbConfig())->_db;

$pdo->exec("CREATE TABLE IF NOT EXISTS accounts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'cash',
    active TINYINT NOT NULL DEFAULT 1,
    sortorder INT NOT NULL DEFAULT 0,
    dt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_accounts_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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
    dt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_am_account (accountid, processdt),
    KEY idx_am_source (source, processdt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed the standard money holders (idempotent by name).
$seed = [
    ['Cash (Till)', 'cash', 1],
    ['M-Pesa', 'mobile', 2],
    ['Bank', 'bank', 3],
];
foreach ($seed as $s) {
    $st = $pdo->prepare("INSERT IGNORE INTO accounts (name, type, sortorder) VALUES (:n, :t, :o)");
    $st->execute([':n' => $s[0], ':t' => $s[1], ':o' => $s[2]]);
}

echo "Accounts module migrated. Accounts:\n";
foreach ($pdo->query("SELECT id, name, type, active FROM accounts ORDER BY sortorder") as $r) {
    echo "  #{$r['id']} {$r['name']} ({$r['type']}) active={$r['active']}\n";
}
echo "Done.\n";

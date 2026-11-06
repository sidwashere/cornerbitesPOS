<?php
/**
 * setup_tables.php - idempotent DB migration for the order orchestration features.
 *
 * Creates any missing tables from the canonical POS schema required by:
 *   platform_configs      - integrations and webhook authentication
 *   order_status_history  - order status audit trail
 *   wpos_order_inbox      - durable inbound webhook log (redundancy / replay)
 *   wpos_whatsapp_queue   - durable WhatsApp notification queue (retry / dead-letter)
 * brings older sales tables up to the canonical install.sql columns, and
 * seeds default platform_configs rows (disabled until a secret is set).
 *
 * Run once after deploy:  curl -s https://pos.cornerbites.co.ke/setup_tables.php
 * Safe to re-run (CREATE TABLE IF NOT EXISTS, INSERT IGNORE).
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

$db = new DbConfig();
$pdo = $db->_db;

$statements = [
    "CREATE TABLE IF NOT EXISTS `platform_configs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `platform` varchar(32) NOT NULL,
        `api_key` varchar(255) NULL DEFAULT NULL,
        `store_id` varchar(128) NULL DEFAULT NULL,
        `webhook_secret` varchar(255) NULL DEFAULT NULL,
        `enabled` tinyint(1) DEFAULT 1,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `platform` (`platform`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `order_status_history` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sale_id` int(11) NOT NULL,
        `from_status` varchar(32) NULL DEFAULT NULL,
        `to_status` varchar(32) NOT NULL,
        `changed_by` varchar(64) NULL DEFAULT NULL,
        `changed_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `notes` text NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_osh_sale_id` (`sale_id`),
        INDEX `idx_osh_changed_at` (`changed_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE utf8_unicode_ci",

    "CREATE TABLE IF NOT EXISTS `wpos_order_inbox` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `channel` varchar(32) NOT NULL,
        `external_ref` varchar(128) NOT NULL DEFAULT '',
        `payload` mediumtext NOT NULL,
        `status` varchar(16) NOT NULL DEFAULT 'pending',
        `attempts` int(11) NOT NULL DEFAULT 0,
        `last_error` varchar(512) NULL DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_inbox_status` (`status`),
        KEY `idx_inbox_channel_ref` (`channel`, `external_ref`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",

    "CREATE TABLE IF NOT EXISTS `wpos_whatsapp_queue` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sale_id` int(11) NOT NULL DEFAULT 0,
        `channel` varchar(32) NOT NULL,
        `recipient` varchar(64) NOT NULL,
        `message` text NOT NULL,
        `status` varchar(16) NOT NULL DEFAULT 'pending',
        `attempts` int(11) NOT NULL DEFAULT 0,
        `last_error` varchar(512) NULL DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_wa_status` (`status`),
        KEY `idx_wa_recipient` (`recipient`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
];

foreach ($statements as $sql) {
    $pdo->exec($sql);
}

// Existing installations may have the original POS schema without the order
// metadata columns now written by SalesModel and queried by reports.
$salesColumns = [
    'source' => "`source` varchar(32) NULL DEFAULT 'pos'",
    'external_ref' => '`external_ref` varchar(128) NULL DEFAULT NULL',
    'order_type' => "`order_type` varchar(16) NULL DEFAULT 'dinein'",
    'delivery_address' => '`delivery_address` text NULL',
    'platform_metadata' => '`platform_metadata` JSON NULL',
    'received_at' => '`received_at` datetime NULL DEFAULT NULL',
    'confirmed_at' => '`confirmed_at` datetime NULL DEFAULT NULL',
    'ready_at' => '`ready_at` datetime NULL DEFAULT NULL',
    'delivered_at' => '`delivered_at` datetime NULL DEFAULT NULL',
    'cancelled_at' => '`cancelled_at` datetime NULL DEFAULT NULL',
    'cancellation_reason' => '`cancellation_reason` varchar(255) NULL DEFAULT NULL',
    'estimated_ready_time' => '`estimated_ready_time` datetime NULL DEFAULT NULL',
    'priority' => '`priority` int(11) NULL DEFAULT 0',
];
$existingSalesColumns = $pdo->query('SHOW COLUMNS FROM `sales`')->fetchAll(PDO::FETCH_COLUMN);
foreach ($salesColumns as $column => $definition) {
    if (!in_array($column, $existingSalesColumns, true)) {
        $pdo->exec("ALTER TABLE `sales` ADD COLUMN " . $definition);
    }
}

$salesIndexes = $pdo->query('SHOW INDEX FROM `sales`')->fetchAll(PDO::FETCH_COLUMN, 2);
foreach ([
    'idx_sales_external_ref' => '(`external_ref`)',
    'idx_sales_source' => '(`source`)',
] as $index => $columns) {
    if (!in_array($index, $salesIndexes, true)) {
        $pdo->exec("CREATE INDEX `{$index}` ON `sales` {$columns}");
    }
}

// Seed platform_configs rows (disabled; set webhook_secret + enabled=1 to activate signature checks)
$seed = ['website', 'glovo', 'ubereats', 'phone'];
foreach ($seed as $p) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO platform_configs (platform, enabled) VALUES (:p, 0)");
    $stmt->execute([':p' => $p]);
}

header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'tables' => ['platform_configs', 'order_status_history', 'wpos_order_inbox', 'wpos_whatsapp_queue'],
    'sales_columns' => array_keys($salesColumns),
    'platform_configs_seeded' => $seed,
]);

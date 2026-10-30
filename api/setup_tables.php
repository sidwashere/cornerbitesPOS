<?php
/**
 * setup_tables.php - idempotent DB migration for the order orchestration features.
 *
 * Creates:
 *   wpos_order_inbox      - durable inbound webhook log (redundancy / replay)
 *   wpos_whatsapp_queue   - durable WhatsApp notification queue (retry / dead-letter)
 * and seeds default platform_configs rows (disabled until a secret is set).
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

// Seed platform_configs rows (disabled; set webhook_secret + enabled=1 to activate signature checks)
$seed = ['website', 'glovo', 'ubereats', 'phone'];
foreach ($seed as $p) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO platform_configs (platform, enabled) VALUES (:p, 0)");
    $stmt->execute([':p' => $p]);
}

header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'tables' => ['wpos_order_inbox', 'wpos_whatsapp_queue'],
    'platform_configs_seeded' => $seed,
]);

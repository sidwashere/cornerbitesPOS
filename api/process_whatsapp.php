<?php
/**
 * process_whatsapp.php - drains the durable WhatsApp notification queue.
 * Sends pending/failed rows via the configured provider, retries with backoff,
 * and dead-letters after max_attempts. Order ingestion is never blocked by this.
 *
 * Trigger via cron (recommended every 1-2 min) or manually:
 *   curl -s https://pos.cornerbites.co.ke/process_whatsapp.php
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

$notifier = new WhatsAppNotifier();
$stats = $notifier->processQueue();

header('Content-Type: application/json');
echo json_encode($stats);

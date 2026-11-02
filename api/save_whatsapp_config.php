<?php
/**
 * save_whatsapp_config.php - No-op stub.
 *
 * WhatsApp orders are now handled via direct chat from the website.
 * No server-side configuration is needed.
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'WhatsApp is now handled via direct chat from the website. No server-side config needed.']);
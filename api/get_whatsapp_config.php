<?php
/**
 * get_whatsapp_config.php - No-op stub.
 *
 * WhatsApp orders are now handled via direct chat from the website.
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

echo json_encode(['enabled' => true, 'provider' => 'direct_chat', 'message' => 'WhatsApp is handled via direct chat from the website.']);
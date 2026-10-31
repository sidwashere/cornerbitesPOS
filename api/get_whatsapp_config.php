<?php
/**
 * get_whatsapp_config.php - Returns WhatsApp config for admin dashboard.
 * Requires auth.
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/autoload.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

header('Content-Type: application/json');

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$path = $_SERVER['DOCUMENT_ROOT'] . '/docs/whatsapp.json';
if (file_exists($path)) {
    echo file_get_contents($path);
} else {
    echo json_encode(['enabled' => false]);
}
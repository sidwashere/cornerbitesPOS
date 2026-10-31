<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');

header('Content-Type: application/json');

$path = $_SERVER['DOCUMENT_ROOT'] . '/docs/whatsapp.json';
if (file_exists($path)) {
    $config = json_decode(file_get_contents($path), true);
    echo json_encode(['ok' => true, 'config' => $config]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Config file not found']);
}

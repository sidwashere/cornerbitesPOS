<?php
/**
 * save_whatsapp_config.php - Save WhatsApp configuration from admin dashboard.
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

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$config = [
    'enabled' => !empty($input['enabled']),
    'provider' => $input['provider'] ?? 'callmebot',
    'callmebot' => [
        'apikey' => $input['apikey'] ?? '',
        'phone' => $input['phone'] ?? '',
    ],
    'webhook' => [
        'url' => $input['webhook_url'] ?? '',
        'headers' => [],
        'body_template' => '{"phone":"{{phone}}","message":"{{message}}"}',
        'phone' => $input['webhook_phone'] ?? '',
    ],
    'recipients' => array_filter(array_map('trim', explode(',', $input['recipients'] ?? ''))),
    'max_attempts' => (int)($input['max_attempts'] ?? 5),
    'retry_backoff_seconds' => (int)($input['retry_backoff_seconds'] ?? 60),
];

$path = $_SERVER['DOCUMENT_ROOT'] . '/docs/whatsapp.json';
$dir = dirname($path);
if (!is_dir($dir)) mkdir($dir, 0755, true);

if (file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT))) {
    echo json_encode(['ok' => true, 'message' => 'WhatsApp config saved.']);
} else {
    echo json_encode(['error' => 'Failed to write config file.']);
}

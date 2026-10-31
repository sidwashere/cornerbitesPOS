<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');

header('Content-Type: application/json');

$config = [
    'enabled' => true,
    'provider' => 'callmebot',
    'callmebot' => [
        'apikey' => '2464389',
        'phone' => '+254795690549',
    ],
    'webhook' => [
        'url' => '',
        'headers' => [],
        'body_template' => '{"phone":"{{phone}}","message":"{{message}}"}',
        'phone' => '',
    ],
    'recipients' => ['+254795690549'],
    'max_attempts' => 5,
    'retry_backoff_seconds' => 60,
];

$path = $_SERVER['DOCUMENT_ROOT'] . '/docs/whatsapp.json';
$dir = dirname($path);
if (!is_dir($dir)) mkdir($dir, 0755, true);

if (file_put_contents($path, json_encode($config, JSON_PRETTY_PRINT))) {
    echo json_encode(['ok' => true, 'path' => $path, 'size' => filesize($path)]);
} else {
    echo json_encode(['error' => 'Failed to write config file.']);
}

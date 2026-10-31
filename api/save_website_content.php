<?php
/**
 * save_website_content.php - Save website content from admin dashboard.
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

$path = $_SERVER['DOCUMENT_ROOT'] . '/docs/website_content.json';
$dir = dirname($path);
if (!is_dir($dir)) mkdir($dir, 0755, true);

if (file_put_contents($path, json_encode($input, JSON_PRETTY_PRINT))) {
    echo json_encode(['ok' => true, 'message' => 'Website content saved. Changes are live.']);
} else {
    echo json_encode(['error' => 'Failed to write content file.']);
}

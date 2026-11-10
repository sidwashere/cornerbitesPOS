<?php
/**
 * save_website_content.php - Save website content from admin (CMS v2).
 * Auth-required. Validates structure, backs up the previous file, writes atomically.
 * Body: the full website_content.json object (site/categories/menu/offers/...).
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    $u = isset($_REQUEST['username']) ? trim((string)$_REQUEST['username']) : '';
    $h = isset($_REQUEST['auth_hash']) ? trim((string)$_REQUEST['auth_hash']) : '';
    $renewed = false;
    if ($u !== '' && $h !== '') { try { $renewed = (bool)$auth->renewTokenSession($u, $h); } catch (Exception $e) { $renewed = false; } }
    if (!$renewed || !$auth->isLoggedIn()) { http_response_code(403); echo json_encode(['error' => 'Unauthorized — please log in again']); exit; }
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { http_response_code(400); echo json_encode(['error' => 'Invalid JSON']); exit; }

$path = $_SERVER['DOCUMENT_ROOT'] . '/docs/website_content.json';
$dir = dirname($path);
$bakDir = $_SERVER['DOCUMENT_ROOT'] . '/docs/backup';
foreach ([$dir, $bakDir] as $d) { if (!is_dir($d)) mkdir($d, 0755, true); }

// Light validation (accept any structurally sound v1/v2 payload; require known sections if v2)
if (isset($input['version']) && (int)$input['version'] >= 2) {
    if (!isset($input['site']) || !is_array($input['site'])) { http_response_code(400); echo json_encode(['error' => 'site section required']); exit; }
    if (!isset($input['menu']['items']) || !is_array($input['menu']['items'])) { http_response_code(400); echo json_encode(['error' => 'menu.items required']); exit; }
}

// Backup previous file (keep last 20)
if (file_exists($path)) {
    $bak = $bakDir . '/website_content-' . date('Ymd-His') . '.json';
    copy($path, $bak);
    $baks = glob($bakDir . '/website_content-*.json');
    sort($baks);
    while (count($baks) > 20) { unlink(array_shift($baks)); }
}

// Atomic write
$tmp = $path . '.tmp';
if (file_put_contents($tmp, json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    http_response_code(500); echo json_encode(['error' => 'Failed to write content file.']); exit;
}
rename($tmp, $path);
echo json_encode(['ok' => true, 'message' => 'Website content saved. Changes are live.', 'items' => count($input['menu']['items'] ?? [])]);

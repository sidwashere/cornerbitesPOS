<?php
/**
 * table_qr.php - Per-table QR code image (dine-in ordering).
 * GET ?table=1[&size=8] -> PNG QR encoding the website dine-in URL:
 *   https?://<host>/website/?table=1#menu
 * Scanning it opens the Corner Bites menu for that table, and checkout
 * tags the order with table_number + order_type=dinein.
 *
 * Public (no login) so printed plaques work day after day.
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

$table = isset($_GET['table']) ? (int)$_GET['table'] : 0;
if ($table < 1 || $table > 100) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'table must be 1..100']);
    exit;
}
$size = isset($_GET['size']) ? (int)$_GET['size'] : 8;
if ($size < 3) $size = 3;
if ($size > 16) $size = 16;

$scheme = 'http';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off') {
    $scheme = 'https';
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $proto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
    if ($proto === 'https' || $proto === 'http') $scheme = $proto;
}
$host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
$url = $scheme . '://' . $host . '/website/?table=' . $table . '#menu';

include($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/phpqrcode.php');
// $outfile=false outputs the PNG directly (with headers).
QRcode::png($url, false, QR_ECLEVEL_L, $size, 2, false, 0xFFFFFF, 0x000000);

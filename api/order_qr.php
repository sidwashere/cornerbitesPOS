<?php
// order_qr.php - Public endpoint returning a PNG QR for an order reference.
ob_start(); // swallow PHP warnings/deprecations so the PNG stays clean
// GET /api/order_qr.php?ref=<sale-ref> -> image/png
// Encodes the public website order link for the reference.
$ref = isset($_GET['ref']) ? preg_replace('/[^A-Za-z0-9_-]/', '', (string)$_GET['ref']) : '';
if ($ref === '' || $ref === null) {
    http_response_code(400);
    echo "ref required";
    exit;
}
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$url = $proto . '://' . $host . '/website/?order=' . rawurlencode($ref);

require_once($_SERVER['DOCUMENT_ROOT'] . '/library/phpqrcode.php');
// phpqrcode echoes the PNG to stdout when $outfile=false; also emits PHP 8.5 deprecation notices.
// Capture everything, then keep only the PNG binary (trim at the PNG magic signature).
$buf = ob_get_clean();
ob_start();
QRcode::png($url, false, QR_ECLEVEL_M, 8, 2);
$buf = ob_get_clean();
$pos = strpos($buf, "\x89PNG");
$png = ($pos !== false) ? substr($buf, $pos) : $buf;
if (ob_get_level() > 0) ob_end_flush();
header('Content-Type: image/png');
header('Cache-Control: no-cache, must-revalidate');
echo $png;

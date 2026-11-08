<?php
// Accounts movements CSV export. Auth required.
// GET /api/accounts_export.php?accountid=&source=&direction=&from=&to=
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userId'])) { http_response_code(403); echo "unauthorized"; exit; }

$payload = ['limit' => 10000];
foreach (['accountid', 'source', 'direction'] as $k) { if (isset($_GET[$k]) && $_GET[$k] !== '') $payload[$k] = ($k === 'accountid') ? (int)$_GET[$k] : $_GET[$k]; }
foreach (['from', 'to'] as $k) { if (isset($_GET[$k]) && $_GET[$k] !== '') $payload[$k] = (int)$_GET[$k]; }

$ac = new WposAccounts(json_decode(json_encode($payload)));
$res = $ac->movements(['errorCode' => 'OK', 'error' => 'OK', 'data' => '']);
$moves = $res['data'] ?? [];

$fn = 'accounts-movements-' . date('Ymd-His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $fn . '"');
echo "\xEF\xBB\xBF"; // BOM for Excel
$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Account', 'Direction', 'Amount (KSh)', 'Source', 'Reference', 'Description']);
$tin = 0; $tout = 0;
foreach ($moves as $m) {
    if ($m['direction'] === 'in') $tin += (float)$m['amount']; else $tout += (float)$m['amount'];
    fputcsv($out, [
        $m['processdt'] ? date('Y-m-d H:i', (int)($m['processdt'] / 1000)) : ($m['dt'] ?? ''),
        $m['account'], strtoupper($m['direction']), number_format((float)$m['amount'], 2),
        $m['source'], $m['ref'], $m['description'],
    ]);
}
fputcsv($out, []);
fputcsv($out, ['', 'Totals', '', 'In: ' . number_format($tin, 2) . ' · Out: ' . number_format($tout, 2) . ' · Net: ' . number_format($tin - $tout, 2), count($moves) . ' movements']);
fclose($out);

<?php
// Neat Accounts report (print → Save as PDF). Auth required.
// GET /api/accounts_report.php?accountid=&source=&direction=&from=&to=  (from/to = epoch ms)
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userId'])) { http_response_code(403); echo "unauthorized"; exit; }

$schema = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'];
$gen = json_decode(@file_get_contents($schema . "docs/.config.json"));
$bizName = ($gen && isset($gen->businessname)) ? $gen->businessname : "Corner Bites";
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$logoUrl = $proto . '://' . $host . '/assets/images/biz-logo.jpg';
$phone = ($gen && isset($gen->phone)) ? $gen->phone : "";
$loc = ($gen && isset($gen->address)) ? $gen->address : "Utawala-Kincar, Nairobi";

$qs = ['accountid' => $_GET['accountid'] ?? '', 'source' => $_GET['source'] ?? '', 'direction' => $_GET['direction'] ?? '', 'from' => $_GET['from'] ?? '', 'to' => $_GET['to'] ?? ''];
$payload = [];
foreach ($qs as $k => $v) { if ($v !== '' && $v !== null) $payload[$k] = (strpos($k, 'id') !== false || $k === 'from' || $k === 'to') ? (int)$v : $v; }
$payload['limit'] = 5000;

$ac = new WposAccounts(json_decode(json_encode($payload)));
$stRes = $ac->stats(['errorCode' => 'OK', 'error' => 'OK', 'data' => '']);
$stats = $stRes['data'] ?? ['totals' => ['in' => 0, 'out' => 0, 'net' => 0, 'moves' => 0], 'by_source' => [], 'daily_14d' => [], 'accounts' => []];
$mvRes = $ac->movements(['errorCode' => 'OK', 'error' => 'OK', 'data' => '']);
$moves = $mvRes['data'] ?? [];

$fmt = function ($v) { return "KSh " . number_format((float)$v, 2); };
$dt = function ($ts) { return $ts ? date('d M Y, H:i', (int)($ts / 1000)) : '—'; };
$srcLabel = function ($s) {
    return ['purchase' => 'Stock purchase', 'supplierpay' => 'Supplier payment', 'transfer' => 'Sweep / Transfer', 'manual' => 'External / Manual', 'sale' => 'Sale', 'opening' => 'Opening balance', 'external' => 'External (not recoverable)', 'marketing' => 'Marketing', 'customer_refund' => 'Cash to customer', 'supplier_refund' => 'Refund from supplier'][$s] ?? $s;
};

// filter caption
$flt = [];
if (!empty($payload['accountid'])) $flt[] = 'Account #' . $payload['accountid'];
if (!empty($payload['source'])) $flt[] = $srcLabel($payload['source']);
if (!empty($payload['direction'])) $flt[] = ($payload['direction'] === 'in' ? 'Money in' : 'Money out');
if (!empty($payload['from'])) $flt[] = 'From ' . date('d M Y', (int)($payload['from'] / 1000));
if (!empty($payload['to'])) $flt[] = 'To ' . date('d M Y', (int)($payload['to'] / 1000));
$filterTxt = $flt ? implode(' · ', $flt) : 'All accounts · All sources · All time';

// per-account table
$acRows = ''; $acTotBal = 0; $acTotIn = 0; $acTotOut = 0;
foreach ($stats['accounts'] as $a) {
    $acTotBal += (float)$a['balance']; $acTotIn += (float)$a['total_in']; $acTotOut += (float)$a['total_out'];
    $col = (float)$a['balance'] >= 0 ? '#256029' : '#c00';
    $acRows .= "<tr><td><strong>" . htmlspecialchars($a['name']) . "</strong> <span style='color:#888'>(" . htmlspecialchars($a['type']) . ")</span></td>"
        . "<td style='text-align:right'>" . $fmt($a['total_in']) . "</td><td style='text-align:right'>" . $fmt($a['total_out']) . "</td>"
        . "<td style='text-align:right; color:$col; font-weight:800;'>" . $fmt($a['balance']) . "</td><td style='text-align:center'>" . (int)$a['moves'] . "</td></tr>";
}
if (!$acRows) $acRows = "<tr><td colspan='5' class='empty'>No accounts yet.</td></tr>";

// by-source table
$bsRows = ''; $bsIn = 0; $bsOut = 0;
foreach ($stats['by_source'] as $b) {
    $bsIn += (float)$b['in']; $bsOut += (float)$b['out'];
    $bsRows .= "<tr><td>" . $srcLabel($b['source']) . "</td><td style='text-align:right'>" . $fmt($b['in']) . "</td><td style='text-align:right'>" . $fmt($b['out']) . "</td><td style='text-align:right'>" . $fmt($b['net']) . "</td><td style='text-align:center'>" . (int)$b['moves'] . "</td></tr>";
}
if (!$bsRows) $bsRows = "<tr><td colspan='5' class='empty'>No movements in this filter.</td></tr>";

// movements detail
$mvRows = ''; $mvIn = 0; $mvOut = 0; $n = 1;
foreach ($moves as $m) {
    if ($m['direction'] === 'in') $mvIn += (float)$m['amount']; else $mvOut += (float)$m['amount'];
    $dir = $m['direction'] === 'in' ? "<span style='color:#256029;font-weight:700;'>IN</span>" : "<span style='color:#c00;font-weight:700;'>OUT</span>";
    $mvRows .= "<tr><td>$n</td><td>" . $dt($m['processdt']) . "</td><td>" . htmlspecialchars($m['account']) . "</td><td>$dir</td>"
        . "<td style='text-align:right'>" . $fmt($m['amount']) . "</td><td>" . $srcLabel($m['source']) . "</td>"
        . "<td>" . htmlspecialchars($m['ref'] ?: '—') . "</td><td style='font-size:11px;'>" . htmlspecialchars($m['description'] ?: '—') . "</td></tr>";
    $n++;
}
if (!$mvRows) $mvRows = "<tr><td colspan='8' class='empty'>No movements in this filter.</td></tr>";

$tot = $stats['totals'];
$html = "<!doctype html><html><head><meta charset='utf-8'><style>
 body{font-family:DejaVu Sans,Arial,sans-serif;color:#222;font-size:12px;margin:24px;}
 .h{display:flex;justify-content:space-between;border-bottom:3px solid #84cc16;padding-bottom:10px;}
 .biz{font-size:22px;font-weight:800;} .muted{color:#666;font-size:11px;}
 h2{font-size:14px;margin:20px 0 8px;}
 table{width:100%;border-collapse:collapse;margin-top:6px;}
 th{background:#1a1a1a;color:#84cc16;padding:7px;text-align:left;font-size:11px;}
 td{border-bottom:1px solid #e5e5e5;padding:7px;}
 tr:nth-child(even) td{background:#f7f7f5;}
 thead{display:table-header-group;} tfoot{display:table-footer-group;}
 .tot{font-weight:800;background:#f0f0f0 !important;}
 .empty{text-align:center;color:#777;}
 .kpi{display:flex;gap:10px;margin-top:14px;} .box{flex:1;border:1px solid #ddd;border-radius:8px;padding:10px;}
 .box b{font-size:16px;} .foot{margin-top:18px;color:#666;font-size:11px;border-top:1px solid #ddd;padding-top:8px;}
</style></head><body>
<div class='h'><div style='display:flex; align-items:center; gap:12px;'><img src='{$logoUrl}' style='height:52px; width:52px; border-radius:50%%; border:2px solid #84cc16; object-fit:cover;'><div><div class='biz'>{$bizName}</div><div class='muted'>{$loc} · {$phone}</div></div></div>
<div style='text-align:right'><div style='font-weight:800;font-size:16px;'>ACCOUNTS REPORT</div><div class='muted'>Printed " . date('d M Y, H:i') . "</div><div class='muted'>{$filterTxt}</div></div></div>
<div class='kpi'>
 <div class='box'>Money in<br/><b style='color:#256029'>" . $fmt($tot['in']) . "</b></div>
 <div class='box'>Money out<br/><b style='color:#c00'>" . $fmt($tot['out']) . "</b></div>
 <div class='box'>Net<br/><b>" . $fmt($tot['net']) . "</b></div>
 <div class='box'>Movements<br/><b>" . (int)$tot['moves'] . "</b></div>
</div>
<h2>Balances by account</h2>
<table><thead><tr><th>Account</th><th style='text-align:right'>In</th><th style='text-align:right'>Out</th><th style='text-align:right'>Balance</th><th style='text-align:center'>Moves</th></tr></thead>
<tbody>{$acRows}</tbody><tfoot><tr class='tot'><td>Combined</td><td style='text-align:right'>" . $fmt($acTotIn) . "</td><td style='text-align:right'>" . $fmt($acTotOut) . "</td><td style='text-align:right'>" . $fmt($acTotBal) . "</td><td style='text-align:center'>—</td></tr></tfoot></table>
<h2>Money by source</h2>
<table><thead><tr><th>Source</th><th style='text-align:right'>In</th><th style='text-align:right'>Out</th><th style='text-align:right'>Net</th><th style='text-align:center'>Moves</th></tr></thead>
<tbody>{$bsRows}</tbody><tfoot><tr class='tot'><td>Filtered</td><td style='text-align:right'>" . $fmt($bsIn) . "</td><td style='text-align:right'>" . $fmt($bsOut) . "</td><td style='text-align:right'>" . $fmt($bsIn - $bsOut) . "</td><td style='text-align:center'>—</td></tr></tfoot></table>
<h2>Movements detail (" . count($moves) . ")</h2>
<table><thead><tr><th>#</th><th>Date</th><th>Account</th><th>Dir</th><th style='text-align:right'>Amount</th><th>Source</th><th>Ref</th><th>Description</th></tr></thead>
<tbody>{$mvRows}</tbody><tfoot><tr class='tot'><td colspan='4'>In " . $fmt($mvIn) . " · Out " . $fmt($mvOut) . "</td><td colspan='4'>Net " . $fmt($mvIn - $mvOut) . "</td></tr></tfoot></table>
<div class='foot'>Balances = Σ(in) − Σ(out). Transfers carry one OUT + one IN row with the same ref. Generated from the cash book ledger.</div>
</body></html>";

$printJs = "<script>window.onload=function(){setTimeout(function(){window.print();},400);};</script>";
$html = str_replace('</body>', $printJs . '</body>', $html);
header('Content-Type: text/html; charset=utf-8');
echo $html;

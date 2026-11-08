<?php
// Neat customer credit statement PDF (dompdf). Auth required.
// GET /api/credit_statement.php?custid=12        -> inline PDF
// GET /api/credit_statement.php?custid=12&dl=1   -> download
// GET /api/credit_statement.php?custid=12&type=html -> HTML only (print preview)
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userId'])) { http_response_code(403); echo "unauthorized"; exit; }
$custid = isset($_GET['custid']) ? (int)$_GET['custid'] : 0;
if ($custid <= 0) { http_response_code(400); echo "custid required"; exit; }

$pdo = (new DbConfig())->_db;
$st = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
$st->execute([':id' => $custid]);
$c = $st->fetch(PDO::FETCH_ASSOC);
if (!$c) { http_response_code(404); echo "customer not found"; exit; }
$limit = (float)($c['credit_limit'] ?? 0);
$days = (int)($c['credit_due_days'] ?? 7);
$schema = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'];
$gen = json_decode(file_get_contents($schema . "docs/.config.json"));
$bizName = ($gen && isset($gen->businessname)) ? $gen->businessname : "Corner Bites";
$phone = ($gen && isset($gen->phone)) ? $gen->phone : "";
$loc = ($gen && isset($gen->address)) ? $gen->address : "Utawala-Kincar, Nairobi";

// reuse WposCredit statement entries
$credit = new WposCredit(json_decode(json_encode(['custid' => $custid])));
$res = $credit->statement(['errorCode' => 'OK', 'error' => 'OK', 'data' => '']);
$entries = ($res['data'] && isset($res['data']['entries'])) ? $res['data']['entries'] : [];
$owed = (float)($res['data']['open_balance'] ?? 0);
$credited = (float)($res['data']['total_credited'] ?? 0);
$paid = (float)($res['data']['total_paid'] ?? 0);
$overdue = (float)($res['data']['overdue'] ?? 0);
$fmt = function ($v) { return "KSh " . number_format((float)$v, 2); };
$dt = function ($ts) { return $ts ? date('d M Y', (int)($ts / 1000)) : '—'; };

$rows = '';
$n = 1;
foreach ($entries as $e) {
    $status = $e['owed'] <= 0.005 ? '<span style="color:#2e7d32;font-weight:700;">Paid</span>' : ($e['overdue'] ? '<span style="color:#c62828;font-weight:700;">OVERDUE</span>' : '<span style="color:#e65100;font-weight:700;">Open</span>');
    $rows .= "<tr><td>{$n}</td><td>{$e['ref']}</td><td>" . $dt($e['processdt']) . "</td><td>" . $dt($e['duedt']) .
        "</td><td style='text-align:right'>" . $fmt($e['total']) . "</td><td style='text-align:right'>" . $fmt($e['credit_amt']) .
        "</td><td style='text-align:right'>" . $fmt($e['paid']) . "</td><td style='text-align:right'><strong>" . $fmt($e['owed']) . "</strong></td><td>{$status}</td></tr>";
    $n++;
}
if (!$rows) $rows = "<tr><td colspan='9' style='text-align:center;color:#777;'>No open credit — nothing owed.</td></tr>";

$html = "<!doctype html><html><head><meta charset='utf-8'><style>
 body{font-family:DejaVu Sans,Arial,sans-serif;color:#222;font-size:12px;margin:24px;}
 .h{display:flex;justify-content:space-between;border-bottom:3px solid #84cc16;padding-bottom:10px;}
 .biz{font-size:22px;font-weight:800;} .muted{color:#666;font-size:11px;}
 table{width:100%;border-collapse:collapse;margin-top:14px;}
 th{background:#1a1a1a;color:#84cc16;padding:7px;text-align:left;font-size:11px;}
 td{border-bottom:1px solid #e5e5e5;padding:7px;}
 tr:nth-child(even) td{background:#f7f7f5;}
 thead{display:table-header-group;} tfoot{display:table-footer-group;}
 .tot{font-weight:800;background:#f0f0f0 !important;}
 .kpi{display:flex;gap:10px;margin-top:14px;} .box{flex:1;border:1px solid #ddd;border-radius:8px;padding:10px;}
 .box b{font-size:16px;} .foot{margin-top:18px;color:#666;font-size:11px;border-top:1px solid #ddd;padding-top:8px;}
</style></head><body>
<div class='h'><div style='display:flex; align-items:center; gap:12px;'><img src='/assets/images/biz-logo.jpg' style='height:52px; width:52px; border-radius:50%; border:2px solid #84cc16; object-fit:cover;'><div><div class='biz'>{$bizName}</div><div class='muted'>{$loc} · {$phone}</div></div>
<div style='text-align:right'><div style='font-weight:800;font-size:16px;'>CREDIT STATEMENT</div><div class='muted'>Printed " . date('d M Y, H:i') . "</div></div></div>
<p><strong>{$c['name']}</strong> · {$c['phone']} · {$c['email']}<br/><span class='muted'>{$c['address']}, {$c['suburb']} {$c['postcode']} · Net {$days} days</span></p>
<div class='kpi'>
 <div class='box'>Open balance<br/><b>" . $fmt($owed) . "</b></div>
 <div class='box'>Limit<br/><b>" . ($limit > 0 ? $fmt($limit) : 'No limit') . "</b></div>
 <div class='box'>Available<br/><b>" . ($limit > 0 ? $fmt(max(0, $limit - $owed)) : '—') . "</b></div>
 <div class='box'>Overdue<br/><b style='color:#c62828'>" . $fmt($overdue) . "</b></div>
</div>
<table><thead><tr><th>#</th><th>Invoice / Sale Ref</th><th>Credited</th><th>Due</th><th style='text-align:right'>Total</th><th style='text-align:right'>On Credit</th><th style='text-align:right'>Paid</th><th style='text-align:right'>Owed</th><th>Status</th></tr></thead>
<tbody>{$rows}</tbody></table>
<p style='text-align:right;margin-top:10px;'>Totals: credited " . $fmt($credited) . " · paid " . $fmt($paid) . " · <strong>owed " . $fmt($owed) . "</strong></p>
<div class='foot'>Please pay by the due dates shown. Queries:{$phone}. Thank you for your business.</div>
</body></html>";

// dompdf (bundled) targets pre-PHP8 (__autoload) and 500s on PHP 8+.
// Serve a tidy print-ready page instead: browser Print -> Save as PDF is pixel-neat.
$printJs = "<script>window.onload=function(){setTimeout(function(){window.print();},400);};</script>";
$html = str_replace('</body>', $printJs . '</body>', $html);
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: ' . ((isset($_GET['dl']) && $_GET['dl'] === '1') ? 'attachment' : 'inline') . '; filename="statement.pdf"');
echo $html;

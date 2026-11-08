<?php
// Neat supplier payable statement PDF (dompdf). Auth required.
// GET /api/supplier_statement.php?supplierid=3  -> inline PDF; &dl=1 download; &type=html HTML preview
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/config.php");
require($_SERVER['DOCUMENT_ROOT'] . "/library/wpos/AutoLoader.php");
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['userId'])) { http_response_code(403); echo "unauthorized"; exit; }
$supid = isset($_GET['supplierid']) ? (int)$_GET['supplierid'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($supid <= 0) { http_response_code(400); echo "supplierid required"; exit; }
$pdo = (new DbConfig())->_db;
$st = $pdo->prepare("SELECT * FROM stored_suppliers WHERE id = :id");
$st->execute([':id' => $supid]);
$c = $st->fetch(PDO::FETCH_ASSOC);
if (!$c) { http_response_code(404); echo "supplier not found"; exit; }
$limit = (float)($c['credit_limit'] ?? 0);
$days = (int)($c['credit_due_days'] ?? 7);
$schema = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'];
$gen = json_decode(file_get_contents($schema . "docs/.config.json"));
$bizName = ($gen && isset($gen->businessname)) ? $gen->businessname : "Corner Bites";
$phone = ($gen && isset($gen->phone)) ? $gen->phone : "";
$loc = ($gen && isset($gen->address)) ? $gen->address : "Utawala-Kincar, Nairobi";
$credit = new WposSupplierCredit(json_decode(json_encode(['supplierid' => $supid])));
$res = $credit->statement(['errorCode' => 'OK', 'error' => 'OK', 'data' => '']);
$entries = ($res['data'] && isset($res['data']['entries'])) ? $res['data']['entries'] : [];
$owed = (float)($res['data']['open_balance'] ?? 0);
$pur = (float)($res['data']['total_purchased'] ?? 0);
$paid = (float)($res['data']['total_paid'] ?? 0);
$overdue = (float)($res['data']['overdue'] ?? 0);
$fmt = function ($v) { return "KSh " . number_format((float)$v, 2); };
$dt = function ($ts) { return $ts ? date('d M Y', (int)($ts / 1000)) : '—'; };
$rows = ''; $n = 1;
foreach ($entries as $e) {
    $status = $e['owed'] <= 0.005 ? '<span style="color:#2e7d32;font-weight:700;">Paid</span>' : ($e['overdue'] ? '<span style="color:#c62828;font-weight:700;">OVERDUE</span>' : '<span style="color:#e65100;font-weight:700;">Open</span>');
    $itemsText = '';
    if (!empty($e['items']) && is_array($e['items'])) {
        $parts = [];
        foreach ($e['items'] as $it) {
            $nm = is_array($it) ? ($it['name'] ?? '') : (is_object($it) ? ($it->name ?? '') : '');
            $qt = is_array($it) ? ($it['qty'] ?? 0) : (is_object($it) ? ($it->qty ?? 0) : 0);
            $cs = is_array($it) ? ($it['cost'] ?? 0) : (is_object($it) ? ($it->cost ?? 0) : 0);
            if ($nm !== '') $parts[] = $nm . ' ×' . $qt . ' (' . number_format((float)$cs, 2) . ')';
        }
        $itemsText = implode('<br/>', array_map(function ($s) { return htmlspecialchars($s); }, $parts));
    }
    if ($itemsText === '') $itemsText = '—';
    $rows .= "<tr><td>{$n}</td><td>{$e['ref']}</td><td>" . $dt($e['processdt']) . "</td><td>" . $dt($e['duedt']) .
        "</td><td style='font-size:11px;'>" . $itemsText . "</td><td style='text-align:right'>" . $fmt($e['total']) . "</td><td style='text-align:right'>" . $fmt($e['paid']) . "</td><td style='text-align:right'><strong>" . $fmt($e['owed']) . "</strong></td><td>{$status}</td></tr>";
    $n++;
}
if (!$rows) $rows = "<tr><td colspan='9' style='text-align:center;color:#777;'>No open payables — nothing owed to this supplier.</td></tr>";
$payments = $res['data']['payments'] ?? [];
$payRows = ''; $pn = 1;
foreach ($payments as $py) {
    $payRows .= "<tr><td>{$pn}</td><td>" . $dt($py['processdt']) . "</td><td>" . htmlspecialchars((string)($py['account'] ?? '—')) . "</td><td style='text-align:right'>" . $fmt($py['amount']) . "</td><td>" . htmlspecialchars((string)($py['description'] ?? '')) . "</td></tr>";
    $pn++;
}
if (!$payRows) $payRows = "<tr><td colspan='5' style='text-align:center;color:#777;'>No payments from accounts recorded yet.</td></tr>";
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
<div style='text-align:right'><div style='font-weight:800;font-size:16px;'>SUPPLIER STATEMENT</div><div class='muted'>Printed " . date('d M Y, H:i') . "</div></div></div>
<p><strong>{$c['name']}</strong> · {$c['phone']} · {$c['email']}<br/><span class='muted'>{$c['address']} · Terms net {$days} days</span></p>
<div class='kpi'>
 <div class='box'>Payable (owed)<br/><b>" . $fmt($owed) . "</b></div>
 <div class='box'>Limit<br/><b>" . ($limit > 0 ? $fmt($limit) : 'No limit') . "</b></div>
 <div class='box'>Available<br/><b>" . ($limit > 0 ? $fmt(max(0, $limit - $owed)) : '—') . "</b></div>
 <div class='box'>Overdue<br/><b style='color:#c62828'>" . $fmt($overdue) . "</b></div>
</div>
<table><thead><tr><th>#</th><th>Purchase Ref</th><th>Bought</th><th>Due</th><th>Items</th><th style='text-align:right'>Total</th><th style='text-align:right'>Paid</th><th style='text-align:right'>Owed</th><th>Status</th></tr></thead>
<tbody>{$rows}</tbody></table>
<p style='text-align:right;margin-top:10px;'>Purchased " . $fmt($pur) . " · paid " . $fmt($paid) . " · <strong>owed " . $fmt($owed) . "</strong></p>
<h2>Payment history (account → supplier)</h2>
<table><thead><tr><th>#</th><th>Date</th><th>From account</th><th style='text-align:right'>Paid</th><th>Notes</th></tr></thead>
<tbody>{$payRows}</tbody></table>
<div class='foot'>Payables due on the dates shown. Thank you.</div>
</body></html>";
// dompdf (bundled) targets pre-PHP8 (__autoload) and 500s on PHP 8+.
// Serve a tidy print-ready page instead: browser Print -> Save as PDF is pixel-neat.
$printJs = "<script>window.onload=function(){setTimeout(function(){window.print();},400);};</script>";
$html = str_replace('</body>', $printJs . '</body>', $html);
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: ' . ((isset($_GET['dl']) && $_GET['dl'] === '1') ? 'attachment' : 'inline') . '; filename="statement.pdf"');
echo $html;

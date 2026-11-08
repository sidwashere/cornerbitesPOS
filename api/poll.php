<?php
/**
 * poll.php - lightweight realtime replacement for the node.js websocket feed.
 *
 * The shared host cannot run node.js, so the kitchen/admin apps poll this
 * endpoint instead of listening on the socket. Returns active orders (status 0)
 * plus any order changed since ?since= (unix seconds), in the same sale-object
 * shape the websocket 'sale' event delivers, so the frontend can feed them
 * straight into its existing processIncomingSale / processOrder handlers.
 *
 * Requires a logged-in session (same as the rest of the API).
 */
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

session_start();
if (!isset($_SESSION['userId'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'unauthorized']);
    exit;
}
// Sliding idle touch + release the session lock so sibling tabs poll concurrently
try {
    $_SESSION['last_activity'] = time();
    if (session_status() === PHP_SESSION_ACTIVE) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        @setcookie(session_name(), session_id(), time() + 43200, '/', '', $secure, true);
        session_write_close();
    }
} catch (Exception $e) {}

$since = isset($_GET['since']) ? (int)$_GET['since'] : 0;
$source = isset($_GET['source']) ? $_GET['source'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$db = new DbConfig();
$pdo = $db->_db;

$conditions = [];
$params = [];

if ($since > 0) {
    $conditions[] = "(status = 0 OR (status != 0 AND dt > FROM_UNIXTIME(:since)))";
    $params[':since'] = $since;
} else {
    $conditions[] = "status = 0";
}

$sql = "SELECT * FROM sales WHERE " . implode(' AND ', $conditions);

if ($source) {
    $sql .= " AND source = :source";
    $params[':source'] = $source;
}

$sql .= " ORDER BY dt DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sales = [];
foreach ($rows as $r) {
    $json = json_decode($r['data']);
    if ($json) {
        // Surface delivery/table/source from DB columns (JSON may pre-date these fields)
        if (!empty($r['delivery_address']) && empty($json->delivery_address)) $json->delivery_address = $r['delivery_address'];
        if (!empty($r['source']) && empty($json->source)) $json->source = $r['source'];
        if (!empty($r['external_ref']) && empty($json->external_ref)) $json->external_ref = $r['external_ref'];
        if (!empty($r['order_type']) && empty($json->order_type)) $json->order_type = $r['order_type'];
        if (!empty($r['received_at']) && empty($json->received_at)) $json->received_at = $r['received_at'];
        if (isset($r['status']) && !isset($json->status)) $json->status = (int)$r['status'];
        $sales[] = $json;
    }
}

header('Content-Type: application/json');
echo json_encode(['result' => ['sales' => $sales]]);

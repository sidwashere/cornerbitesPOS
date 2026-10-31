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
        $sales[] = $json;
    }
}

header('Content-Type: application/json');
echo json_encode(['result' => ['sales' => $sales]]);

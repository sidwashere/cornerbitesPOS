<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

header('Content-Type: application/json');

try {
    $authMdl = new AuthModel();
    $perms = json_encode(["sections" => ["access" => "yes", "dashboard" => "both", "reports" => 1, "graph" => 1, "realtime" => 1, "sales" => 1, "items" => 1, "stock" => 1, "categories" => 1, "suppliers" => 1, "customers" => 1], "apicalls" => []]);
    
    // Try direct SQL insert
    $db = new DbConfig();
    $pdo = $db->_db;
    $uuid = uniqid();
    $stmt = $pdo->prepare("INSERT INTO auth (username, password, uuid, admin, permissions) VALUES (:username, :password, :uuid, :isadmin, :perm)");
    $result = $stmt->execute([
        ':username' => 'staff',
        ':password' => hash('sha256', 'staff123'),
        ':uuid' => $uuid,
        ':isadmin' => 0,
        ':perm' => $perms,
    ]);
    
    echo json_encode(['ok' => $result, 'error' => $stmt->errorInfo(), 'lastInsertId' => $pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

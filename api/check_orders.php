<?php
$_SERVER['APP_ROOT'] = "/";
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/config.php');
require($_SERVER['DOCUMENT_ROOT'] . $_SERVER['APP_ROOT'] . 'library/wpos/AutoLoader.php');

header('Content-Type: application/json');

try {
    $db = new DbConfig();
    $pdo = $db->_db;
    
    // Check table structure
    $stmt = $pdo->query("DESCRIBE sales");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($columns, 'Field');
    
    // Get recent sales
    $stmt = $pdo->query("SELECT * FROM sales ORDER BY id DESC LIMIT 5");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['ok' => true, 'columns' => $colNames, 'recent_sales' => $sales]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
